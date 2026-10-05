<?php

namespace Modules\Frontdeskcrm\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Modules\Frontdeskcrm\Exports\GuestImportGuide;
use Modules\Frontdeskcrm\Exports\GuestImportHeaderReader;
use Modules\Frontdeskcrm\Exports\GuestsExcelImport;
use Modules\Frontdeskcrm\Exports\GuestsImport;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Modules\Frontdeskcrm\Support\GuestImportFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use UnitEnum;

/**
 * Bulk guest import, replacing the Blade form at frontdesk.guests.import.
 *
 * The part that matters here is that the report is honest. The old screen
 * redirected back to the directory with "3 guests imported. 2 skipped
 * (duplicates)." and nothing else, so a phone number that belonged to somebody
 * else and a birthday the database refused both arrived as an anonymous
 * "skipped (duplicate)" with no way to tell which row was at fault or why.
 * Every row that is not written is listed on this page with its row number and
 * the reason, and one bad row no longer takes the rest of the file with it.
 */
class ImportGuests extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $title = 'Import guests';

    protected static ?string $slug = 'frontdesk/guests/import';

    protected static ?int $navigationSort = 4;

    /**
     * Reached from the guest directory's header action, so it does not also
     * sit permanently in the sidebar.
     */
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'frontdeskcrm::filament.pages.import-guests';

    /**
     * Result of the last run, or null before one has happened.
     *
     * @var array{imported: int, skipped: int, rejections: array<int, array{row: int, reason: string, detail: ?string}>, file: string}|null
     */
    public ?array $report = null;

    /**
     * Public because Filament resolves the upload's state path against the
     * Livewire component's public properties.
     */
    public ?array $data = [];

    /**
     * What the uploaded file's header row turned out to contain.
     *
     * Read as soon as a file is chosen rather than at import time, because a
     * misspelled or missing column is otherwise invisible: the row imports and
     * the data is simply absent, with nothing to suggest why.
     *
     * @var array{found: int, recognised: int, missing_required: array<int, string>, unrecognised: array<int, string>}|null
     */
    public ?array $headerCheck = null;

    /**
     * Which uploaded file the check above describes, so a stale verdict is
     * never shown against a newly chosen file.
     */
    protected ?string $headerCheckFor = null;

    /**
     * The column reference shown on screen, split so required and optional can
     * be presented separately rather than as one long undifferentiated list.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function fieldReference(): array
    {
        $required = [];
        $optional = [];

        foreach (GuestImportFields::all() as $name => $field) {
            $entry = [
                'name' => $name,
                'example' => $field['example'],
                'notes' => $field['notes'],
                'aliases' => $field['aliases'],
            ];

            if ($field['required']) {
                $required[] = $entry;

                continue;
            }

            $optional[] = $entry;
        }

        return ['required' => $required, 'optional' => $optional];
    }

    public static function canAccess(): bool
    {
        // GUESTS_IMPORT, not GUESTS_CREATE: the two are deliberately separate
        // because a several-thousand-row import is a much bigger write than
        // adding one profile by hand, and the role grants treat them so.
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::GUESTS_IMPORT,
            FrontdeskPermission::GUESTS_MANAGE,
        );
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Upload your file')
                    ->description('Excel or CSV, up to 5 MB. The first row must be the column headings; the accepted columns are listed below.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Guest file')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'text/plain',
                                'text/comma-separated-values',
                            ])
                            ->maxSize(5120)
                            ->required()
                            ->live()
                            // Kept off the public disk: a guest file holds
                            // identifiable data and has no business being
                            // readable by anything that has a URL.
                            ->disk('local')
                            ->directory('guest-imports')
                            ->visibility('private')
                            ->helperText('Read once, then deleted from the server straight after the run.')
                            // Checked on selection, not on import, so a wrong
                            // heading is caught before anything is written.
                            ->afterStateUpdated(function (): void {
                                $this->inspectHeader();
                            }),
                    ]),

                Placeholder::make('header_check')
                    ->hiddenLabel()
                    ->content(fn (): HtmlString => $this->headerCheckContent()),

                Section::make('What the import will do')
                    ->schema([
                        Placeholder::make('rules')
                            ->hiddenLabel()
                            ->content(new HtmlString(
                                '<ul class="list-disc space-y-1 ps-5 text-sm text-gray-600 dark:text-gray-400">'
                                .'<li>A guest already on file, matched on phone number or email, is skipped and listed with the reason.</li>'
                                .'<li>A row missing a required column is skipped and listed, so the rest of the file still imports.</li>'
                                .'<li>One bad row never stops the rest of the file.</li>'
                                .'<li>Gender accepts male or female; anything else is stored as Other.</li>'
                                .'</ul>'
                            )),
                    ]),
            ])
            // The upload lives under `data`, matching the public property above.
            // Without this the file field would resolve to a `$file` Livewire
            // property that does not exist.
            ->statePath('data');
    }

    /**
     * Read the uploaded file's header row and record what it does and does not
     * satisfy.
     *
     * Only the first row is read, so choosing a large file stays instant.
     */
    public function inspectHeader(): void
    {
        $this->headerCheck = null;
        $this->headerCheckFor = null;

        $state = $this->data['file'] ?? null;

        if (blank($state) || ! is_string($state)) {
            return;
        }

        $absolutePath = $this->resolveUploadPath($state);

        if ($absolutePath === null) {
            return;
        }

        try {
            // Only the heading row is read, so choosing a large file stays
            // instant instead of parsing the whole thing to answer a question
            // about column names.
            $headerRow = Excel::toArray(new GuestImportHeaderReader, $absolutePath)[0] ?? [];
        } catch (Throwable) {
            // An unreadable file is reported by the import itself, which has
            // better context for what went wrong.
            return;
        }

        $headers = collect($headerRow)
            ->flatten()
            ->map(fn ($header): string => trim((string) $header))
            ->all();

        $this->headerCheck = [
            'found' => count(array_filter($headers, fn (string $h): bool => trim($h) !== '')),
            'recognised' => GuestImportFields::recognisedCount($headers),
            'missing_required' => GuestImportFields::missingRequired($headers),
            'unrecognised' => GuestImportFields::unrecognised($headers),
        ];
        $this->headerCheckFor = $state;
    }

    /**
     * The readable path for the upload, wherever it currently lives.
     *
     * Between choosing a file and pressing the button the file is still in
     * Livewire's temporary store under a UUID, not on the configured disk. The
     * check has to find it there too, otherwise it silently reports nothing for
     * exactly the file the user has just chosen.
     */
    protected function resolveUploadPath(string $state): ?string
    {
        $diskPath = Storage::disk('local')->path($state);

        if (is_file($diskPath)) {
            return $diskPath;
        }

        $temporary = TemporaryUploadedFile::find($state);

        if ($temporary?->isValid()) {
            return $temporary->getRealPath();
        }

        return null;
    }

    /**
     * The check for the file currently chosen, reading it on demand.
     *
     * Deliberately not dependent on the upload callback firing: the verdict has
     * to be right whenever the page renders, including on a re-render that
     * arrives without the callback.
     *
     * @return array{found: int, recognised: int, missing_required: array<int, string>, unrecognised: array<int, string>}|null
     */
    protected function currentHeaderCheck(): ?array
    {
        $relativePath = $this->data['file'] ?? null;

        if (blank($relativePath) || ! is_string($relativePath)) {
            return null;
        }

        if ($this->headerCheck === null || $this->headerCheckFor !== $relativePath) {
            $this->inspectHeader();
        }

        return $this->headerCheck;
    }

    /**
     * The upload verdict, as markup.
     */
    protected function headerCheckContent(): HtmlString
    {
        $check = $this->currentHeaderCheck();

        if ($check === null) {
            return new HtmlString('');
        }

        $missing = $check['missing_required'];
        $unknown = $check['unrecognised'];

        $parts = [];

        if ($missing === [] && $unknown === []) {
            $parts[] = '<p class="text-sm font-medium text-success-600 dark:text-success-400">'
                .'File looks good: every required column is present and every column will be used.</p>';
        }

        if ($missing !== []) {
            $parts[] = '<p class="text-sm font-medium text-danger-600 dark:text-danger-400">'
                .'Missing required column'.(count($missing) === 1 ? '' : 's').': '
                .implode(', ', array_map(fn (string $h): string => "<code>{$h}</code>", $missing))
                .'</p>';
            $parts[] = '<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">'
                .'Rows without these will be skipped. Rename your headings, or use the header '
                .implode(', ', array_map(fn (string $h): string => "<code>{$h}</code>", GuestImportFields::requiredHeadings()))
                .'.</p>';
        }

        if ($unknown !== []) {
            $parts[] = '<p class="mt-1 text-sm text-warning-600 dark:text-warning-400">'
                .'Not recognised, so this data will be ignored: '
                .implode(', ', array_map(fn (string $h): string => "<code>{$h}</code>", $unknown))
                .'</p>';
            $parts[] = '<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">'
                .'Check for a spelling mistake, or download the guide for the accepted column list.</p>';
        }

        $parts[] = '<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">'
            ."{$check['found']} column".($check['found'] === 1 ? '' : 's').' found in the first row.</p>';

        return new HtmlString(
            '<div class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/5 dark:bg-gray-800/50 dark:ring-white/10">'
            .implode('', $parts)
            .'</div>'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadGuide')
                ->label('Download guide')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action('downloadGuide'),

            Action::make('import')
                ->label('Import')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->action('runImport'),
        ];
    }

    /**
     * @return BinaryFileResponse
     */
    public function downloadGuide()
    {
        // Excel::download() streams the export and sets the filename header.
        // Excel::raw() would need the writer type passed before the callback
        // and only returns the bytes, so the header would have to be built here.
        return Excel::download(new GuestImportGuide, 'guest-import-guide.xlsx');
    }

    public function runImport(): void
    {
        $state = $this->form->getState();

        $relativePath = $state['file'] ?? null;

        if (blank($relativePath)) {
            Notification::make()
                ->danger()
                ->title('Choose a file first')
                ->body('Pick an Excel or CSV file to import.')
                ->send();

            return;
        }

        $fullPath = Storage::disk('local')->path($relativePath);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Excel needs the multi-sheet import so the guide's own sheets are not
        // read as guest rows.
        $import = in_array($extension, ['xlsx', 'xls'], true)
            ? new GuestsExcelImport
            : new GuestsImport;

        try {
            // Wrapped so a run that fails at the file level leaves nothing
            // behind. Rows rejected individually are the import's own report,
            // not an error, so they do not roll the run back.
            DB::transaction(function () use ($import, $fullPath): void {
                Excel::import($import, $fullPath);
            });
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->danger()
                ->title('Nothing was imported')
                ->body(($e instanceof ValidationException
                    ? 'Some rows failed validation: '.$e->getMessage().' Nothing from this file was saved - fix the rows and upload it again.'
                    : 'This file could not be read as Excel or CSV, so nothing from it was saved. Check it opens in Excel, and that the first row holds the column headings.'))
                ->persistent()
                ->send();

            $this->cleanUp($relativePath);

            return;
        }

        $this->cleanUp($relativePath);

        $imported = $import->getImportedCount();
        $skipped = $import->getSkippedCount();

        $this->report = [
            'imported' => $imported,
            'skipped' => $skipped,
            'rejections' => $import->getRejections(),
            'file' => basename((string) $relativePath),
        ];

        // Clear the upload so the same file cannot be run twice by accident and
        // the next import starts from an empty field.
        $this->data['file'] = null;
        $this->headerCheck = null;
        $this->headerCheckFor = null;

        // A file whose every column is unrecognised imported nothing because no row
        // could ever be read. "0 imported" would read as success, so say what
        // happened. Judged on the heading row rather than on the import, because
        // an empty file legitimately has no rows to judge by.
        $check = $this->headerCheckFor === null ? null : $this->headerCheck;

        if ($check !== null && $check['found'] > 0 && $check['recognised'] === 0) {
            Notification::make()
                ->danger()
                ->title('Nothing was imported')
                ->body('None of the columns in the first row were recognised, so no rows were read. '
                    .'Check you are uploading the "Guest Template" sheet, with headings like '
                    .implode(', ', GuestImportFields::requiredHeadings()).'.')
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            // Warning rather than success when anything was refused, so a
            // partial import cannot be mistaken for a clean one.
            ->status($skipped > 0 ? 'warning' : 'success')
            ->title($skipped > 0
                ? "{$imported} imported, {$skipped} not imported"
                : "{$imported} guests imported")
            ->body($skipped > 0
                ? 'Every skipped row is listed on the page with the reason.'
                : 'Every row in the file was imported.')
            ->persistent($skipped > 0)
            ->send();
    }

    /**
     * Remove the uploaded file from the server.
     *
     * Guest files hold names, phone numbers and identification numbers, so
     * there is no reason to leave one lying around after the run.
     *
     * Takes the disk-relative path, not the resolved one: the delete goes back
     * through the disk, so handing it an absolute path silently deletes nothing.
     */
    protected function cleanUp(string $relativePath): void
    {
        if (blank($relativePath)) {
            return;
        }

        Storage::disk('local')->delete($relativePath);
    }

    public function guestDirectoryUrl(): string
    {
        return GuestResource::getUrl('index');
    }

    public function getSubheading(): ?string
    {
        return 'Upload a guest list from Excel or CSV, or download the guide for the template and the column definitions.';
    }
}

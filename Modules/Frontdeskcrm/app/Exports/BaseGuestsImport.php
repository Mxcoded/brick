<?php

namespace Modules\Frontdeskcrm\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Support\GuestGender;
use Modules\Frontdeskcrm\Support\GuestIdentificationType;
use Modules\Frontdeskcrm\Support\GuestImportFields;
use Modules\Frontdeskcrm\Support\GuestNationality;
use Modules\Frontdeskcrm\Support\GuestPhoneNumber;
use Modules\Frontdeskcrm\Support\GuestTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

/**
 * Shared guest-import row handling.
 *
 * The original version counted an import as "N imported, M skipped" and threw
 * the reason away. That made a partial import indistinguishable from a clean
 * one: a phone number that belonged to somebody else, a birthday the database
 * refused, a blank name - all of them collapsed into the same "M skipped" with
 * nothing on screen to act on. Every row that is not written is now recorded
 * with its row number and the reason, so the report can be worked from.
 */
abstract class BaseGuestsImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    protected int $imported = 0;

    protected int $skipped = 0;

    /**
     * Whether any row so far had a heading the importer recognises.
     *
     * A user who uploads the wrong sheet, or a file whose headings are all
     * typos, used to be told "0 imported, 0 skipped" - which reads as success
     * and is indistinguishable from deliberately importing an empty file.
     */
    protected bool $sawRecognisedHeading = false;

    /** @var array<int, array{row: int, reason: string, detail: ?string}> */
    protected array $rejections = [];

    public function model(array $row): Model|array|null
    {
        // Row 1 is the heading row, so the first data row is 2 in the file the
        // user is looking at - which is the number they need to go and fix.
        $this->advanceRow();

        // Headers folded onto canonical names first, so a file using `phone`
        // and one using `contact_number` take exactly the same path.
        $resolved = $this->resolveRow($row);

        if ($resolved !== []) {
            $this->sawRecognisedHeading = true;
        }

        // A row that resolves to nothing is a blank line, not a guest. Excel
        // hands over rows holding only formatting as arrays of empty strings,
        // and reporting those as "Missing guest name" buried the real problems
        // in a screenful of noise about nothing.
        if ($resolved === []) {
            return null;
        }

        $name = trim((string) ($resolved['full_name'] ?? ''));
        $email = $this->blankToNull($resolved['email'] ?? null);
        $phone = $this->formatPhone($resolved['contact_number'] ?? null);

        if ($name === '') {
            $this->reject('Missing guest name', 'full_name was empty.');

            return null;
        }

        // The rules() checks, now per row. Same rules as before, but a failure
        // costs this one row and is reported by name instead of taking the
        // file down with a generic "could not read this".
        if ($problem = $this->checkRow($name, $email, $resolved)) {
            $this->reject($problem[0], $problem[1]);

            return null;
        }

        // contact_number is NOT NULL, so a row without one cannot be stored.
        // Caught here rather than by WithValidation, because a validation
        // failure aborts the whole file while this only costs one row.
        if ($phone === null) {
            $this->reject(
                'Missing phone number',
                'contact_number is required, so the row cannot be stored. Add a contact_number or phone column.'
            );

            return null;
        }

        if ($existing = $this->findDuplicate($email, $phone)) {
            $this->reject(
                'Already on file',
                sprintf(
                    '%s is the same guest as "%s" (matched on %s).',
                    $email ?: $phone,
                    $existing->full_name,
                    implode(' or ', $this->matchedOn($existing, $email, $phone))
                )
            );

            return null;
        }

        try {
            $guest = new Guest($this->mapRow($resolved, $name, $email, $phone));
            $guest->save();

            $this->recordImported();

            return $guest;
        } catch (Throwable $e) {
            $this->reject('Could not save', $e->getMessage());

            return null;
        }
    }

    /**
     * Match on the columns the schema actually constrains.
     *
     * `guests.email` and `guests.contact_number` are both unique, so they are
     * the honest identity of a guest. The previous check also matched on
     * `full_name`, which is neither unique nor reliable: it quietly skipped
     * every second guest named "Chinedu Balogun" in a file and reported them
     * all as duplicates of each other.
     *
     * The phone is matched against every spelling it could have been stored as,
     * not just the exact string that was typed. Comparing the raw string meant
     * that `0801 111 2222` was not recognised as the guest already stored as
     * `08011112222`: the lookup missed, and the row then died on the UNIQUE
     * index and was reported as "Could not save", which tells the user nothing
     * about the fact that they are about to create the same guest twice.
     */
    protected function findDuplicate(?string $email, ?string $phone): ?Guest
    {
        $spellings = GuestPhoneNumber::spellings($phone);

        if ($email === null && $spellings === []) {
            return null;
        }

        return Guest::query()
            ->where(function ($query) use ($email, $spellings): void {
                if ($email !== null) {
                    $query->where('email', $email);
                }

                if ($spellings === []) {
                    return;
                }

                $method = $email !== null ? 'orWhere' : 'where';

                $query->{$method}(fn ($nested) => $nested->whereIn('contact_number', $spellings));
            })
            ->first();
    }

    /**
     * @return array<int, string>
     */
    protected function matchedOn(Guest $existing, ?string $email, ?string $phone): array
    {
        $matched = [];

        if ($email !== null && $existing->email === $email) {
            $matched[] = 'email';
        }

        if (GuestPhoneNumber::matches($existing->contact_number, $phone)) {
            $matched[] = 'contact_number';
        }

        return $matched ?: ['contact_number'];
    }

    /**
     * Fold an uploaded row's headers onto the canonical column names.
     *
     * Header matching runs through GuestImportFields, so the set of accepted
     * spellings - and the set documented in the guide and on the import screen -
     * are the same list. A header the guide never mentioned still resolves if
     * it is a listed alias, and a typo resolves to nothing rather than silently
     * arriving as an empty column.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function resolveRow(array $row): array
    {
        $resolved = [];

        foreach ($row as $header => $value) {
            $canonical = GuestImportFields::resolveHeader((string) $header);

            if ($canonical !== null) {
                $resolved[$canonical] = $value;
            }
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $resolved  Already run through resolveRow().
     * @return array<string, mixed>
     */
    protected function mapRow(array $resolved, string $name, ?string $email, ?string $phone): array
    {
        return [
            'title' => GuestTitle::normalise($resolved['title'] ?? null),
            'full_name' => $name,
            'email' => $email,
            'contact_number' => $phone,
            'nationality' => GuestNationality::normalise($resolved['nationality'] ?? null),
            // Falls back to the raw cell: a guest may hold a document this code
            // has no name for, and the import must not silently discard it.
            'identification_type' => GuestIdentificationType::normalise($resolved['identification_type'] ?? null)
                ?? $this->blankToNull($resolved['identification_type'] ?? null),
            'identification_number' => $this->blankToNull($resolved['identification_number'] ?? null),
            'gender' => $this->normaliseGender($resolved['gender'] ?? null),
            'birthday' => $this->blankToNull($resolved['birthday'] ?? null),
            'occupation' => $this->blankToNull($resolved['occupation'] ?? null),
            'company_name' => $this->blankToNull($resolved['company_name'] ?? null),
            'home_address' => $this->blankToNull($resolved['home_address'] ?? null),
            'city' => $this->blankToNull($resolved['city'] ?? null),
            'state' => $this->blankToNull($resolved['state'] ?? null),
            'zip_code' => $this->blankToNull($resolved['zip_code'] ?? null),
            'emergency_name' => $this->blankToNull($resolved['emergency_name'] ?? null),
            'emergency_relationship' => $this->blankToNull($resolved['emergency_relationship'] ?? null),
            'emergency_contact' => $this->formatPhone($resolved['emergency_contact'] ?? null),
        ];
    }

    protected function blankToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * guests.gender is a free-text varchar, so a spreadsheet can put anything in
     * it. Reduced to the canonical vocabulary so the directory's gender filter and
     * the registration form's honorific both have something to compare against.
     */
    protected function normaliseGender(mixed $value): ?string
    {
        return GuestGender::normalise($value);
    }

    /**
     * Strip separators so a phone can be compared.
     *
     * Only formatting is removed. Which of the local and international spellings
     * a guest is stored under is left alone: GuestPhoneNumber works on both.
     */
    protected function formatPhone(mixed $value): ?string
    {
        return GuestPhoneNumber::clean(is_scalar($value) ? (string) $value : null);
    }

    /**
     * Counters are written through these hooks rather than touched inline,
     * because a multi-sheet import processes the rows on a separate sheet
     * instance. That instance forwards each call to the object the caller is
     * holding, which is what keeps the reported totals and row numbers
     * pointing at one place.
     */
    protected function recordImported(): void
    {
        $this->imported++;
    }

    protected function rowNumber(): int
    {
        return $this->currentRow;
    }

    protected function advanceRow(): void
    {
        $this->currentRow++;
    }

    /**
     * Row number of the data row currently being handled, as it appears in the
     * user's file.
     *
     * Starts at 1 because row 1 is the heading row, so the first call to
     * model() lands on row 2. It used to start at 0, which reported every
     * rejection one row above where it actually was - and the number is the
     * only thing telling the user which line of their spreadsheet to go and fix.
     */
    private int $currentRow = 1;

    protected function reject(string $reason, ?string $detail = null): void
    {
        $this->skipped++;
        $this->rejections[] = [
            'row' => $this->rowNumber(),
            'reason' => $reason,
            'detail' => $detail,
        ];
    }

    /**
     * The old rules(), applied one row at a time.
     *
     * Returns null when the row is fine, or a [reason, detail] pair to reject it
     * with. Anything the database would refuse is caught here for the same reason:
     * a constraint violation that escapes as an exception aborts the file, so the
     * row is dropped and reported rather than allowed to fail loudly.
     *
     * @param  array<string, mixed>  $resolved
     * @return array{0: string, 1: string}|null
     */
    protected function checkRow(string $name, ?string $email, array $resolved): ?array
    {
        if (mb_strlen($name) > 191) {
            return [
                'Guest name is too long',
                sprintf('full_name is %d characters; the column holds 191.', mb_strlen($name)),
            ];
        }

        if ($email !== null) {
            if (mb_strlen($email) > 191) {
                return [
                    'Email is too long',
                    sprintf('email is %d characters; the column holds 191.', mb_strlen($email)),
                ];
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                // An email that looks wrong is worth refusing rather than
                // storing, because it is unique: a typo silently creates a
                // second guest who can never receive anything sent to the first.
                return [
                    'Email is not a valid address',
                    sprintf('email "%s" is not a valid address. Leave the cell empty if there is none.', $email),
                ];
            }
        }

        $birthday = $this->blankToNull($resolved['birthday'] ?? null);

        if ($birthday !== null && ! $this->isDate($birthday)) {
            return [
                'Birthday is not a date',
                sprintf('birthday "%s" could not be read as a date. Use YYYY-MM-DD.', $birthday),
            ];
        }

        // No length check on gender: GuestGender::normalise reduces the cell to
        // one of three values before it is stored, so it cannot overflow the
        // column. An earlier check rejected anything over 20 characters and
        // claimed that was the column's width, which it was not.
        return null;
    }

    /**
     * Whether a cell holds a date the database will accept.
     *
     * Deliberately permissive about the format but strict about the result: the
     * guide says YYYY-MM-DD, while Excel hands over a serial number or a
     * locale-formatted string depending on the cell type, and both are fine as
     * long as they land on a real date.
     */
    protected function isDate(string $value): bool
    {
        // A bare number is an Excel serial date, which Carbon can read.
        if (is_numeric($value)) {
            try {
                Date::PHPToDateTime((float) $value);

                return true;
            } catch (Throwable) {
                return false;
            }
        }

        try {
            Carbon::parse($value);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Deliberately empty.
     *
     * These rules used to live here, and that was the single worst thing about
     * the importer: a WithValidation failure throws, which aborts the entire
     * file. One guest with a mistyped email took down 4,999 good rows and the
     * screen reported "Could not read that file" - telling the user their file
     * was corrupt when it was not, and naming a field they had never heard of.
     *
     * The same checks now run per row in model(), where a failure costs one row
     * and produces a report entry with the row number and the field. That is the
     * whole point of the rejection report.
     */
    public function rules(): array
    {
        return [];
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    /**
     * Rows that were not written, each with the reason.
     *
     * @return array<int, array{row: int, reason: string, detail: ?string}>
     */
    public function getRejections(): array
    {
        return $this->rejections;
    }

    /**
     * Whether any row had a heading the importer recognises.
     *
     * Only meaningful for a file that had rows. An empty file has nothing to
     * judge by, so the import screen decides "wrong sheet" from the heading row
     * itself rather than from this.
     */
    public function sawRecognisedHeading(): bool
    {
        return $this->sawRecognisedHeading;
    }
}

{{-- The guest import page body.

     Filament's default page view renders `{{ $this->content }}` and nothing
     else, so a Page carrying a form needs a view of its own or the upload
     exists, validates, and is invisible. --}}
<x-filament-panels::page>
    <form wire:submit="runImport" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            {{-- Doubles the header action, so importing works from the body of
                 the form as well as from the top of the page. --}}
            <x-filament::button type="submit" icon="heroicon-m-arrow-up-tray">
                Import guests
            </x-filament::button>

            <x-filament::button
                type="button"
                color="gray"
                icon="heroicon-m-arrow-down-tray"
                wire:click="downloadGuide"
            >
                Download guide
            </x-filament::button>
        </div>
    </form>

    {{-- The column reference. Before this the only place that said which
         columns were required was the instructions sheet inside the downloaded
         workbook, and that sheet marked the phone number optional when the
         column is NOT NULL - so a user following the guide lost those rows and
         had no way to know why. --}}
    <div class="fi-section mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">
            Which columns to include
        </h2>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            The first row of your file must be the headings, spelled as below.
            Spaces and capitals do not matter. Leave out any column you do not have
            &mdash; every column is optional except the two marked required.
        </p>

        @php($reference = $this->fieldReference())

        @foreach ($reference as $group => $fields)
            <h3 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">
                @if ($group === 'required')
                    Required &mdash; a row without these is skipped
                @else
                    Optional
                @endif
            </h3>

            <div class="mt-2 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="py-2 pe-4 font-medium">Heading</th>
                            <th scope="col" class="py-2 pe-4 font-medium">Example</th>
                            <th scope="col" class="py-2 font-medium">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($fields as $field)
                            <tr>
                                <td class="py-2 pe-4 align-top">
                                    <code class="rounded bg-gray-100 px-1 py-0.5 text-xs font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100">
                                        {{ $field['name'] }}
                                    </code>
                                    @if ($field['aliases'])
                                        <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                                            also accepted:
                                            {{ implode(', ', $field['aliases']) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 pe-4 align-top text-gray-500 dark:text-gray-400">
                                    {{ $field['example'] }}
                                </td>
                                <td class="py-2 align-top text-gray-500 dark:text-gray-400">
                                    {{ $field['notes'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            A guest already on file, matched on
            <code>contact_number</code> or <code>email</code>, is skipped and listed
            with the reason. So is a row missing a required column. One bad row never
            stops the rest of the file.
        </p>
    </div>

    @if ($this->report)
        @php($report = $this->report)

        <div class="fi-section mt-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                Import result
            </h2>

            <dl class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">File</dt>
                    <dd class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ $report['file'] }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Imported</dt>
                    <dd class="text-sm font-medium text-success-600 dark:text-success-400">
                        {{ number_format($report['imported']) }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Not imported</dt>
                    {{-- Not called "skipped (duplicates)": a row can also be
                         missing a name or a phone number, and calling all of
                         them duplicates is how rows went missing unnoticed. --}}
                    <dd @class([
                        'text-sm font-medium',
                        'text-warning-600 dark:text-warning-400' => $report['skipped'] > 0,
                        'text-gray-950 dark:text-white' => $report['skipped'] === 0,
                    ])>
                        {{ number_format($report['skipped']) }}
                    </dd>
                </div>
            </dl>

            @if ($report['skipped'] === 0)
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    Every row in the file was imported.
                </p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-2 pe-4 font-medium">Row</th>
                                <th scope="col" class="py-2 pe-4 font-medium">Reason</th>
                                <th scope="col" class="py-2 font-medium">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($report['rejections'] as $rejection)
                                <tr>
                                    {{-- The number the user sees in Excel: row 1 is
                                         the heading row, so the first data row is 2. --}}
                                    <td class="py-2 pe-4 font-medium text-gray-950 dark:text-white">
                                        {{ $rejection['row'] }}
                                    </td>
                                    <td class="py-2 pe-4 text-gray-700 dark:text-gray-300">
                                        {{ $rejection['reason'] }}
                                    </td>
                                    <td class="py-2 text-gray-500 dark:text-gray-400">
                                        {{ $rejection['detail'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    Fix the rows listed above and upload the file again; the guests that
                    did import will be reported as already on file.
                </p>
            @endif

            <x-filament::button
                tag="a"
                href="{{ $this->guestDirectoryUrl() }}"
                icon="heroicon-m-users"
                color="gray"
                class="mt-4"
            >
                Back to guest directory
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
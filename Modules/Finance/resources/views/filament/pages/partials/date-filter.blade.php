@php
    $presetPeriods = $fiscalPeriods ?? collect();
    $currentYear = $activeFiscalYear ?? null;
@endphp
<form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
    <div>
        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">From</label>
        <input type="date" name="from" value="{{ $from }}"
            class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">To</label>
        <input type="date" name="to" value="{{ $to }}"
            class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
    </div>
    <div class="flex items-center gap-2">
        <x-filament::button type="submit" size="sm">Filter</x-filament::button>
        @if ($from || $to)
            <x-filament::button tag="a" href="{{ url()->current() }}" color="gray" size="sm">Clear</x-filament::button>
        @endif
    </div>

    @if ($presetPeriods->isNotEmpty())
        <div class="ml-auto flex flex-wrap items-center gap-2">
            @if ($currentYear)
                <a href="{{ url()->current() }}?from={{ $currentYear->start_date->toDateString() }}&to={{ $currentYear->end_date->toDateString() }}"
                    class="rounded-lg border border-gray-300 px-2 py-1 text-xs font-medium hover:bg-gray-100 dark:border-white/10 dark:hover:bg-white/5">
                    {{ $currentYear->name }} (year to date)
                </a>
            @endif

            @foreach ($presetPeriods->take(6) as $period)
                <a href="{{ url()->current() }}?from={{ $period->start_date->toDateString() }}&to={{ $period->end_date->toDateString() }}"
                    title="{{ $period->name }}{{ $period->isLocked() ? ' — locked' : '' }}"
                    class="rounded-lg border px-2 py-1 text-xs font-medium
                        {{ $period->isLocked() ? 'border-danger-300 text-danger-600 dark:border-danger-500/40' : 'border-gray-300 hover:bg-gray-100 dark:border-white/10 dark:hover:bg-white/5' }}">
                    {{ $period->name }}{{ $period->isLocked() ? ' 🔒' : '' }}
                </a>
            @endforeach
        </div>
    @endif
</form>

{{--
    Stat tiles are plain markup rather than x-filament-widgets::stats-overview-item.

    Filament v5 ships no Blade component for a stat tile - StatsOverviewWidget is a
    PHP widget class, and turning a static read-only summary into one would mean
    registering a widget and duplicating the figures already computed here. The
    existing Executive Dashboard uses the same tile markup, so this matches the
    panel's own look without a second source of truth.
--}}
@php
    $tiles = [];

    if ($canViewBookings && $bookings) {
        $tiles[] = ['label' => 'Arriving today', 'value' => $bookings['arrivingToday'], 'accent' => 'primary', 'url' => $bookingsUrl];
        $tiles[] = ['label' => 'Departing today', 'value' => $bookings['departingToday'], 'accent' => 'info', 'url' => $bookingsUrl];
        $tiles[] = ['label' => 'Awaiting confirmation', 'value' => $bookings['pending'], 'accent' => 'warning', 'url' => $bookingsUrl];
        $tiles[] = ['label' => 'Paid this month', 'value' => '₦'.number_format($bookings['monthRevenue'], 2), 'accent' => 'success', 'note' => $bookings['monthCount'].' bookings', 'url' => $bookingsUrl];
        $tiles[] = ['label' => 'Outstanding', 'value' => '₦'.number_format($bookings['outstanding'], 2), 'accent' => 'danger', 'note' => 'charged but not paid', 'url' => $bookingsUrl];
    }

    if ($canViewEnquiries && $enquiries) {
        $tiles[] = ['label' => 'Unread enquiries', 'value' => $enquiries['unread'], 'accent' => $enquiries['unread'] > 0 ? 'danger' : 'success', 'url' => $enquiriesUrl];
        $tiles[] = ['label' => 'Awaiting a reply', 'value' => $enquiries['awaitingReply'], 'accent' => 'warning', 'note' => $enquiries['thisWeek'].' this week', 'url' => $enquiriesUrl];
    }

    if ($canViewTestimonials && $testimonials) {
        $tiles[] = ['label' => 'Reviews to approve', 'value' => $testimonials['awaitingApproval'], 'accent' => 'warning', 'url' => $testimonialsUrl];
        $tiles[] = ['label' => 'Published average', 'value' => $testimonials['averageRating'].' ★', 'accent' => 'success', 'note' => $testimonials['published'].' live', 'url' => $testimonialsUrl];
    }

    if ($audience && $canViewSubscribers) {
        $tiles[] = ['label' => 'Subscribers', 'value' => $audience['subscribers'], 'accent' => 'primary', 'note' => $audience['unsubscribed'].' unsubscribed', 'url' => $subscribersUrl];
    }

    if ($audience && $canViewNewsletters) {
        $tiles[] = ['label' => 'Draft campaigns', 'value' => $audience['draftCampaigns'], 'accent' => 'info', 'note' => $audience['scheduledCampaigns'].' scheduled, '.$audience['sentCampaigns'].' sent', 'url' => $newslettersUrl];
    }
@endphp

<x-filament-panels::page>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ now()->format('l, F d, Y') }} &middot; the public website
            </p>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                Welcome, {{ auth()->user()->name }}
            </h1>
        </div>
    </div>

    @if ($tiles !== [])
        <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @foreach ($tiles as $tile)
                <a href="{{ $tile['url'] }}"
                   class="rounded-xl border-s-4 border-{{ $tile['accent'] }}-500 bg-white p-4 shadow-sm ring-1 ring-primary-100/50 transition-shadow duration-200 hover:shadow-md dark:border-{{ $tile['accent'] }}-500 dark:bg-white/5 dark:ring-primary-500/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ $tile['label'] }}
                    </p>
                    <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                        {{ $tile['value'] }}
                    </p>
                    @isset($tile['note'])
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $tile['note'] }}</p>
                    @endisset
                </a>
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        @if ($canViewBookings && $arrivals->isNotEmpty())
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-primary-100/50 dark:bg-white/5 dark:ring-primary-500/10">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Arrivals in the next three days
                </h2>
                <ul class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($arrivals as $arrival)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $arrival->guest_name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $arrival->roomType?->name ?? 'Room type not set' }}
                                    &middot; {{ $arrival->adults }}A{{ $arrival->children ? ' / '.$arrival->children.'C' : '' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-gray-900 dark:text-white">
                                    {{ $arrival->check_in_date?->format('d M') }}
                                </p>
                                <span @class([
                                    'text-xs font-medium',
                                    'text-yellow-600' => $arrival->status === 'pending',
                                    'text-green-600' => $arrival->status === 'confirmed',
                                    'text-gray-500' => ! in_array($arrival->status, ['pending', 'confirmed'], true),
                                ])>{{ str_replace('_', ' ', ucfirst($arrival->status)) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($canViewEnquiries && $unrepliedEnquiries->isNotEmpty())
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-primary-100/50 dark:bg-white/5 dark:ring-primary-500/10">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Enquiries still to answer
                </h2>
                <ul class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($unrepliedEnquiries as $enquiry)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $enquiry->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $enquiry->subject ?: 'No subject' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-gray-900 dark:text-white">
                                    {{ $enquiry->created_at?->diffForHumans(short: true) }}
                                </p>
                                @if ($enquiry->status === 'unread')
                                    <span class="text-xs font-medium text-red-600">New</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @if ($canViewCatalogue && $catalogue)
        <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-primary-100/50 dark:bg-white/5 dark:ring-primary-500/10">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Published catalogue
            </h2>
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Live room types</dt>
                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $catalogue['liveRoomTypes'] }}
                        @if ($catalogue['inactiveRoomTypes'] > 0)
                            <span class="text-xs font-normal text-gray-500">
                                +{{ $catalogue['inactiveRoomTypes'] }} hidden
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Featured room types</dt>
                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $catalogue['featuredRoomTypes'] }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Amenities</dt>
                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $catalogue['amenities'] }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Active add-ons</dt>
                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $catalogue['activeAddons'] }}
                    </dd>
                </div>
            </dl>
        </div>
    @endif
</x-filament-panels::page>

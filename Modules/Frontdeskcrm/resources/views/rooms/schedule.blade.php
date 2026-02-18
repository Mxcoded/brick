@extends('layouts.master')

@section('title', 'Room Schedule')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="card shadow border-0 pb-4 px-4">
            {{-- Header Controls --}}
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-gray-800"><i class="fas fa-calendar-alt me-2 text-gold"></i>Room Schedule</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.calendarChangeMonth(-1)"><i
                            class="fas fa-chevron-left"></i></button>
                    <span id="calendar-month-label" class="fw-bold mx-3 align-self-center text-uppercase">...</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.calendarChangeMonth(1)"><i
                            class="fas fa-chevron-right"></i></button>
                    <button class="btn btn-sm btn-gold ms-2" onclick="window.loadCalendarData()"><i
                            class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>


            {{-- Legend --}}
            <div class="card-body bg-light py-2 border-bottom small">
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <span class="d-flex align-items-center">
                        <span class="badge me-1" style="background-color: #32CD32;">&nbsp;</span>
                        In-House (Light Green)
                    </span>

                    <span class="d-flex align-items-center">
                        <span class="badge me-1" style="background-color: #006400;">&nbsp;</span>
                        Checked Out (Dark Green)
                    </span>

                    <span class="d-flex align-items-center">
                        <span class="badge me-1" style="background-color: #0DCAF0;">&nbsp;</span>
                        Reserved (Cyan)
                    </span>

                    <span class="d-flex align-items-center">
                        <span class="badge me-1" style="background-color: #FF00FF;">&nbsp;</span>
                        Maintenance (Magenta)
                    </span>

                    <span class="d-flex align-items-center">
                        <span class="badge bg-primary me-1">&nbsp;</span>
                        Online Booking
                    </span>
                </div>
            </div>

            {{-- The Grid --}}
            <div class="card-body p-0 ">
                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table table-bordered table-sm mb-0 align-middle" id="calendar-table"
                        style="min-width: 1500px;">
                        <thead class="bg-light sticky-top" style="z-index: 10;">
                            <tr id="calendar-header-row"></tr>
                        </thead>
                        <tbody id="calendar-body">
                            <tr>
                                <td colspan="30" class="text-center py-5">
                                    <div class="spinner-border text-gold"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
 {{-- ✅ NEW: Expected Arrivals Section --}}
    @if ($expectedArrivals->count() > 0)
        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-plane-arrival me-2 text-gold"></i>Expected Arrivals (Online Bookings)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Guest Name</th>
                            <th>Reference</th>
                            <th>Dates</th>
                            <th>Payment</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expectedArrivals as $booking)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark">{{ $booking->guest_name }}</span>
                                    <br><small class="text-muted">{{ $booking->guest_phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $booking->booking_reference }}</span>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="text-success"><i
                                                class="fas fa-sign-in-alt me-1"></i>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d') }}</span>
                                        <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                        <span class="text-danger"><i
                                                class="fas fa-sign-out-alt me-1"></i>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d') }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($booking->payment_status === 'paid')
                                        <span class="badge bg-success">Paid
                                            (₦{{ number_format($booking->amount_paid) }})</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pay on Arrival</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    {{-- ✅ THE MAGIC BUTTON: Links to the Check-in Form --}}
                                    <a href="{{ route('frontdesk.bookings.checkin', $booking->booking_reference) }}"
                                        class="btn btn-sm btn-primary fw-bold shadow-sm">
                                        <i class="fas fa-key me-1"></i> Check In
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    </div>
   
@endsection

@section('page-scripts')
    <script>
        (function() {
            let currentDate = new Date();
            const calendarApiUrl = '{{ route('website.admin.api.calendar.data') }}';

            window.calendarChangeMonth = function(offset) {
                currentDate.setMonth(currentDate.getMonth() + offset);
                window.loadCalendarData();
            }

            window.loadCalendarData = function() {
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth() + 1;
                const start = new Date(Date.UTC(year, month - 1, 1)).toISOString().slice(0, 10);
                const end = new Date(Date.UTC(year, month, 0)).toISOString().slice(0, 10);

                const monthName = currentDate.toLocaleString('default', {
                    month: 'long',
                    year: 'numeric'
                });
                document.getElementById('calendar-month-label').textContent = monthName;

                fetch(`${calendarApiUrl}?start=${start}&end=${end}`)
                    .then(r => r.json())
                    .then(data => renderCalendar(data))
                    .catch(err => console.error('Calendar Load Error:', err));
            }

            function renderCalendar(data) {
                const headerRow = document.getElementById('calendar-header-row');
                const body = document.getElementById('calendar-body');

                if (!headerRow || !body) return;

                // 1. Headers
                let headers =
                    '<th class="bg-light sticky-start border-end text-center" style="width: 120px; left: 0; z-index: 11;">Room</th>';
                data.days.forEach(day => {
                    const bg = day.is_today ? 'background-color: #fff3cd;' : '';
                    const color = day.is_weekend ? 'color: #dc3545;' : '';
                    headers += `
                <th class="text-center" style="min-width: 40px; ${bg} ${color}">
                    <div class="small fw-bold">${day.day}</div>
                    <div style="font-size: 0.6rem; text-transform: uppercase;">${day.weekday}</div>
                </th>`;
                });
                headerRow.innerHTML = headers;

                // 2. Rows
                let rows = '';
                data.rooms.forEach(room => {
                    rows += `<tr>
                <td class="sticky-start bg-white fw-bold text-dark border-end text-center" style="left: 0; z-index: 5;">
                    ${room.name}
                </td>`;

                    data.days.forEach(day => {
                        // ✅ CRITICAL FIX: Strip Time from API Timestamps (YYYY-MM-DD HH:MM:SS -> YYYY-MM-DD)
                        // This ensures "2023-10-25" == "2023-10-25" instead of being "less than".
                        const event = room.events.find(e => {
                            const start = e.start.substring(0, 10);
                            const end = e.end.substring(0, 10);

                            // Logic: Occupied if Day is >= Start AND Day < End (Night Counts)
                            return day.date >= start && day.date < end;
                        });

                        if (event) {
                            const eventStart = event.start.substring(0, 10);
                            const isStart = day.date === eventStart;
                            const title = isStart ? event.title : '';

                            rows += `
                        <td class="p-0" title="${event.status}: ${event.title}">
                            <div class="d-block w-100 h-100 text-white small" 
                               style="background-color: ${event.color}; min-height: 45px; border-right: 1px solid rgba(255,255,255,0.2); font-size: 0.7rem; padding-top:12px; overflow:hidden;">
                               ${isStart ? `<span class="px-1 text-truncate d-block">${title}</span>` : ''}
                            </div>
                        </td>`;
                        } else {
                            rows += `<td class="p-0"></td>`;
                        }
                    });
                    rows += `</tr>`;
                });
                body.innerHTML = rows;
            }

            document.addEventListener('DOMContentLoaded', function() {
                window.loadCalendarData();
            });
        })();
    </script>
    <style>
        .sticky-start {
            position: sticky;
            left: 0;
        }

        .btn-gold {
            background-color: #C8A165;
            color: white;
            border: none;
        }

        .btn-gold:hover {
            background-color: #b08c54;
            color: white;
        }

        .text-gold {
            color: #C8A165;
        }
    </style>
@endsection

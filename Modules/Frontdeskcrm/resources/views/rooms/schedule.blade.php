@extends('layouts.master')

@section('title', 'Room Density Chart')

@section('page-content')
    <div class="container-fluid py-4">
        <div class="card shadow border-0">
            {{-- Header with Title and Month Navigation --}}
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-calendar-alt me-2 text-gold"></i>
                        DENSITY CHART FOR <span id="calendar-month-label" class="text-uppercase">...</span>
                    </h4>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-outline-secondary btn-sm" onclick="window.calendarChangeMonth(-1)">
                            <i class="fas fa-chevron-left"></i> Prev
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="window.goToToday()">
                            <i class="fas fa-calendar-day"></i> Today
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="window.calendarChangeMonth(1)">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                        <button class="btn btn-gold btn-sm ms-2" onclick="window.loadCalendarData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="card-body bg-light py-2 border-bottom">
                <div class="d-flex gap-4 justify-content-center flex-wrap small">
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #32CD32;"></span>
                        <strong class="ms-1">In-House</strong>
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #006400;"></span>
                        <strong class="ms-1">Checked Out</strong>
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #00CED1;"></span>
                        <strong class="ms-1">Reserved</strong>
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #FFC107;"></span>
                        <strong class="ms-1">Pending Check-in</strong>
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #FF00FF;"></span>
                        <strong class="ms-1">Maintenance</strong>
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-marker-o">O</span>
                        <span class="ms-1">Occupied</span>
                    </span>
                    <span class="d-flex align-items-center">
                        <span class="legend-marker-r">R</span>
                        <span class="ms-1">Reserved</span>
                    </span>
                </div>
            </div>

            {{-- The Density Chart Grid --}}
            <div class="card-body p-0">
                <div class="table-responsive density-chart-wrapper">
                    <table class="table table-bordered table-sm mb-0 density-table" id="calendar-table">
                        <thead class="sticky-top" style="z-index: 10;">
                            {{-- Day of Week Row --}}
                            <tr id="calendar-weekday-row" class="bg-dark text-white"></tr>
                            {{-- Day Number Row --}}
                            <tr id="calendar-header-row" class="bg-secondary text-white"></tr>
                        </thead>
                        <tbody id="calendar-body">
                            <tr>
                                <td colspan="35" class="text-center py-5">
                                    <div class="spinner-border text-gold"></div>
                                    <div class="mt-2 text-muted">Loading density chart...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Summary Stats --}}
            <div class="card-footer bg-white py-3">
                <div class="row text-center" id="density-stats">
                    <div class="col">
                        <div class="h4 mb-0 text-success" id="stat-occupied">-</div>
                        <small class="text-muted">Occupied Tonight</small>
                    </div>
                    <div class="col">
                        <div class="h4 mb-0 text-info" id="stat-reserved">-</div>
                        <small class="text-muted">Reserved (Future)</small>
                    </div>
                    <div class="col">
                        <div class="h4 mb-0 text-secondary" id="stat-available">-</div>
                        <small class="text-muted">Available Tonight</small>
                    </div>
                    <div class="col">
                        <div class="h4 mb-0 text-primary" id="stat-occupancy">-</div>
                        <small class="text-muted">Occupancy Rate</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expected Arrivals Section --}}
        @if ($expectedArrivals->count() > 0)
            <div class="card shadow border-0 mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-plane-arrival me-2 text-gold"></i>
                        Expected Arrivals ({{ $expectedArrivals->count() }} bookings)
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Guest Name</th>
                                <th>Reference</th>
                                <th>Room Type</th>
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
                                        <span class="badge bg-secondary">{{ $booking->roomType->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="text-success">
                                                <i class="fas fa-sign-in-alt me-1"></i>
                                                {{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d') }}
                                            </span>
                                            <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                            <span class="text-danger">
                                                <i class="fas fa-sign-out-alt me-1"></i>
                                                {{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($booking->payment_status === 'paid')
                                            <span class="badge bg-success">Paid (₦{{ number_format($booking->amount_paid) }})</span>
                                        @elseif ($booking->payment_status === 'partial')
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('frontdesk.registrations.create', ['ref' => $booking->booking_reference]) }}"
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

            window.goToToday = function() {
                currentDate = new Date();
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
                }).toUpperCase();
                document.getElementById('calendar-month-label').textContent = monthName;

                fetch(`${calendarApiUrl}?start=${start}&end=${end}`)
                    .then(r => r.json())
                    .then(data => renderDensityChart(data))
                    .catch(err => {
                        console.error('Calendar Load Error:', err);
                        document.getElementById('calendar-body').innerHTML = 
                            '<tr><td colspan="35" class="text-center text-danger py-5">Failed to load data</td></tr>';
                    });
            }

            function renderDensityChart(data) {
                const weekdayRow = document.getElementById('calendar-weekday-row');
                const headerRow = document.getElementById('calendar-header-row');
                const body = document.getElementById('calendar-body');

                if (!weekdayRow || !headerRow || !body) return;

                const today = new Date().toISOString().slice(0, 10);

                // 1. Build Header Rows (Weekday + Day Number)
                let weekdays = '<th class="density-header-room bg-dark" rowspan="2">Room Type</th>' +
                               '<th class="density-header-num bg-dark" rowspan="2">Room</th>';
                let dayNumbers = '';

                data.days.forEach(day => {
                    const isToday = day.date === today;
                    const isWeekend = day.is_weekend;
                    const bgClass = isToday ? 'density-today-header' : (isWeekend ? 'density-weekend' : 'bg-dark');
                    
                    weekdays += `<th class="density-day-header ${bgClass}">${day.weekday}</th>`;
                    dayNumbers += `<th class="density-day-header ${isToday ? 'density-today-header' : 'bg-secondary'}">${day.day}</th>`;
                });

                weekdayRow.innerHTML = weekdays;
                headerRow.innerHTML = dayNumbers;

                // 2. Build Room Rows
                let rows = '';
                let todayOccupied = 0;
                let todayReserved = 0;
                let totalRooms = data.rooms.length;

                data.rooms.forEach(room => {
                    // Use room_type and room_number from API (with fallback to parseRoomName)
                    const roomType = room.room_type || parseRoomName(room.name).type;
                    const roomNumber = room.room_number || parseRoomName(room.name).number;
                    
                    rows += `<tr>
                        <td class="density-room-type">${roomType}</td>
                        <td class="density-room-number">${roomNumber}</td>`;

                    data.days.forEach(day => {
                        const cellData = getCellData(room.events, day.date);
                        const isToday = day.date === today;
                        
                        // Track today's stats
                        if (isToday) {
                            if (cellData.status === 'checked_in') todayOccupied++;
                            else if (cellData.status === 'reserved' || cellData.status === 'online_booking' || cellData.status === 'draft_by_guest') todayReserved++;
                        }

                        rows += renderCell(cellData, isToday);
                    });

                    rows += `</tr>`;
                });

                body.innerHTML = rows;

                // 3. Update Stats
                const available = totalRooms - todayOccupied - todayReserved;
                const occupancyRate = totalRooms > 0 ? Math.round((todayOccupied / totalRooms) * 100) : 0;
                
                document.getElementById('stat-occupied').textContent = todayOccupied;
                document.getElementById('stat-reserved').textContent = todayReserved;
                document.getElementById('stat-available').textContent = available;
                document.getElementById('stat-occupancy').textContent = occupancyRate + '%';
            }

            function parseRoomName(name) {
                // Try to extract "Room 101 (Deluxe Suite)" -> {type: "Deluxe Suite", number: "101"}
                const match = name.match(/Room\s+(\w+)\s*\(([^)]+)\)/i);
                if (match) {
                    return { number: match[1], type: match[2] };
                }
                // Fallback: just use the name
                return { type: name, number: '' };
            }

            function getCellData(events, date) {
                // Find event that covers this date
                const event = events.find(e => {
                    const start = e.start.substring(0, 10);
                    const end = e.end.substring(0, 10);
                    return date >= start && date < end;
                });

                if (!event) {
                    return { status: 'available', color: null, isStart: false, title: '' };
                }

                const start = event.start.substring(0, 10);
                
                return {
                    status: event.status,
                    color: event.color,
                    isStart: date === start,
                    title: event.title || ''
                };
            }

            function renderCell(cellData, isToday) {
                if (cellData.status === 'available') {
                    return `<td class="density-cell ${isToday ? 'density-today-cell' : ''}"></td>`;
                }

                let bgColor = cellData.color || '#6c757d';
                let marker = '';
                let textColor = '#000';
                
                // Determine marker based on status and position
                if (cellData.isStart) {
                    if (cellData.status === 'checked_in' || cellData.status === 'checked_out') {
                        marker = 'O';
                    } else if (cellData.status === 'reserved' || cellData.status === 'online_booking' || cellData.status === 'draft_by_guest') {
                        marker = 'R';
                    }
                }

                // Adjust colors to match the sample
                switch (cellData.status) {
                    case 'checked_in':
                        bgColor = '#32CD32'; // Light green
                        break;
                    case 'checked_out':
                        bgColor = '#006400'; // Dark green
                        textColor = '#fff';
                        break;
                    case 'reserved':
                    case 'online_booking':
                        bgColor = '#00CED1'; // Cyan
                        break;
                    case 'draft_by_guest':
                        bgColor = '#FFC107'; // Yellow/pending
                        break;
                    case 'maintenance':
                        bgColor = '#FF00FF'; // Magenta
                        textColor = '#fff';
                        break;
                }

                return `<td class="density-cell ${isToday ? 'density-today-cell' : ''}" 
                            style="background-color: ${bgColor};" 
                            title="${cellData.title}">
                    ${marker ? `<span class="density-marker" style="color: ${textColor}">${marker}</span>` : ''}
                </td>`;
            }

            document.addEventListener('DOMContentLoaded', function() {
                window.loadCalendarData();
            });
        })();
    </script>

    <style>
        .density-chart-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .density-table {
            border-collapse: collapse;
            font-size: 0.75rem;
        }

        .density-table th,
        .density-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }

        .density-header-room {
            min-width: 130px;
            max-width: 150px;
            position: sticky;
            left: 0;
            z-index: 12;
            white-space: nowrap;
            font-size: 0.7rem;
            color: #fff;
        }

        .density-header-num {
            min-width: 70px;
            position: sticky;
            left: 130px;
            z-index: 12;
            font-size: 0.7rem;
            color: #fff;
        }

        .density-day-header {
            min-width: 30px;
            max-width: 30px;
            padding: 3px 1px !important;
            font-size: 0.65rem;
            font-weight: bold;
            color: #fff;
        }

        .density-weekend {
            background-color: #dc3545 !important;
            color: #fff;
        }

        .density-today-header {
            background-color: #ffc107 !important;
            color: #000 !important;
            font-weight: bold;
        }

        .density-room-type {
            position: sticky;
            left: 0;
            z-index: 5;
            background: #fff;
            font-weight: 500;
            text-align: left !important;
            padding-left: 6px !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
            font-size: 0.68rem;
            border-right: 1px solid #000;
        }

        .density-room-number {
            position: sticky;
            left: 130px;
            z-index: 5;
            background: #fff;
            font-weight: bold;
            min-width: 70px;
            font-size: 0.72rem;
            border-right: 2px solid #000;
        }

        .density-cell {
            min-width: 30px;
            max-width: 30px;
            height: 24px;
            padding: 0 !important;
            position: relative;
        }

        .density-today-cell {
            box-shadow: inset 0 0 0 2px #ffc107;
        }

        .density-marker {
            font-weight: bold;
            font-size: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .legend-box {
            display: inline-block;
            width: 18px;
            height: 12px;
            border: 1px solid #333;
        }

        .legend-marker-o {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            background: #32CD32;
            color: #000;
            font-weight: bold;
            font-size: 0.65rem;
            border: 1px solid #333;
        }

        .legend-marker-r {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            background: #00CED1;
            color: #000;
            font-weight: bold;
            font-size: 0.65rem;
            border: 1px solid #333;
        }

        .sticky-top {
            position: sticky;
            top: 0;
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

        /* Print styles */
        @media print {
            .density-chart-wrapper {
                max-height: none;
                overflow: visible;
            }
            .btn, .card-header .d-flex.gap-2 {
                display: none !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }
            .density-table {
                font-size: 0.6rem;
            }
            .density-cell {
                min-width: 20px;
                max-width: 20px;
                height: 18px;
            }
            .card-footer {
                display: none;
            }
        }
    </style>
@endsection

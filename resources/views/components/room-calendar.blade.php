<div class="card shadow border-0" id="calendar-container">
    {{-- Header / Controls --}}
    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center no-print">
        <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i>Room Schedule / Density Chart</h5>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-outline-light" onclick="window.calendarChangeMonth(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span id="calendar-month-label" class="fw-bold mx-2">...</span>
            <button class="btn btn-sm btn-outline-light" onclick="window.calendarChangeMonth(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            <button class="btn btn-sm btn-info ms-2" onclick="window.goToToday()">
                <i class="fas fa-calendar-day"></i> Today
            </button>
            <button class="btn btn-sm btn-success ms-2" onclick="window.loadCalendarData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button class="btn btn-sm btn-secondary ms-2" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    {{-- Legend --}}
    <div class="card-body bg-light py-2 border-bottom small no-print">
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <span class="d-flex align-items-center">
                <span class="badge me-1" style="background-color: #32CD32; width: 20px;">&nbsp;</span>
                <strong>O</strong> = In-House
            </span>
            <span class="d-flex align-items-center">
                <span class="badge me-1" style="background-color: #006400; width: 20px;">&nbsp;</span>
                Checked Out
            </span>
            <span class="d-flex align-items-center">
                <span class="badge me-1" style="background-color: #00CED1; width: 20px;">&nbsp;</span>
                <strong>R</strong> = Reserved
            </span>
            <span class="d-flex align-items-center">
                <span class="badge me-1" style="background-color: #FFC107; width: 20px;">&nbsp;</span>
                Pending
            </span>
            <span class="d-flex align-items-center">
                <span class="badge me-1" style="background-color: #0d6efd; width: 20px;">&nbsp;</span>
                Online Booking
            </span>
            <span class="d-flex align-items-center">
                <span class="badge me-1" style="background-color: #FF00FF; width: 20px;">&nbsp;</span>
                Maintenance
            </span>
        </div>
    </div>

    {{-- Density Chart Grid --}}
    <div class="card-body p-0">
        <div class="density-chart-wrapper">
            <table class="table table-bordered density-table mb-0" id="calendar-table">
                <thead>
                    <tr id="calendar-weekday-row" class="text-white text-center"></tr>
                    <tr id="calendar-header-row" class="text-white text-center"></tr>
                </thead>
                <tbody id="calendar-body">
                    <tr>
                        <td colspan="35" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stats Footer --}}
    <div class="card-footer bg-white py-3 no-print">
        <div class="row text-center">
            <div class="col-3">
                <div class="h4 mb-0 text-success" id="stat-occupied">0</div>
                <small class="text-muted">Occupied</small>
            </div>
            <div class="col-3">
                <div class="h4 mb-0 text-info" id="stat-reserved">0</div>
                <small class="text-muted">Reserved</small>
            </div>
            <div class="col-3">
                <div class="h4 mb-0 text-secondary" id="stat-available">0</div>
                <small class="text-muted">Available</small>
            </div>
            <div class="col-3">
                <div class="h4 mb-0 text-primary" id="stat-occupancy">0%</div>
                <small class="text-muted">Occupancy</small>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let currentDate = new Date();
    const calendarApiUrl = '{{ route("website.admin.api.calendar.data") }}';

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
        return { type: name, number: '' };
    }

    function getCellData(events, date) {
        const event = events.find(e => {
            const start = e.start.substring(0, 10);
            const end = e.end.substring(0, 10);
            return date >= start && date < end;
        });

        if (!event) {
            return { status: 'available', color: null, isStart: false, title: '', details_url: null };
        }

        const start = event.start.substring(0, 10);

        return {
            status: event.status,
            color: event.color,
            isStart: date === start,
            title: event.title || '',
            details_url: event.details_url || null
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

        // Make cell clickable if details_url exists
        const clickAttr = cellData.details_url ? `onclick="window.location='${cellData.details_url}'" style="cursor: pointer;"` : '';

        return `<td class="density-cell ${isToday ? 'density-today-cell' : ''}"
                    style="background-color: ${bgColor};"
                    title="${cellData.title}"
                    ${clickAttr}>
            ${marker ? `<span class="density-marker" style="color: ${textColor}">${marker}</span>` : ''}
        </td>`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.loadCalendarData();
        setInterval(window.loadCalendarData, 30000);
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
        padding: 2px 4px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }

    .density-header-room {
        min-width: 120px;
        max-width: 150px;
        white-space: nowrap;
    }

    .density-header-num {
        min-width: 50px;
    }

    .density-day-header {
        min-width: 28px;
        max-width: 32px;
        font-weight: bold;
    }

    .density-room-type {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: left !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    .density-room-number {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .density-cell {
        min-width: 28px;
        height: 24px;
        position: relative;
    }

    .density-marker {
        font-weight: bold;
        font-size: 0.7rem;
    }

    .density-today-header {
        background-color: #0d6efd !important;
        color: white;
    }

    .density-today-cell {
        border-left: 2px solid #0d6efd !important;
        border-right: 2px solid #0d6efd !important;
    }

    .density-weekend {
        background-color: #6c757d !important;
    }

    /* Print Styles */
    @media print {
        .no-print {
            display: none !important;
        }

        .density-chart-wrapper {
            max-height: none;
            overflow: visible;
        }

        .density-table {
            font-size: 8pt;
        }

        .density-cell {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

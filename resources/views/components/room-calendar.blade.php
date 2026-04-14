<div class="card shadow border-0" id="calendar-container">
    {{-- Header / Controls --}}
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Room Schedule</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.calendarChangeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
            <span id="calendar-month-label" class="fw-bold mx-2 align-self-center">...</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="window.calendarChangeMonth(1)"><i class="fas fa-chevron-right"></i></button>
            <button class="btn btn-sm btn-primary ms-2" onclick="window.loadCalendarData()"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
    </div>

    {{-- Legend --}}
    <div class="card-body bg-light py-2 border-bottom small">
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <span class="d-flex align-items-center"><span class="badge bg-danger me-1">&nbsp;</span> Checked In</span>
            <span class="d-flex align-items-center"><span class="badge bg-primary me-1">&nbsp;</span> Confirmed</span>
            <span class="d-flex align-items-center"><span class="badge bg-warning text-dark me-1">&nbsp;</span> Pending</span>
            <span class="d-flex align-items-center"><span class="badge bg-success me-1">&nbsp;</span> Checked Out</span>
            <span class="d-flex align-items-center"><span class="badge bg-secondary me-1">&nbsp;</span> Maintenance</span>
        </div>
    </div>

    {{-- The Grid --}}
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-bordered table-sm mb-0 align-middle" id="calendar-table" style="min-width: 1200px;">
                <thead class="bg-light sticky-top" style="z-index: 10;">
                    <tr id="calendar-header-row">
                        </tr>
                </thead>
                <tbody id="calendar-body">
                    <tr><td colspan="30" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    // Encapsulate to avoid global variable conflicts, but expose helper functions
    let currentDate = new Date();
    const calendarApiUrl = '{{ route("website.admin.api.calendar.data") }}';

    console.log('Calendar: Script Loaded');

    // Make these available globally for the buttons onclick="window.fn()"
    window.calendarChangeMonth = function(offset) {
        currentDate.setMonth(currentDate.getMonth() + offset);
        window.loadCalendarData();
    }

    window.loadCalendarData = function() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth() + 1;
        
        // Calculate Start/End of month correctly
        const start = new Date(Date.UTC(year, month - 1, 1)).toISOString().slice(0, 10);
        const end = new Date(Date.UTC(year, month, 0)).toISOString().slice(0, 10);

        // Update Label
        const monthName = currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
        const label = document.getElementById('calendar-month-label');
        if(label) label.textContent = monthName;

        console.log(`Calendar: Fetching ${start} to ${end}`);

        fetch(`${calendarApiUrl}?start=${start}&end=${end}`)
            .then(r => {
                if(!r.ok) throw new Error("API Error");
                return r.json();
            })
            .then(data => renderCalendar(data))
            .catch(err => {
                console.error('Calendar Load Error:', err);
                document.getElementById('calendar-body').innerHTML = `<tr><td colspan="30" class="text-center text-danger py-4">Failed to load calendar data.</td></tr>`;
            });
    }

    function renderCalendar(data) {
        const headerRow = document.getElementById('calendar-header-row');
        const body = document.getElementById('calendar-body');
        
        if(!headerRow || !body) return;

        // 1. Render Headers
        let headers = '<th class="bg-light sticky-start border-end" style="width: 150px; left: 0; z-index: 11;">Room</th>';
        data.days.forEach(day => {
            const bg = day.is_today ? 'background-color: #e8f4ff;' : '';
            const color = day.is_weekend ? 'color: #dc3545;' : '';
            headers += `
                <th class="text-center" style="min-width: 35px; ${bg} ${color}">
                    <div class="small fw-bold">${day.day}</div>
                    <div style="font-size: 0.6rem; text-transform: uppercase;">${day.weekday}</div>
                </th>`;
        });
        headerRow.innerHTML = headers;

        // 2. Render Rows
        let rows = '';
        data.rooms.forEach(room => {
            rows += `<tr>
                <td class="sticky-start bg-white fw-bold text-dark border-end" style="left: 0; z-index: 5;">
                    ${room.name} <span class="text-muted small fw-normal">(max cap. ${room.capacity})</span>
                </td>`;

            data.days.forEach(day => {
                const event = room.events.find(e => day.date >= e.start && day.date < e.end);
                
                if (event) {
                    const isStart = day.date === event.start;
                    const content = isStart ? `<span class="small text-white text-truncate d-block px-1">${event.title}</span>` : '';
                    rows += `
                        <td class="p-0" title="${event.status}: ${event.title}">
                            <a href="${event.details_url}" class="d-block w-100 h-100 text-decoration-none" 
                               style="background-color: ${event.color}; min-height: 40px; opacity: 0.9; border-right: 1px solid rgba(255,255,255,0.2);">
                               ${content}
                            </a>
                        </td>`;
                } else {
                    rows += `<td class="p-0"></td>`;
                }
            });
            rows += `</tr>`;
        });
        body.innerHTML = rows;
    }

    // Initial Load
    document.addEventListener('DOMContentLoaded', function() {
        window.loadCalendarData();
        setInterval(window.loadCalendarData, 30000);
    });

})();
</script>

<style>
    .sticky-start { position: sticky; left: 0; }
    .table-responsive::-webkit-scrollbar { height: 8px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 4px; }
</style>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-th me-2"></i>Live Room Status</h5>
        <div class="small text-muted">
            <span class="badge bg-success me-1">Available</span>
            <span class="badge bg-danger me-1">Occupied</span>
            <span class="badge bg-warning text-dark me-1">Reserved</span>
            <span class="badge bg-secondary">Out of Service</span>
        </div>
    </div>
    <div class="card-body">
        <div id="room-rack-grid" class="row g-3">
            {{-- Initial Spinner --}}
            <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2">Loading Room Status...</div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT MOVED OUTSIDE @PUSH TO ENSURE IT RUNS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Room Rack: Script Loaded'); // Debug Line

    const gridContainer = document.getElementById('room-rack-grid');
    const apiUrl = '{{ route("website.admin.api.room.status") }}';
    
    console.log('Room Rack: Fetching from', apiUrl); // Debug Line

    function fetchRoomStatus() {
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error("Network response was not ok: " + response.statusText);
                return response.json();
            })
            .then(data => {
                console.log('Room Rack: Data received', data); // Debug Line
                
                let html = '';
                if(data.length === 0) {
                    html = '<div class="col-12 text-center p-3">No rooms found.</div>';
                } else {
                    data.forEach(room => {
                        html += `
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <div class="card h-100 border-${room.color} mb-3 text-center" style="border-width: 2px;">
                                <div class="card-header bg-${room.color} text-white py-1 small fw-bold text-truncate">
                                    ${room.name}
                                </div>
                                <div class="card-body p-2 d-flex flex-column justify-content-center align-items-center">
                                    <i class="fas ${room.status === 'occupied' ? 'fa-user' : (['maintenance', 'blocked'].includes(room.status) ? 'fa-tools' : 'fa-check-circle')} mb-1 text-${room.color} fa-2x"></i>
                                    <small class="d-block fw-bold text-dark text-truncate w-100" style="font-size: 0.8rem;">
                                        ${room.guest}
                                    </small>
                                    ${room.checkout 
                                        ? `<span class="badge bg-light text-dark border mt-1" style="font-size: 0.65rem">Out: ${room.checkout}</span>` 
                                        : ''
                                    }
                                </div>
                            </div>
                        </div>`;
                    });
                }
                gridContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Room Rack Error:', error);
                gridContainer.innerHTML = `
                    <div class="col-12 text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle mb-2"></i><br>
                        Failed to load data.<br>
                        <small class="text-muted">${error.message}</small>
                    </div>`;
            });
    }

    // Run immediately
    fetchRoomStatus();
    // Refresh every 10s
    setInterval(fetchRoomStatus, 10000); 
});
</script>
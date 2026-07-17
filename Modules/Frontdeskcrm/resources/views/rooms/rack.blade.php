@extends('layouts.master')

@section('title', 'Live Room Status')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-th me-2 text-gold"></i>Room Status</h1>
        <div class="d-flex gap-2">
            <span class="badge bg-success p-2">Available</span>
            <span class="badge bg-danger p-2">Occupied (In-House)</span>
            <span class="badge bg-warning text-dark p-2">Reserved (Coming Soon)</span>
            <span class="badge bg-primary p-2">Online Booking</span>
            <span class="badge bg-secondary p-2">Out of Service</span>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body">
            <div id="room-rack-grid" class="row g-3">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-gold" role="status"></div>
                    <div class="mt-2 text-muted">Loading Real-time Data...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridContainer = document.getElementById('room-rack-grid');
    // ✅ REUSE THE API: This fetches the merged data (Bookings + Registrations)
    const apiUrl = '{{ route("website.admin.api.room.status") }}'; 

    function fetchRoomStatus() {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if(data.length === 0) {
                    html = '<div class="col-12 text-center p-3">No rooms found.</div>';
                } else {
                    data.forEach(room => {
                        // Logic to handle click: If occupied, go to registration; if vacant, go to create walkin
                        let clickAction = '#';
                        if(room.status === 'available') {
                            clickAction = "{{ route('frontdesk.registrations.createWalkin') }}"; // Click to Book
                        }
                        
                        html += `
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <a href="${clickAction}" class="text-decoration-none">
                                <div class="card h-100 border-${room.color} mb-3 text-center shadow-sm position-relative room-card" style="border-width: 2px;">
                                    <div class="card-header bg-${room.color} text-white py-1 small fw-bold text-truncate">
                                        ${room.name}
                                    </div>
                                    <div class="card-body p-2 d-flex flex-column justify-content-center align-items-center" style="min-height: 100px;">
                                        <i class="fas ${room.status === 'occupied' ? 'fa-user' : (['maintenance', 'blocked'].includes(room.status) ? 'fa-tools' : 'fa-check-circle')} mb-2 text-${room.color} fa-2x"></i>
                                        
                                        <small class="d-block fw-bold text-dark text-truncate w-100" style="font-size: 0.8rem;">
                                            ${room.guest}
                                        </small>
                                        
                                        ${room.checkout 
                                            ? `<span class="badge bg-light text-dark border mt-1" style="font-size: 0.65rem">
                                                ${room.status === 'available' ? '' : 'Out: ' + room.checkout}
                                               </span>` 
                                            : ''
                                        }
                                    </div>
                                </div>
                            </a>
                        </div>`;
                    });
                }
                gridContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                gridContainer.innerHTML = `<div class="col-12 text-center text-danger">Failed to load data.</div>`;
            });
    }

    fetchRoomStatus();
    setInterval(fetchRoomStatus, 30000); // Auto-refresh every 30s
});
</script>
<style>
    .room-card { transition: transform 0.2s; }
    .room-card:hover { transform: translateY(-5px); }
</style>
@endsection
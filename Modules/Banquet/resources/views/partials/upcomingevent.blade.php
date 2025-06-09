<div class="card-body">
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Upcoming Events</h5>
    </div>
    
        @if (isset($upcomingEvents) && $upcomingEvents->isNotEmpty())
        <ul class="list-group">
            @foreach ($upcomingEvents as $event)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #{{ $event->order_id }}</strong> - {{ $event->customer->organization }} <br>
                        Starts on {{ \Carbon\Carbon::parse($event->earliest_event_date)->format('M d, Y') }} <br>
                        Status: <span class="badge bg-{{ $event->status == 'Confirmed' ? 'success' : 'warning' }}">{{ $event->status }}</span>
                    </div>
                    <a href="{{ route('banquet.orders.show', $event->order_id) }}"
                        class="btn btn-sm btn-outline-primary">
                        View Details
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-muted">No upcoming events.</p>
    @endif
    </div>
</div>
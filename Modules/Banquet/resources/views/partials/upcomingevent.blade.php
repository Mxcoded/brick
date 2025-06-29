<div class="card-body">
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Upcoming Events <i class="fas fa-bell"></i></h5>
    </div>
    
        @if (isset($upcomingEvents) && $upcomingEvents->isNotEmpty())
        <ul class="list-group">
            @foreach ($upcomingEvents as $event)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #{{ $event->order_id }}</strong><br> 
                        
                        @if($event->customer->organization == "Individual")
                        Guest Name -    
                        <strong>{{ strtoupper($event->customer->name) }}</strong>
                            
                        @else
                        Organization Name -
                            {{ strtoupper($event->customer->organization) }}
                        @endif
                        <br>
                        <strong>Starts on {{ \Carbon\Carbon::parse($event->earliest_event_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($event->last_event_date)->format('M d, Y') }}</strong><br>
                        Location : <strong>{{strtoupper($event->eventDays->first()->room);}}</strong><br>
                        Expected Guest: <strong>{{$event->eventDays->sum('guest_count');}}</strong><br>
                        Status: <span class="badge bg-{{ $event->status == 'Confirmed' ? 'success' : 'warning' }}">{{ $event->status }}</span>
                    </div>
                    {{-- {{ route('banquet.orders.show', $event->order_id) }} --}}
                    <a href="#"class="btn btn-sm btn-outline-primary" title="Disabled Temprorarily">
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
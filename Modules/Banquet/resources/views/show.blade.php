@extends('staff::layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Order #{{ $order->order_id }}</li>
@endsection

@section('page-content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold display-5 text-primary">Order #{{ $order->order_id }}</h1>
            <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>

        <!-- Order Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Preparation Date</small>
                                <div class="h6 mb-0">{{ $order->preparation_date->format('M d, Y') }}</div>
                            </div>
                            <i class="fas fa-calendar-alt text-primary fs-4"></i>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Total Revenue</small>
                                <div class="h6 mb-0 text-success">₦{{ number_format($order->total_revenue, 2) }}</div>
                            </div>
                            <i class="fas fa-chart-line text-success fs-4"></i>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Expenses</small>
                                <div
                                    class="h6 mb-0 @if ($order->total_revenue < $order->expenses) {
                                text-danger
                                }
                            @else{
                            text-success
                            } @endif">
                                    ₦{{ number_format($order->expenses, 2) }}</div>
                            </div>
                            <i class="fas fa-chart-line text-danger fs-4"></i>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Profit Margin</small>
                                <div class="h6 mb-0 text-primary">
                                    @if ($order->total_revenue > 0 && $order->profit_margin === null)
                                        {{ number_format((($order->total_revenue - $order->expenses) / $order->total_revenue) * 100, 2) }}%
                                    @else
                                        {{ $order->profit_margin ? number_format($order->profit_margin, 2) . '%' : 'N/A' }}
                                    @endif
                                </div>
                            </div>
                            <i class="fas fa-percent text-primary fs-4"></i>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Event Status</small>
                                <div class="h6 mb-0 ">{{ $order->status }}</div>
                            </div>
                            @if ($order->status == 'Pending')
                                <i class="fas fa-sync text-warning fs-4"></i>
                            @elseif($order->status == 'Completed')
                                <i class="fas fa-check text-success fs-4"></i>
                            @elseif($order->status == 'Confirmed')
                                <i class="fas fa-calendar-check text-primary fs-4"></i>
                            @elseif($order->status == 'Cancelled')
                                <i class="fa-solid fa-calendar-xmark text-danger fs-4"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-user-tie me-2"></i>Customer Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5 text-muted">Main Contact:</dt>
                                    <dd class="col-sm-7">{{ $order->contact_person_name }}</dd>

                                    <dt class="col-sm-5 text-muted">Department:</dt>
                                    <dd class="col-sm-7">{{ $order->department ?? 'N/A' }}</dd>

                                    <dt class="col-sm-5 text-muted">Phone:</dt>
                                    <dd class="col-sm-7">
                                        <a href="tel:{{ $order->contact_person_phone }}" class="text-decoration-none">
                                            {{ $order->contact_person_phone }}
                                        </a>
                                    </dd>
                                    <dt class="col-sm-5 text-muted">Organization:</dt>
                                    <dd class="col-sm-7">
                                        {{ $order->customer->organization }}
                                        </a>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5 text-muted">Secondary Contact:</dt>
                                    <dd class="col-sm-7">{{ $order->contact_person_name_ii ?? 'N/A' }}</dd>

                                    <dt class="col-sm-5 text-muted">Email:</dt>
                                    <dd class="col-sm-7">
                                        <a href="mailto:{{ $order->contact_person_email }}" class="text-decoration-none">
                                            {{ $order->contact_person_email }}
                                        </a>
                                    </dd>

                                    <dt class="col-sm-5 text-muted">Referred By:</dt>
                                    <dd class="col-sm-7">{{ $order->referred_by ?? 'N/A' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Days -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-calendar-days me-2"></i>Event Schedule</h5>
                <a href="{{ route('banquet.orders.add-day', $order->order_id) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-2"></i>Add Event Day
                </a>
            </div>
            <div class="card-body p-0">
                @if ($order->eventDays->isEmpty())
                    <div class="alert alert-info m-4">No event days scheduled yet.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Event Type</th>
                                    <th>Guest Count</th>
                                    <th>Setup Style</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->eventDays as $day)
                                    <tr>
                                        <td>{{ $day->event_date->format('M d, Y') }}</td>
                                        <td>{{ $day->event_type }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="fas fa-users me-2"></i>{{ $day->guest_count }}
                                            </span>
                                        </td>
                                        <td>{{ $day->setup_style }}</td>
                                        <td>
                                            {{ $day->start_time }} - {{ $day->end_time }}<br>
                                            <small class="text-muted">{{ $day->duration_minutes }} mins</small>
                                        </td>
                                        <td>
                                            @php
                                                $statusColor =
                                                    [
                                                        'planned' => 'secondary',
                                                        'confirmed' => 'success',
                                                        'completed' => 'primary',
                                                        'cancelled' => 'danger',
                                                    ][strtolower($day->event_status)] ?? 'warning';
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}">
                                                {{ $day->event_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <a href="{{ route('banquet.orders.add-menu-item', [$order->order_id, $day->id]) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Add Menu">
                                                    <i class="fas fa-utensils me-2"></i>Add Menu
                                                </a>

                                                {{-- <a href="{{ route('banquet.orders.event-days.show', [$order->order_id, $day->id]) }}"
                                                    class="btn btn-sm btn-info" title="View Event Day">
                                                    <i class="fas fa-eye me-2"></i>View
                                                </a> --}}

                                                <a href="{{ route('banquet.orders.event-days.edit', [$order->order_id, $day->id]) }}"
                                                    class="btn btn-sm btn-warning" title="Edit Event Day">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>

                                                <form
                                                    action="{{ route('banquet.orders.event-days.destroy', [$order->order_id, $day->id]) }}"
                                                    method="POST" style="display:inline;">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this event day?')"
                                                        title="Delete Event Day">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Menu Items -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-utensils me-2"></i>Menu Details</h5>
            </div>
            <div class="card-body p-0">
                @if ($order->eventDays->flatMap->menuItems->isEmpty())
                    <div class="alert alert-info m-4">No menu items added yet.</div>
                @else
                    <div class="accordion" id="menuAccordion">
                        @foreach ($order->eventDays as $day)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $day->id }}">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ $day->id }}" aria-expanded="true">
                                        {{ $day->event_date->format('F j, Y') }} -
                                        {{ $day->event_description ?? 'Event Day' }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $day->id }}" class="accordion-collapse collapse show"
                                    aria-labelledby="heading{{ $day->id }}">
                                    <div class="accordion-body">
                                        <div class="row row-cols-1 row-cols-md-2 g-4">
                                            @foreach ($day->menuItems as $item)
                                                <div class="col">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div
                                                            class="card-header bg-light d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0">
                                                                <i
                                                                    class="fas fa-{{ $item->meal_type === 'breakfast' ? 'sun' : 'moon' }} me-2"></i>
                                                                {{ ucfirst($item->meal_type) }}
                                                            </h6>
                                                            <div>
                                                                <a href="{{ route('banquet.orders.edit-menu-item', [$order->order_id, $day->id, $item->id]) }}"
                                                                    class="btn btn-sm btn-warning me-2"
                                                                    title="Edit Menu Item">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <form
                                                                    action="{{ route('banquet.orders.menu-item.destroy', [$order->order_id, $day->id, $item->id]) }}"
                                                                    method="POST" style="display:inline;">
                                                                    @method('DELETE')
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                                        onclick="return confirm('Are you sure you want to delete this menu item?')"
                                                                        title="Delete Menu Item">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <div>
                                                                    <small class="text-muted">Total Price</small>
                                                                    <div class="h5 text-primary">
                                                                        ₦{{ number_format($item->total_price, 2) }}</div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <small class="text-muted">Quantity</small>
                                                                    <div class="h5">{{ $item->quantity }}</div>
                                                                </div>
                                                            </div>
                                                            <h6 class="mb-2">Menu Items:</h6>
                                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                                @foreach (json_decode($item->menu_items, true) as $menu)
                                                                    <span
                                                                        class="badge bg-primary-subtle text-primary">{{ $menu }}</span>
                                                                @endforeach
                                                            </div>
                                                            @if ($item->dietary_restrictions)
                                                                <h6 class="mb-2">Dietary Needs:</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    @foreach (json_decode($item->dietary_restrictions, true) as $restriction)
                                                                        <span
                                                                            class="badge bg-warning-subtle text-warning">{{ $restriction }}</span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>


    <style>
        .card {
            border-radius: 0.75rem;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 0.5em 0.75em;
            border-radius: 0.5rem;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
    </style>
@endsection

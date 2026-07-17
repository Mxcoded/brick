@if ($notificationType === 'new')
** New Maintenance Request **
@else
** Maintenance Status Update **
@endif

Location: {{ $log->location }}
Department: {{ ucfirst($log->department) }}
Priority: {{ ucfirst($log->priority) }}
Status: {{ str_replace('_', ' ', ucfirst($log->status)) }}
@if ($notificationType === 'status_update' && $previousStatus)
Previous Status: {{ str_replace('_', ' ', ucfirst($previousStatus)) }}
@endif
Reported: {{ $log->complaint_datetime->format('F j, Y g:i A') }}
Lodged By: {{ $log->lodged_by }}
Received By: {{ $log->received_by ?? 'N/A' }}
@if ($log->cost_of_fixing)
Cost: {{ number_format($log->cost_of_fixing, 2) }}
@endif
@if ($log->completion_date)
Completion Date: {{ \Carbon\Carbon::parse($log->completion_date)->format('F j, Y') }}
@endif

Nature of Complaint:
{{ $log->nature_of_complaint }}

View full details: {{ route('maintenance.show', $log->id) }}

---
&copy; {{ date('Y') }} Brickspoint. All rights reserved.

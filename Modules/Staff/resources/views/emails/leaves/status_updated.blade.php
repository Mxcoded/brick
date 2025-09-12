<x-mail::message>
# Leave Request {{ ucfirst($leaveRequest->status) }}

Hello {{ $leaveRequest->employee->name }},

Your leave request has been **{{ $leaveRequest->status }}**.

**Details:**
- **Leave Type:** {{ $leaveRequest->leave_type }}
- **Start Date:** {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M, Y') }}
- **End Date:** {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M, Y') }}

@if($leaveRequest->status === 'rejected' && $leaveRequest->admin_note)
**Reason for Rejection:**
{{ $leaveRequest->admin_note }}
@endif

You can view your leave history by clicking the button below.

<x-mail::button :url="route('staff.leaves.index')">
View My Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
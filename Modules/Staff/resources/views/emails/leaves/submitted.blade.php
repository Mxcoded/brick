<x-mail::message>
# New Leave Request

A new leave request has been submitted by **{{ $leaveRequest->employee->name }}**.

**Details:**
- **Leave Type:** {{ $leaveRequest->leave_type }}
- **Start Date:** {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M, Y') }}
- **End Date:** {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M, Y') }}
- **Total Days:** {{ $leaveRequest->days_count }}

You can review this request by clicking the button below.

<x-mail::button :url="route('staff.leaves.admin')">
View Pending Requests
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
<x-mail::message>
# Welcome to Brickspoint, {{ $name }}!

We are pleased to inform you that your staff record has been successfully created in the **Brickspoint ERP** system.

Here is a summary of your registered details:

<x-mail::table>
| Detail | Value |
|--------|-------|
| **Full Name** | {{ $name }} |
| **Staff Code** | {{ $staffCode }} |
| **Email** | {{ $email }} |
| **Phone** | {{ $phone }} |
| **Position** | {{ $position }} |
| **Department** | {{ $department }} |
</x-mail::table>

You will receive a separate email with your login credentials once your system account is created by the administrator.

If you have any questions, please contact the HR department.

Welcome aboard!

<x-mail::button :url="$loginUrl">
Visit Staff Portal
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

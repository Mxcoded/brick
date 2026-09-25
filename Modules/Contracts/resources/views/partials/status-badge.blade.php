@php
    $status = \Modules\Contracts\Enums\AgreementStatus::from($agreement->status);
@endphp
<span class="badge {{ $status->badgeClass() }}">{{ $status->label() }}</span>
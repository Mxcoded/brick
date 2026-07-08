@php
    $ordered = collect($steps)->sortBy('order');
    $stepKeys = $ordered->keys()->toArray();
    $currentIndex = array_search($current, $stepKeys);
@endphp
<div class="mb-4">
    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
        @foreach($ordered as $key => $step)
            <div class="d-flex align-items-center gap-2">
                @if($step['completed'])
                    <span class="badge rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #28a745; color: #fff;">
                        <i class="fas fa-check" style="font-size: 12px;"></i>
                    </span>
                @elseif($key === $current)
                    <span class="badge rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #C8A165; color: #fff; font-weight: 700;">
                        {{ $loop->iteration }}
                    </span>
                @else
                    <span class="badge rounded-circle d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px; background: transparent; color: #999; font-weight: 600;">
                        {{ $loop->iteration }}
                    </span>
                @endif
                <span class="small fw-semibold {{ $key === $current ? 'text-dark' : ($step['completed'] ? 'text-success' : 'text-muted') }}">
                    {{ $step['label'] }}
                </span>
                @if(!$loop->last)
                    <div class="d-none d-sm-block" style="width: 24px; height: 2px; background: {{ $step['completed'] ? '#28a745' : '#ddd' }};"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

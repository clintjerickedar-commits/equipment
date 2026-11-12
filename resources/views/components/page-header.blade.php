@props(['title', 'subtitle' => null])
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        @if($subtitle)<p class="page-sub">{{ $subtitle }}</p>@endif
    </div>
    <div class="d-flex gap-2 no-print">{{ $slot }}</div>
</div>

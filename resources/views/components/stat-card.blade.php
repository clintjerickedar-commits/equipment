@props(['icon', 'label', 'value', 'tone' => 'blue', 'hint' => null])
<div class="card stat-card">
    <div class="stat-icon tone-{{ $tone }}"><i class="bi bi-{{ $icon }}"></i></div>
    <div>
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
        @if($hint)<div class="stat-label" style="font-size:.75rem">{{ $hint }}</div>@endif
    </div>
</div>

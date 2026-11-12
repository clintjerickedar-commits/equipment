@props(['item' => null])
@if($item?->image_url)
    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="eq-thumb">
@else
    <span class="eq-thumb"><i class="bi bi-box-seam"></i></span>
@endif

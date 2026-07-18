@if ($item->image_path)
    <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}" class="item-thumb">
@else
    <span class="text-muted">-</span>
@endif

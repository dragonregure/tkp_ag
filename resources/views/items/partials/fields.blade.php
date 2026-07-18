<div class="row g-3">
    <div class="col-md-4">
        <label for="code" class="form-label">Kode</label>
        <input type="text" id="code" name="code" value="{{ old('code', $item->code) }}" class="form-control" required>
    </div>
    <div class="col-md-8">
        <label for="name" class="form-label">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label for="price" class="form-label">Harga</label>
        <input type="number" id="price" name="price" value="{{ old('price', $item->price) }}" class="form-control" min="0" step="0.01" required>
    </div>
    <div class="col-md-4">
        <label for="stock" class="form-label">Stock</label>
        <input type="number" id="stock" name="stock" value="{{ old('stock', $item->stock ?? 0) }}" class="form-control" min="0" step="1" required>
    </div>
    <div class="col-md-4">
        <label for="image" class="form-label">Image</label>
        <input type="file" id="image" name="image" class="form-control" accept="image/*">
        @if ($item->image_path)
            <div class="mt-2">
                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}" class="item-thumb">
            </div>
        @endif
    </div>
</div>

<?php

namespace App\Services;

use App\Contracts\ItemRepositoryInterface;
use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ItemService
{
    public function __construct(private readonly ItemRepositoryInterface $items)
    {
    }

    public function create(array $data, ?UploadedFile $image): Item
    {
        if ($image !== null) {
            $data['image_path'] = $image->store('items', 'public');
        }

        unset($data['image']);

        return $this->items->create($data);
    }

    public function update(Item $item, array $data, ?UploadedFile $image): Item
    {
        if ($image !== null) {
            if ($item->image_path !== null) {
                Storage::disk('public')->delete($item->image_path);
            }

            $data['image_path'] = $image->store('items', 'public');
        }

        unset($data['image']);

        return $this->items->update($item, $data);
    }

    public function delete(Item $item): void
    {
        if ($item->saleItems()->exists()) {
            throw new RuntimeException('Item yang sudah digunakan pada penjualan tidak bisa dihapus.');
        }

        if ($item->image_path !== null) {
            Storage::disk('public')->delete($item->image_path);
        }

        $this->items->delete($item);
    }
}

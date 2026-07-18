<?php

namespace App\Repositories;

use App\Contracts\ItemRepositoryInterface;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ItemRepository implements ItemRepositoryInterface
{
    private function dataTableQuery(): Builder
    {
        return Item::query()->latest();
    }

    public function dataTableResponse(): JsonResponse
    {
        return DataTables::eloquent($this->dataTableQuery())
            ->editColumn('price', fn (Item $item): string => number_format((float) $item->price, 0, ',', '.'))
            ->editColumn('stock', fn (Item $item): string => number_format((int) $item->stock, 0, ',', '.'))
            ->addColumn('image', fn (Item $item): string => view('items.partials.image', compact('item'))->render())
            ->addColumn('actions', fn (Item $item): string => view('items.partials.table-actions', compact('item'))->render())
            ->rawColumns(['image', 'actions'])
            ->toJson();
    }

    public function select2Options(array $filters = [], int $perPage = 20): array
    {
        $search = $filters['term'] ?? $filters['q'] ?? null;
        $page = (int) ($filters['page'] ?? 1);
        $items = Item::query()
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->orderBy('name')
            ->paginate(min($perPage, 50), ['id', 'code', 'name', 'price', 'stock'], 'page', max($page, 1));

        return [
            'results' => $items->getCollection()
                ->map(fn (Item $item): array => [
                    'id' => (int) $item->id,
                    'text' => $item->code . ' - ' . $item->name,
                    'price' => (float) $item->price,
                    'stock' => (int) $item->stock,
                ])
                ->values()
                ->all(),
            'pagination' => ['more' => $items->hasMorePages()],
        ];
    }

    public function create(array $data): Item
    {
        return Item::query()->create($data);
    }

    public function update(Item $item, array $data): Item
    {
        $item->update($data);

        return $item->refresh();
    }

    public function delete(Item $item): void
    {
        $item->delete();
    }
}

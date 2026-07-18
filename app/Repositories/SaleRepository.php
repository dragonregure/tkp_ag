<?php

namespace App\Repositories;

use App\Contracts\CodeGeneratorInterface;
use App\Contracts\SaleRepositoryInterface;
use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class SaleRepository implements SaleRepositoryInterface
{
    public function __construct(private readonly CodeGeneratorInterface $codeGenerator)
    {
    }

    private function dataTableQuery(array $filters = []): Builder
    {
        return Sale::query()
            ->withCount('items')
            ->when($filters['start_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('sale_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('sale_date', '<=', $date))
            ->latest('sale_date')
            ->latest('id');
    }

    public function dataTableResponse(array $filters = []): JsonResponse
    {
        return DataTables::eloquent($this->dataTableQuery($filters))
            ->editColumn('sale_date', fn (Sale $sale): string => Carbon::parse($sale->sale_date)->format('d/m/Y'))
            ->editColumn('subtotal', fn (Sale $sale): string => number_format((float) $sale->subtotal, 0, ',', '.'))
            ->addColumn('status_badge', fn (Sale $sale): string => view('sales.partials.status-badge', compact('sale'))->render())
            ->addColumn('actions', fn (Sale $sale): string => view('sales.partials.table-actions', compact('sale'))->render())
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    public function findWithRelations(Sale $sale): Sale
    {
        return $sale->load(['items.item', 'payments']);
    }

    public function saleItemRows(Sale $sale): array
    {
        return SaleItem::query()
            ->where('sale_id', $sale->id)
            ->orderBy('id')
            ->get()
            ->map(fn (SaleItem $row): array => [
                'item_id' => (int) $row->item_id,
                'item_text' => $row->item_code . ' - ' . $row->item_name,
                'qty' => (int) $row->qty,
                'price' => (float) $row->price,
            ])
            ->values()
            ->all();
    }

    public function createWithItems(array $data, ?int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId): Sale {
            $sale = Sale::query()->create([
                'code' => $this->codeGenerator->generate('sales', 'SL', $data['sale_date']),
                'sale_date' => $data['sale_date'],
                'status' => SaleStatus::Unpaid,
                'created_by' => $userId,
            ]);

            $requestedQuantities = $this->aggregateQuantities($data['items']);
            $lockedItems = $this->lockItems(array_keys($requestedQuantities));
            $this->assertStockIsAvailable($lockedItems, $requestedQuantities);
            $this->applyStockDifferences($lockedItems, $requestedQuantities);
            $subtotal = $this->syncItems($sale, $data['items'], $lockedItems);
            $sale->update([
                'subtotal' => $subtotal,
                'paid_amount' => 0,
                'remaining_amount' => $subtotal,
            ]);

            return $sale->refresh()->load('items.item');
        }, 3);
    }

    public function updateWithItems(Sale $sale, array $data): Sale
    {
        if ($sale->isPaid()) {
            throw new RuntimeException('Penjualan yang sudah dibayar tidak bisa diedit.');
        }

        return DB::transaction(function () use ($sale, $data): Sale {
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->id);

            if ($sale->isPaid()) {
                throw new RuntimeException('Penjualan yang sudah dibayar tidak bisa diedit.');
            }

            $currentQuantities = $this->saleItemQuantities($sale);
            $requestedQuantities = $this->aggregateQuantities($data['items']);
            $stockDifferences = $this->stockDifferences($requestedQuantities, $currentQuantities);
            $lockedItems = $this->lockItems(array_keys($currentQuantities + $requestedQuantities));
            $this->assertStockIsAvailable($lockedItems, $stockDifferences);

            $sale->update(['sale_date' => $data['sale_date']]);
            $this->applyStockDifferences($lockedItems, $stockDifferences);
            $subtotal = $this->syncItems($sale, $data['items'], $lockedItems);
            $paidAmount = (float) $sale->payments()->sum('amount');

            if ($paidAmount > $subtotal) {
                throw ValidationException::withMessages([
                    'items' => 'Total penjualan tidak boleh lebih kecil dari pembayaran yang sudah diterima.',
                ]);
            }

            $sale->update(['subtotal' => $subtotal]);

            return $this->recalculatePaymentStatus($sale)->load('items.item', 'payments');
        }, 3);
    }

    public function delete(Sale $sale): void
    {
        if ($sale->isPaid()) {
            throw new RuntimeException('Penjualan yang sudah dibayar tidak bisa dihapus.');
        }

        DB::transaction(function () use ($sale): void {
            $sale = Sale::query()->lockForUpdate()->findOrFail($sale->id);

            if ($sale->isPaid()) {
                throw new RuntimeException('Penjualan yang sudah dibayar tidak bisa dihapus.');
            }

            $currentQuantities = $this->saleItemQuantities($sale);
            $stockDifferences = $this->stockDifferences([], $currentQuantities);
            $lockedItems = $this->lockItems(array_keys($currentQuantities));
            $this->applyStockDifferences($lockedItems, $stockDifferences);

            $sale->delete();
        }, 3);
    }

    public function recalculatePaymentStatus(Sale $sale): Sale
    {
        $paidAmount = (float) $sale->payments()->sum('amount');
        $subtotal = (float) $sale->subtotal;
        $remainingAmount = max($subtotal - $paidAmount, 0);
        $status = match (true) {
            $paidAmount <= 0 => SaleStatus::Unpaid,
            $paidAmount < $subtotal => SaleStatus::PartiallyPaid,
            default => SaleStatus::Paid,
        };

        $sale->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'status' => $status,
        ]);

        return $sale->refresh();
    }

    public function payableSaleSelect2Options(array $filters = [], int $perPage = 20): array
    {
        $search = $filters['term'] ?? $filters['q'] ?? null;
        $page = (int) ($filters['page'] ?? 1);
        $sales = Sale::query()
            ->whereIn('status', [SaleStatus::Unpaid->value, SaleStatus::PartiallyPaid->value])
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('sale_date', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(
                min($perPage, 50),
                ['id', 'code', 'sale_date', 'remaining_amount'],
                'page',
                max($page, 1)
            );

        return [
            'results' => $sales->getCollection()
                ->map(fn (Sale $sale): array => [
                    'id' => (int) $sale->id,
                    'text' => $sale->code . ' - Sisa Rp ' . number_format((float) $sale->remaining_amount, 0, ',', '.'),
                    'remaining_amount' => (float) $sale->remaining_amount,
                ])
                ->values()
                ->all(),
            'pagination' => ['more' => $sales->hasMorePages()],
        ];
    }

    /**
     * @param  EloquentCollection<int, Item>  $items
     */
    private function syncItems(Sale $sale, array $rows, EloquentCollection $items): float
    {
        $sale->items()->delete();
        $subtotal = 0.0;

        foreach ($rows as $row) {
            $item = $items->get((int) $row['item_id']);
            $qty = (int) $row['qty'];
            $price = (float) $row['price'];
            $total = $qty * $price;

            if (!$item instanceof Item) {
                throw ValidationException::withMessages([
                    'items' => 'Item yang dipilih tidak ditemukan.',
                ]);
            }

            $sale->items()->create([
                'item_id' => $item->id,
                'item_code' => $item->code,
                'item_name' => $item->name,
                'qty' => $qty,
                'price' => $price,
                'total_price' => $total,
            ]);

            $subtotal += $total;
        }

        return $subtotal;
    }

    /**
     * @return array<int, int>
     */
    private function aggregateQuantities(array $rows): array
    {
        $quantities = [];

        foreach ($rows as $row) {
            $itemId = (int) $row['item_id'];
            $quantities[$itemId] = ($quantities[$itemId] ?? 0) + (int) $row['qty'];
        }

        ksort($quantities);

        return $quantities;
    }

    /**
     * @return array<int, int>
     */
    private function saleItemQuantities(Sale $sale): array
    {
        $quantities = [];
        $rows = $sale->items()
            ->select('item_id', DB::raw('SUM(qty) as qty'))
            ->groupBy('item_id')
            ->pluck('qty', 'item_id');

        foreach ($rows as $itemId => $qty) {
            $quantities[(int) $itemId] = (int) $qty;
        }

        ksort($quantities);

        return $quantities;
    }

    /**
     * Positive values reduce stock; negative values restore stock.
     *
     * @param  array<int, int>  $requestedQuantities
     * @param  array<int, int>  $currentQuantities
     * @return array<int, int>
     */
    private function stockDifferences(array $requestedQuantities, array $currentQuantities): array
    {
        $differences = [];
        $itemIds = array_unique([...array_keys($requestedQuantities), ...array_keys($currentQuantities)]);
        sort($itemIds);

        foreach ($itemIds as $itemId) {
            $difference = ($requestedQuantities[$itemId] ?? 0) - ($currentQuantities[$itemId] ?? 0);

            if ($difference !== 0) {
                $differences[(int) $itemId] = $difference;
            }
        }

        return $differences;
    }

    /**
     * Locks item rows in ascending id order so concurrent sales acquire stock locks consistently.
     *
     * @param  array<int, int|string>  $itemIds
     * @return EloquentCollection<int, Item>
     */
    private function lockItems(array $itemIds): EloquentCollection
    {
        $ids = array_values(array_unique(array_map('intval', $itemIds)));
        sort($ids);

        if ($ids === []) {
            return new EloquentCollection();
        }

        return Item::query()
            ->whereKey($ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  EloquentCollection<int, Item>  $items
     * @param  array<int, int>  $stockDifferences
     */
    private function assertStockIsAvailable(EloquentCollection $items, array $stockDifferences): void
    {
        foreach ($stockDifferences as $itemId => $quantity) {
            if ($quantity <= 0) {
                continue;
            }

            $item = $items->get($itemId);

            if (!$item instanceof Item) {
                throw ValidationException::withMessages([
                    'items' => 'Item yang dipilih tidak ditemukan.',
                ]);
            }

            if ((int) $item->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => sprintf(
                        'Stok %s tidak mencukupi. Tersedia %d, diminta %d.',
                        $item->name,
                        (int) $item->stock,
                        $quantity
                    ),
                ]);
            }
        }
    }

    /**
     * @param  EloquentCollection<int, Item>  $items
     * @param  array<int, int>  $stockDifferences
     */
    private function applyStockDifferences(EloquentCollection $items, array $stockDifferences): void
    {
        foreach ($stockDifferences as $itemId => $quantity) {
            $item = $items->get($itemId);

            if (!$item instanceof Item) {
                throw ValidationException::withMessages([
                    'items' => 'Item yang dipilih tidak ditemukan.',
                ]);
            }

            $newStock = (int) $item->stock - $quantity;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'items' => sprintf(
                        'Stok %s tidak boleh kurang dari 0.',
                        $item->name
                    ),
                ]);
            }

            $item->forceFill(['stock' => $newStock])->save();
            $item->setAttribute('stock', $newStock);
        }
    }
}

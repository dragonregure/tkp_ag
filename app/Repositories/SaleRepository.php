<?php

namespace App\Repositories;

use App\Contracts\CodeGeneratorInterface;
use App\Contracts\SaleRepositoryInterface;
use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
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

            $subtotal = $this->syncItems($sale, $data['items']);
            $sale->update([
                'subtotal' => $subtotal,
                'paid_amount' => 0,
                'remaining_amount' => $subtotal,
            ]);

            return $sale->refresh()->load('items.item');
        });
    }

    public function updateWithItems(Sale $sale, array $data): Sale
    {
        if ($sale->isPaid()) {
            throw new RuntimeException('Penjualan yang sudah dibayar tidak bisa diedit.');
        }

        return DB::transaction(function () use ($sale, $data): Sale {
            $sale->update(['sale_date' => $data['sale_date']]);
            $subtotal = $this->syncItems($sale, $data['items']);
            $paidAmount = (float) $sale->payments()->sum('amount');

            if ($paidAmount > $subtotal) {
                throw ValidationException::withMessages([
                    'items' => 'Total penjualan tidak boleh lebih kecil dari pembayaran yang sudah diterima.',
                ]);
            }

            $sale->update(['subtotal' => $subtotal]);

            return $this->recalculatePaymentStatus($sale)->load('items.item', 'payments');
        });
    }

    public function delete(Sale $sale): void
    {
        if ($sale->isPaid()) {
            throw new RuntimeException('Penjualan yang sudah dibayar tidak bisa dihapus.');
        }

        DB::transaction(function () use ($sale): void {
            $sale->delete();
        });
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

    private function syncItems(Sale $sale, array $rows): float
    {
        $sale->items()->delete();
        $subtotal = 0.0;

        foreach ($rows as $row) {
            $item = Item::query()->findOrFail($row['item_id']);
            $qty = (int) $row['qty'];
            $price = (float) $row['price'];
            $total = $qty * $price;

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
}

<?php

namespace App\Repositories;

use App\Contracts\DashboardRepositoryInterface;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function summary(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $query = Sale::query()->whereBetween('sale_date', [$startDate->toDateString(), $endDate->toDateString()]);

        return [
            'transaction_count' => (clone $query)->count(),
            'sales_amount' => (float) (clone $query)->sum('subtotal'),
            'item_qty' => (int) SaleItem::query()
                ->whereHas('sale', fn ($saleQuery) => $saleQuery->whereBetween('sale_date', [$startDate->toDateString(), $endDate->toDateString()]))
                ->sum('qty'),
        ];
    }

    public function salesAmountPerMonth(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', sale_date)"
            : 'DATE_FORMAT(sale_date, "%Y-%m")';

        return Sale::query()
            ->selectRaw($monthExpression . ' as month, SUM(subtotal) as total')
            ->whereBetween('sale_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy(DB::raw($monthExpression))
            ->orderBy('month')
            ->get()
            ->map(fn (Sale $sale): array => [
                'month' => (string) $sale->getAttribute('month'),
                'total' => (float) $sale->getAttribute('total'),
            ])
            ->all();
    }

    public function itemQty(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        return SaleItem::query()
            ->selectRaw('item_name as name, SUM(qty) as qty')
            ->whereHas('sale', fn ($saleQuery) => $saleQuery->whereBetween('sale_date', [$startDate->toDateString(), $endDate->toDateString()]))
            ->groupBy('item_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get()
            ->map(fn (SaleItem $item): array => [
                'name' => (string) $item->getAttribute('name'),
                'qty' => (int) $item->getAttribute('qty'),
            ])
            ->all();
    }
}

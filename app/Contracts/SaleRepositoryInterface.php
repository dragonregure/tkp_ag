<?php

namespace App\Contracts;

use App\Models\Sale;
use Illuminate\Http\JsonResponse;

interface SaleRepositoryInterface
{
    public function dataTableResponse(array $filters = []): JsonResponse;

    public function findWithRelations(Sale $sale): Sale;

    /**
     * @return list<array{item_id: int, item_text: string, qty: int, price: float}>
     */
    public function saleItemRows(Sale $sale): array;

    public function createWithItems(array $data, ?int $userId): Sale;

    public function updateWithItems(Sale $sale, array $data): Sale;

    public function delete(Sale $sale): void;

    public function recalculatePaymentStatus(Sale $sale): Sale;

    /**
     * @return array{results: list<array{id: int, text: string, remaining_amount: float}>, pagination: array{more: bool}}
     */
    public function payableSaleSelect2Options(array $filters = [], int $perPage = 20): array;
}

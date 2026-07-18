<?php

namespace App\Contracts;

use App\Models\Item;
use Illuminate\Http\JsonResponse;

interface ItemRepositoryInterface
{
    public function dataTableResponse(): JsonResponse;

    /**
     * @return array{results: list<array{id: int, text: string, price: float}>, pagination: array{more: bool}}
     */
    public function select2Options(array $filters = [], int $perPage = 20): array;

    public function create(array $data): Item;

    public function update(Item $item, array $data): Item;

    public function delete(Item $item): void;
}

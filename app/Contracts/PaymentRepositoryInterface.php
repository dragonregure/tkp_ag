<?php

namespace App\Contracts;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;

interface PaymentRepositoryInterface
{
    public function dataTableResponse(array $filters = []): JsonResponse;

    public function findWithRelations(Payment $payment): Payment;

    public function create(array $data, ?int $userId): Payment;

    public function update(Payment $payment, array $data): Payment;

    public function delete(Payment $payment): void;
}

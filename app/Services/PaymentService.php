<?php

namespace App\Services;

use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\SaleRepositoryInterface;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly SaleRepositoryInterface $sales,
    ) {
    }

    public function create(array $data, ?int $userId): Payment
    {
        return DB::transaction(function () use ($data, $userId): Payment {
            $sale = Sale::query()->lockForUpdate()->findOrFail($data['sale_id']);
            $this->assertPaymentDoesNotExceedSale($sale, (float) $data['amount']);

            $payment = $this->payments->create($data, $userId);
            $this->sales->recalculatePaymentStatus($sale);

            return $payment->load('sale');
        });
    }

    public function update(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data): Payment {
            $payment->load('sale');
            $sale = Sale::query()->lockForUpdate()->findOrFail($payment->sale_id);
            $currentAmount = (float) $payment->amount;
            $this->assertPaymentDoesNotExceedSale($sale, (float) $data['amount'], $currentAmount);

            $updatedPayment = $this->payments->update($payment, $data);
            $this->sales->recalculatePaymentStatus($sale);

            return $updatedPayment->load('sale');
        });
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $sale = Sale::query()->lockForUpdate()->findOrFail($payment->sale_id);
            $this->payments->delete($payment);
            $this->sales->recalculatePaymentStatus($sale);
        });
    }

    private function assertPaymentDoesNotExceedSale(Sale $sale, float $amount, float $currentPaymentAmount = 0): void
    {
        $paidAmount = (float) $sale->payments()->sum('amount') - $currentPaymentAmount;
        $maximumAllowed = (float) $sale->subtotal - $paidAmount;

        if ($amount > $maximumAllowed) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan.',
            ]);
        }
    }
}

<?php

namespace App\Repositories;

use App\Contracts\CodeGeneratorInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(private readonly CodeGeneratorInterface $codeGenerator)
    {
    }

    private function dataTableQuery(array $filters = []): Builder
    {
        return Payment::query()
            ->with('sale')
            ->when($filters['start_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('payment_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('payment_date', '<=', $date))
            ->latest('payment_date')
            ->latest('id');
    }

    public function dataTableResponse(array $filters = []): JsonResponse
    {
        return DataTables::eloquent($this->dataTableQuery($filters))
            ->editColumn('payment_date', fn (Payment $payment): string => Carbon::parse($payment->payment_date)->format('d/m/Y'))
            ->editColumn('amount', fn (Payment $payment): string => number_format((float) $payment->amount, 0, ',', '.'))
            ->addColumn('sale_code', fn (Payment $payment): string => $payment->sale instanceof Sale ? $payment->sale->code : '-')
            ->addColumn('actions', fn (Payment $payment): string => view('payments.partials.table-actions', compact('payment'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function findWithRelations(Payment $payment): Payment
    {
        return $payment->load('sale');
    }

    public function create(array $data, ?int $userId): Payment
    {
        return Payment::query()->create([
            ...$data,
            'code' => $this->codeGenerator->generate('payments', 'PY', $data['payment_date']),
            'created_by' => $userId,
        ]);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->refresh();
    }

    public function delete(Payment $payment): void
    {
        $payment->delete();
    }
}

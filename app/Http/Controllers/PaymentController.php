<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentRepositoryInterface;
use App\Http\Requests\DataTableDateFilterRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentService $paymentService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Payment::class);

        return view('payments.index');
    }

    public function data(DataTableDateFilterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        return $this->payments->dataTableResponse($request->validated());
    }

    public function create(): View
    {
        $this->authorize('create', Payment::class);

        return view('payments.form', [
            'payment' => new Payment(['payment_date' => now()]),
            'selectedSale' => null,
        ]);
    }

    public function store(PaymentRequest $request): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $payment = $this->paymentService->create($request->validated(), Auth::id());

        return redirect()->route('payments.show', $payment)->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('payments.show', ['payment' => $this->payments->findWithRelations($payment)]);
    }

    public function edit(Payment $payment): View
    {
        $this->authorize('update', $payment);

        $payment = $this->payments->findWithRelations($payment);

        return view('payments.form', [
            'payment' => $payment,
            'selectedSale' => $payment->sale,
        ]);
    }

    public function update(PaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $payment = $this->paymentService->update($payment, $request->validated());

        return redirect()->route('payments.show', $payment)->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $this->paymentService->delete($payment);

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dihapus.');
    }
}

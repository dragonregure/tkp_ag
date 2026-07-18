<?php

namespace App\Http\Controllers;

use App\Contracts\SaleRepositoryInterface;
use App\Http\Requests\DataTableDateFilterRequest;
use App\Http\Requests\SaleRequest;
use App\Http\Requests\Select2Request;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class SaleController extends Controller
{
    public function __construct(private readonly SaleRepositoryInterface $sales)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Sale::class);

        return view('sales.index');
    }

    public function data(DataTableDateFilterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);

        return $this->sales->dataTableResponse($request->validated());
    }

    public function select2(Select2Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);

        return response()->json($this->sales->payableSaleSelect2Options($request->validated()));
    }

    public function create(): View
    {
        $this->authorize('create', Sale::class);

        return view('sales.form', [
            'sale' => new Sale(['sale_date' => now()]),
            'saleItemRows' => [],
        ]);
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $this->authorize('create', Sale::class);

        $sale = $this->sales->createWithItems($request->validated(), Auth::id());

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan berhasil ditambahkan.');
    }

    public function show(Sale $sale): View
    {
        $this->authorize('view', $sale);

        return view('sales.show', ['sale' => $this->sales->findWithRelations($sale)]);
    }

    public function edit(Sale $sale): View|RedirectResponse
    {
        $this->authorize('update', $sale);

        if ($sale->isPaid()) {
            return redirect()->route('sales.show', $sale)->with('error', 'Penjualan yang sudah dibayar tidak bisa diedit.');
        }

        $sale = $this->sales->findWithRelations($sale);

        return view('sales.form', [
            'sale' => $sale,
            'saleItemRows' => $this->sales->saleItemRows($sale),
        ]);
    }

    public function update(SaleRequest $request, Sale $sale): RedirectResponse
    {
        $this->authorize('update', $sale);

        try {
            $sale = $this->sales->updateWithItems($sale, $request->validated());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan berhasil diperbarui.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $this->authorize('delete', $sale);

        try {
            $this->sales->delete($sale);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('sales.index')->with('success', 'Penjualan berhasil dihapus.');
    }
}

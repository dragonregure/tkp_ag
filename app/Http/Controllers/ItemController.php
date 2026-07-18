<?php

namespace App\Http\Controllers;

use App\Contracts\ItemRepositoryInterface;
use App\Http\Requests\ItemRequest;
use App\Http\Requests\Select2Request;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemRepositoryInterface $items,
        private readonly ItemService $itemService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Item::class);

        return view('items.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Item::class);

        return $this->items->dataTableResponse();
    }

    public function select2(Select2Request $request): JsonResponse
    {
        $this->authorize('viewAny', Item::class);

        return response()->json($this->items->select2Options($request->validated()));
    }

    public function create(): View
    {
        $this->authorize('create', Item::class);

        return view('items.form', ['item' => new Item()]);
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        $this->authorize('create', Item::class);

        $this->itemService->create($request->validated(), $request->file('image'));

        return redirect()->route('items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(Item $item): View
    {
        $this->authorize('update', $item);

        return view('items.form', compact('item'));
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $this->itemService->update($item, $request->validated(), $request->file('image'));

        return redirect()->route('items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        try {
            $this->itemService->delete($item);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('items.index')->with('success', 'Item berhasil dihapus.');
    }
}

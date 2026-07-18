<div class="table-actions" role="group">
    <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary btn-icon-action" title="Detail">
        <i class="bi bi-eye"></i>
    </a>
    @can('update', $sale)
        @if (! $sale->isPaid())
            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-outline-primary btn-icon-action" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
        @endif
    @endcan
    @can('delete', $sale)
        @if (! $sale->isPaid())
            <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Hapus penjualan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-icon-action" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        @endif
    @endcan
</div>

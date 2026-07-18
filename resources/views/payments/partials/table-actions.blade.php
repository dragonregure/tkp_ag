<div class="table-actions" role="group">
    <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-secondary btn-icon-action" title="Detail">
        <i class="bi bi-eye"></i>
    </a>
    @can('update', $payment)
        <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline-primary btn-icon-action" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endcan
    @can('delete', $payment)
        <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('Hapus pembayaran ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-icon-action" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endcan
</div>

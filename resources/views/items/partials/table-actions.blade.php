<div class="table-actions" role="group">
    @can('update', $item)
        <a href="{{ route('items.edit', $item) }}" class="btn btn-outline-primary btn-icon-action" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endcan
    @can('delete', $item)
        <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Hapus item ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-icon-action" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endcan
</div>

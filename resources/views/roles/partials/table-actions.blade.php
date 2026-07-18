<div class="table-actions" role="group">
    @can('update', $role)
        <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-primary btn-icon-action" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endcan
    @can('delete', $role)
        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-icon-action" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endcan
</div>

<div class="table-actions" role="group">
    @can('update', $user)
        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-icon-action" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endcan
    @can('delete', $user)
        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-icon-action" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endcan
</div>

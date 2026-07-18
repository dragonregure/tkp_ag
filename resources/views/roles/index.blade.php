@extends('layouts.admin')

@section('title', 'Role')
@section('page_title', 'Role')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">List Role</h3>
            @can('create', \Spatie\Permission\Models\Role::class)
                <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm ms-auto">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Role
                </a>
            @endcan
        </div>
        <div class="card-body">
            <table id="roles-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>User</th>
                        <th>Permission</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#roles-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('roles.data') }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'users_count', name: 'users_count', searchable: false },
                { data: 'permissions_count', name: 'permissions_count', searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    </script>
@endpush

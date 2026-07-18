@extends('layouts.admin')

@section('title', 'User')
@section('page_title', 'User')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">List User</h3>
            @can('create', \App\Models\User::class)
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm ms-auto">
                    <i class="bi bi-plus-lg me-1"></i>Tambah User
                </a>
            @endcan
        </div>
        <div class="card-body">
            <table id="users-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('users.data') }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'roles_text', name: 'roles.name', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    </script>
@endpush

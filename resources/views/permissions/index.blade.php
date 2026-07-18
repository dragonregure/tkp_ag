@extends('layouts.admin')

@section('title', 'Permission')
@section('page_title', 'Permission')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">List Permission</h3>
        </div>
        <div class="card-body">
            <table id="permissions-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#permissions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('permissions.data') }}',
            order: [[0, 'asc'], [1, 'asc']],
            columns: [
                { data: 'module', name: 'name' },
                { data: 'name', name: 'name' },
                { data: 'label', name: 'name', orderable: false }
            ]
        });
    </script>
@endpush

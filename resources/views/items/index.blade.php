@extends('layouts.admin')

@section('title', 'Item')
@section('page_title', 'Item')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">List Item</h3>
            @can('create', \App\Models\Item::class)
                <a href="{{ route('items.create') }}" class="btn btn-primary btn-sm ms-auto">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Item
                </a>
            @endcan
        </div>
        <div class="card-body">
            <table id="items-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#items-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('items.data') }}',
            order: [[1, 'asc']],
            columns: [
                { data: 'image', name: 'image_path', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'price', name: 'price' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    </script>
@endpush

@extends('layouts.admin')

@section('title', 'Penjualan')
@section('page_title', 'Penjualan')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="start_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" id="end_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="button" id="filter-button" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">List Penjualan</h3>
            @can('create', \App\Models\Sale::class)
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm ms-auto">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Penjualan
                </a>
            @endcan
        </div>
        <div class="card-body">
            <table id="sales-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const salesTable = $('#sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('sales.data') }}',
                data: function (data) {
                    data.start_date = $('#start_date').val();
                    data.end_date = $('#end_date').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'code', name: 'code' },
                { data: 'sale_date', name: 'sale_date' },
                { data: 'items_count', name: 'items_count', searchable: false },
                { data: 'subtotal', name: 'subtotal' },
                { data: 'status_badge', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });

        $('#filter-button').on('click', function () {
            salesTable.ajax.reload();
        });
    </script>
@endpush

@extends('layouts.admin')

@section('title', 'Pembayaran')
@section('page_title', 'Pembayaran')

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
            <h3 class="card-title mb-0">List Pembayaran</h3>
            @can('create', \App\Models\Payment::class)
                <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm ms-auto">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Pembayaran
                </a>
            @endcan
        </div>
        <div class="card-body">
            <table id="payments-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Penjualan</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const paymentsTable = $('#payments-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('payments.data') }}',
                data: function (data) {
                    data.start_date = $('#start_date').val();
                    data.end_date = $('#end_date').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'code', name: 'code' },
                { data: 'payment_date', name: 'payment_date' },
                { data: 'sale_code', name: 'sale.code' },
                { data: 'amount', name: 'amount' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });

        $('#filter-button').on('click', function () {
            paymentsTable.ajax.reload();
        });
    </script>
@endpush

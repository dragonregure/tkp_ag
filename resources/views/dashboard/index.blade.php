@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    <form method="GET" class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        @include('dashboard.partials.widget', [
            'label' => 'Jumlah Transaksi',
            'value' => number_format($summary['transaction_count'], 0, ',', '.'),
            'icon' => 'bi bi-receipt',
            'color' => 'text-bg-primary',
        ])
        @include('dashboard.partials.widget', [
            'label' => 'Jumlah Penjualan',
            'value' => 'Rp '.number_format($summary['sales_amount'], 0, ',', '.'),
            'icon' => 'bi bi-cash-stack',
            'color' => 'text-bg-success',
        ])
        @include('dashboard.partials.widget', [
            'label' => 'Qty Item Terjual',
            'value' => number_format($summary['item_qty'], 0, ',', '.'),
            'icon' => 'bi bi-box',
            'color' => 'text-bg-warning',
        ])
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Penjualan per Bulan</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesAmountChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Qty Item per Item</h3>
                </div>
                <div class="card-body">
                    <canvas id="itemQtyChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const salesChart = @json($salesChart);
        const itemChart = @json($itemChart);

        new Chart(document.getElementById('salesAmountChart'), {
            type: 'bar',
            data: {
                labels: salesChart.map((row) => row.month),
                datasets: [{
                    label: 'Rupiah',
                    data: salesChart.map((row) => row.total),
                    backgroundColor: '#0d6efd'
                }]
            }
        });

        new Chart(document.getElementById('itemQtyChart'), {
            type: 'doughnut',
            data: {
                labels: itemChart.map((row) => row.name),
                datasets: [{
                    data: itemChart.map((row) => row.qty),
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#0dcaf0', '#6610f2', '#adb5bd']
                }]
            }
        });
    </script>
@endpush

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $sale->code }}</h3>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-5">Tanggal</dt>
            <dd class="col-7">{{ $sale->sale_date->format('d/m/Y') }}</dd>
            <dt class="col-5">Status</dt>
            <dd class="col-7">@include('sales.partials.status-badge')</dd>
            <dt class="col-5">Total</dt>
            <dd class="col-7">Rp {{ number_format((float) $sale->subtotal, 0, ',', '.') }}</dd>
            <dt class="col-5">Dibayar</dt>
            <dd class="col-7">Rp {{ number_format((float) $sale->paid_amount, 0, ',', '.') }}</dd>
            <dt class="col-5">Sisa</dt>
            <dd class="col-7">Rp {{ number_format((float) $sale->remaining_amount, 0, ',', '.') }}</dd>
        </dl>
    </div>
    <div class="card-footer d-flex gap-2">
        @if (! $sale->isPaid())
            @can('update', $sale)
                <a href="{{ route('sales.edit', $sale) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            @endcan
        @endif
        <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

@extends('layouts.admin')

@section('title', 'Detail Pembayaran')
@section('page_title', 'Detail Pembayaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $payment->code }}</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-md-3">Tanggal</dt>
                <dd class="col-md-9">{{ $payment->payment_date->format('d/m/Y') }}</dd>
                <dt class="col-md-3">Penjualan</dt>
                <dd class="col-md-9">
                    <a href="{{ route('sales.show', $payment->sale) }}">{{ $payment->sale->code }}</a>
                </dd>
                <dt class="col-md-3">Nominal</dt>
                <dd class="col-md-9">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</dd>
                <dt class="col-md-3">Catatan</dt>
                <dd class="col-md-9">{{ $payment->note ?: '-' }}</dd>
            </dl>
        </div>
        <div class="card-footer d-flex gap-2">
            @can('update', $payment)
                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            @endcan
            <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
@endsection

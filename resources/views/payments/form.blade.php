@extends('layouts.admin')

@section('title', $payment->exists ? 'Edit Pembayaran' : 'Tambah Pembayaran')
@section('page_title', $payment->exists ? 'Edit Pembayaran' : 'Tambah Pembayaran')

@section('content')
    <form method="POST" action="{{ $payment->exists ? route('payments.update', $payment) : route('payments.store') }}" class="card">
        @csrf
        @if ($payment->exists)
            @method('PUT')
        @endif

        <div class="card-body">
            @include('payments.partials.fields')
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Simpan
            </button>
            <a href="{{ $payment->exists ? route('payments.show', $payment) : route('payments.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('#sale_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih penjualan',
            ajax: {
                url: '{{ route('sales.select2') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        term: params.term,
                        page: params.page || 1,
                    };
                },
                processResults: function (data) {
                    return data;
                },
            },
        });
    </script>
@endpush

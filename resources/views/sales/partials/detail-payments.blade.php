<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pembayaran</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sale->payments as $payment)
                    <tr>
                        <td>{{ $payment->code }}</td>
                        <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                        <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Belum ada pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

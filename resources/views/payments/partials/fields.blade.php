<div class="row g-3">
    <div class="col-md-6">
        <label for="sale_id" class="form-label">Penjualan</label>
        <select id="sale_id" name="sale_id" class="form-select" @disabled($payment->exists) @required(! $payment->exists)>
            @php($selectedSaleId = old('sale_id', $payment->sale_id))
            @if ($selectedSale !== null)
                <option value="{{ $selectedSale->id }}" selected>
                    {{ $selectedSale->code }} - Sisa Rp {{ number_format((float) $selectedSale->remaining_amount, 0, ',', '.') }}
                </option>
            @elseif ($selectedSaleId)
                <option value="{{ $selectedSaleId }}" selected>Penjualan #{{ $selectedSaleId }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-3">
        <label for="payment_date" class="form-label">Tanggal Pembayaran</label>
        <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', optional($payment->payment_date)->toDateString() ?? now()->toDateString()) }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label for="amount" class="form-label">Nominal</label>
        <input type="number" id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" class="form-control" min="0.01" step="0.01" required>
    </div>
    <div class="col-12">
        <label for="note" class="form-label">Catatan</label>
        <textarea id="note" name="note" class="form-control" rows="3">{{ old('note', $payment->note) }}</textarea>
    </div>
</div>

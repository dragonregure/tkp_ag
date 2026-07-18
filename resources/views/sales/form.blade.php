@extends('layouts.admin')

@section('title', $sale->exists ? 'Edit Penjualan' : 'Tambah Penjualan')
@section('page_title', $sale->exists ? 'Edit Penjualan' : 'Tambah Penjualan')

@section('content')
    <form method="POST" action="{{ $sale->exists ? route('sales.update', $sale) : route('sales.store') }}" class="card" id="sale-form">
        @csrf
        @if ($sale->exists)
            @method('PUT')
        @endif

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="sale_date" class="form-label">Tanggal Penjualan</label>
                    <input type="date" id="sale_date" name="sale_date" value="{{ old('sale_date', optional($sale->sale_date)->toDateString() ?? now()->toDateString()) }}" class="form-control" required>
                </div>
            </div>

            @include('sales.partials.items-table')
        </div>

        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Simpan
            </button>
            <a href="{{ $sale->exists ? route('sales.show', $sale) : route('sales.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const itemSelectUrl = '{{ route('items.select2') }}';

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function refreshRowIndexes() {
            $('#sale-items tbody tr').each(function (index) {
                $(this).find('[name]').each(function () {
                    this.name = this.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
            });
        }

        function recalcRow(row) {
            const qty = Number($(row).find('.item-qty').val() || 0);
            const price = Number($(row).find('.item-price').val() || 0);
            $(row).find('.item-total').val(qty * price);
        }

        function selectedItemOption(row) {
            if (!row.item_id) {
                return '';
            }

            const text = row.item_text || ('Item #' + row.item_id);
            const price = row.price || '';

            return `<option value="${escapeHtml(row.item_id)}" data-price="${escapeHtml(price)}" selected>${escapeHtml(text)}</option>`;
        }

        function initItemSelect(select) {
            $(select).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih item',
                ajax: {
                    url: itemSelectUrl,
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
        }

        function addRow(row = {}) {
            const index = $('#sale-items tbody tr').length;
            $('#sale-items tbody').append(`
                <tr>
                    <td><select name="items[${index}][item_id]" class="form-select item-select" required>${selectedItemOption(row)}</select></td>
                    <td><input type="number" name="items[${index}][qty]" class="form-control item-qty" min="1" value="${row.qty || 1}" required></td>
                    <td><input type="number" name="items[${index}][price]" class="form-control item-price" min="0" step="0.01" value="${row.price || ''}" required></td>
                    <td><input type="number" class="form-control item-total" readonly></td>
                    <td><button type="button" class="btn btn-outline-danger btn-icon-action remove-row"><i class="bi bi-trash"></i></button></td>
                </tr>
            `);
            const tr = $('#sale-items tbody tr').last();
            initItemSelect(tr.find('.item-select'));
            if (!row.price) {
                tr.find('.item-price').val(tr.find('.item-select option:selected').data('price'));
            }
            recalcRow(tr);
        }

        $('#add-row').on('click', function () {
            addRow();
        });

        $('#sale-items').on('select2:select', '.item-select', function (event) {
            const row = $(this).closest('tr');
            const selected = event.params.data;
            $(this).find('option:selected').attr('data-price', selected.price);
            row.find('.item-price').val(selected.price);
            recalcRow(row);
        });

        $('#sale-items').on('change', '.item-select', function () {
            const price = $(this).find('option:selected').data('price');

            if (price !== undefined) {
                const row = $(this).closest('tr');
                row.find('.item-price').val(price);
                recalcRow(row);
            }
        });

        $('#sale-items').on('input', '.item-qty, .item-price', function () {
            recalcRow($(this).closest('tr'));
        });

        $('#sale-items').on('click', '.remove-row', function () {
            if ($('#sale-items tbody tr').length > 1) {
                $(this).closest('tr').remove();
                refreshRowIndexes();
            }
        });

        const existingRows = {{ Illuminate\Support\Js::from(old('items', $saleItemRows)) }};
        (existingRows.length ? existingRows : [{}]).forEach(addRow);
    </script>
@endpush

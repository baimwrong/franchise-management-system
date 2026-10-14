@extends('layouts.app')

@section('title', 'Point-of-Sale Register')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" class="text-decoration-none">Sales</a></li>
                <li class="breadcrumb-item active">New Order</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Point-of-Sale (POS) Register</h3>
        <p class="text-muted small">Record an in-store order transaction, print customer receipt, and deplete inventory.</p>
    </div>

    <form action="{{ route('sales.store') }}" method="POST" id="posForm">
        @csrf
        <div class="row g-4">
            <!-- Left: Order Builder -->
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Serving Outlet Store <span class="text-danger">*</span></label>
                                <select name="outlet_id" id="outletSelect" class="form-select" required onchange="updateTaxRate(this)">
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" data-tax="{{ $outlet->tax_rate_percentage }}" {{ ($currentOutlet && $currentOutlet->id == $outlet->id) ? 'selected' : '' }}>
                                            {{ $outlet->name }} (Tax: {{ $outlet->tax_rate_percentage }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="credit_card">Credit / Debit Card</option>
                                    <option value="cash">Cash Tender</option>
                                    <option value="qris">QRIS / Digital Wallet</option>
                                    <option value="bank_transfer">Bank Wire / Corporate Account</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Customer / Guest Name</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Walk-in Guest" value="Walk-in Guest">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Customer Mobile / Loyalty Phone</label>
                                <input type="text" name="customer_phone" class="form-control" placeholder="+1-555-0199">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="fw-bold text-dark mb-0">Ordered Items</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addPosRowBtn">
                                <i class="bi bi-plus-lg me-1"></i> Add Item
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle small mb-0" id="posTable">
                                <thead class="table-light text-muted text-uppercase">
                                    <tr>
                                        <th style="width: 50%;">Product Item</th>
                                        <th style="width: 20%;">Price</th>
                                        <th style="width: 15%;">Qty</th>
                                        <th style="width: 15%;" class="text-end">Subtotal</th>
                                        <th style="width: 5%;" class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="posItemsBody">
                                    <tr class="pos-row">
                                        <td>
                                            <select name="items[0][product_id]" class="form-select form-select-sm pos-product-select" required onchange="calculateLine(this)">
                                                <option value="">-- Choose Item --</option>
                                                @foreach($products as $prod)
                                                    <option value="{{ $prod->id }}" data-price="{{ $prod->suggested_retail_price }}">
                                                        {{ $prod->name }} [${{ number_format($prod->suggested_retail_price, 2) }}]
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm bg-light line-price" value="$0.00" readonly>
                                        </td>
                                        <td>
                                            <input type="number" step="1" min="1" name="items[0][quantity]" class="form-control form-control-sm line-qty" value="1" required oninput="calculateTotal()">
                                        </td>
                                        <td class="text-end fw-bold line-subtotal">$0.00</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 pos-remove-btn" onclick="removePosRow(this)" disabled>&times;</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary Slip -->
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 5rem;">
                    <div class="card-header bg-light">
                        <span class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-receipt"></i> POS Receipt Summary
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Items Subtotal:</span>
                            <span class="fw-bold" id="displaySubtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Tax (<span id="taxRateLabel">10.0</span>%):</span>
                            <span class="fw-bold" id="displayTax">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 small">
                            <span class="text-muted">Discount:</span>
                            <span class="text-muted">$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5 text-dark">Amount Due:</span>
                            <span class="fw-bold fs-4 text-primary" id="displayTotal">$0.00</span>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm py-2">
                            <i class="bi bi-check2-circle me-1"></i> Complete & Print Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let posRowIdx = 1;
    let currentTaxRate = 10.0;

    function updateTaxRate(selectElem) {
        const option = selectElem.options[selectElem.selectedIndex];
        currentTaxRate = parseFloat(option.getAttribute('data-tax') || 10.0);
        document.getElementById('taxRateLabel').innerText = currentTaxRate.toFixed(2);
        calculateTotal();
    }

    function calculateLine(selectElem) {
        const row = selectElem.closest('tr');
        const price = parseFloat(selectElem.options[selectElem.selectedIndex].getAttribute('data-price') || 0);
        row.querySelector('.line-price').value = '$' + price.toFixed(2);
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.pos-row').forEach(row => {
            const select = row.querySelector('.pos-product-select');
            const price = parseFloat(select.options[select.selectedIndex]?.getAttribute('data-price') || 0);
            const qty = parseFloat(row.querySelector('.line-qty').value || 0);
            const lineTotal = price * qty;
            row.querySelector('.line-subtotal').innerText = '$' + lineTotal.toFixed(2);
            subtotal += lineTotal;
        });

        const tax = (subtotal * currentTaxRate) / 100;
        const total = subtotal + tax;

        document.getElementById('displaySubtotal').innerText = '$' + subtotal.toFixed(2);
        document.getElementById('displayTax').innerText = '$' + tax.toFixed(2);
        document.getElementById('displayTotal').innerText = '$' + total.toFixed(2);
    }

    document.getElementById('addPosRowBtn').addEventListener('click', function () {
        const tbody = document.getElementById('posItemsBody');
        const firstRow = tbody.querySelector('.pos-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelector('.pos-product-select').name = `items[${posRowIdx}][product_id]`;
        newRow.querySelector('.pos-product-select').selectedIndex = 0;
        newRow.querySelector('.line-price').value = '$0.00';
        newRow.querySelector('.line-qty').name = `items[${posRowIdx}][quantity]`;
        newRow.querySelector('.line-qty').value = '1';
        newRow.querySelector('.line-subtotal').innerText = '$0.00';
        newRow.querySelector('.pos-remove-btn').removeAttribute('disabled');

        tbody.appendChild(newRow);
        posRowIdx++;

        if (tbody.querySelectorAll('.pos-row').length > 1) {
            tbody.querySelectorAll('.pos-remove-btn').forEach(b => b.removeAttribute('disabled'));
        }
    });

    function removePosRow(btn) {
        const tbody = document.getElementById('posItemsBody');
        if (tbody.querySelectorAll('.pos-row').length > 1) {
            btn.closest('tr').remove();
            calculateTotal();
        }
        if (tbody.querySelectorAll('.pos-row').length === 1) {
            tbody.querySelector('.pos-remove-btn').setAttribute('disabled', 'disabled');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const outletSel = document.getElementById('outletSelect');
        if (outletSel) updateTaxRate(outletSel);
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Requisition Supplies from HQ')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('supply-orders.index') }}" class="text-decoration-none">Supply Orders</a></li>
                <li class="breadcrumb-item active">New Requisition</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Submit Central Supply Requisition</h3>
        <p class="text-muted small">Place a wholesale inventory order from Corporate Headquarters Warehouse.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('supply-orders.store') }}" method="POST" id="supplyOrderForm">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Destination Outlet Store <span class="text-danger">*</span></label>
                        <select name="outlet_id" class="form-select" required>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->outlet_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Requisition Notes / Logistics Remarks</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Expedited delivery requested for weekend rush">
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                    <span>Requisition Line Items</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add Another Item
                    </button>
                </h5>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle small mb-0" id="itemsTable">
                        <thead class="table-light text-muted text-uppercase">
                            <tr>
                                <th style="width: 55%;">Product SKU / Name</th>
                                <th style="width: 20%;">Wholesale Unit Cost</th>
                                <th style="width: 15%;">Quantity</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][product_id]" class="form-select form-select-sm product-select" required onchange="updatePrice(this)">
                                        <option value="">-- Choose Product --</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}">
                                                {{ $prod->name }} [{{ $prod->sku }}] ({{ $prod->unit_of_measure }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm bg-light cost-display" value="$0.00" readonly>
                                </td>
                                <td>
                                    <input type="number" step="1" min="1" name="items[0][quantity]" class="form-control form-control-sm" value="10" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-btn" onclick="removeRow(this)" disabled>
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('supply-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-send me-1"></i> Dispatch Requisition to HQ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = 1;

    function updatePrice(selectElem) {
        const cost = selectElem.options[selectElem.selectedIndex].getAttribute('data-cost') || 0;
        const row = selectElem.closest('tr');
        row.querySelector('.cost-display').value = '$' + parseFloat(cost).toFixed(2);
    }

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const tbody = document.getElementById('itemsBody');
        const firstRow = tbody.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelector('.product-select').name = `items[${rowIndex}][product_id]`;
        newRow.querySelector('.product-select').selectedIndex = 0;
        newRow.querySelector('.cost-display').value = '$0.00';
        newRow.querySelector('input[type="number"]').name = `items[${rowIndex}][quantity]`;
        newRow.querySelector('input[type="number"]').value = '10';
        newRow.querySelector('.remove-btn').removeAttribute('disabled');

        tbody.appendChild(newRow);
        rowIndex++;

        // Enable first remove btn if more than 1 row
        if (tbody.querySelectorAll('.item-row').length > 1) {
            tbody.querySelectorAll('.remove-btn').forEach(btn => btn.removeAttribute('disabled'));
        }
    });

    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.querySelectorAll('.item-row').length > 1) {
            btn.closest('tr').remove();
        }
        if (tbody.querySelectorAll('.item-row').length === 1) {
            tbody.querySelector('.remove-btn').setAttribute('disabled', 'disabled');
        }
    }
</script>
@endpush

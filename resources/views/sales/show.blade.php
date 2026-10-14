@extends('layouts.app')

@section('title', 'Receipt ' . $salesTransaction->transaction_code)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" class="text-decoration-none">Sales</a></li>
                    <li class="breadcrumb-item active">{{ $salesTransaction->transaction_code }}</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">Transaction Receipt</h3>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Thermal Receipt
            </button>
            <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> New Transaction
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border p-4">
                <div class="text-center pb-3 border-bottom mb-3">
                    <div class="fs-4 fw-bold text-dark">{{ $salesTransaction->outlet->brand->name ?? 'OmniBrew' }}</div>
                    <div class="small fw-semibold text-muted">{{ $salesTransaction->outlet->name }} ({{ $salesTransaction->outlet->outlet_code }})</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ $salesTransaction->outlet->address }}, {{ $salesTransaction->outlet->city }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Tel: {{ $salesTransaction->outlet->phone }}</div>
                </div>

                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Receipt Code:</span>
                    <strong class="text-dark">{{ $salesTransaction->transaction_code }}</strong>
                </div>
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Date & Time:</span>
                    <span>{{ $salesTransaction->transaction_date->format('Y-m-d H:i:s') }}</span>
                </div>
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Cashier / Terminal:</span>
                    <span>{{ $salesTransaction->user->name ?? 'Store Cashier' }}</span>
                </div>
                <div class="d-flex justify-content-between small text-muted mb-3">
                    <span>Customer:</span>
                    <span>{{ $salesTransaction->customer_name ?? 'Guest' }}</span>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-sm small mb-0">
                        <thead class="border-top border-bottom text-uppercase" style="font-size: 0.7rem;">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesTransaction->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                        <small class="text-muted">{{ $item->sku }}</small>
                                    </td>
                                    <td class="text-center align-middle">{{ number_format($item->quantity, 0) }}</td>
                                    <td class="text-end align-middle">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end align-middle fw-semibold">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-top pt-2 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal:</span>
                        <span>${{ number_format($salesTransaction->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Sales Tax ({{ $salesTransaction->outlet->tax_rate_percentage ?? 10 }}%):</span>
                        <span>${{ number_format($salesTransaction->tax_amount, 2) }}</span>
                    </div>
                    @if($salesTransaction->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-1 text-danger">
                        <span>Discount:</span>
                        <span>-${{ number_format($salesTransaction->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-5 text-dark border-top pt-2 mt-2">
                        <span>TOTAL PAID:</span>
                        <span class="text-primary">${{ number_format($salesTransaction->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 0.75rem;">
                        <span>Tender: {{ strtoupper(str_replace('_', ' ', $salesTransaction->payment_method)) }}</span>
                        <span>Status: {{ strtoupper($salesTransaction->payment_status) }}</span>
                    </div>
                </div>

                <div class="text-center border-top pt-3 mt-3 text-muted small">
                    <p class="mb-1 fw-semibold">Thank you for visiting {{ $salesTransaction->outlet->brand->name ?? 'OmniBrew' }}!</p>
                    <p class="mb-0" style="font-size: 0.7rem;">Please retain this receipt for warranty and customer support.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

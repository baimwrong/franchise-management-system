@extends('layouts.app')

@section('title', 'Sales & POS Ledger')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Sales & POS Transactions</h3>
            <p class="text-muted small mb-0">Consolidated point-of-sale transaction ledger across all brand outlets.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.bulk-imports.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> <span>Bulk Import Sales</span>
            </a>
            @endif
            <a href="{{ route('sales.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cart-plus"></i> <span>New POS Sale</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar & Summary Banner -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('sales.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by transaction code or guest..." value="{{ request('search') }}">
                    </div>
                </div>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isFranchiseOwner())
                <div class="col-6 col-md-3">
                    <select name="outlet_id" class="form-select form-select-sm">
                        <option value="">-- All Outlets --</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}" {{ ($selectedOutletId == $outlet->id) ? 'selected' : '' }}>
                                {{ $outlet->name }} ({{ $outlet->outlet_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-6 col-md-3">
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">-- Payment Method --</option>
                        <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS / Mobile Pay</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['search', 'outlet_id', 'payment_method']))
                        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-receipt text-success"></i> Transaction Journal
            </span>
            <div class="small fw-semibold text-dark">
                Filtered Revenue: <span class="text-success fs-6">${{ number_format($periodSalesTotal, 2) }}</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>Transaction Code</th>
                        <th>Store Location</th>
                        <th>Cashier / Staff</th>
                        <th>Customer</th>
                        <th>Items Count</th>
                        <th>Payment</th>
                        <th>Timestamp</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $trx) }}" class="fw-bold text-decoration-none">
                                    {{ $trx->transaction_code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $trx->outlet->name ?? 'Store' }}</div>
                                <small class="text-muted">{{ $trx->outlet->outlet_code ?? '' }}</small>
                            </td>
                            <td>{{ $trx->user->name ?? 'Cashier' }}</td>
                            <td>{{ $trx->customer_name ?? 'Walk-in Guest' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $trx->items_count }} Items</span></td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $trx->payment_method) }}
                                </span>
                            </td>
                            <td>{{ $trx->transaction_date->format('M d, Y H:i:s') }}</td>
                            <td class="text-end fw-bold text-dark">${{ number_format($trx->total_amount, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $trx) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-receipt-cutoff"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-receipt-cutoff fs-1 text-muted mb-2 d-block"></i>
                                No sales transactions match this criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

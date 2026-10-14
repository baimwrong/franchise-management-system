@extends('layouts.app')

@section('title', 'Stock Adjustments Audit Trail')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
                    <li class="breadcrumb-item active">Audit Log</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">Stock Adjustments & Loss Audit History</h3>
            <p class="text-muted small">Accountability record for inventory corrections, damages, spoilage, and returns.</p>
        </div>
        <div>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>Reference</th>
                        <th>Outlet</th>
                        <th>Authorized By</th>
                        <th>Type</th>
                        <th>Product Items</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        <tr>
                            <td class="fw-bold text-dark">{{ $adj->reference_code }}</td>
                            <td>{{ $adj->outlet->name ?? 'Outlet' }}</td>
                            <td>{{ $adj->user->name ?? 'Staff' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border text-capitalize">
                                    {{ str_replace('_', ' ', $adj->type) }}
                                </span>
                            </td>
                            <td>
                                @foreach($adj->items as $item)
                                    <div>
                                        <strong>{{ $item->product->name ?? 'Product' }}</strong>:
                                        <span class="{{ $item->adjustment_quantity > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                            {{ $item->adjustment_quantity > 0 ? '+' : '' }}{{ $item->adjustment_quantity }}
                                        </span>
                                        <span class="text-muted">({{ $item->previous_quantity }} &rarr; {{ $item->new_quantity }})</span>
                                    </div>
                                @endforeach
                                @if($adj->reason)
                                    <small class="text-muted fst-italic">Note: {{ $adj->reason }}</small>
                                @endif
                            </td>
                            <td>{{ $adj->adjustment_date->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No stock adjustment audits logged yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $adjustments->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Supply Requisitions')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Central Supply Requisitions</h3>
            <p class="text-muted small mb-0">Store-to-HQ warehouse supply ordering, batch fulfillment, and replenishment workflow.</p>
        </div>
        <div>
            <a href="{{ route('supply-orders.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-circle"></i> <span>Order Supplies from HQ</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('supply-orders.index') }}" method="GET" class="row g-2 align-items-center">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isFranchiseOwner())
                <div class="col-12 col-md-5">
                    <select name="outlet_id" class="form-select form-select-sm">
                        <option value="">-- All Requesting Outlets --</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}" {{ ($selectedOutletId == $outlet->id) ? 'selected' : '' }}>
                                {{ $outlet->name }} ({{ $outlet->outlet_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-6 col-md-4">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- All Fulfillment Statuses --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending HQ Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing in Warehouse</option>
                        <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched / En Route</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Delivered & Received</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['outlet_id', 'status']))
                        <a href="{{ route('supply-orders.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Requisitions List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>Order Number</th>
                        <th>Outlet Store</th>
                        <th>Requested By</th>
                        <th>Line Items</th>
                        <th>Status</th>
                        <th>Submission Date</th>
                        <th class="text-end">Wholesale Value</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('supply-orders.show', $order) }}" class="fw-bold text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->outlet->name ?? 'Outlet' }}</div>
                                <small class="text-muted">{{ $order->outlet->outlet_code ?? '' }}</small>
                            </td>
                            <td>{{ $order->requestedBy->name ?? 'Staff' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $order->items_count }} Items</span></td>
                            <td>
                                <span class="badge {{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-end fw-bold text-dark">${{ number_format($order->total_amount, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('supply-orders.show', $order) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-truck fs-1 text-muted mb-2 d-block"></i>
                                No supply requisition orders recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

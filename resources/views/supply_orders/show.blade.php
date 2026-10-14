@extends('layouts.app')

@section('title', 'Order ' . $supplyOrder->order_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('supply-orders.index') }}" class="text-decoration-none">Supply Orders</a></li>
                    <li class="breadcrumb-item active">{{ $supplyOrder->order_number }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <h3 class="fw-bold mb-0 text-dark">Requisition #{{ $supplyOrder->order_number }}</h3>
                <span class="badge {{ $supplyOrder->status_badge_class }}">{{ strtoupper($supplyOrder->status) }}</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Requested by: <strong>{{ $supplyOrder->requestedBy->name ?? 'Staff' }}</strong> &bull;
                Destination Outlet: <strong>{{ $supplyOrder->outlet->name ?? 'Outlet' }}</strong> ({{ $supplyOrder->outlet->outlet_code ?? '' }}) &bull;
                Date: {{ $supplyOrder->created_at->format('M d, Y H:i') }}
            </p>
        </div>

        <!-- Workflow Action Form -->
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin() && $supplyOrder->status === 'pending')
                <form action="{{ route('supply-orders.update-status', $supplyOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle"></i> <span>Approve Requisition</span>
                    </button>
                </form>
                <form action="{{ route('supply-orders.update-status', $supplyOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                </form>
            @elseif(auth()->user()->isSuperAdmin() && $supplyOrder->status === 'approved')
                <form action="{{ route('supply-orders.update-status', $supplyOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="dispatched">
                    <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                        <i class="bi bi-truck"></i> <span>Mark Dispatched from Warehouse</span>
                    </button>
                </form>
            @elseif($supplyOrder->status === 'dispatched' && auth()->user()->canManageOutlet($supplyOrder->outlet_id))
                <form action="{{ route('supply-orders.update-status', $supplyOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="received">
                    <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-box-seam-fill"></i> <span>Confirm Shipment Received & Update Stock</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Requisition Items Card -->
    <div class="card mb-4">
        <div class="card-header">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-box-seam text-primary"></i> Requisition Items & Quantities
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th class="text-end">Requested</th>
                        <th class="text-end">Fulfilled</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Line Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplyOrder->items as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->product->sku ?? 'N/A' }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->product->name ?? 'Product' }}</div>
                                <small class="text-muted">{{ $item->product->unit_of_measure ?? 'unit' }}</small>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($item->quantity_requested, 0) }}</td>
                            <td class="text-end">{{ number_format($item->quantity_fulfilled, 0) }}</td>
                            <td class="text-end text-muted">${{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end fw-bold text-dark">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold text-uppercase">Total Wholesale Cost:</td>
                        <td class="text-end fw-bold fs-6 text-primary">${{ number_format($supplyOrder->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Tracking Timeline Card -->
    <div class="card">
        <div class="card-header">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-info"></i> Fulfillment Status Tracking
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-12 col-md-3">
                    <div class="text-muted fw-semibold">Order Placed:</div>
                    <div>{{ $supplyOrder->created_at->format('M d, Y H:i:s') }}</div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="text-muted fw-semibold">HQ Approved:</div>
                    <div>{{ $supplyOrder->approved_at ? $supplyOrder->approved_at->format('M d, Y H:i:s') : 'Pending approval' }}</div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="text-muted fw-semibold">Warehouse Dispatched:</div>
                    <div>{{ $supplyOrder->dispatched_at ? $supplyOrder->dispatched_at->format('M d, Y H:i:s') : 'Not yet dispatched' }}</div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="text-muted fw-semibold">Store Received:</div>
                    <div>{{ $supplyOrder->received_at ? $supplyOrder->received_at->format('M d, Y H:i:s') : 'In transit / Pending delivery' }}</div>
                </div>
            </div>
            @if($supplyOrder->notes)
                <div class="mt-3 pt-3 border-top small">
                    <strong class="text-dark">Requisition Notes:</strong> {{ $supplyOrder->notes }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

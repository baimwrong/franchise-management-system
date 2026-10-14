@extends('layouts.app')

@section('title', 'Multi-Outlet Inventory')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Multi-Outlet Inventory Matrix</h3>
            <p class="text-muted small mb-0">Monitor real-time stock levels, reorder thresholds, and trigger manual stock adjustments.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.adjustments') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-clock-history"></i> <span>Audit Log</span>
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.bulk-imports.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> <span>Bulk Import Stock</span>
            </a>
            @endif
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                <i class="bi bi-sliders"></i> <span>Adjust Stock</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('inventory.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search product name or SKU..." value="{{ request('search') }}">
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

                <div class="col-6 col-md-2">
                    <div class="form-check pt-1">
                        <input class="form-check-input" type="checkbox" name="low_stock_only" id="lowStockOnly" value="1" {{ request('low_stock_only') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label small fw-semibold text-danger" for="lowStockOnly">
                            Low Stock Only ({{ $lowStockTotal }})
                        </label>
                    </div>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['search', 'outlet_id', 'low_stock_only']))
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>Store Location</th>
                        <th>SKU</th>
                        <th>Product Description</th>
                        <th>Category</th>
                        <th class="text-end">On Hand Qty</th>
                        <th class="text-end">Reorder Point</th>
                        <th class="text-end">Target Par</th>
                        <th class="text-center">Status</th>
                        <th>Last Restocked</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td>
                                <a href="{{ route('outlets.show', $stock->outlet) }}" class="fw-semibold text-decoration-none text-dark">
                                    {{ $stock->outlet->name ?? 'N/A' }}
                                </a>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $stock->outlet->outlet_code ?? '' }}</div>
                            </td>
                            <td class="fw-bold">{{ $stock->product->sku ?? '' }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $stock->product->name ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $stock->product->category->name ?? 'Menu' }}
                                </span>
                            </td>
                            <td class="text-end fw-bold {{ $stock->is_low_stock ? 'text-danger' : 'text-dark' }}">
                                {{ number_format($stock->quantity_on_hand, 2) }} {{ $stock->product->unit_of_measure ?? 'units' }}
                            </td>
                            <td class="text-end text-muted">{{ number_format($stock->reorder_threshold, 2) }}</td>
                            <td class="text-end text-muted">{{ number_format($stock->target_stock_level, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $stock->stock_badge_class }}">
                                    {{ $stock->stock_status }}
                                </span>
                            </td>
                            <td class="text-muted">
                                {{ $stock->last_restocked_at ? $stock->last_restocked_at->diffForHumans() : 'Never' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-boxes fs-1 text-muted mb-2 d-block"></i>
                                No inventory stock records found matching filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $stocks->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal: Adjust Stock -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.adjust') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="adjustStockModalLabel">Adjust Store Stock Level</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Outlet <span class="text-danger">*</span></label>
                        <select name="outlet_id" class="form-select" required>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ $selectedOutletId == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }} ({{ $outlet->outlet_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Product SKU / Line <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select" required>
                            @foreach(\App\Models\Product::where('is_active', true)->orderBy('name')->get() as $prod)
                                <option value="{{ $prod->id }}">
                                    {{ $prod->name }} [{{ $prod->sku }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                            <select name="adjustment_type" class="form-select" required>
                                <option value="audit_correction">Audit Physical Count</option>
                                <option value="damage">Damaged / Broken Goods (-)</option>
                                <option value="waste">Expired / Spoilage (-)</option>
                                <option value="initial_stock">Initial Setup Stock (+)</option>
                                <option value="return">Customer Return (+)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Quantity Delta <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="adjustment_quantity" class="form-control" placeholder="e.g. +20 or -5" required>
                            <small class="text-muted" style="font-size: 0.7rem;">Use negative (-) for waste/loss</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Audit Reason / Discrepancy Notes</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Broken during shelf restock or monthly physical count discrepancy"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Stock Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

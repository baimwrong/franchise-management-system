@extends('layouts.app')

@section('title', $outlet->name)

@section('content')
<div class="container-fluid p-0">
    <!-- Header with breadcrumbs & actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('outlets.index') }}" class="text-decoration-none">Outlets</a></li>
                    <li class="breadcrumb-item active">{{ $outlet->outlet_code }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <h3 class="fw-bold mb-0 text-dark">{{ $outlet->name }}</h3>
                <span class="badge {{ $outlet->status_badge_class }}">{{ strtoupper($outlet->status) }}</span>
                <span class="badge bg-secondary bg-opacity-25 text-secondary text-capitalize">{{ str_replace('_', ' ', $outlet->type) }}</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $outlet->address }}, {{ $outlet->city }}, {{ $outlet->state_province }} {{ $outlet->postal_code }} &bull;
                <i class="bi bi-envelope me-1"></i> {{ $outlet->email }} &bull;
                <i class="bi bi-telephone me-1"></i> {{ $outlet->phone }}
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('sales.create', ['outlet_id' => $outlet->id]) }}" class="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cart-plus"></i> <span>POS Terminal</span>
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('outlets.edit', $outlet) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-pencil"></i> <span>Edit Outlet</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Outlet Key Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Month Gross Sales</div>
                    <h4 class="fw-bold my-1 text-dark">${{ number_format($monthGrossSales, 2) }}</h4>
                    <small class="text-muted">Tax Rate: {{ $outlet->tax_rate_percentage }}%</small>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Licensee Operator</div>
                    <div class="fw-bold my-1 text-dark text-truncate" style="max-width: 140px;">{{ $outlet->franchisee_name }}</div>
                    <small class="text-muted">Region: {{ $outlet->region->name ?? 'HQ Central' }}</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Low Stock Alerts</div>
                    <h4 class="fw-bold my-1 {{ $lowStocks->count() > 0 ? 'text-danger' : 'text-dark' }}">{{ $lowStocks->count() }}</h4>
                    <small class="text-muted">Total SKUs in store</small>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Staff Onboarding</div>
                    <h4 class="fw-bold my-1 text-dark">{{ $outlet->users->count() }}</h4>
                    <small class="text-muted">Active credentials</small>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabbed Detailed Panels -->
    <div class="card mb-4">
        <div class="card-header border-bottom-0 pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="outletDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab">
                        <i class="bi bi-receipt me-1"></i> Sales Ledger ({{ $recentSales->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                        <i class="bi bi-box-seam me-1"></i> Low Stock Alerts ({{ $lowStocks->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="supply-tab" data-bs-toggle="tab" data-bs-target="#supply" type="button" role="tab">
                        <i class="bi bi-truck me-1"></i> Supply Orders ({{ $recentSupplyOrders->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="royalties-tab" data-bs-toggle="tab" data-bs-target="#royalties" type="button" role="tab">
                        <i class="bi bi-cash-stack me-1"></i> Royalties ({{ $royaltyInvoices->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="audits-tab" data-bs-toggle="tab" data-bs-target="#audits" type="button" role="tab">
                        <i class="bi bi-clipboard-check me-1"></i> Quality Audits ({{ $recentAudits->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab">
                        <i class="bi bi-people me-1"></i> Store Staff ({{ $outlet->users->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="outletDetailTabsContent">
                <!-- Tab 1: Sales -->
                <div class="tab-pane fade show active p-3" id="sales" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted text-uppercase">
                                <tr>
                                    <th>Transaction Code</th>
                                    <th>Customer</th>
                                    <th>Payment Method</th>
                                    <th>Time</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ route('sales.show', $sale) }}" class="fw-semibold text-decoration-none">
                                                {{ $sale->transaction_code }}
                                            </a>
                                        </td>
                                        <td>{{ $sale->customer_name ?? 'Walk-in Guest' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ strtoupper($sale->payment_method) }}</span></td>
                                        <td>{{ $sale->transaction_date->format('M d, Y H:i') }}</td>
                                        <td class="text-end fw-bold">${{ number_format($sale->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No sales transactions recorded for this outlet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Low Stock -->
                <div class="tab-pane fade p-3" id="inventory" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted text-uppercase">
                                <tr>
                                    <th>SKU</th>
                                    <th>Product</th>
                                    <th>Quantity On Hand</th>
                                    <th>Reorder Point</th>
                                    <th>Target Level</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStocks as $stock)
                                    <tr>
                                        <td class="fw-bold">{{ $stock->product->sku ?? '' }}</td>
                                        <td>{{ $stock->product->name ?? '' }}</td>
                                        <td class="fw-bold text-danger">{{ $stock->quantity_on_hand }} {{ $stock->product->unit_of_measure ?? 'units' }}</td>
                                        <td>{{ $stock->reorder_threshold }}</td>
                                        <td>{{ $stock->target_stock_level }}</td>
                                        <td><span class="badge {{ $stock->stock_badge_class }}">{{ $stock->stock_status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-success py-3"><i class="bi bi-check-circle me-1"></i> All inventory lines are above reorder threshold.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Supply Orders -->
                <div class="tab-pane fade p-3" id="supply" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted text-uppercase">
                                <tr>
                                    <th>Order #</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-end">Total Wholesale</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSupplyOrders as $so)
                                    <tr>
                                        <td>
                                            <a href="{{ route('supply-orders.show', $so) }}" class="fw-semibold text-decoration-none">
                                                {{ $so->order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $so->requestedBy->name ?? 'Staff' }}</td>
                                        <td><span class="badge {{ $so->status_badge_class }}">{{ ucfirst($so->status) }}</span></td>
                                        <td>{{ $so->created_at->format('M d, Y') }}</td>
                                        <td class="text-end fw-bold">${{ number_format($so->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No supply orders recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 4: Royalties -->
                <div class="tab-pane fade p-3" id="royalties" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted text-uppercase">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Billing Period</th>
                                    <th>Gross Sales</th>
                                    <th>Royalty ({{ $outlet->brand->royalty_rate_percentage ?? 5 }}%)</th>
                                    <th>Total Due</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($royaltyInvoices as $inv)
                                    <tr>
                                        <td>
                                            <a href="{{ route('royalties.show', $inv) }}" class="fw-semibold text-decoration-none">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $inv->period_start->format('M Y') }}</td>
                                        <td>${{ number_format($inv->gross_sales, 2) }}</td>
                                        <td>${{ number_format($inv->royalty_fee, 2) }}</td>
                                        <td class="fw-bold">${{ number_format($inv->total_amount_due, 2) }}</td>
                                        <td><span class="badge {{ $inv->status_badge_class }}">{{ strtoupper($inv->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No royalty invoices for this outlet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 5: Audits -->
                <div class="tab-pane fade p-3" id="audits" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted text-uppercase">
                                <tr>
                                    <th>Inspection Date</th>
                                    <th>Auditor</th>
                                    <th>Food Safety</th>
                                    <th>Cleanliness</th>
                                    <th>Brand Standard</th>
                                    <th>Overall Score</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAudits as $audit)
                                    <tr>
                                        <td>
                                            <a href="{{ route('audits.show', $audit) }}" class="fw-semibold text-decoration-none">
                                                {{ $audit->audit_date->format('M d, Y') }}
                                            </a>
                                        </td>
                                        <td>{{ $audit->auditor->name ?? 'Corporate Inspector' }}</td>
                                        <td>{{ $audit->food_safety_score }}%</td>
                                        <td>{{ $audit->cleanliness_score }}%</td>
                                        <td>{{ $audit->brand_standards_score }}%</td>
                                        <td class="fw-bold">{{ $audit->overall_score }}%</td>
                                        <td>
                                            <span class="badge {{ $audit->passed ? 'bg-success' : 'bg-danger' }}">
                                                {{ $audit->passed ? 'PASSED' : 'NEEDS ACTION' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-3">No audit evaluations on record.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 6: Staff -->
                <div class="tab-pane fade p-3" id="staff" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted text-uppercase">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($outlet->users as $member)
                                    <tr>
                                        <td class="fw-semibold">{{ $member->name }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td><span class="badge {{ $member->role_badge_class }}">{{ $member->role_label }}</span></td>
                                        <td>{{ $member->phone ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $member->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $member->is_active ? 'Active' : 'Deactivated' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No staff members assigned to this outlet yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

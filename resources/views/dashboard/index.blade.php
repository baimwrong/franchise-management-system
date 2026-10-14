@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div class="container-fluid p-0">
    <!-- Top Context Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                {{ $selectedOutlet ? $selectedOutlet->name : 'Consolidated Franchise Dashboard' }}
            </h3>
            <p class="text-muted small mb-0">
                @if($selectedOutlet)
                    <span class="badge {{ $selectedOutlet->status_badge_class }} me-1">{{ strtoupper($selectedOutlet->status) }}</span>
                    <span>Code: <strong>{{ $selectedOutlet->outlet_code }}</strong></span> &bull;
                    <span>City: {{ $selectedOutlet->city }}, {{ $selectedOutlet->state_province }}</span> &bull;
                    <span>Licensee: {{ $selectedOutlet->franchisee_name }}</span>
                @else
                    Brand HQ: <strong>{{ $brand->name ?? 'OmniBrew Enterprise' }}</strong> &bull; Managing {{ $totalOutlets }} Total Outlets across all regions
                @endif
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('sales.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cart-plus"></i> <span>POS Terminal</span>
            </a>
            <a href="{{ route('supply-orders.create') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-truck"></i> <span>Order Supplies</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Metric 1: Monthly Sales -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Month Revenue</div>
                    <h3 class="fw-bold my-1 text-dark">${{ number_format($totalSalesMonth, 2) }}</h3>
                    <div class="small text-success">
                        <i class="bi bi-calendar-check me-1"></i> Today: ${{ number_format($totalSalesToday, 2) }}
                    </div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>

        <!-- Metric 2: Outlets Status -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Network Outlets</div>
                    <h3 class="fw-bold my-1 text-dark">{{ $activeOutlets }} <span class="fs-6 text-muted fw-normal">/ {{ $totalOutlets }} Active</span></h3>
                    <div class="small text-muted">
                        <i class="bi bi-building me-1"></i> Across {{ $allOutlets->groupBy('region_id')->count() }} Territories
                    </div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shop"></i>
                </div>
            </div>
        </div>

        <!-- Metric 3: Low Stock Alerts -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Low Stock Items</div>
                    <h3 class="fw-bold my-1 {{ $lowStockCount > 0 ? 'text-danger' : 'text-dark' }}">{{ $lowStockCount }}</h3>
                    <div class="small">
                        @if($lowStockCount > 0)
                            <a href="{{ route('inventory.index', ['low_stock_only' => 1]) }}" class="text-danger fw-semibold text-decoration-none">
                                <i class="bi bi-exclamation-octagon me-1"></i> Needs restock &rarr;
                            </a>
                        @else
                            <span class="text-success"><i class="bi bi-check-circle me-1"></i> Healthy inventory</span>
                        @endif
                    </div>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>

        <!-- Metric 4: Supply Requisitions -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Pending Supplies</div>
                    <h3 class="fw-bold my-1 text-dark">{{ $pendingSupplyOrders }}</h3>
                    <div class="small text-muted">
                        <a href="{{ route('supply-orders.index', ['status' => 'pending']) }}" class="text-primary text-decoration-none">
                            <i class="bi bi-clock-history me-1"></i> In HQ pipeline &rarr;
                        </a>
                    </div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Performance Row -->
    <div class="row g-3 mb-4">
        <!-- Sales Trend Chart (Local Chart.js) -->
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up text-primary"></i> 7-Day Revenue Trajectory
                    </span>
                    <span class="badge bg-light text-muted border">Local Chart.js</span>
                </div>
                <div class="card-body">
                    <div style="height: 280px; position: relative;">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outlet Performance Ranking -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-trophy text-warning"></i> Outlet Sales Leaderboard
                    </span>
                    <small class="text-muted">This Month</small>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($outletPerformance as $perf)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                <div>
                                    <div class="fw-semibold small text-truncate" style="max-width: 170px;">
                                        <a href="{{ route('outlets.show', $perf) }}" class="text-decoration-none text-dark">
                                            {{ $perf->name }}
                                        </a>
                                    </div>
                                    <small class="text-muted">{{ $perf->outlet_code }} &bull; {{ $perf->city }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold small text-dark">${{ number_format($perf->sales_transactions_sum_total_amount ?? 0, 2) }}</div>
                                    <span class="badge {{ $perf->status_badge_class }}" style="font-size: 0.65rem;">{{ $perf->status }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center py-4">No outlet sales recorded yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Tables Row -->
    <div class="row g-3">
        <!-- Recent Transactions -->
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-receipt-cutoff text-success"></i> Recent Point-of-Sale Transactions
                    </span>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-link text-decoration-none">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th>Transaction</th>
                                <th>Outlet</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($recentTransactions as $trx)
                                <tr>
                                    <td>
                                        <a href="{{ route('sales.show', $trx) }}" class="fw-semibold text-decoration-none">
                                            {{ $trx->transaction_code }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $trx->transaction_date->diffForHumans() }}</div>
                                    </td>
                                    <td>{{ $trx->outlet->name ?? 'N/A' }}</td>
                                    <td>{{ $trx->customer_name ?? 'Guest' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                            {{ str_replace('_', ' ', $trx->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">${{ number_format($trx->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No recent sales transactions recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Supply Requisitions & Top Products -->
        <div class="col-12 col-xl-5">
            <!-- Top Selling Products -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-star-fill text-warning"></i> Network Best Sellers
                    </span>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-link text-decoration-none">Catalog &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @foreach($topProducts as $tp)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                <div>
                                    <div class="fw-semibold">{{ $tp->name }}</div>
                                    <small class="text-muted">{{ $tp->sku }} &bull; {{ $tp->category->name ?? 'Menu' }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">
                                        {{ (int) ($tp->sales_transaction_items_sum_quantity ?? 0) }} sold
                                    </span>
                                    <div class="text-muted small">${{ number_format($tp->suggested_retail_price, 2) }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Recent Supply Orders -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-in-down text-info"></i> Warehouse Requisitions
                    </span>
                    <a href="{{ route('supply-orders.index') }}" class="btn btn-sm btn-link text-decoration-none">All Orders &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($recentSupplyOrders as $so)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                <div>
                                    <a href="{{ route('supply-orders.show', $so) }}" class="fw-semibold text-decoration-none">
                                        {{ $so->order_number }}
                                    </a>
                                    <div class="text-muted small">{{ $so->outlet->name ?? 'Outlet' }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $so->status_badge_class }}">{{ ucfirst($so->status) }}</span>
                                    <div class="text-muted small">${{ number_format($so->total_amount, 2) }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center py-3">No supply requisitions yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesTrendChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Gross Sales ($)',
                        data: @json($chartSalesData),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointHoverRadius: 6,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return '$' + value; }
                            },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Franchise Royalties & Invoicing')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Franchise Royalty Billing & Fees</h3>
            <p class="text-muted small mb-0">Automated calculation of franchise royalties ({{ $brand->royalty_rate_percentage ?? 5 }}%), marketing fund contributions ({{ $brand->marketing_fund_percentage ?? 2 }}%), and statements.</p>
        </div>
        <div>
            @if(auth()->user()->isSuperAdmin())
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#generateBillingModal">
                <i class="bi bi-gear-wide-connected"></i> <span>Generate Monthly Invoices</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Unpaid Royalty Due</div>
                    <h3 class="fw-bold my-1 text-danger">${{ number_format($totalRoyaltiesDue, 2) }}</h3>
                    <small class="text-muted">Awaiting franchisee wire</small>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Royalties Collected</div>
                    <h3 class="fw-bold my-1 text-success">${{ number_format($totalRoyaltiesCollected, 2) }}</h3>
                    <small class="text-muted">Paid to Corporate HQ</small>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-all"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Royalty Standard Rate</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $brand->royalty_rate_percentage ?? 5.0 }}%</h3>
                    <small class="text-muted">Of Gross POS Sales</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-percent"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Marketing Fund</div>
                    <h3 class="fw-bold my-1 text-info">{{ $brand->marketing_fund_percentage ?? 2.0 }}%</h3>
                    <small class="text-muted">National Brand Advertising</small>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-megaphone"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-receipt text-primary"></i> Franchise Royalty Invoices
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>Invoice Number</th>
                        <th>Franchise Outlet</th>
                        <th>Billing Cycle</th>
                        <th class="text-end">Gross Sales</th>
                        <th class="text-end">Royalty Fee</th>
                        <th class="text-end">Marketing Fund</th>
                        <th class="text-end">Total Due</th>
                        <th>Due Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('royalties.show', $inv) }}" class="fw-bold text-decoration-none">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $inv->outlet->name ?? 'Outlet' }}</div>
                                <small class="text-muted">{{ $inv->outlet->outlet_code ?? '' }} &bull; {{ $inv->outlet->franchisee_name ?? '' }}</small>
                            </td>
                            <td>{{ $inv->period_start->format('M Y') }}</td>
                            <td class="text-end">${{ number_format($inv->gross_sales, 2) }}</td>
                            <td class="text-end text-primary fw-semibold">${{ number_format($inv->royalty_fee, 2) }}</td>
                            <td class="text-end text-muted">${{ number_format($inv->marketing_fee, 2) }}</td>
                            <td class="text-end fw-bold text-dark fs-6">${{ number_format($inv->total_amount_due, 2) }}</td>
                            <td>{{ $inv->due_date->format('M d, Y') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $inv->status_badge_class }}">{{ strtoupper($inv->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('royalties.show', $inv) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                    <i class="bi bi-file-earmark-text"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="bi bi-cash-stack fs-1 text-muted mb-2 d-block"></i>
                                No royalty invoices generated yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $invoices->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal: Generate Monthly Billing Run -->
<div class="modal fade" id="generateBillingModal" tabindex="-1" aria-labelledby="generateBillingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('royalties.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="generateBillingModalLabel">Generate Franchise Royalty Run</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Calculates royalty percentage ({{ $brand->royalty_rate_percentage ?? 5 }}%) and marketing fund ({{ $brand->marketing_fund_percentage ?? 2 }}%) based on total verified sales for the specified monthly cycle.
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Billing Month Cycle <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control" value="{{ date('Y-m', strtotime('-1 month')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Franchise Store</label>
                        <select name="outlet_id" class="form-select">
                            <option value="">-- All Active Franchise Outlets (Batch Generation) --</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->outlet_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-calculator me-1"></i> Compute & Issue Invoices
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

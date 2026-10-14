@extends('layouts.app')

@section('title', 'Invoice ' . $royaltyInvoice->invoice_number)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('royalties.index') }}" class="text-decoration-none">Royalties</a></li>
                    <li class="breadcrumb-item active">{{ $royaltyInvoice->invoice_number }}</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">Franchise Royalty Billing Statement</h3>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Statement
            </button>
            @if($royaltyInvoice->status !== 'paid' && (auth()->user()->isSuperAdmin() || auth()->user()->isFranchiseOwner()))
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#markPaidModal">
                <i class="bi bi-check2-circle me-1"></i> Record Settlement / Paid
            </button>
            @endif
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm p-4 p-md-5">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                    <div>
                        <div class="fs-4 fw-bold text-dark">{{ $royaltyInvoice->outlet->brand->name ?? 'OmniBrew Enterprise' }}</div>
                        <div class="text-muted small">Franchise Corporate Headquarters</div>
                        <div class="text-muted small">{{ $royaltyInvoice->outlet->brand->hq_address ?? 'New York, NY' }}</div>
                        <div class="text-muted small">Tax ID: {{ $royaltyInvoice->outlet->brand->corporate_tax_id ?? 'N/A' }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ $royaltyInvoice->status_badge_class }} fs-6 mb-2">{{ strtoupper($royaltyInvoice->status) }}</span>
                        <div class="fw-bold text-dark">{{ $royaltyInvoice->invoice_number }}</div>
                        <div class="text-muted small">Due Date: {{ $royaltyInvoice->due_date->format('M d, Y') }}</div>
                    </div>
                </div>

                <!-- Billed To -->
                <div class="row mb-4 small">
                    <div class="col-6">
                        <strong class="text-uppercase text-muted" style="font-size: 0.7rem;">Franchisee Bill-To:</strong>
                        <div class="fw-bold text-dark mt-1">{{ $royaltyInvoice->outlet->franchisee_name }}</div>
                        <div>Store: {{ $royaltyInvoice->outlet->name }} ({{ $royaltyInvoice->outlet->outlet_code }})</div>
                        <div>{{ $royaltyInvoice->outlet->address }}, {{ $royaltyInvoice->outlet->city }}</div>
                    </div>
                    <div class="col-6 text-end">
                        <strong class="text-uppercase text-muted" style="font-size: 0.7rem;">Billing Accounting Period:</strong>
                        <div class="fw-bold text-dark mt-1">{{ $royaltyInvoice->period_start->format('M d, Y') }} &ndash; {{ $royaltyInvoice->period_end->format('M d, Y') }}</div>
                        <div class="text-muted">Generated Cycle: {{ $royaltyInvoice->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <!-- Breakdown Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle small mb-0">
                        <thead class="table-light text-muted text-uppercase">
                            <tr>
                                <th>Assessment Fee Description</th>
                                <th class="text-end">Basis (Gross Sales)</th>
                                <th class="text-center">Rate</th>
                                <th class="text-end">Assessed Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Franchise Royalty License Fee</strong>
                                    <div class="text-muted" style="font-size: 0.72rem;">Operational use of brand marks, systems, and proprietary recipes</div>
                                </td>
                                <td class="text-end">${{ number_format($royaltyInvoice->gross_sales, 2) }}</td>
                                <td class="text-center">{{ $royaltyInvoice->royalty_percentage }}%</td>
                                <td class="text-end fw-bold">${{ number_format($royaltyInvoice->royalty_fee, 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>National Brand Advertising & Marketing Contribution</strong>
                                    <div class="text-muted" style="font-size: 0.72rem;">Digital marketing, national brand campaigns, and social media placement</div>
                                </td>
                                <td class="text-end">${{ number_format($royaltyInvoice->gross_sales, 2) }}</td>
                                <td class="text-center">2.00%</td>
                                <td class="text-end fw-bold">${{ number_format($royaltyInvoice->marketing_fee, 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Franchise POS Portal & Software License</strong>
                                    <div class="text-muted" style="font-size: 0.72rem;">Fixed monthly internal network server allocation & database upkeep</div>
                                </td>
                                <td class="text-end">&mdash;</td>
                                <td class="text-center">Flat</td>
                                <td class="text-end fw-bold">${{ number_format($royaltyInvoice->technology_fee, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold fs-6">TOTAL AMOUNT DUE:</td>
                                <td class="text-end fw-bold fs-5 text-primary">${{ number_format($royaltyInvoice->total_amount_due, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Settlement Info -->
                @if($royaltyInvoice->status === 'paid')
                    <div class="alert alert-success d-flex align-items-center gap-3 p-3">
                        <i class="bi bi-shield-check fs-2"></i>
                        <div>
                            <strong class="d-block">Settlement Confirmed</strong>
                            <small>Paid on {{ $royaltyInvoice->paid_at?->format('M d, Y H:i') }} &bull; Wire / ACH Ref: <code>{{ $royaltyInvoice->payment_reference }}</code></small>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle me-1"></i> Please remit payment via Corporate Wire Transfer or ACH within 15 days of invoice date.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('royalties.mark-paid', $royaltyInvoice) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Record Royalty Settlement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Wire / ACH Reference Number <span class="text-danger">*</span></label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="e.g. WIRE-ACH-992104" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Settlement Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Cleared via Citibank Treasury"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold">Confirm Payment Cleared</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

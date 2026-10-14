@extends('layouts.app')

@section('title', 'Audit Report ' . $outletAudit->outlet->name)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('audits.index') }}" class="text-decoration-none">Audits</a></li>
                    <li class="breadcrumb-item active">{{ $outletAudit->audit_date->format('Y-m-d') }}</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">Quality Standards Inspection Report</h3>
        </div>
        <div>
            <a href="{{ route('audits.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Audits
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                    <div>
                        <div class="fs-4 fw-bold text-dark">{{ $outletAudit->outlet->name }}</div>
                        <div class="text-muted small">Store Code: <strong>{{ $outletAudit->outlet->outlet_code }}</strong> &bull; Region: {{ $outletAudit->outlet->region->name ?? 'HQ' }}</div>
                        <div class="text-muted small">Inspector: {{ $outletAudit->auditor->name ?? 'Corporate Audit Lead' }}</div>
                        <div class="text-muted small">Inspection Date: {{ $outletAudit->audit_date->format('F d, Y') }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ $outletAudit->score_badge_class }} fs-4 mb-1">{{ $outletAudit->overall_score }}%</span>
                        <div>
                            <span class="badge {{ $outletAudit->passed ? 'bg-success' : 'bg-danger' }} text-uppercase">
                                {{ $outletAudit->passed ? 'APPROVED / PASSED' : 'DEFICIENCIES NOTED' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Scores Breakdown Grid -->
                <h6 class="fw-bold text-dark text-uppercase small mb-3">Core Performance Dimensions</h6>
                <div class="row g-3 mb-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <div class="small text-muted mb-1">Food Safety</div>
                            <div class="fs-4 fw-bold text-dark">{{ $outletAudit->food_safety_score }}%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <div class="small text-muted mb-1">Sanitization</div>
                            <div class="fs-4 fw-bold text-dark">{{ $outletAudit->cleanliness_score }}%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <div class="small text-muted mb-1">Brand Standards</div>
                            <div class="fs-4 fw-bold text-dark">{{ $outletAudit->brand_standards_score }}%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <div class="small text-muted mb-1">Guest Service</div>
                            <div class="fs-4 fw-bold text-dark">{{ $outletAudit->customer_service_score }}%</div>
                        </div>
                    </div>
                </div>

                @if($outletAudit->findings)
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase small mb-2">Detailed Audit Findings & Field Notes</h6>
                    <div class="p-3 bg-light rounded small text-dark">
                        {{ $outletAudit->findings }}
                    </div>
                </div>
                @endif

                @if($outletAudit->corrective_actions)
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase small mb-2">Required Corrective Action Protocol</h6>
                    <div class="p-3 bg-warning bg-opacity-10 rounded small text-dark border border-warning border-opacity-25">
                        {{ $outletAudit->corrective_actions }}
                    </div>
                </div>
                @endif

                @if($outletAudit->follow_up_date)
                <div class="text-muted small">
                    <i class="bi bi-calendar-event me-1"></i> Follow-up Verification Scheduled: <strong>{{ $outletAudit->follow_up_date->format('F d, Y') }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

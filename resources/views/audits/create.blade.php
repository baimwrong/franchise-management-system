@extends('layouts.app')

@section('title', 'Conduct Store Audit')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('audits.index') }}" class="text-decoration-none">Audits</a></li>
                <li class="breadcrumb-item active">New Inspection</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Conduct Store Quality Audit</h3>
        <p class="text-muted small">Evaluate store adherence to franchise brand quality standards and food hygiene protocols.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('audits.store') }}" method="POST">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Target Outlet Store <span class="text-danger">*</span></label>
                        <select name="outlet_id" class="form-select" required>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }} ({{ $outlet->outlet_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Inspection Date <span class="text-danger">*</span></label>
                        <input type="date" name="audit_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Score Evaluation Breakdown (0 - 100%)</h5>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Food Safety & Temps (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="0" max="100" name="food_safety_score" class="form-control" placeholder="95.0" value="95.0" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Cleanliness & Sanitization (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="0" max="100" name="cleanliness_score" class="form-control" placeholder="90.0" value="90.0" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Brand Standards & Uniform (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="0" max="100" name="brand_standards_score" class="form-control" placeholder="92.0" value="92.0" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Customer Service Speed (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" min="0" max="100" name="customer_service_score" class="form-control" placeholder="88.0" value="88.0" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Inspector Observations & Findings</label>
                        <textarea name="findings" class="form-control" rows="3" placeholder="Detailed notes regarding physical inspection, bar station organization, temperature logs..."></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Corrective Action Plan</label>
                        <textarea name="corrective_actions" class="form-control" rows="3" placeholder="Actionable remedial steps required from the store manager prior to next cycle..."></textarea>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Follow-up Re-inspection Date</label>
                        <input type="date" name="follow_up_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('audits.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Submit Audit Evaluation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Brand Standards & Audits')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Store Quality Standards & Inspections</h3>
            <p class="text-muted small mb-0">Brand compliance audits, hygiene evaluations, and mystery shopper quality scores.</p>
        </div>
        <div>
            <a href="{{ route('audits.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-clipboard-plus"></i> <span>Conduct New Store Audit</span>
            </a>
        </div>
    </div>

    <!-- Audits Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary"></i> Inspection Scorecards
            </span>
            <div class="small fw-semibold text-dark">
                Network Average Score: <span class="badge bg-primary fs-6">{{ number_format($averageAuditScore, 1) }}%</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>Outlet Store</th>
                        <th>Inspector</th>
                        <th>Audit Date</th>
                        <th class="text-center">Food Safety</th>
                        <th class="text-center">Cleanliness</th>
                        <th class="text-center">Brand Uniformity</th>
                        <th class="text-center">Service</th>
                        <th class="text-center">Overall</th>
                        <th class="text-center">Outcome</th>
                        <th class="text-end">Report</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $audit->outlet->name ?? 'Outlet' }}</div>
                                <small class="text-muted">{{ $audit->outlet->outlet_code ?? '' }}</small>
                            </td>
                            <td>{{ $audit->auditor->name ?? 'Inspector' }}</td>
                            <td>{{ $audit->audit_date->format('M d, Y') }}</td>
                            <td class="text-center">{{ $audit->food_safety_score }}%</td>
                            <td class="text-center">{{ $audit->cleanliness_score }}%</td>
                            <td class="text-center">{{ $audit->brand_standards_score }}%</td>
                            <td class="text-center">{{ $audit->customer_service_score }}%</td>
                            <td class="text-center fw-bold fs-6">{{ $audit->overall_score }}%</td>
                            <td class="text-center">
                                <span class="badge {{ $audit->passed ? 'bg-success' : 'bg-danger' }}">
                                    {{ $audit->passed ? 'PASSED' : 'ACTION REQUIRED' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('audits.show', $audit) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                    <i class="bi bi-eye"></i> Scorecard
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="bi bi-clipboard-check fs-1 text-muted mb-2 d-block"></i>
                                No audit records logged.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $audits->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Import Batch Log ' . $bulkImport->filename)

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.bulk-imports.index') }}" class="text-decoration-none">Bulk Imports</a></li>
                    <li class="breadcrumb-item active">Batch #{{ $bulkImport->id }}</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">Bulk Import Batch Log: {{ $bulkImport->filename }}</h3>
            <p class="text-muted small">Executed by {{ $bulkImport->user->name ?? 'HQ Admin' }} on {{ $bulkImport->created_at->format('M d, Y H:i:s') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.bulk-imports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Import Hub
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Entity Type</div>
                    <h5 class="fw-bold my-1 text-dark text-uppercase">{{ $bulkImport->type }}</h5>
                    <small class="text-muted">Target model</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-diagram-3"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Rows</div>
                    <h4 class="fw-bold my-1 text-dark">{{ number_format($bulkImport->total_rows) }}</h4>
                    <small class="text-muted">In CSV payload</small>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-list-ol"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Success Rows</div>
                    <h4 class="fw-bold my-1 text-success">{{ number_format($bulkImport->successful_rows) }}</h4>
                    <small class="text-success">Committed to database</small>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Failed Rows</div>
                    <h4 class="fw-bold my-1 {{ $bulkImport->failed_rows > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($bulkImport->failed_rows) }}</h4>
                    <small class="text-muted">Skipped with error</small>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Log Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center gap-2 fw-bold text-dark">
                <i class="bi bi-bug text-danger"></i> Row Execution Errors & Diagnostic Trace
            </span>
            <span class="badge {{ $bulkImport->status_badge_class }}">{{ strtoupper($bulkImport->status) }}</span>
        </div>
        <div class="card-body p-0">
            @if(is_array($bulkImport->error_log) && count($bulkImport->error_log) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-muted text-uppercase">
                            <tr>
                                <th style="width: 10%;">Row #</th>
                                <th style="width: 40%;">Exception Reason</th>
                                <th style="width: 50%;">Raw Row Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bulkImport->error_log as $err)
                                <tr>
                                    <td class="fw-bold text-danger">Row {{ $err['row'] ?? '?' }}</td>
                                    <td class="text-danger fw-semibold">{{ $err['error'] ?? 'Unknown error' }}</td>
                                    <td>
                                        <pre class="bg-light p-2 rounded mb-0 text-dark" style="font-size: 0.72rem; white-space: pre-wrap;">{{ json_encode($err['data'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5 text-center text-success">
                    <i class="bi bi-check2-circle fs-1 mb-2 d-block"></i>
                    <h5>Clean Batch Run</h5>
                    <p class="small text-muted mb-0">No validation or transactional failures encountered during this import.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

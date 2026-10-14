@extends('layouts.app')

@section('title', 'Admin Bulk Import Hub')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Admin Bulk Data Import Hub</h3>
            <p class="text-muted small mb-0">High-speed batch CSV onboarding for stores, catalog products, warehouse inventory, staff credentials, and historical sales.</p>
        </div>
        <div>
            <span class="badge bg-light text-dark border p-2">
                <i class="bi bi-shield-lock-fill text-danger me-1"></i> HQ Super Admin Authorization Required
            </span>
        </div>
    </div>

    <!-- Error Alert Banner (if last import had row failures) -->
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="card border-danger mb-4 shadow-sm">
            <div class="card-header bg-danger text-white d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span class="fw-bold">Bulk Import Row Discrepancies ({{ count(session('import_errors')) }} Issues Flagged)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">Row #</th>
                                <th style="width: 40%;">Validation Error</th>
                                <th style="width: 50%;">Row Payload</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('import_errors') as $err)
                                <tr>
                                    <td class="fw-bold text-danger">Row {{ $err['row'] }}</td>
                                    <td class="text-danger">{{ $err['error'] }}</td>
                                    <td><code>{{ json_encode($err['data']) }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Downloadable Templates Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4 col-xl-2-4">
            <div class="card h-100 text-center p-3 border">
                <i class="bi bi-shop fs-2 text-primary mb-2"></i>
                <h6 class="fw-bold mb-1">Outlets Template</h6>
                <p class="text-muted small mb-3">Store codes, cities, taxes, addresses</p>
                <a href="{{ route('admin.bulk-imports.template', 'outlets') }}" class="btn btn-sm btn-outline-primary mt-auto">
                    <i class="bi bi-download me-1"></i> Download CSV
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4 col-xl-2-4">
            <div class="card h-100 text-center p-3 border">
                <i class="bi bi-cup-hot fs-2 text-info mb-2"></i>
                <h6 class="fw-bold mb-1">Products Template</h6>
                <p class="text-muted small mb-3">SKUs, MSRP, costs, menu categories</p>
                <a href="{{ route('admin.bulk-imports.template', 'products') }}" class="btn btn-sm btn-outline-info mt-auto">
                    <i class="bi bi-download me-1"></i> Download CSV
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4 col-xl-2-4">
            <div class="card h-100 text-center p-3 border">
                <i class="bi bi-boxes fs-2 text-warning mb-2"></i>
                <h6 class="fw-bold mb-1">Inventory Template</h6>
                <p class="text-muted small mb-3">Store SKU stock levels, reorder pars</p>
                <a href="{{ route('admin.bulk-imports.template', 'inventory') }}" class="btn btn-sm btn-outline-warning mt-auto">
                    <i class="bi bi-download me-1"></i> Download CSV
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4 col-xl-2-4">
            <div class="card h-100 text-center p-3 border">
                <i class="bi bi-people fs-2 text-success mb-2"></i>
                <h6 class="fw-bold mb-1">Staff Template</h6>
                <p class="text-muted small mb-3">Manager & cashier credentials, roles</p>
                <a href="{{ route('admin.bulk-imports.template', 'staff') }}" class="btn btn-sm btn-outline-success mt-auto">
                    <i class="bi bi-download me-1"></i> Download CSV
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4 col-xl-2-4">
            <div class="card h-100 text-center p-3 border">
                <i class="bi bi-receipt fs-2 text-purple mb-2" style="color: #8b5cf6;"></i>
                <h6 class="fw-bold mb-1">Sales Template</h6>
                <p class="text-muted small mb-3">Historical POS orders, payments</p>
                <a href="{{ route('admin.bulk-imports.template', 'sales') }}" class="btn btn-sm btn-outline-secondary mt-auto">
                    <i class="bi bi-download me-1"></i> Download CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Active Upload Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <span class="d-flex align-items-center gap-2 fw-bold text-dark">
                <i class="bi bi-cloud-arrow-up text-primary"></i> Upload & Process CSV File
            </span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.bulk-imports.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Target Entity Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">-- Choose Data Model --</option>
                            <option value="outlets">Franchise Outlets & Stores</option>
                            <option value="products">Central Product & Menu Catalog</option>
                            <option value="inventory">Outlet Stock Quantities</option>
                            <option value="staff">Staff & User Onboarding</option>
                            <option value="sales">Sales Transactions & POS Data</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold">Select CSV File (.csv, up to 10MB) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                    </div>

                    <div class="col-12 col-md-3 mt-md-4">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i> Execute Import
                        </button>
                    </div>
                </div>

                <div class="mt-3 text-muted small">
                    <i class="bi bi-info-circle me-1"></i> The import parser auto-detects commas and semicolons, skips UTF-8 BOM, validates headers, and updates existing records matching unique identifiers.
                </div>
            </form>
        </div>
    </div>

    <!-- Import History Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-secondary"></i> Bulk Import Audit Journal
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>Batch File</th>
                        <th>Entity Type</th>
                        <th>Submitted By</th>
                        <th class="text-center">Total Rows</th>
                        <th class="text-center text-success">Imported</th>
                        <th class="text-center text-danger">Failed</th>
                        <th class="text-center">Status</th>
                        <th>Execution Time</th>
                        <th class="text-end">Log</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $imp)
                        <tr>
                            <td class="fw-bold text-dark">
                                <a href="{{ route('admin.bulk-imports.show', $imp) }}" class="text-decoration-none">
                                    {{ $imp->filename }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                    {{ $imp->type }}
                                </span>
                            </td>
                            <td>{{ $imp->user->name ?? 'Admin' }}</td>
                            <td class="text-center fw-semibold">{{ number_format($imp->total_rows) }}</td>
                            <td class="text-center fw-bold text-success">{{ number_format($imp->successful_rows) }}</td>
                            <td class="text-center fw-bold text-danger">{{ number_format($imp->failed_rows) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $imp->status_badge_class }}">{{ strtoupper($imp->status) }}</span>
                            </td>
                            <td>{{ $imp->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.bulk-imports.show', $imp) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                No previous bulk imports on record.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $imports->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

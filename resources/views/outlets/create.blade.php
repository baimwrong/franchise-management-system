@extends('layouts.app')

@section('title', 'Onboard New Outlet')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('outlets.index') }}" class="text-decoration-none">Outlets</a></li>
                <li class="breadcrumb-item active">New Outlet</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Onboard New Franchise Outlet</h3>
        <p class="text-muted small">Register a new store branch under {{ $brand->name ?? 'OmniBrew' }} brand network.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('outlets.store') }}" method="POST">
                @csrf
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Store Identification & Territory</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Store / Outlet Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. OmniBrew Downtown Flagship" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Store Code (Unique) <span class="text-danger">*</span></label>
                        <input type="text" name="outlet_code" class="form-control" placeholder="e.g. OMNI-DWT-001" value="{{ old('outlet_code') }}" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Regional Territory</label>
                        <select name="region_id" class="form-select">
                            <option value="">-- No Region Assigned --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }} ({{ $region->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Franchisee / Licensee Entity Name <span class="text-danger">*</span></label>
                        <input type="text" name="franchisee_name" class="form-control" placeholder="e.g. Apex Hospitality Group LLC or Corporate" value="{{ old('franchisee_name', 'Corporate / Direct Owned') }}" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Store Model / Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="franchise" {{ old('type') === 'franchise' ? 'selected' : '' }}>Franchise Licensed</option>
                            <option value="company_owned" {{ old('type') === 'company_owned' ? 'selected' : '' }}>Company Owned</option>
                            <option value="kiosk" {{ old('type') === 'kiosk' ? 'selected' : '' }}>Kiosk / Mall Cart</option>
                            <option value="express" {{ old('type') === 'express' ? 'selected' : '' }}>Express / Airport</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Operational Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active / Operational</option>
                            <option value="pending_approval" {{ old('status') === 'pending_approval' ? 'selected' : '' }}>Pending Opening</option>
                            <option value="renovation" {{ old('status') === 'renovation' ? 'selected' : '' }}>Renovation</option>
                            <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Contact & Physical Location</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Store Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="store@franchise.local" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Store Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="+1-555-0100" value="{{ old('phone') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Street Address <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" placeholder="123 Market Street, Suite 100" value="{{ old('address') }}" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">City <span class="text-danger">*</span></label>
                        <input type="text" name="city" class="form-control" placeholder="New York" value="{{ old('city') }}" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">State / Province</label>
                        <input type="text" name="state_province" class="form-control" placeholder="NY" value="{{ old('state_province') }}">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Postal / ZIP Code</label>
                        <input type="text" name="postal_code" class="form-control" placeholder="10001" value="{{ old('postal_code') }}">
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Financial & Opening Settings</h5>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Local Tax Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" name="tax_rate_percentage" class="form-control" placeholder="8.875" value="{{ old('tax_rate_percentage', '10.00') }}" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Grand Opening Date</label>
                        <input type="date" name="opening_date" class="form-control" value="{{ old('opening_date', date('Y-m-d')) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('outlets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Register Outlet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit ' . $outlet->name)

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('outlets.index') }}" class="text-decoration-none">Outlets</a></li>
                <li class="breadcrumb-item"><a href="{{ route('outlets.show', $outlet) }}" class="text-decoration-none">{{ $outlet->outlet_code }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Edit Outlet: {{ $outlet->name }}</h3>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('outlets.update', $outlet) }}" method="POST">
                @csrf
                @method('PUT')

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Store Identification & Territory</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Store / Outlet Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $outlet->name) }}" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Store Code (Unique) <span class="text-danger">*</span></label>
                        <input type="text" name="outlet_code" class="form-control" value="{{ old('outlet_code', $outlet->outlet_code) }}" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Regional Territory</label>
                        <select name="region_id" class="form-select">
                            <option value="">-- No Region Assigned --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id', $outlet->region_id) == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }} ({{ $region->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Franchisee / Licensee Entity Name <span class="text-danger">*</span></label>
                        <input type="text" name="franchisee_name" class="form-control" value="{{ old('franchisee_name', $outlet->franchisee_name) }}" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Store Model / Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="franchise" {{ old('type', $outlet->type) === 'franchise' ? 'selected' : '' }}>Franchise Licensed</option>
                            <option value="company_owned" {{ old('type', $outlet->type) === 'company_owned' ? 'selected' : '' }}>Company Owned</option>
                            <option value="kiosk" {{ old('type', $outlet->type) === 'kiosk' ? 'selected' : '' }}>Kiosk / Mall Cart</option>
                            <option value="express" {{ old('type', $outlet->type) === 'express' ? 'selected' : '' }}>Express / Airport</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold">Operational Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', $outlet->status) === 'active' ? 'selected' : '' }}>Active / Operational</option>
                            <option value="pending_approval" {{ old('status', $outlet->status) === 'pending_approval' ? 'selected' : '' }}>Pending Opening</option>
                            <option value="renovation" {{ old('status', $outlet->status) === 'renovation' ? 'selected' : '' }}>Renovation</option>
                            <option value="suspended" {{ old('status', $outlet->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="closed" {{ old('status', $outlet->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Contact & Physical Location</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Store Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $outlet->email) }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Store Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $outlet->phone) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Street Address <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $outlet->address) }}" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">City <span class="text-danger">*</span></label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $outlet->city) }}" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">State / Province</label>
                        <input type="text" name="state_province" class="form-control" value="{{ old('state_province', $outlet->state_province) }}">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Postal / ZIP Code</label>
                        <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $outlet->postal_code) }}">
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Financial Settings</h5>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Local Tax Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" name="tax_rate_percentage" class="form-control" value="{{ old('tax_rate_percentage', $outlet->tax_rate_percentage) }}" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Grand Opening Date</label>
                        <input type="date" name="opening_date" class="form-control" value="{{ old('opening_date', $outlet->opening_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('outlets.show', $outlet) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check2 me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

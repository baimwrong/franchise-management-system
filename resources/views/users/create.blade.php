@extends('layouts.app')

@section('title', 'Add New User')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Users</a></li>
                <li class="breadcrumb-item active">New User</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Add New Staff / User Account</h3>
        <p class="text-muted small">Provision login access, assign franchise roles, and bind to a store branch.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Rachel Adams" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Corporate Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="rachel@franchise.local" value="{{ old('email') }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Contact Telephone / Mobile</label>
                        <input type="text" name="phone" class="form-control" placeholder="+1-555-0155" value="{{ old('phone') }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Franchise Role Level <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Outlet Staff / Cashier / Barista</option>
                            <option value="outlet_manager" {{ old('role') === 'outlet_manager' ? 'selected' : '' }}>Store General Manager</option>
                            <option value="franchise_owner" {{ old('role') === 'franchise_owner' ? 'selected' : '' }}>Franchise Licensee / Owner</option>
                            @if(auth()->user()->isSuperAdmin())
                            <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Corporate HQ Super Admin</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Assigned Store Branch</label>
                        <select name="outlet_id" class="form-select">
                            <option value="">-- None (HQ Super Admin Scope) --</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }} ({{ $outlet->outlet_code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size: 0.72rem;">Required for store managers and staff to limit POS access.</small>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked>
                        <label class="form-check-label small fw-semibold" for="isActive">Account is active and allowed to authenticate</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-person-check me-1"></i> Create User Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

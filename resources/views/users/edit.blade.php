@extends('layouts.app')

@section('title', 'Edit ' . $user->name)

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Users</a></li>
                <li class="breadcrumb-item active">Edit User</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Edit User Account: {{ $user->name }}</h3>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Corporate Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">New Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password if changing">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Contact Telephone / Mobile</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Franchise Role Level <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Outlet Staff / Cashier / Barista</option>
                            <option value="outlet_manager" {{ old('role', $user->role) === 'outlet_manager' ? 'selected' : '' }}>Store General Manager</option>
                            <option value="franchise_owner" {{ old('role', $user->role) === 'franchise_owner' ? 'selected' : '' }}>Franchise Licensee / Owner</option>
                            @if(auth()->user()->isSuperAdmin())
                            <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Corporate HQ Super Admin</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Assigned Store Branch</label>
                        <select name="outlet_id" class="form-select">
                            <option value="">-- None (HQ Super Admin Scope) --</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ old('outlet_id', $user->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }} ({{ $outlet->outlet_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $user->is_active ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="isActive">Account is active and allowed to authenticate</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-check2 me-1"></i> Update User Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

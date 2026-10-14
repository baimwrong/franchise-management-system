@extends('layouts.app')

@section('title', 'Staff & User Accounts')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Staff & Franchise User Accounts</h3>
            <p class="text-muted small mb-0">Role-based access control (RBAC), credentials management, and outlet store assignments.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.bulk-imports.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> <span>Bulk Import Staff</span>
            </a>
            @endif
            <a href="{{ route('users.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-person-plus"></i> <span>Add New User</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">-- All Roles --</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>HQ Super Admin</option>
                        <option value="franchise_owner" {{ request('role') === 'franchise_owner' ? 'selected' : '' }}>Franchise Owner</option>
                        <option value="outlet_manager" {{ request('role') === 'outlet_manager' ? 'selected' : '' }}>Outlet Manager</option>
                        <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Store Staff / Cashier</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="outlet_id" class="form-select form-select-sm">
                        <option value="">-- All Stores --</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                {{ $outlet->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['search', 'role', 'outlet_id']))
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>User Profile</th>
                        <th>Email Address</th>
                        <th>Role Level</th>
                        <th>Assigned Store Outlet</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role_badge_class }}">{{ $user->role_label }}</span>
                            </td>
                            <td>
                                @if($user->outlet)
                                    <div class="fw-semibold text-dark">{{ $user->outlet->name }}</div>
                                    <small class="text-muted">{{ $user->outlet->outlet_code }}</small>
                                @else
                                    <span class="badge bg-light text-muted border">Corporate HQ Scope</span>
                                @endif
                            </td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-muted">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No staff or user accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

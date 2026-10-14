@extends('layouts.app')

@section('title', 'Outlets & Stores')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Franchise Outlets & Stores</h3>
            <p class="text-muted small mb-0">Manage corporate and franchisee-licensed stores across all regional territories.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.bulk-imports.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> <span>Bulk Import Outlets</span>
            </a>
            <a href="{{ route('outlets.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-circle"></i> <span>Add New Outlet</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('outlets.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by outlet name, store code, city, franchisee..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="region_id" class="form-select form-select-sm">
                        <option value="">-- All Regions --</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                                {{ $region->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- All Statuses --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending</option>
                        <option value="renovation" {{ request('status') === 'renovation' ? 'selected' : '' }}>Renovation</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['search', 'region_id', 'status']))
                        <a href="{{ route('outlets.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Outlets Grid -->
    <div class="row g-3 mb-4">
        @forelse($outlets as $outlet)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-light text-dark border fw-bold text-uppercase" style="font-size: 0.7rem;">
                                    {{ $outlet->outlet_code }}
                                </span>
                                <span class="badge {{ $outlet->status_badge_class }} ms-1" style="font-size: 0.7rem;">
                                    {{ strtoupper($outlet->status) }}
                                </span>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary text-capitalize" style="font-size: 0.7rem;">
                                {{ str_replace('_', ' ', $outlet->type) }}
                            </span>
                        </div>

                        <h5 class="fw-bold mb-1 text-dark">
                            <a href="{{ route('outlets.show', $outlet) }}" class="text-decoration-none text-dark">
                                {{ $outlet->name }}
                            </a>
                        </h5>

                        <div class="text-muted small mb-3">
                            <i class="bi bi-geo-alt text-danger me-1"></i> {{ $outlet->address }}, {{ $outlet->city }}
                            @if($outlet->region)
                                &bull; <span class="text-primary">{{ $outlet->region->name }}</span>
                            @endif
                        </div>

                        <div class="bg-light rounded p-2 mb-3 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Licensee / Operator:</span>
                                <strong class="text-truncate" style="max-width: 170px;">{{ $outlet->franchisee_name }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Staff Headcount:</span>
                                <span>{{ $outlet->users_count }} Team members</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Inventory SKUs:</span>
                                <span>{{ $outlet->inventory_stocks_count }} Stocked lines</span>
                            </div>
                        </div>

                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                <i class="bi bi-telephone me-1"></i> {{ $outlet->phone }}
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('outlets.show', $outlet) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                                @if(auth()->user()->isSuperAdmin())
                                <a href="{{ route('outlets.edit', $outlet) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center text-muted">
                    <i class="bi bi-shop fs-1 text-muted mb-2"></i>
                    <h5>No outlets match the selected criteria.</h5>
                    <p class="small">Try adjusting search parameters or add a new outlet.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div>
        {{ $outlets->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

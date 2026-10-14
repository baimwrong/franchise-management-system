@extends('layouts.app')

@section('title', 'Products & Menu Catalog')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Central Product & Menu Catalog</h3>
            <p class="text-muted small mb-0">Master brand product lines, standard wholesale supply costs, and recommended MSRP.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
            <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                <i class="bi bi-tag"></i> <span>New Category</span>
            </button>
            <a href="{{ route('admin.bulk-imports.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-arrow-up"></i> <span>Bulk Import</span>
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-circle"></i> <span>Add Product</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search product name, SKU, or barcode..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Status --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    @if(request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-muted text-uppercase">
                    <tr>
                        <th>SKU / Barcode</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th class="text-end">HQ Supply Cost</th>
                        <th class="text-end">MSRP Price</th>
                        <th class="text-end">Min Selling</th>
                        <th class="text-center">Track Stock</th>
                        <th class="text-center">Status</th>
                        @if(auth()->user()->isSuperAdmin())
                        <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold">{{ $product->sku }}</span>
                                @if($product->barcode)
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $product->barcode }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-muted text-truncate" style="max-width: 280px; font-size: 0.75rem;">
                                        {{ $product->description }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="text-capitalize">{{ $product->unit_of_measure }}</td>
                            <td class="text-end fw-semibold text-secondary">${{ number_format($product->cost_price, 2) }}</td>
                            <td class="text-end fw-bold text-primary">${{ number_format($product->suggested_retail_price, 2) }}</td>
                            <td class="text-end text-muted">${{ number_format($product->minimum_selling_price, 2) }}</td>
                            <td class="text-center">
                                @if($product->is_track_inventory)
                                    <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i> Yes</span>
                                @else
                                    <span class="badge bg-light text-muted border">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $product->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            @if(auth()->user()->isSuperAdmin())
                            <td class="text-end">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="bi bi-cup-hot fs-2 text-muted mb-2 d-block"></i>
                                No products found in the catalog matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal: New Category -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="newCategoryModalLabel">Add New Menu Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Seasonal Holiday Specials" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief details about menu collection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

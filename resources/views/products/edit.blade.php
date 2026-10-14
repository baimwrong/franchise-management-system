@extends('layouts.app')

@section('title', 'Edit ' . $product->name)

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item active">{{ $product->sku }}</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark">Edit Product: {{ $product->name }}</h3>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">SKU Code (Unique) <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Barcode / UPC</label>
                        <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Unit of Measure <span class="text-danger">*</span></label>
                        <input type="text" name="unit_of_measure" class="form-control" value="{{ old('unit_of_measure', $product->unit_of_measure) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Pricing & Supply Chain Cost</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">HQ Wholesale Supply Cost ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Suggested Retail Price / MSRP ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="suggested_retail_price" class="form-control" value="{{ old('suggested_retail_price', $product->suggested_retail_price) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Minimum Allowed Selling Price ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="minimum_selling_price" class="form-control" value="{{ old('minimum_selling_price', $product->minimum_selling_price) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_track_inventory" id="trackStock" value="1" {{ $product->is_track_inventory ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="trackStock">Track physical stock quantity for this product</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $product->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="isActive">Product is active and available in stores</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-check2 me-1"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

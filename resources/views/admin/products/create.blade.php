@extends('layouts.admin')
@section('title', 'Add Product')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600">Add New Product</h5>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
@csrf
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Product Details</div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="Auto-generated if empty">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Barcode</label>
                    <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}" placeholder="EAN-13 or custom">
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" id="typeSelect" required>
                        <option value="part" {{ old('type')=='part'?'selected':'' }}>Spare Part</option>
                        <option value="vehicle" {{ old('type')=='vehicle'?'selected':'' }}>Vehicle</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">—</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Brand</label>
                    <select name="brand_id" class="form-select" id="brandSelect">
                        <option value="">—</option>
                        @foreach($brands as $b)<option value="{{ $b->id }}" {{ old('brand_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-sm-6" id="modelRow">
                    <label class="form-label">Model</label>
                    <select name="model_id" class="form-select" id="modelSelect">
                        <option value="">—</option>
                        @foreach($vehicleModels as $m)<option value="{{ $m->id }}" data-brand="{{ $m->brand_id }}" {{ old('model_id')==$m->id?'selected':'' }}>{{ $m->brand->name }} – {{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Pricing</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Purchase Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price','0') }}" step="0.01" min="0" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Selling Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price','0') }}" step="0.01" min="0" required>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Image</div>
            <div class="card-body">
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
        </div>
        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i>Create Product</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light w-100 mt-2">Cancel</a>
    </div>
</div>
</form>
@endsection
<script>
document.getElementById('brandSelect')?.addEventListener('change', function() {
    const bid = this.value;
    document.querySelectorAll('#modelSelect option').forEach(o => {
        o.style.display = (!o.dataset.brand || o.dataset.brand == bid || !bid) ? '' : 'none';
    });
});
</script>

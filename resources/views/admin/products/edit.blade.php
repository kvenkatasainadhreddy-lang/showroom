@extends('layouts.admin')
@section('title', 'Edit Product')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600">Edit: {{ $product->name }}</h5>
</div>
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Product Details</div>
            <div class="card-body row g-3">
                <div class="col-12"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name',$product->name) }}" required></div>
                <div class="col-sm-6"><label class="form-label">SKU</label><input type="text" name="sku" class="form-control" value="{{ old('sku',$product->sku) }}"></div>
                <div class="col-sm-6"><label class="form-label">Barcode</label><input type="text" name="barcode" class="form-control" value="{{ old('barcode',$product->barcode) }}"></div>
                <div class="col-sm-6">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="part" {{ $product->type=='part'?'selected':'' }}>Spare Part</option>
                        <option value="vehicle" {{ $product->type=='vehicle'?'selected':'' }}>Vehicle</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">—</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ $product->category_id==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Brand</label>
                    <select name="brand_id" class="form-select">
                        <option value="">—</option>
                        @foreach($brands as $b)<option value="{{ $b->id }}" {{ $product->brand_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Model</label>
                    <select name="model_id" class="form-select">
                        <option value="">—</option>
                        @foreach($vehicleModels as $m)<option value="{{ $m->id }}" {{ $product->model_id==$m->id?'selected':'' }}>{{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description',$product->description) }}</textarea></div>
                <div class="col-sm-6">
                    <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $product->is_active?'checked':'' }}><label class="form-check-label" for="isActive">Active</label></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Pricing</div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Purchase Price (₹)</label><input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price',$product->purchase_price) }}" step="0.01" min="0"></div>
                <div class="mb-3"><label class="form-label">Selling Price (₹)</label><input type="number" name="selling_price" class="form-control" value="{{ old('selling_price',$product->selling_price) }}" step="0.01" min="0"></div>
            </div>
        </div>
        @if($product->image)
        <div class="card mb-3"><div class="card-body text-center"><img src="{{ asset('storage/'.$product->image) }}" class="img-thumbnail" style="max-height:120px"></div></div>
        @endif
        <div class="card mb-3"><div class="card-header">Replace Image</div><div class="card-body"><input type="file" name="image" class="form-control" accept="image/*"></div></div>
        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light w-100 mt-2">Cancel</a>
    </div>
</div>
</form>
@endsection

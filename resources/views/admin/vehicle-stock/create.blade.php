@extends('layouts.admin')
@section('title','Add Vehicle to Stock')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="{{ route('admin.vehicle-stock.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-600">Add Vehicle to Stock</h5>
</div>
<div class="card" style="max-width:600px"><div class="card-body">
<form method="POST" action="{{ route('admin.vehicle-stock.store') }}">@csrf
<div class="row g-3">
  <div class="col-12">
    <label class="form-label">Variant <span class="text-danger">*</span></label>
    <select name="variant_id" class="form-select" required>
      <option value="">— Select Variant —</option>
      @foreach($variants as $v)
      <option value="{{ $v->id }}" {{ old('variant_id')==$v->id?'selected':'' }}>{{ $v->vehicleModel?->brand?->name }} {{ $v->vehicleModel?->name }} — {{ $v->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Chassis Number <span class="text-danger">*</span></label>
    <input type="text" name="chassis_number" class="form-control text-uppercase" value="{{ old('chassis_number') }}" required placeholder="e.g. ME4JC657*M8000001">
  </div>
  <div class="col-md-6">
    <label class="form-label">Engine Number</label>
    <input type="text" name="engine_number" class="form-control" value="{{ old('engine_number') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Color</label>
    <input type="text" name="color" class="form-control" value="{{ old('color') }}" placeholder="e.g. Matte Axis Grey">
  </div>
  <div class="col-md-6">
    <label class="form-label">Branch</label>
    <select name="branch_id" class="form-select">
      <option value="">— Select Branch —</option>
      @foreach($branches as $b)<option value="{{ $b->id }}" {{ old('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Purchase Price (₹) <span class="text-danger">*</span></label>
    <div class="input-group"><span class="input-group-text">₹</span>
    <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', 0) }}" min="0" step="0.01" required></div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Selling Price (₹) <span class="text-muted small">— leave 0 to use variant base price</span></label>
    <div class="input-group"><span class="input-group-text">₹</span>
    <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', 0) }}" min="0" step="0.01"></div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Received Date</label>
    <input type="date" name="received_date" class="form-control" value="{{ old('received_date', date('Y-m-d')) }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Notes</label>
    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Any remarks">
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Add to Stock</button>
    <a href="{{ route('admin.vehicle-stock.index') }}" class="btn btn-light ms-2">Cancel</a>
  </div>
</div>
</form></div></div>
@endsection

@extends('layouts.admin')
@section('title','Edit Vehicle Stock')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="{{ route('admin.vehicle-stock.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-600">Edit: <code>{{ $vehicleStock->chassis_number }}</code></h5>
</div>
<div class="card" style="max-width:600px"><div class="card-body">
<form method="POST" action="{{ route('admin.vehicle-stock.update', $vehicleStock) }}">@csrf @method('PUT')
<div class="row g-3">
  <div class="col-12">
    <label class="form-label">Variant</label>
    <select name="variant_id" class="form-select">
      @foreach($variants as $v)
      <option value="{{ $v->id }}" {{ $vehicleStock->variant_id==$v->id?'selected':'' }}>{{ $v->vehicleModel?->brand?->name }} {{ $v->vehicleModel?->name }} — {{ $v->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Chassis Number <span class="text-danger">*</span></label>
    <input type="text" name="chassis_number" class="form-control text-uppercase" value="{{ old('chassis_number', $vehicleStock->chassis_number) }}" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Engine Number</label>
    <input type="text" name="engine_number" class="form-control" value="{{ old('engine_number', $vehicleStock->engine_number) }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Color</label>
    <input type="text" name="color" class="form-control" value="{{ old('color', $vehicleStock->color) }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-select" required>
      <option value="available" {{ $vehicleStock->status=='available'?'selected':'' }}>Available</option>
      <option value="reserved"  {{ $vehicleStock->status=='reserved'?'selected':'' }}>Reserved</option>
      <option value="sold"      {{ $vehicleStock->status=='sold'?'selected':'' }}>Sold</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Purchase Price (₹) <span class="text-danger">*</span></label>
    <div class="input-group"><span class="input-group-text">₹</span>
    <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', $vehicleStock->purchase_price) }}" step="0.01" required></div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Selling Price (₹)</label>
    <div class="input-group"><span class="input-group-text">₹</span>
    <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', $vehicleStock->selling_price) }}" step="0.01"></div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Branch</label>
    <select name="branch_id" class="form-select">
      <option value="">— No Branch —</option>
      @foreach($branches as $b)<option value="{{ $b->id }}" {{ $vehicleStock->branch_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Received Date</label>
    <input type="date" name="received_date" class="form-control" value="{{ old('received_date', $vehicleStock->received_date?->format('Y-m-d')) }}">
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
    <a href="{{ route('admin.vehicle-stock.index') }}" class="btn btn-light ms-2">Cancel</a>
  </div>
</div>
</form></div></div>
@endsection

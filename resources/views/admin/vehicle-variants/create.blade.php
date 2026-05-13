@extends('layouts.admin')
@section('title','Add Variant')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="{{ route('admin.vehicle-variants.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-600">Add Vehicle Variant</h5>
</div>
<div class="card" style="max-width:480px"><div class="card-body">
<form method="POST" action="{{ route('admin.vehicle-variants.store') }}">@csrf
<div class="mb-3">
  <label class="form-label">Model <span class="text-danger">*</span></label>
  <select name="model_id" class="form-select" required>
    <option value="">— Select Model —</option>
    @foreach($models as $m)<option value="{{ $m->id }}" {{ old('model_id')==$m->id?'selected':'' }}>{{ $m->brand?->name }} {{ $m->name }}</option>@endforeach
  </select>
</div>
<div class="mb-3">
  <label class="form-label">Variant Name <span class="text-danger">*</span></label>
  <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Activa 6G – Pearl Nightstar Black" required>
</div>
<div class="mb-3">
  <label class="form-label">Color</label>
  <input type="text" name="color" class="form-control" value="{{ old('color') }}" placeholder="e.g. Matte Blue Metallic">
</div>
<div class="alert alert-info small py-2 mb-3">
  <i class="bi bi-info-circle me-1"></i>
  <strong>Prices are set per chassis</strong> — go to <a href="{{ route('admin.vehicle-stock.create') }}">Add Vehicle Stock</a> to set purchase &amp; selling price per chassis number.
</div>
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Create Variant</button>
<a href="{{ route('admin.vehicle-variants.index') }}" class="btn btn-light ms-2">Cancel</a>
</form></div></div>
@endsection

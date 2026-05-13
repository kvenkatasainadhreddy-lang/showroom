@extends('layouts.admin')
@section('title','Edit Variant')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="{{ route('admin.vehicle-variants.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-600">Edit: {{ $vehicleVariant->name }}</h5>
</div>
<div class="card" style="max-width:480px"><div class="card-body">
<form method="POST" action="{{ route('admin.vehicle-variants.update', $vehicleVariant) }}">@csrf @method('PUT')
<div class="mb-3">
  <label class="form-label">Model <span class="text-danger">*</span></label>
  <select name="model_id" class="form-select" required>
    @foreach($models as $m)<option value="{{ $m->id }}" {{ $vehicleVariant->model_id==$m->id?'selected':'' }}>{{ $m->brand?->name }} {{ $m->name }}</option>@endforeach
  </select>
</div>
<div class="mb-3">
  <label class="form-label">Variant Name <span class="text-danger">*</span></label>
  <input type="text" name="name" class="form-control" value="{{ old('name', $vehicleVariant->name) }}" required>
</div>
<div class="mb-3">
  <label class="form-label">Color</label>
  <input type="text" name="color" class="form-control" value="{{ old('color', $vehicleVariant->color) }}">
</div>
<div class="mb-3"><div class="form-check">
  <input type="hidden" name="is_active" value="0">
  <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $vehicleVariant->is_active?'checked':'' }}>
  <label class="form-check-label">Active (shows in sale form)</label>
</div></div>
<div class="alert alert-info small py-2 mb-3">
  <i class="bi bi-info-circle me-1"></i>
  Prices are managed per chassis — visit <a href="{{ route('admin.vehicle-stock.index') }}">Vehicle Stock</a> to edit purchase &amp; selling prices.
</div>
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
<a href="{{ route('admin.vehicle-variants.index') }}" class="btn btn-light ms-2">Cancel</a>
</form></div></div>
@endsection

@extends('layouts.admin')
@section('title','Edit Vehicle Model')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.vehicle-models.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Edit: {{ $vehicleModel->name }}</h5></div>
<div class="card" style="max-width:450px"><div class="card-body">
<form method="POST" action="{{ route('admin.vehicle-models.update', $vehicleModel) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Brand <span class="text-danger">*</span></label><select name="brand_id" class="form-select" required><option value="">— Select Brand —</option>@foreach($brands as $b)<option value="{{ $b->id }}" {{ $vehicleModel->brand_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label">Model Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $vehicleModel->name) }}" required></div>
<div class="mb-3"><label class="form-label">Year</label><input type="number" name="year" class="form-control" value="{{ old('year', $vehicleModel->year) }}"></div>
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
<a href="{{ route('admin.vehicle-models.index') }}" class="btn btn-light ms-2">Cancel</a>
</form></div></div>
@endsection

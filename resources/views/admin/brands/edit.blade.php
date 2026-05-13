@extends('layouts.admin')
@section('title','Edit Brand')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.brands.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Edit: {{ $brand->name }}</h5></div>
<div class="card" style="max-width:450px"><div class="card-body">
<form method="POST" action="{{ route('admin.brands.update', $brand) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Brand Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $brand->name) }}" required></div>
<div class="mb-3"><label class="form-label">Country</label><input type="text" name="country" class="form-control" value="{{ old('country', $brand->country) }}"></div>
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
<a href="{{ route('admin.brands.index') }}" class="btn btn-light ms-2">Cancel</a>
</form></div></div>
@endsection

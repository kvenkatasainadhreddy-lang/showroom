@extends('layouts.admin')
@section('title','Add Brand')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.brands.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Add Brand</h5></div>
<div class="card" style="max-width:450px"><div class="card-body">
<form method="POST" action="{{ route('admin.brands.store') }}">@csrf
<div class="mb-3"><label class="form-label">Brand Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
<div class="mb-3"><label class="form-label">Country of Origin</label><input type="text" name="country" class="form-control" value="{{ old('country') }}" placeholder="e.g. Japan"></div>
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Create Brand</button>
<a href="{{ route('admin.brands.index') }}" class="btn btn-light ms-2">Cancel</a>
</form></div></div>
@endsection

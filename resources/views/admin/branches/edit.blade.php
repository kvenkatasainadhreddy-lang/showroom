@extends('layouts.admin')
@section('title','Edit Branch')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Edit: {{ $branch->name }}</h5></div>
<div class="card" style="max-width:500px"><div class="card-body">
<form method="POST" action="{{ route('admin.branches.update', $branch) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Branch Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required></div>
<div class="mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="{{ old('city', $branch->city) }}"></div>
<div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2">{{ old('address', $branch->address) }}</textarea></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}"></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $branch->is_active?'checked':'' }}><label class="form-check-label">Active</label></div>
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
<a href="{{ route('admin.branches.index') }}" class="btn btn-light ms-2">Cancel</a>
</form>
</div></div>
@endsection

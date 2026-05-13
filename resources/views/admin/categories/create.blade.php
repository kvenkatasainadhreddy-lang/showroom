@extends('layouts.admin')
@section('title','Add Category')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Add Category</h5></div>
<div class="card" style="max-width:500px"><div class="card-body">
<form method="POST" action="{{ route('admin.categories.store') }}">@csrf
<div class="mb-3"><label class="form-label">Category Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
<div class="mb-3"><label class="form-label">Type <span class="text-danger">*</span></label><select name="type" class="form-select" required><option value="vehicle">Vehicle</option><option value="part">Part</option></select></div>
<div class="mb-3"><label class="form-label">Parent Category</label><select name="parent_id" class="form-select"><option value="">— None —</option>@foreach($parents as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Create</button>
<a href="{{ route('admin.categories.index') }}" class="btn btn-light ms-2">Cancel</a>
</form></div></div>
@endsection

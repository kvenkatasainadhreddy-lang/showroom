@extends('layouts.admin')
@section('title','Edit Customer')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600">Edit: {{ $customer->name }}</h5>
</div>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}"></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}"></div>
        <div class="mb-3"><label class="form-label">Type</label><select name="type" class="form-select"><option value="individual" {{ $customer->type=='individual'?'selected':'' }}>Individual</option><option value="company" {{ $customer->type=='company'?'selected':'' }}>Company</option></select></div>
        <div class="mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control" value="{{ old('company_name', $customer->company_name) }}"></div>
        <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea></div>
        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-light ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection

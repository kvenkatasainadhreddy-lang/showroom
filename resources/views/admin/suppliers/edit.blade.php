@extends('layouts.admin')
@section('title','Edit Supplier')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Edit: {{ $supplier->name }}</h5></div>
<div class="card" style="max-width:600px"><div class="card-body">
    <form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Supplier Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required></div>
    <div class="mb-3"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}"></div>
    <div class="row g-3 mb-3"><div class="col-sm-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}"></div><div class="col-sm-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}"></div></div>
    <div class="mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="{{ old('city', $supplier->city) }}"></div>
    <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea></div>
    <div class="mb-3"><label class="form-label">GST Number</label><input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $supplier->gst_number) }}"></div>
    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light ms-2">Cancel</a>
    </form>
</div></div>
@endsection

@extends('layouts.admin')
@section('title','Add User')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Add User</h5></div>
<div class="card" style="max-width:500px"><div class="card-body">
    <form method="POST" action="{{ route('admin.users.store') }}">@csrf
    <div class="mb-3"><label class="form-label">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
    <div class="mb-3"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
    <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="8"></div>
    <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Role <span class="text-danger">*</span></label><select name="role" class="form-select" required>@foreach($roles as $r)<option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>@endforeach</select></div>
    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Create User</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light ms-2">Cancel</a>
    </form>
</div></div>
@endsection

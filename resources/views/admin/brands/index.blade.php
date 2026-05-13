@extends('layouts.admin')
@section('title','Brands')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 fw-600">Brands</h5>
<a href="{{ route('admin.brands.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Brand</a></div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead><tr><th class="px-3">Brand</th><th>Country</th><th class="text-center">Models</th><th class="text-center">Products</th><th></th></tr></thead>
    <tbody>
    @forelse($brands as $b)
    <tr>
        <td class="px-3 fw-500">{{ $b->name }}</td>
        <td class="small">{{ $b->country ?? '—' }}</td>
        <td class="text-center">{{ $b->vehicle_models_count }}</td>
        <td class="text-center">{{ $b->products_count }}</td>
        <td>
            <a href="{{ route('admin.brands.edit', $b) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>
            <form class="d-inline" method="POST" action="{{ route('admin.brands.destroy', $b) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>
        </td>
    </tr>
    @empty<tr><td colspan="5" class="text-center py-4 text-muted">No brands</td></tr>@endforelse
    </tbody>
</table></div></div>
@endsection

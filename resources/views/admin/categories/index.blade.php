@extends('layouts.admin')
@section('title','Categories')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 fw-600">Categories</h5>
<a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Category</a></div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead><tr><th class="px-3">Name</th><th>Type</th><th>Parent</th><th></th></tr></thead>
    <tbody>
    @forelse($categories as $cat)
    <tr>
        <td class="px-3 fw-500">{{ $cat->name }}</td>
        <td><span class="badge {{ $cat->type=='vehicle'?'bg-primary-subtle text-primary':'bg-warning-subtle text-warning-emphasis' }}">{{ ucfirst($cat->type) }}</span></td>
        <td class="small text-muted">{{ $cat->parent->name ?? '—' }}</td>
        <td>
            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>
            <form class="d-inline" method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>
        </td>
    </tr>
    @empty<tr><td colspan="4" class="text-center py-4 text-muted">No categories</td></tr>@endforelse
    </tbody>
</table></div></div>
@endsection

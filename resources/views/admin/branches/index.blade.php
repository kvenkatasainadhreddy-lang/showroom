@extends('layouts.admin')
@section('title','Branches')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 fw-600">Branches</h5>
@can('branches.create')<a href="{{ route('admin.branches.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Branch</a>@endcan</div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead><tr><th class="px-3">Branch Name</th><th>City</th><th>Phone</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($branches as $b)
    <tr>
        <td class="px-3 fw-500">{{ $b->name }}</td>
        <td class="small">{{ $b->city ?? '—' }}</td>
        <td class="small">{{ $b->phone ?? '—' }}</td>
        <td><span class="badge {{ $b->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $b->is_active ? 'Active' : 'Inactive' }}</span></td>
        <td>
            @can('branches.edit')<a href="{{ route('admin.branches.edit', $b) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>@endcan
            @can('branches.delete')<form class="d-inline" method="POST" action="{{ route('admin.branches.destroy', $b) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endcan
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="text-center py-4 text-muted">No branches</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection

@extends('layouts.admin')
@section('title','Vehicle Models')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 fw-600">Vehicle Models</h5>
<a href="{{ route('admin.vehicle-models.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Model</a></div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead><tr><th class="px-3">Model Name</th><th>Brand</th><th>Year</th><th></th></tr></thead>
    <tbody>
    @forelse($models as $m)
    <tr>
        <td class="px-3 fw-500">{{ $m->name }}</td>
        <td class="small">{{ $m->brand->name ?? '—' }}</td>
        <td class="small text-muted">{{ $m->year ?? '—' }}</td>
        <td>
            <a href="{{ route('admin.vehicle-models.edit', $m) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>
            <form class="d-inline" method="POST" action="{{ route('admin.vehicle-models.destroy', $m) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>
        </td>
    </tr>
    @empty<tr><td colspan="4" class="text-center py-4 text-muted">No models</td></tr>@endforelse
    </tbody>
</table></div></div>
@endsection

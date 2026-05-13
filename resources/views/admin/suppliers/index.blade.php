@extends('layouts.admin')
@section('title','Suppliers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-600">Suppliers</h5>
    @can('suppliers.create')<a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Supplier</a>@endcan
</div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2 align-items-end">
    <div class="col-sm-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Name or phone…" value="{{ request('search') }}"></div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Search</button> <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light btn-sm">Reset</a></div>
</form></div></div>
<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Name</th><th>Phone</th><th>Email</th><th>City</th><th class="text-center">Purchases</th><th></th></tr></thead>
        <tbody>
        @forelse($suppliers as $s)
        <tr>
            <td class="px-3 fw-500">{{ $s->name }}</td>
            <td class="small">{{ $s->phone ?? '—' }}</td>
            <td class="small">{{ $s->email ?? '—' }}</td>
            <td class="small">{{ $s->city ?? '—' }}</td>
            <td class="text-center">{{ $s->purchases_count }}</td>
            <td>
                <a href="{{ route('admin.suppliers.show', $s) }}" class="btn btn-xs btn-light"><i class="bi bi-eye"></i></a>
                @can('suppliers.edit')<a href="{{ route('admin.suppliers.edit', $s) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>@endcan
                @can('suppliers.delete')<form class="d-inline" method="POST" action="{{ route('admin.suppliers.destroy', $s) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endcan
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center py-4 text-muted">No suppliers found</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer">{{ $suppliers->links() }}</div>
</div>
@endsection

@extends('layouts.admin')
@section('title','Customers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-600">Customers</h5>
    @can('customers.create')<a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Customer</a>@endcan
</div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2 align-items-end">
    <div class="col-sm-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Name or phone…" value="{{ request('search') }}"></div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Search</button> <a href="{{ route('admin.customers.index') }}" class="btn btn-light btn-sm">Reset</a></div>
</form></div></div>
<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Name</th><th>Phone</th><th>Email</th><th>Type</th><th class="text-center">Sales</th><th></th></tr></thead>
        <tbody>
        @forelse($customers as $c)
        <tr>
            <td class="px-3 fw-500">{{ $c->name }}</td>
            <td class="small">{{ $c->phone ?? '—' }}</td>
            <td class="small">{{ $c->email ?? '—' }}</td>
            <td><span class="badge bg-light text-dark">{{ ucfirst($c->type) }}</span></td>
            <td class="text-center">{{ $c->sales_count }}</td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-xs btn-light"><i class="bi bi-eye"></i></a>
                    @can('customers.edit')<a href="{{ route('admin.customers.edit', $c) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>@endcan
                    @can('customers.delete')<form class="d-inline" method="POST" action="{{ route('admin.customers.destroy', $c) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endcan
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center py-4 text-muted">No customers found</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer">{{ $customers->links() }}</div>
</div>
@endsection

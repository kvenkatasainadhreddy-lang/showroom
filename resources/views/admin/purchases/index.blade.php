@extends('layouts.admin')
@section('title', 'Purchases')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-600">Purchases</h5>
    @can('purchases.create')<a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Purchase</a>@endcan
</div>
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Reference No…" value="{{ request('search') }}"></div>
            <div class="col-sm-3 col-lg-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="received" {{ request('status')=='received'?'selected':'' }}>Received</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-sm-3 col-lg-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
            <div class="col-sm-3 col-lg-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button> <a href="{{ route('admin.purchases.index') }}" class="btn btn-light btn-sm">Reset</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Reference</th><th>Supplier</th><th>Branch</th><th>Date</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($purchases as $p)
        <tr>
            <td class="px-3 fw-500"><a href="{{ route('admin.purchases.show', $p) }}" class="text-decoration-none">{{ $p->reference_no }}</a></td>
            <td class="small">{{ $p->supplier->name ?? '—' }}</td>
            <td class="small text-muted">{{ $p->branch->name ?? '—' }}</td>
            <td class="small">{{ $p->purchase_date->format('d M Y') }}</td>
            <td class="text-end fw-500">₹{{ number_format($p->total,2) }}</td>
            <td><span class="badge {{ $p->status=='received'?'bg-success-subtle text-success':($p->status=='cancelled'?'bg-danger-subtle text-danger':'bg-warning-subtle text-warning-emphasis') }}">{{ ucfirst($p->status) }}</span></td>
            <td>
                <a href="{{ route('admin.purchases.show', $p) }}" class="btn btn-xs btn-light"><i class="bi bi-eye"></i></a>
                @can('purchases.delete')
                <form class="d-inline" method="POST" action="{{ route('admin.purchases.destroy', $p) }}" onsubmit="return confirm('Delete purchase?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endcan
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-muted">No purchases found</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer">{{ $purchases->links() }}</div>
</div>
@endsection

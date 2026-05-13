@extends('layouts.admin')
@section('title', $customer->name)
@section('content')
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600 flex-fill">{{ $customer->name }}</h5>
    @can('customers.edit')<a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil me-1"></i>Edit</a>@endcan
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Contact Info</div>
            <div class="card-body">
                <div class="mb-2"><div class="small text-muted">Phone</div><div>{{ $customer->phone ?? '—' }}</div></div>
                <div class="mb-2"><div class="small text-muted">Email</div><div>{{ $customer->email ?? '—' }}</div></div>
                <div class="mb-2"><div class="small text-muted">Type</div><span class="badge bg-light text-dark">{{ ucfirst($customer->type) }}</span></div>
                @if($customer->company_name)<div class="mb-2"><div class="small text-muted">Company</div><div>{{ $customer->company_name }}</div></div>@endif
                @if($customer->address)<div class="mb-2"><div class="small text-muted">Address</div><div class="small">{{ $customer->address }}</div></div>@endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Purchase History</div>
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th class="px-3">Invoice</th><th>Date</th><th class="text-end">Total</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($customer->sales as $s)
                <tr>
                    <td class="px-3"><a href="{{ route('admin.sales.show', $s) }}" class="text-decoration-none">{{ $s->invoice_no }}</a></td>
                    <td class="small">{{ $s->sale_date->format('d M Y') }}</td>
                    <td class="text-end fw-500">₹{{ number_format($s->total,2) }}</td>
                    <td><span class="badge badge-status-{{ $s->payment_status }}">{{ ucfirst($s->payment_status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3 small">No purchases</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection

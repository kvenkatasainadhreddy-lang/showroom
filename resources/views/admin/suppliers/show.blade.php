@extends('layouts.admin')
@section('title', $supplier->name)
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">{{ $supplier->name }}</h5></div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Info</div><div class="card-body">
            <div class="mb-2"><div class="small text-muted">Contact</div><div>{{ $supplier->contact_person ?? '—' }}</div></div>
            <div class="mb-2"><div class="small text-muted">Phone</div><div>{{ $supplier->phone ?? '—' }}</div></div>
            <div class="mb-2"><div class="small text-muted">Email</div><div>{{ $supplier->email ?? '—' }}</div></div>
            <div class="mb-2"><div class="small text-muted">City</div><div>{{ $supplier->city ?? '—' }}</div></div>
            <div class="mb-2"><div class="small text-muted">GST</div><div><code>{{ $supplier->gst_number ?? '—' }}</code></div></div>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Purchase History</div>
        <div class="table-responsive"><table class="table table-sm mb-0">
            <thead><tr><th class="px-3">Reference</th><th>Date</th><th class="text-end">Total</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($supplier->purchases as $p)
            <tr>
                <td class="px-3"><a href="{{ route('admin.purchases.show', $p) }}" class="text-decoration-none">{{ $p->reference_no }}</a></td>
                <td class="small">{{ $p->purchase_date->format('d M Y') }}</td>
                <td class="text-end fw-500">₹{{ number_format($p->total,2) }}</td>
                <td><span class="badge {{ $p->status=='received'?'bg-success-subtle text-success':'bg-warning-subtle text-warning-emphasis' }}">{{ ucfirst($p->status) }}</span></td>
            </tr>
            @empty<tr><td colspan="4" class="text-center text-muted py-3 small">No purchases</td></tr>@endforelse
            </tbody>
        </table></div>
        </div>
    </div>
</div>
@endsection

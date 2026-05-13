@extends('layouts.admin')
@section('title', $purchase->reference_no)
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600 flex-fill">{{ $purchase->reference_no }}</h5>
    <span class="badge {{ $purchase->status=='received'?'bg-success':($purchase->status=='cancelled'?'bg-danger':'bg-warning text-dark') }}">{{ ucfirst($purchase->status) }}</span>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Items</div>
            <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th class="px-3">Product</th><th class="text-center">Qty</th><th class="text-end">Cost Price</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td class="px-3 small">{{ $item->product->name ?? '—' }}<br><span class="text-muted">{{ $item->product->sku ?? '' }}</span></td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">₹{{ number_format($item->cost_price,2) }}</td>
                    <td class="text-end fw-500">₹{{ number_format($item->total,2) }}</td>
                </tr>
                @endforeach
                <tr class="table-light"><td colspan="3" class="text-end fw-700 px-3">Total</td><td class="text-end fw-700 text-primary">₹{{ number_format($purchase->total,2) }}</td></tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Purchase Info</div>
            <div class="card-body">
                <div class="mb-2"><div class="small text-muted">Supplier</div><div class="fw-500">{{ $purchase->supplier->name ?? '—' }}</div></div>
                <div class="mb-2"><div class="small text-muted">Branch</div><div>{{ $purchase->branch->name ?? '—' }}</div></div>
                <div class="mb-2"><div class="small text-muted">Date</div><div>{{ $purchase->purchase_date->format('d M Y') }}</div></div>
                @if($purchase->notes)<div><div class="small text-muted">Notes</div><div class="small">{{ $purchase->notes }}</div></div>@endif
            </div>
        </div>
    </div>
</div>
@endsection

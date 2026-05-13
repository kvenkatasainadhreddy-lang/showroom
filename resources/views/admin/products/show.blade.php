@extends('layouts.admin')
@section('title', $product->name)
@section('content')
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600 flex-fill">{{ $product->name }}</h5>
    @can('products.edit')<a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil me-1"></i>Edit</a>@endcan
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6"><div class="small text-muted">SKU</div><div class="fw-500">{{ $product->sku ?? '—' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Barcode</div><div class="fw-500"><code>{{ $product->barcode ?? '—' }}</code></div></div>
                    <div class="col-sm-6"><div class="small text-muted">Type</div><span class="badge {{ $product->type=='vehicle'?'bg-primary':'bg-warning text-dark' }}">{{ ucfirst($product->type) }}</span></div>
                    <div class="col-sm-6"><div class="small text-muted">Category</div><div>{{ $product->category->name ?? '—' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Brand</div><div>{{ $product->brand->name ?? '—' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Model</div><div>{{ $product->vehicleModel->name ?? '—' }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Purchase Price</div><div class="fw-500">₹{{ number_format($product->purchase_price,2) }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Selling Price</div><div class="fw-500 text-success">₹{{ number_format($product->selling_price,2) }}</div></div>
                    <div class="col-sm-6"><div class="small text-muted">Margin</div><div class="fw-500 {{ $product->getMarginPercent()>0?'text-success':'text-danger' }}">{{ $product->getMarginPercent() }}%</div></div>
                    <div class="col-12"><div class="small text-muted mb-1">Description</div><div>{{ $product->description ?? '—' }}</div></div>
                </div>
            </div>
        </div>

        <!-- Inventory across branches -->
        <div class="card">
            <div class="card-header">Inventory</div>
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th class="px-3">Branch</th><th class="px-3 text-center">Qty</th><th class="px-3 text-center">Min</th><th class="px-3">Status</th></tr></thead>
                <tbody>
                @forelse($product->inventories as $inv)
                <tr>
                    <td class="px-3">{{ $inv->branch->name }}</td>
                    <td class="px-3 text-center fw-600">{{ $inv->quantity }}</td>
                    <td class="px-3 text-center text-muted">{{ $inv->min_quantity }}</td>
                    <td class="px-3"><span class="badge {{ $inv->isLowStock() ? 'bg-danger' : 'bg-success' }}">{{ $inv->isLowStock() ? 'Low Stock' : 'OK' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No inventory records</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @if($product->image)
        <div class="card mb-3"><div class="card-body text-center"><img src="{{ asset('storage/'.$product->image) }}" class="img-fluid rounded" style="max-height:180px"></div></div>
        @endif
        <div class="card">
            <div class="card-header">Recent Sales</div>
            <div class="card-body p-0">
                @forelse($product->saleItems->take(8) as $si)
                <div class="d-flex justify-content-between px-3 py-2 border-bottom small">
                    <a href="{{ route('admin.sales.show', $si->sale) }}" class="text-decoration-none">{{ $si->sale->invoice_no }}</a>
                    <span>{{ $si->quantity }} × ₹{{ number_format($si->unit_price,2) }}</span>
                </div>
                @empty
                <p class="text-center text-muted py-3 small mb-0">No sales yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-600">Products</h5>
    @can('products.create')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
    @endcan
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-5 col-lg-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Name / SKU / Barcode…" value="{{ request('search') }}"></div>
            <div class="col-sm-3 col-lg-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="vehicle" {{ request('type')=='vehicle'?'selected':'' }}>Vehicle</option>
                    <option value="part" {{ request('type')=='part'?'selected':'' }}>Part</option>
                </select>
            </div>
            <div class="col-sm-4 col-lg-2">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button> <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">Reset</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Product</th><th>SKU</th><th>Barcode</th><th>Type</th><th>Category</th><th class="text-end">Buy Price</th><th class="text-end">Sell Price</th><th class="text-end">Margin</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($products as $p)
        <tr>
            <td class="px-3">
                <div class="fw-500">{{ $p->name }}</div>
                <div class="text-muted small">{{ $p->brand->name ?? '' }} {{ $p->vehicleModel->name ?? '' }}</div>
            </td>
            <td class="small text-muted">{{ $p->sku }}</td>
            <td class="small"><code>{{ $p->barcode }}</code></td>
            <td><span class="badge {{ $p->type=='vehicle' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis' }}">{{ ucfirst($p->type) }}</span></td>
            <td class="small">{{ $p->category->name ?? '—' }}</td>
            <td class="text-end">₹{{ number_format($p->purchase_price,2) }}</td>
            <td class="text-end fw-500">₹{{ number_format($p->selling_price,2) }}</td>
            <td class="text-end {{ $p->getMarginPercent()>0 ? 'text-success' : 'text-danger' }}">{{ $p->getMarginPercent() }}%</td>
            <td><span class="badge {{ $p->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('admin.products.show', $p) }}" class="btn btn-xs btn-light"><i class="bi bi-eye"></i></a>
                    @can('products.edit')<a href="{{ route('admin.products.edit', $p) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>@endcan
                    @can('products.delete')
                    <form method="POST" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    @endcan
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="10" class="text-center py-4 text-muted">No products found</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer">{{ $products->links() }}</div>
</div>
@endsection

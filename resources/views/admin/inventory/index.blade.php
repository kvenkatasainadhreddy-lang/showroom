@extends('layouts.admin')
@section('title','Inventory')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-600">Inventory</h5>
</div>
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Product name or SKU…" value="{{ request('search') }}"></div>
            <div class="col-sm-3 col-lg-3">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-3 col-lg-2">
                <div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="low_stock" id="lowStk" value="1" {{ request('low_stock')?'checked':'' }}><label class="form-check-label small" for="lowStk">Low Stock Only</label></div>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button> <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">Reset</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Product</th><th>Branch</th><th>Type</th><th class="text-center">Qty</th><th class="text-center">Min</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($inventory as $inv)
        <tr>
            <td class="px-3">
                <div class="fw-500 small">{{ $inv->product->name ?? '—' }}</div>
                <div class="text-muted" style="font-size:.75rem">{{ $inv->product->sku }}</div>
            </td>
            <td class="small">{{ $inv->branch->name ?? '—' }}</td>
            <td><span class="badge {{ $inv->product->type=='vehicle'?'bg-primary-subtle text-primary':'bg-warning-subtle text-warning-emphasis' }}">{{ ucfirst($inv->product->type ?? '') }}</span></td>
            <td class="text-center fw-700 {{ $inv->isLowStock() ? 'text-danger' : '' }}">{{ $inv->quantity }}</td>
            <td class="text-center text-muted small">{{ $inv->min_quantity }}</td>
            <td><span class="badge {{ $inv->isLowStock() ? 'bg-danger' : 'bg-success' }}">{{ $inv->isLowStock() ? 'Low Stock' : 'OK' }}</span></td>
            <td>
                @can('inventory.adjust')
                <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#adjModal{{ $inv->id }}"><i class="bi bi-sliders"></i> Adjust</button>
                <!-- Adjust Modal -->
                <div class="modal fade" id="adjModal{{ $inv->id }}" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content">
                        <div class="modal-header"><h6 class="modal-title">Adjust Stock – {{ $inv->product->name }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <form method="POST" action="{{ route('admin.inventory.adjust', $inv) }}">
                        @csrf
                        <div class="modal-body row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select"><option value="in">Add (In)</option><option value="out">Remove (Out)</option><option value="adjustment">Set Exact</option></select>
                            </div>
                            <div class="col-sm-6"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required></div>
                            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Apply</button></div>
                        </form>
                    </div></div>
                </div>
                @endcan
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-muted">No inventory records</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer">{{ $inventory->links() }}</div>
</div>
@endsection

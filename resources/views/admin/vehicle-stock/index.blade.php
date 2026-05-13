@extends('layouts.admin')
@section('title','Vehicle Stock / Chassis Registry')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-600"><i class="bi bi-car-front me-2 text-success"></i>Vehicle Stock</h5>
  <a href="{{ route('admin.vehicle-stock.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Vehicle</a>
</div>

<form class="card mb-3"><div class="card-body py-2">
  <div class="row g-2 align-items-end">
    <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Chassis / Engine Number…" value="{{ request('search') }}"></div>
    <div class="col-md-2">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="available" {{ request('status')=='available'?'selected':'' }}>Available</option>
        <option value="reserved" {{ request('status')=='reserved'?'selected':'' }}>Reserved</option>
        <option value="sold" {{ request('status')=='sold'?'selected':'' }}>Sold</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="branch_id" class="form-select form-select-sm">
        <option value="">All Branches</option>
        @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
      <a href="{{ route('admin.vehicle-stock.index') }}" class="btn btn-light btn-sm">Reset</a>
    </div>
  </div>
</div></form>

<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead><tr>
    <th class="px-3">Chassis No.</th><th>Variant</th><th>Color</th>
    <th class="text-end">Purchase</th><th class="text-end">Selling</th>
    <th>Branch</th><th>Received</th><th class="text-center">Status</th><th></th>
  </tr></thead>
  <tbody>
  @forelse($stock as $s)
  <tr>
    <td class="px-3"><code class="fw-600">{{ $s->chassis_number }}</code></td>
    <td class="small">{{ $s->variant?->vehicleModel?->brand?->name }} {{ $s->variant?->vehicleModel?->name }}<br>
      <span class="text-muted">{{ $s->variant?->name }}</span></td>
    <td class="small">{{ $s->color ?? '—' }}</td>
    <td class="text-end small">₹{{ number_format($s->purchase_price, 2) }}</td>
    <td class="text-end small fw-500">₹{{ number_format($s->effective_price, 2) }}</td>
    <td class="small">{{ $s->branch?->name ?? '—' }}</td>
    <td class="small text-muted">{{ $s->received_date?->format('d M Y') ?? '—' }}</td>
    <td class="text-center">
      @php $c=['available'=>'bg-success-subtle text-success-emphasis','reserved'=>'bg-warning-subtle text-warning-emphasis','sold'=>'bg-secondary-subtle text-secondary'][$s->status] @endphp
      <span class="badge {{ $c }}">{{ ucfirst($s->status) }}</span>
    </td>
    <td>
      @if($s->status === 'available')
      <a href="{{ route('admin.vehicle-stock.edit', $s) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>
      @endif
      <form class="d-inline" method="POST" action="{{ route('admin.vehicle-stock.destroy', $s) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
        <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
      </form>
    </td>
  </tr>
  @empty<tr><td colspan="9" class="text-center py-4 text-muted">No vehicle stock found</td></tr>@endforelse
  </tbody>
</table></div>
<div class="px-3 py-2">{{ $stock->links() }}</div>
</div>
@endsection

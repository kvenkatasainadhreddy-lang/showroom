@extends('layouts.admin')
@section('title','Stock Movements')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 class="mb-0 fw-600"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Stock Movement Log</h5>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3 col-lg-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="in"         {{ request('type')=='in'?'selected':'' }}>In</option>
                    <option value="out"        {{ request('type')=='out'?'selected':'' }}>Out</option>
                    <option value="adjustment" {{ request('type')=='adjustment'?'selected':'' }}>Adjustment</option>
                </select>
            </div>
            <div class="col-sm-3 col-lg-2">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}" {{ request('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-3 col-lg-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From"></div>
            <div class="col-sm-3 col-lg-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To"></div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════ --}}
{{--  SECTION 1: VEHICLE MOVEMENTS                        --}}
{{-- ═════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h6 class="fw-700 mb-2 text-primary"><i class="bi bi-car-front me-1"></i>Vehicle Movements
        <span class="badge bg-primary-subtle text-primary-emphasis ms-1">{{ $vehicleSales->count() }}</span>
    </h6>
    <div class="card">
        <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-3">Date Sold</th>
                    <th>Chassis No.</th>
                    <th>Variant / Model</th>
                    <th>Color</th>
                    <th class="text-end">Selling Price</th>
                    <th class="text-center">Status</th>
                    <th>Invoice</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
            @forelse($vehicleSales as $vs)
            <tr>
                <td class="px-3 small text-muted">{{ $vs->updated_at->format('d M Y') }}</td>
                <td><code class="small fw-600">{{ $vs->chassis_number }}</code></td>
                <td class="small">
                    <div class="fw-500">{{ $vs->variant?->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $vs->variant?->vehicleModel?->brand?->name }} {{ $vs->variant?->vehicleModel?->name }}</div>
                </td>
                <td class="small text-muted">{{ $vs->color ?? '—' }}</td>
                <td class="text-end fw-600">₹{{ number_format($vs->selling_price, 0) }}</td>
                <td class="text-center"><span class="badge bg-secondary">Sold</span></td>
                <td class="small">
                    @if($vs->sale)
                    <a href="{{ route('admin.sales.show', $vs->sale) }}" class="text-decoration-none">{{ $vs->sale->invoice_no }}</a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="small text-muted">{{ $vs->branch?->name ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-3 text-muted">No vehicle sales recorded</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════ --}}
{{--  SECTION 2: SPARE PARTS MOVEMENTS                    --}}
{{-- ═════════════════════════════════════════════════════ --}}
<div>
    <h6 class="fw-700 mb-2 text-warning"><i class="bi bi-box-seam me-1"></i>Spare Parts Movements
        <span class="badge bg-warning-subtle text-warning-emphasis ms-1">{{ $movements->total() }}</span>
    </h6>
    <div class="card">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-3">Date</th>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>Type</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Before</th>
                    <th class="text-center">After</th>
                    <th>Reference</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
            @forelse($movements as $m)
            <tr>
                <td class="px-3 small text-muted">{{ $m->created_at->format('d M Y H:i') }}</td>
                <td class="small fw-500">{{ $m->product?->name ?? '—' }}</td>
                <td class="small">{{ $m->branch?->name ?? '—' }}</td>
                <td>
                    <span class="badge {{ $m->type=='in'?'bg-success':($m->type=='out'?'bg-danger':'bg-secondary') }}">
                        <i class="bi bi-arrow-{{ $m->type=='in'?'down':'up' }}-circle me-1"></i>{{ ucfirst($m->type) }}
                    </span>
                </td>
                <td class="text-center fw-600">{{ $m->quantity }}</td>
                <td class="text-center text-muted small">{{ $m->before_quantity }}</td>
                <td class="text-center fw-500">{{ $m->after_quantity }}</td>
                <td class="small text-muted">{{ $m->reference_type ? ucfirst($m->reference_type).' #'.$m->reference_id : ($m->notes ?? '—') }}</td>
                <td class="small">{{ $m->creator?->name ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center py-4 text-muted">No parts movements found</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        <div class="card-footer py-2">{{ $movements->links() }}</div>
    </div>
</div>

@endsection

@extends('layouts.admin')
@section('title', $sale->invoice_no)
@section('content')

@php $isVehicle = $sale->sale_type === 'vehicle' && $sale->vehicleStock; @endphp

<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600 flex-fill">
        {{ $sale->invoice_no }}
        @if($isVehicle)
        <span class="badge bg-primary ms-2"><i class="bi bi-car-front me-1"></i>Vehicle Sale</span>
        @endif
    </h5>
    <span class="badge badge-status-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span>
    <a href="{{ route('admin.sales.invoice', $sale) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-pdf me-1"></i>PDF</a>
    @can('sales.edit')<a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil me-1"></i>Edit</a>@endcan
</div>

<div class="row g-3">
    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- VEHICLE CARD (only for vehicle sales) --}}
        @if($isVehicle)
        @php $vs = $sale->vehicleStock; @endphp
        <div class="card mb-3 border-primary border-opacity-25">
            <div class="card-header bg-primary-subtle d-flex align-items-center gap-2">
                <i class="bi bi-car-front text-primary"></i>
                <span class="fw-700 text-primary-emphasis">Vehicle Details</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="small text-muted mb-1">Brand / Model</div>
                        <div class="fw-600">{{ $vs->variant?->vehicleModel?->brand?->name }} {{ $vs->variant?->vehicleModel?->name }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small text-muted mb-1">Variant</div>
                        <div class="fw-600">{{ $vs->variant?->name }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small text-muted mb-1">Color</div>
                        <div>{{ $vs->color ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small text-muted mb-1">Chassis Number</div>
                        <div><code class="fw-700 text-dark">{{ $vs->chassis_number }}</code></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small text-muted mb-1">Engine Number</div>
                        <div><code class="text-dark">{{ $vs->engine_number ?? '—' }}</code></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small text-muted mb-1">Status</div>
                        <span class="badge bg-secondary">{{ ucfirst($vs->status) }}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="small text-muted">Selling Price</div>
                        <div class="fw-700 text-primary">₹{{ number_format($sale->total, 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Discount</div>
                        <div class="fw-600 text-danger">- ₹{{ number_format($sale->discount ?? 0, 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Exchange</div>
                        <div class="fw-600 text-info">₹{{ number_format($sale->exchange ?? 0, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PAYMENT BREAKDOWN for vehicle --}}
        <div class="card mb-3">
            <div class="card-header fw-600">Payment Breakdown</div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Net Payable</div>
                        <div class="fw-700 fs-5 text-primary">₹{{ number_format($sale->total, 0) }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Cash</div>
                        <div class="fw-600 text-success">₹{{ number_format($sale->cash_amount ?? 0, 0) }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Advance</div>
                        <div class="fw-600 text-warning">₹{{ number_format($sale->advance_amount ?? 0, 0) }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Finance</div>
                        <div class="fw-600 text-info">₹{{ number_format($sale->finance_amount ?? 0, 0) }}</div>
                    </div>
                </div>
                @if($sale->finance_name)
                <div class="mt-2 small text-muted text-center">Finance via: <strong>{{ $sale->finance_name }}</strong></div>
                @endif
            </div>
        </div>

        @else
        {{-- SPARE PARTS ITEMS TABLE --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Items</span>
                <span class="badge badge-status-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span>
            </div>
            <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th class="px-3">Product</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Discount</th><th class="text-end">Total</th><th class="text-end text-muted">Profit</th></tr></thead>
                <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td class="px-3 small">{{ $item->product->name ?? 'Deleted' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">₹{{ number_format($item->unit_price,2) }}</td>
                    <td class="text-end text-danger">{{ $item->discount > 0 ? '- ₹'.number_format($item->discount,2) : '—' }}</td>
                    <td class="text-end fw-500">₹{{ number_format($item->total,2) }}</td>
                    <td class="text-end text-success small">₹{{ number_format($item->getLineProfit(),2) }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr><td colspan="4" class="text-end px-3">Subtotal</td><td class="text-end fw-500">₹{{ number_format($sale->subtotal,2) }}</td><td></td></tr>
                    <tr><td colspan="4" class="text-end px-3">Discount</td><td class="text-end text-danger">- ₹{{ number_format($sale->discount,2) }}</td><td></td></tr>
                    @if($sale->tax > 0)
                    <tr><td colspan="4" class="text-end px-3">Tax</td><td class="text-end">+ ₹{{ number_format($sale->tax,2) }}</td><td></td></tr>
                    @endif
                    <tr><td colspan="4" class="text-end px-3 fw-700">Total</td><td class="text-end fw-700 text-primary">₹{{ number_format($sale->total,2) }}</td><td></td></tr>
                </tfoot>
            </table>
            </div>
        </div>
        @endif

        {{-- PAYMENTS --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                Payments
                @can('payments.create')
                @if($sale->payment_status !== 'paid')
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal"><i class="bi bi-cash me-1"></i>Record Payment</button>
                @endif
                @endcan
            </div>
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th class="px-3">Date</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                @forelse($sale->payments as $pay)
                <tr>
                    <td class="px-3 small">{{ $pay->payment_date->format('d M Y') }}</td>
                    <td class="small">{{ ucfirst(str_replace('_',' ',$pay->method)) }}</td>
                    <td class="small text-muted">{{ $pay->reference ?? '—' }}</td>
                    <td class="text-end fw-500 text-success">₹{{ number_format($pay->amount,2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3 small">No payments recorded</td></tr>
                @endforelse
                <tr class="table-light fw-600"><td colspan="3" class="px-3 text-end">Balance Due</td>
                    <td class="text-end {{ $sale->balance_due > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($sale->balance_due,2) }}</td></tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Sale Info</div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="small text-muted">Customer</div>
                    <div class="fw-500">{{ $sale->customer?->name ?? $sale->customer_name ?? 'Walk-in' }}</div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted">Salesman</div>
                    <div>{{ $sale->soldBy?->name ?? '—' }}</div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted">Branch</div>
                    <div>{{ $sale->branch?->name ?? '—' }}</div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted">Date</div>
                    <div>{{ $sale->sale_date->format('d M Y') }}</div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted">Sale Type</div>
                    <div><span class="badge {{ $isVehicle ? 'bg-primary' : 'bg-secondary' }}">{{ ucfirst($sale->sale_type ?? 'parts') }}</span></div>
                </div>
                @if($sale->notes)<div><div class="small text-muted">Notes</div><div class="small">{{ $sale->notes }}</div></div>@endif
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="card">
            <div class="card-header">Financial Summary</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td class="ps-3 text-muted small">Total</td><td class="text-end pe-3 fw-700 text-primary">₹{{ number_format($sale->total,2) }}</td></tr>
                    <tr><td class="ps-3 text-muted small">Amount Paid</td><td class="text-end pe-3 text-success fw-600">₹{{ number_format($sale->amount_paid,2) }}</td></tr>
                    @if(($sale->exchange ?? 0) > 0)
                    <tr><td class="ps-3 text-muted small">Exchange</td><td class="text-end pe-3 text-info">₹{{ number_format($sale->exchange,2) }}</td></tr>
                    @endif
                    <tr class="table-light"><td class="ps-3 fw-700">Balance Due</td>
                        <td class="text-end pe-3 fw-700 {{ $sale->balance_due > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($sale->balance_due,2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h6 class="modal-title">Record Payment – {{ $sale->invoice_no }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('admin.payments.store') }}">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
        <div class="modal-body row g-3">
            <div class="col-sm-6"><label class="form-label">Amount (₹)</label><input type="number" name="amount" class="form-control" value="{{ $sale->balance_due }}" step="0.01" min="0.01" required></div>
            <div class="col-sm-6"><label class="form-label">Method</label><select name="method" class="form-select"><option value="cash">Cash</option><option value="card">Card</option><option value="bank_transfer">Bank Transfer</option><option value="upi">UPI</option><option value="cheque">Cheque</option></select></div>
            <div class="col-sm-6"><label class="form-label">Date</label><input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="col-sm-6"><label class="form-label">Reference No.</label><input type="text" name="reference" class="form-control" placeholder="Optional"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Record Payment</button></div>
        </form>
    </div></div>
</div>
@endsection

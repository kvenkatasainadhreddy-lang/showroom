@extends('layouts.admin')
@section('title', 'Sales')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-600"><i class="bi bi-receipt me-2 text-primary"></i>Sales</h5>
    <div class="d-flex gap-2 flex-wrap">
      @can('sales.create')
      <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>New Sale
      </a>
      @endcan
      {{-- Excel Export trigger --}}
      <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
      </button>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))<div class="alert alert-success alert-dismissible fade show py-2"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Filter bar --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end" id="filterForm">
            <div class="col-sm-4 col-lg-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice No…" value="{{ request('search') }}"></div>
            <div class="col-sm-3 col-lg-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="unpaid"  {{ request('payment_status')=='unpaid'?'selected':'' }}>Unpaid</option>
                    <option value="partial" {{ request('payment_status')=='partial'?'selected':'' }}>Partial</option>
                    <option value="paid"    {{ request('payment_status')=='paid'?'selected':'' }}>Paid</option>
                </select>
            </div>
            <div class="col-sm-3 col-lg-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From"></div>
            <div class="col-sm-3 col-lg-2"><input type="date" name="to"   class="form-control form-control-sm" value="{{ request('to') }}"   placeholder="To"></div>
            <div class="col-auto">
              <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
              <a href="{{ route('admin.sales.index') }}" class="btn btn-light btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr>
          <th class="px-3">Invoice</th><th>Customer</th><th>Branch</th><th>Date</th>
          <th class="text-end">Total</th><th class="text-end">Paid</th>
          <th>Status</th><th></th>
        </tr></thead>
        <tbody>
        @forelse($sales as $sale)
        <tr>
            <td class="px-3 fw-500"><a href="{{ route('admin.sales.show', $sale) }}" class="text-decoration-none">{{ $sale->invoice_no }}</a></td>
            <td class="small">{{ $sale->customer_name ?? $sale->customer?->name ?? 'Walk-in' }}</td>
            <td class="small text-muted">{{ $sale->branch?->name ?? '—' }}</td>
            <td class="small">{{ $sale->sale_date?->format('d M Y') }}</td>
            <td class="text-end fw-500">₹{{ number_format($sale->total,2) }}</td>
            <td class="text-end text-success">₹{{ number_format($sale->amount_paid,2) }}</td>
            <td><span class="badge badge-status-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-xs btn-light" title="View"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('admin.sales.invoice', $sale) }}" class="btn btn-xs btn-outline-secondary" title="PDF"><i class="bi bi-file-pdf"></i></a>
                    @can('sales.delete')
                    <form method="POST" action="{{ route('admin.sales.destroy', $sale) }}" onsubmit="return confirm('Delete sale?')">@csrf @method('DELETE')
                      <button class="btn btn-xs btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                    </form>
                    @endcan
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center py-4 text-muted">No sales found</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-between">
      <span class="small text-muted">{{ $sales->total() }} total records</span>
      {{ $sales->links() }}
    </div>
</div>

{{-- ── Export Modal ── --}}
<div class="modal fade" id="exportModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
    <div class="modal-content">
      <form method="GET" action="{{ route('admin.sales.export') }}">
        <div class="modal-header bg-success text-white py-2">
          <h6 class="modal-title fw-700"><i class="bi bi-file-earmark-excel me-2"></i>Export Sales to Excel</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">
            The exported file will have <strong>two sheets</strong>:<br>
            <i class="bi bi-table text-primary me-1"></i><strong>All Sales</strong> — every row with all amounts<br>
            <i class="bi bi-calculator text-success me-1"></i><strong>Summary Totals</strong> — grand totals, payment status breakdown, vehicle vs parts split
          </p>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label small fw-600">From Date</label>
              <input type="date" name="from" class="form-control form-control-sm"
                     value="{{ request('from', date('Y-m-d')) }}" id="exportFrom">
            </div>
            <div class="col-6">
              <label class="form-label small fw-600">To Date</label>
              <input type="date" name="to" class="form-control form-control-sm"
                     value="{{ request('to', date('Y-m-d')) }}" id="exportTo">
            </div>
          </div>
          <div class="mt-3 d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setExportPeriod('today')">Today</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setExportPeriod('week')">This Week</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setExportPeriod('month')">This Month</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setExportPeriod('all')">All Time</button>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm fw-600">
            <i class="bi bi-download me-1"></i>Download .xlsx
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function setExportPeriod(period) {
    const today = new Date();
    const fmt = d => d.toISOString().split('T')[0];
    let from = fmt(today), to = fmt(today);

    if (period === 'week') {
        const mon = new Date(today);
        mon.setDate(today.getDate() - today.getDay() + 1);
        from = fmt(mon);
    } else if (period === 'month') {
        from = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
    } else if (period === 'all') {
        from = ''; to = '';
    }
    document.getElementById('exportFrom').value = from;
    document.getElementById('exportTo').value   = to;
}
</script>
@endpush

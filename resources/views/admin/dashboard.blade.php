@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="row g-3 mb-4">
    <!-- Stat Cards -->
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#0d6efd,#0a58ca)">
            <div class="stat-label">Revenue</div>
            <div class="stat-value">₹{{ number_format($stats['revenue']) }}</div>
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="small opacity-75 mt-1">{{ $from }} – {{ $to }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#dc3545,#b02a37)">
            <div class="stat-label">COGS</div>
            <div class="stat-value">₹{{ number_format($stats['cogs']) }}</div>
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="small opacity-75 mt-1">Cost of Goods Sold</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#198754,#146c43)">
            <div class="stat-label">Net Profit</div>
            <div class="stat-value">₹{{ number_format($stats['net_profit']) }}</div>
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="small opacity-75 mt-1">After expenses</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#6f42c1,#5a32a3)">
            <div class="stat-label">Total Sales</div>
            <div class="stat-value">{{ $stats['sales_count'] }}</div>
            <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
            <div class="small opacity-75 mt-1">Invoices this period</div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-3">
                <label class="form-label small fw-500 mb-1">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-sm-4 col-lg-3">
                <label class="form-label small fw-500 mb-1">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-sm-4 col-lg-2">
                <button class="btn btn-primary btn-sm w-100">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Revenue – Last 7 Days</span>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- P&L Summary -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">P&amp;L Summary</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><td class="px-3">Revenue</td><td class="px-3 text-end fw-600 text-primary">₹{{ number_format($stats['revenue'], 2) }}</td></tr>
                        <tr><td class="px-3">COGS</td><td class="px-3 text-end text-danger">- ₹{{ number_format($stats['cogs'], 2) }}</td></tr>
                        <tr><td class="px-3">Gross Profit</td><td class="px-3 text-end fw-600">₹{{ number_format($stats['gross_profit'], 2) }}</td></tr>
                        <tr><td class="px-3">Expenses</td><td class="px-3 text-end text-danger">- ₹{{ number_format($stats['expenses'], 2) }}</td></tr>
                        <tr class="table-success"><td class="px-3 fw-700">Net Profit</td><td class="px-3 text-end fw-700 text-success">₹{{ number_format($stats['net_profit'], 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    {{-- Low Stock / Zero Vehicle Stock --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2 py-2">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <span class="fw-600">Stock Alerts</span>
                @php $totalAlerts = $lowStock->count() + $zeroVehicleStock->count(); @endphp
                <span class="badge bg-warning text-dark ms-auto">{{ $totalAlerts }}</span>
            </div>

            {{-- Tabs --}}
            <ul class="nav nav-tabs nav-tabs-sm px-2 pt-1" style="border-bottom:1px solid #e5e7eb">
                <li class="nav-item">
                    <a class="nav-link active py-1 px-2 small" data-bs-toggle="tab" href="#tabParts">
                        <i class="bi bi-box-seam me-1"></i>Parts
                        <span class="badge bg-secondary ms-1">{{ $lowStock->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 px-2 small" data-bs-toggle="tab" href="#tabVehicles">
                        <i class="bi bi-car-front me-1"></i>Vehicles
                        <span class="badge {{ $zeroVehicleStock->count() > 0 ? 'bg-danger' : 'bg-success' }} ms-1">{{ $zeroVehicleStock->count() }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Spare parts tab --}}
                <div class="tab-pane fade show active" id="tabParts">
                    @if($lowStock->isEmpty())
                        <p class="text-center text-muted py-3 small">All parts stock levels OK ✓</p>
                    @else
                    <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th class="px-3">Product</th><th class="text-center">Qty</th><th class="text-center">Min</th></tr></thead>
                        <tbody>
                            @foreach($lowStock as $inv)
                            <tr>
                                <td class="px-3 small">{{ Str::limit($inv->product?->name ?? '—', 22) }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ $inv->quantity }}</span></td>
                                <td class="text-center text-muted small">{{ $inv->min_quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @endif
                </div>

                {{-- Vehicle stock tab --}}
                <div class="tab-pane fade" id="tabVehicles">
                    @if($zeroVehicleStock->isEmpty())
                        <p class="text-center text-muted py-3 small">All variants have chassis in stock ✓</p>
                    @else
                    <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th class="px-3">Variant</th><th class="text-center">Avail</th></tr></thead>
                        <tbody>
                            @foreach($zeroVehicleStock as $v)
                            <tr>
                                <td class="px-3 small">
                                    <div class="fw-500">{{ $v->name }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $v->vehicleModel?->brand?->name }} {{ $v->vehicleModel?->name }}</div>
                                </td>
                                <td class="text-center"><span class="badge bg-danger">0</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                Recent Sales
                @can('sales.create')
                <a href="{{ route('admin.sales.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>New Sale
                </a>
                @endcan
            </div>
            <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th class="px-3">Invoice</th><th class="px-3">Customer</th><th class="px-3">Date</th><th class="px-3 text-end">Total</th><th class="px-3">Status</th></tr></thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr>
                        <td class="px-3"><a href="{{ route('admin.sales.show', $sale) }}" class="text-decoration-none fw-500">{{ $sale->invoice_no }}</a></td>
                        <td class="px-3 small">{{ $sale->customer?->name ?? $sale->customer_name ?? 'Walk-in' }}</td>
                        <td class="px-3 small text-muted">{{ $sale->sale_date->format('d M Y') }}</td>
                        <td class="px-3 text-end fw-500">₹{{ number_format($sale->total, 2) }}</td>
                        <td class="px-3">
                            <span class="badge badge-status-{{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No sales yet</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Revenue (₹)',
            data: @json($chartData),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#0d6efd',
            pointRadius: 4,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '₹' + v.toLocaleString() } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush

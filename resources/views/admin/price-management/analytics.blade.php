@extends('layouts.admin')
@section('title','Price Analytics')
@section('content')

{{-- ── Page Header ── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h5 class="mb-0 fw-700"><i class="bi bi-graph-up me-2 text-primary"></i>Price Analytics</h5>
    <div class="small text-muted">Track vehicle chassis and spare-part price trends, margins and change history</div>
  </div>
  <a href="{{ route('admin.price-management.index') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-pencil-square me-1"></i>Edit Prices
  </a>
</div>

{{-- ── FILTER BAR ── --}}
<form method="GET" id="filterForm" class="card mb-4">
  <div class="card-body py-2">
    <div class="row g-2 align-items-end">

      {{-- Type selector: NO name attr - JS mirrors to hidden input on change --}}
      <div class="col-md-2 col-sm-6">
        <label class="form-label small fw-600 mb-1">Type</label>
        <select id="entityTypeUI" class="form-select form-select-sm" onchange="syncType(this.value)">
          <option value="vehicle_stock" {{ $entityType==='vehicle_stock'?'selected':'' }}>Vehicles (Chassis)</option>
          <option value="product"       {{ $entityType==='product'      ?'selected':'' }}>Spare Parts</option>
          <option value="all"           {{ $entityType==='all'          ?'selected':'' }}>All Types</option>
        </select>
        <input type="hidden" name="entity_type" id="entityTypeHidden" value="{{ $entityType }}">
      </div>

      {{-- Item selectors: NO name attr on any select - only hidden input submits --}}
      <div class="col-md-3 col-sm-6">
        <label class="form-label small fw-600 mb-1">
          Select Item <span class="text-muted fw-400">(trend chart)</span>
        </label>

        <select id="stockSel" class="form-select form-select-sm"
                {{ $entityType!=='vehicle_stock'?'style=display:none':'' }}
                onchange="setEntityId(this.value)">
          <option value="">-- Select Chassis --</option>
          @foreach($vehicleStocks as $s)
          <option value="{{ $s->id }}" {{ $entityId == $s->id ? 'selected' : '' }}>
            {{ $s->chassis_number }} - {{ $s->variant?->vehicleModel?->brand?->name }}
            {{ $s->variant?->vehicleModel?->name }} ({{ $s->variant?->name }})
          </option>
          @endforeach
        </select>

        <select id="productSel" class="form-select form-select-sm"
                {{ $entityType!=='product'?'style=display:none':'' }}
                onchange="setEntityId(this.value)">
          <option value="">-- Select Product --</option>
          @foreach($products as $p)
          <option value="{{ $p->id }}" {{ $entityId == $p->id ? 'selected' : '' }}>
            {{ $p->name }} {{ $p->sku ? '('.$p->sku.')' : '' }}
          </option>
          @endforeach
        </select>

        <div id="allPlaceholder" class="form-control form-control-sm bg-light text-muted"
             {{ $entityType!=='all'?'style=display:none':'' }}>All entries shown</div>

        {{-- The ONE hidden field that actually posts entity_id --}}
        <input type="hidden" name="entity_id" id="entityIdHidden" value="{{ $entityId }}">
      </div>

      {{-- Brand filter (vehicles only) --}}
      <div class="col-md-2 col-sm-4" id="brandFilterCol"
           style="{{ in_array($entityType,['product','all'])?'display:none':'' }}">
        <label class="form-label small fw-600 mb-1">Brand</label>
        <select name="brand_id" class="form-select form-select-sm">
          <option value="">All Brands</option>
          @foreach($brands as $b)
          <option value="{{ $b->id }}" {{ $brandId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Period presets --}}
      <div class="col-md-2 col-sm-4">
        <label class="form-label small fw-600 mb-1">Period</label>
        <select name="days" id="periodSel" class="form-select form-select-sm" onchange="toggleCustomDate(this.value)">
          <option value="7"   {{ $days==7   && !request('from') ? 'selected' : '' }}>Last 7 days</option>
          <option value="30"  {{ $days==30  && !request('from') ? 'selected' : '' }}>Last 30 days</option>
          <option value="60"  {{ $days==60  && !request('from') ? 'selected' : '' }}>Last 60 days</option>
          <option value="90"  {{ (!request('days')||$days==90) && !request('from') ? 'selected' : '' }}>Last 90 days</option>
          <option value="365" {{ $days==365 && !request('from') ? 'selected' : '' }}>Last 1 year</option>
          <option value="0"   {{ request('from') ? 'selected' : '' }}>Custom range...</option>
        </select>
      </div>

      {{-- Custom date inputs (shown when Custom selected) --}}
      <div class="col-md-2 col-sm-4" id="customDateRow" style="{{ request('from')?'':'display:none' }}">
        <label class="form-label small fw-600 mb-1">From - To</label>
        <div class="d-flex gap-1">
          <input type="date" name="from" class="form-control form-control-sm"
                 value="{{ request('from', $from->toDateString()) }}">
          <input type="date" name="to"   class="form-control form-control-sm"
                 value="{{ request('to', $to->toDateString()) }}">
        </div>
      </div>

      {{-- Keyword search --}}
      <div class="col-md-2 col-sm-6">
        <label class="form-label small fw-600 mb-1">Search</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Chassis / SKU / reason..." value="{{ $search }}">
      </div>

      <div class="col-auto d-flex gap-2 align-items-end">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-funnel me-1"></i>Apply
        </button>
        <a href="{{ route('admin.price-management.analytics') }}" class="btn btn-light btn-sm">
          <i class="bi bi-x-circle me-1"></i>Reset
        </a>
      </div>
    </div>
  </div>
</form>

{{-- ── SUMMARY STAT CARDS ── --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#4361ee,#3a0ca3)">
      <div class="card-body py-3 text-white">
        <div class="small opacity-75">Total Log Entries</div>
        <div class="fs-4 fw-700">{{ number_format($summaryStats->total_logs) }}</div>
        <div class="small opacity-75">in selected period</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#06d6a0,#059669)">
      <div class="card-body py-3 text-white">
        <div class="small opacity-75">Vehicle Changes</div>
        <div class="fs-4 fw-700">{{ number_format($summaryStats->vehicle_logs) }}</div>
        <div class="small opacity-75">chassis price updates</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#f8961e,#d97706)">
      <div class="card-body py-3 text-white">
        <div class="small opacity-75">Parts Changes</div>
        <div class="fs-4 fw-700">{{ number_format($summaryStats->parts_logs) }}</div>
        <div class="small opacity-75">spare part updates</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#f72585,#b5179e)">
      <div class="card-body py-3 text-white">
        <div class="small opacity-75">Avg Price Change</div>
        <div class="fs-4 fw-700">₹{{ number_format($summaryStats->avg_sell_change, 0) }}</div>
        <div class="small opacity-75">per update (selling)</div>
      </div>
    </div>
  </div>
</div>

{{-- ── CHARTS ROW 1: Doughnut / Direction / Daily Bar ── --}}
<div class="row g-3 mb-4">
  <div class="col-lg-3">
    <div class="card h-100">
      <div class="card-header small fw-600 py-2"><i class="bi bi-pie-chart me-1 text-primary"></i>Changes by Type</div>
      <div class="card-body d-flex align-items-center justify-content-center py-2">
        <div style="max-width:200px;width:100%"><canvas id="typeDoughnut"></canvas></div>
      </div>
      <div class="card-footer text-center py-2 small">
        <span class="text-success fw-600">{{ $typeBreakdown['vehicle_stock'] ?? 0 }}</span> Chassis ·
        <span class="text-warning fw-600">{{ $typeBreakdown['product'] ?? 0 }}</span> Parts
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card h-100">
      <div class="card-header small fw-600 py-2"><i class="bi bi-pie-chart me-1 text-danger"></i>Price Direction</div>
      <div class="card-body d-flex align-items-center justify-content-center py-2">
        <div style="max-width:200px;width:100%"><canvas id="directionPie"></canvas></div>
      </div>
      <div class="card-footer text-center py-2 small">
        <span class="text-danger fw-600">▲ {{ $directionStats->up ?? 0 }}</span> up ·
        <span class="text-success fw-600">▼ {{ $directionStats->down ?? 0 }}</span> down ·
        <span class="text-secondary">{{ $directionStats->same ?? 0 }}</span> same
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header small fw-600 py-2"><i class="bi bi-bar-chart me-1 text-success"></i>Daily Change Activity</div>
      <div class="card-body"><canvas id="dailyBar" height="130"></canvas></div>
    </div>
  </div>
</div>

{{-- ── CHARTS ROW 2: Trend Line + Top Chassis ── --}}
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header small fw-600 py-2 d-flex align-items-center gap-2">
        <i class="bi bi-graph-up-arrow text-primary"></i>
        Price Trend —
        @if($selectedEntity)
          <strong>{{ $selectedEntity->chassis_number ?? $selectedEntity->name ?? 'Selected Item' }}</strong>
          @if($entityType === 'vehicle_stock' && $selectedEntity->variant)
          <span class="text-muted fw-400 small">{{ $selectedEntity->variant?->vehicleModel?->brand?->name }} {{ $selectedEntity->variant?->vehicleModel?->name }} / {{ $selectedEntity->variant?->name }}</span>
          @endif
        @else
          <span class="text-muted fw-400">Select a chassis or product above to see its price trend</span>
        @endif
      </div>
      <div class="card-body">
        @if($selectedEntity && count($trendData))
          <canvas id="trendLine" height="150"></canvas>
        @elseif($selectedEntity)
          <div class="text-center py-5 text-muted"><i class="bi bi-clock-history fs-2 d-block mb-2"></i>No price changes recorded in this period for the selected item.</div>
        @else
          <div class="text-center py-5 text-muted">
            <i class="bi bi-cursor fs-2 d-block mb-2"></i>
            Select Type → Item from the filter above, then click Apply.
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header small fw-600 py-2"><i class="bi bi-trophy me-1 text-warning"></i>Most Frequently Updated Chassis</div>
      <div class="card-body">
        @if($topStocks->isNotEmpty())
          <canvas id="topStocksBar" height="220"></canvas>
        @else
          <div class="text-center py-4 text-muted small"><i class="bi bi-exclamation-circle d-block mb-1 fs-3"></i>No vehicle chassis price changes in this period.</div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ── RECENT CHANGE LOG TABLE ── --}}
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between py-2">
    <span class="small fw-600"><i class="bi bi-clock-history me-1"></i>Price Change Log
      <span class="badge bg-primary-subtle text-primary-emphasis ms-1">{{ $recentLogs->count() }}</span>
    </span>
    <span class="small text-muted">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</span>
  </div>
  <div class="table-responsive">
  <table class="table table-sm align-middle mb-0 table-hover">
    <thead class="table-light">
      <tr>
        <th class="px-3">Date</th>
        <th>Type</th>
        <th>Item</th>
        <th class="text-end">Old Sell</th>
        <th class="text-end">New Sell</th>
        <th class="text-center">Δ Sell</th>
        <th class="text-end">Old Buy</th>
        <th class="text-end">New Buy</th>
        <th class="text-center">Margin</th>
        <th>By</th>
        <th>Reason</th>
      </tr>
    </thead>
    <tbody>
    @forelse($recentLogs as $log)
    @php
      $dir   = $log->selling_direction;
      $chg   = $log->selling_change;
      $mDiff = round(($log->new_margin_percent ?? 0) - ($log->old_margin_percent ?? 0), 2);
    @endphp
    <tr>
      <td class="px-3 small text-muted">{{ $log->created_at->format('d M, H:i') }}</td>
      <td>
        @if($log->entity_type === 'vehicle_stock')
          <span class="badge bg-primary-subtle text-primary-emphasis"><i class="bi bi-car-front me-1"></i>Chassis</span>
        @else
          <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-box-seam me-1"></i>Part</span>
        @endif
      </td>
      <td class="small fw-500" style="max-width:200px">
        @if($log->entity_type === 'vehicle_stock')
          <code class="small">{{ $log->vehicleStock?->chassis_number ?? '—' }}</code>
          <div class="text-muted" style="font-size:10px">
            {{ $log->vehicleStock?->variant?->vehicleModel?->brand?->name }}
            {{ $log->vehicleStock?->variant?->vehicleModel?->name }}
            / {{ $log->vehicleStock?->variant?->name }}
          </div>
        @else
          <div>{{ $log->product?->name ?? '—' }}</div>
          @if($log->product?->sku)<div class="text-muted" style="font-size:10px">{{ $log->product->sku }}</div>@endif
        @endif
      </td>
      <td class="text-end small text-muted">₹{{ number_format($log->old_selling_price, 0) }}</td>
      <td class="text-end small fw-600">₹{{ number_format($log->new_selling_price, 0) }}</td>
      <td class="text-center">
        @if($dir === 'up')
          <span class="badge bg-danger-subtle text-danger-emphasis"><i class="bi bi-arrow-up-short"></i>+₹{{ number_format($chg, 0) }}</span>
        @elseif($dir === 'down')
          <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-arrow-down-short"></i>₹{{ number_format(abs($chg), 0) }}</span>
        @else
          <span class="text-muted small">—</span>
        @endif
      </td>
      <td class="text-end small text-muted">₹{{ number_format($log->old_purchase_price, 0) }}</td>
      <td class="text-end small fw-600">₹{{ number_format($log->new_purchase_price, 0) }}</td>
      <td class="text-center small {{ $mDiff > 0 ? 'text-success' : ($mDiff < 0 ? 'text-danger' : 'text-muted') }}">
        {{ $mDiff != 0 ? ($mDiff > 0 ? '+' : '') . $mDiff . '%' : '—' }}
      </td>
      <td class="small">{{ $log->changedBy?->name ?? '—' }}</td>
      <td class="small text-muted" style="max-width:150px;word-break:break-word">{{ $log->reason ?? '—' }}</td>
    </tr>
    @empty
    <tr><td colspan="11" class="text-center py-5 text-muted">
      <i class="bi bi-inbox fs-2 d-block mb-2"></i>No price changes match the current filters.
    </td></tr>
    @endforelse
    </tbody>
  </table>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
// ── Filter helpers ──────────────────────────────────────────────

// Called by the Type <select> – updates hidden inputs and shows correct item picker
function syncType(type) {
  // 1. Persist type value to the real hidden input
  document.getElementById('entityTypeHidden').value = type;

  // 2. Show/hide item selectors
  document.getElementById('stockSel').style.display      = type === 'vehicle_stock' ? '' : 'none';
  document.getElementById('productSel').style.display    = type === 'product'       ? '' : 'none';
  document.getElementById('allPlaceholder').style.display= type === 'all'           ? '' : 'none';

  // 3. Brand filter visible only for vehicles
  document.getElementById('brandFilterCol').style.display = (type === 'product' || type === 'all') ? 'none' : '';

  // 4. Reset entity_id when type switches (old selection no longer valid)
  document.getElementById('stockSel').value    = '';
  document.getElementById('productSel').value  = '';
  document.getElementById('entityIdHidden').value = '';
}

// Called whenever a UI selector changes – writes value to the one real hidden field
function setEntityId(val) {
  document.getElementById('entityIdHidden').value = val;
}

function toggleCustomDate(val) {
  document.getElementById('customDateRow').style.display = val === '0' ? '' : 'none';
}

const COLORS = ['#4361ee','#f72585','#06d6a0','#f8961e','#4cc9f0','#7209b7','#3a0ca3','#ef233c'];

// ── Type doughnut ────────────────────────────────────────────────
new Chart(document.getElementById('typeDoughnut'), {
  type: 'doughnut',
  data: {
    labels: ['Chassis/Vehicle', 'Spare Parts'],
    datasets: [{ data: [
      {{ $typeBreakdown['vehicle_stock'] ?? 0 }},
      {{ $typeBreakdown['product'] ?? 0 }}
    ], backgroundColor: ['#06d6a0','#f8961e'], borderWidth: 2, borderColor: '#fff' }]
  },
  options: { responsive: true, cutout: '60%',
    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
});

// ── Direction pie ────────────────────────────────────────────────
new Chart(document.getElementById('directionPie'), {
  type: 'pie',
  data: {
    labels: ['Price Up ▲', 'Price Down ▼', 'Unchanged'],
    datasets: [{ data: [
      {{ $directionStats->up ?? 0 }},
      {{ $directionStats->down ?? 0 }},
      {{ $directionStats->same ?? 0 }}
    ], backgroundColor: ['#ef233c','#06d6a0','#adb5bd'], borderWidth: 2, borderColor: '#fff' }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
});

// ── Daily bar ────────────────────────────────────────────────────
new Chart(document.getElementById('dailyBar'), {
  type: 'bar',
  data: {
    labels: @json($filledLabels),
    datasets: [{
      label: 'Changes', data: @json($filledDaily),
      backgroundColor: 'rgba(67,97,238,0.7)', borderRadius: 4
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1 } },
      x: { ticks: { font: { size: 9 }, maxRotation: 45 } }
    }
  }
});

// ── Trend line ───────────────────────────────────────────────────
@if($selectedEntity && count($trendData))
const trendRaw    = @json($trendData);
const trendDates  = trendRaw.map(r => r.created_at?.substring(0,10) || '');
const trendNewS   = trendRaw.map(r => parseFloat(r.new_selling_price  || 0));
const trendOldS   = trendRaw.map(r => parseFloat(r.old_selling_price  || 0));
const trendNewP   = trendRaw.map(r => parseFloat(r.new_purchase_price || 0));
const trendMargin = trendRaw.map(r => parseFloat(r.new_margin_percent || 0));

new Chart(document.getElementById('trendLine'), {
  type: 'line',
  data: {
    labels: trendDates,
    datasets: [
      { label: 'Selling Price ₹',  data: trendNewS,  borderColor: '#4361ee', backgroundColor: 'rgba(67,97,238,0.07)', tension: 0.4, fill: true,  pointRadius: 5, yAxisID: 'yPrice' },
      { label: 'Prev Selling ₹',   data: trendOldS,  borderColor: '#adb5bd', borderDash: [4,3], tension: 0.4, fill: false, pointRadius: 3, yAxisID: 'yPrice' },
      { label: 'Purchase Price ₹', data: trendNewP,  borderColor: '#f72585', backgroundColor: 'rgba(247,37,133,0.05)', tension: 0.4, fill: true,  pointRadius: 4, borderDash: [5,3], yAxisID: 'yPrice' },
      { label: 'Margin %',         data: trendMargin,borderColor: '#06d6a0', borderDash: [2,2], tension: 0.4, fill: false, pointRadius: 3, yAxisID: 'yMargin' },
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
    scales: {
      yPrice:  { position: 'left',  ticks: { callback: v => '₹' + v.toLocaleString('en-IN') }, title: { display: true, text: 'Price (₹)' } },
      yMargin: { position: 'right', ticks: { callback: v => v + '%' }, title: { display: true, text: 'Margin %' }, grid: { drawOnChartArea: false } }
    }
  }
});
@endif

// ── Top chassis bar ──────────────────────────────────────────────
@if($topStocks->isNotEmpty())
new Chart(document.getElementById('topStocksBar'), {
  type: 'bar',
  data: {
    labels: @json($topStocks->map(fn($r) => $r->vehicleStock?->chassis_number ?? 'Unknown')),
    datasets: [{
      label: 'Changes',
      data: @json($topStocks->pluck('changes')),
      backgroundColor: COLORS, borderRadius: 4
    }]
  },
  options: {
    indexAxis: 'y', responsive: true,
    plugins: { legend: { display: false } },
    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { ticks: { font: { size: 9 } } } }
  }
});
@endif
</script>
@endpush

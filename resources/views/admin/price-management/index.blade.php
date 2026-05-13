@extends('layouts.admin')
@section('title','Price Management')
@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h5 class="mb-0 fw-700"><i class="bi bi-tag me-2 text-primary"></i>Price Management</h5>
    <div class="small text-muted mt-1">Edit chassis prices. All changes are auto-logged for daily tracking.</div>
  </div>
  <a href="{{ route('admin.price-management.analytics') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-graph-up me-1"></i>Price Analytics
  </a>
</div>

{{-- Flash messages --}}
@if(session('success'))<div class="alert alert-success alert-dismissible fade show py-2"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('info'))<div class="alert alert-info alert-dismissible fade show py-2"><i class="bi bi-info-circle me-2"></i>{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>@endif

{{-- Summary stat cards --}}
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-700 text-success">{{ number_format($stats->available) }}</div>
      <div class="small text-muted">Available Chassis</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-700 text-secondary">{{ number_format($stats->sold) }}</div>
      <div class="small text-muted">Sold Chassis</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-700 text-warning">{{ number_format($stats->products) }}</div>
      <div class="small text-muted">Spare Parts</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-700 text-info">{{ number_format($stats->logs_today) }}</div>
      <div class="small text-muted">Changes Today</div>
    </div>
  </div>
</div>

{{-- Search / filter bar --}}
<form method="GET" id="filterForm" class="card mb-3">
  <div class="card-body py-2">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search chassis number, product name or SKU…" value="{{ request('search') }}">
      </div>
      @if($products->isNotEmpty())
      <div class="col-md-3">
        <select name="category_id" class="form-select form-select-sm">
          <option value="">All Categories</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <input type="hidden" name="parts" id="partsHidden" value="{{ request('parts','0') }}">
        <div class="col-auto d-flex gap-2 align-items-center">
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
          <a href="{{ route('admin.price-management.index') }}" class="btn btn-light btn-sm">Reset</a>
          <button type="button" id="toggleParts"
                  class="btn btn-sm {{ request('parts') ? 'btn-warning' : 'btn-outline-secondary' }}">
            <i class="bi bi-box-seam me-1"></i>
            {{ request('parts') ? 'Spare Parts ON ✓' : 'Spare Parts OFF' }}
          </button>
          <button type="button" id="toggleGroupEdit" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-layers me-1" id="groupEditIcon"></i><span id="groupEditLabel">Group Edit OFF</span>
          </button>
        </div>
    </div>
  </div>
</form>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{--  SECTION 1: VEHICLE STOCK CHASSIS PRICES (primary, always on) --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="mb-4" id="stockSection">
  <div class="d-flex align-items-center mb-2">
    <h6 class="mb-0 fw-700 text-success"><i class="bi bi-car-front me-2"></i>Chassis-Level Prices</h6>
    <span class="badge bg-success-subtle text-success-emphasis ms-2">{{ $stock->total() }} available</span>
  </div>
  <form method="POST" action="{{ route('admin.price-management.update') }}" id="stockForm">
    @csrf @method('PUT')
    <input type="hidden" name="section" value="stock">

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between py-2 bg-success-subtle">
        <span class="small fw-600 text-success-emphasis">
          <i class="bi bi-info-circle me-1"></i>Per-chassis purchase &amp; selling prices — set 0 if not applicable
        </span>
        <div class="d-flex gap-2 align-items-center">
          <input type="text" name="reason" class="form-control form-control-sm" style="width:200px"
                 placeholder="Reason for change">
          <button type="submit" class="btn btn-success btn-sm fw-600">
            <i class="bi bi-check-circle me-1"></i>Save Chassis Prices
            <span class="badge bg-white text-success ms-1" id="stockChangedCount" style="display:none">0</span>
          </button>
        </div>
      </div>
      <div class="table-responsive">
      <table class="table table-sm align-middle mb-0 table-hover">
        <thead class="table-light">
          <tr>
            <th class="px-3">Chassis No.</th>
            <th>Variant / Model</th>
            <th>Color</th>
            <th class="text-end" style="width:145px">Purchase (₹)</th>
            <th class="text-end" style="width:145px">Selling (₹)</th>
            <th class="text-center" style="width:90px">Margin %</th>
            <th class="text-center" style="width:90px">Change</th>
          </tr>
        </thead>
        <tbody>
        @forelse($stock as $j => $s)
        @php
          $sm = $s->purchase_price > 0
            ? round((($s->selling_price - $s->purchase_price) / $s->purchase_price) * 100, 1)
            : 0;
        @endphp
        <input type="hidden" name="stock[{{ $j }}][stock_id]" value="{{ $s->id }}">
        <tr class="stock-row"
            data-orig-p="{{ $s->purchase_price }}"
            data-orig-s="{{ $s->selling_price }}"
            data-idx="{{ $j }}">
          <td class="px-3"><code class="small fw-600">{{ $s->chassis_number }}</code></td>
          <td class="small text-muted">
            {{ $s->variant?->vehicleModel?->brand?->name }}
            {{ $s->variant?->vehicleModel?->name }} —
            <span class="text-dark">{{ $s->variant?->name }}</span>
          </td>
          <td class="small text-muted">{{ $s->color ?? '—' }}</td>
          <td class="text-end">
            <div class="input-group input-group-sm justify-content-end">
              <span class="input-group-text px-1">₹</span>
              <input type="number" name="stock[{{ $j }}][purchase_price]"
                     class="form-control text-end stock-price-input"
                     value="{{ number_format($s->purchase_price, 2, '.', '') }}"
                     min="0" step="0.01" style="max-width:90px"
                     data-field="p" data-idx="{{ $j }}"
                     data-orig="{{ $s->purchase_price }}">
            </div>
          </td>
          <td class="text-end">
            <div class="input-group input-group-sm justify-content-end">
              <span class="input-group-text px-1">₹</span>
              <input type="number" name="stock[{{ $j }}][selling_price]"
                     class="form-control text-end stock-price-input"
                     value="{{ number_format($s->selling_price, 2, '.', '') }}"
                     min="0" step="0.01" style="max-width:90px"
                     data-field="s" data-idx="{{ $j }}"
                     data-orig="{{ $s->selling_price }}">
            </div>
          </td>
          <td class="text-center" id="smargin_{{ $j }}">
            <span class="{{ $sm < 0 ? 'text-danger fw-700' : ($sm < 10 ? 'text-warning fw-600' : 'text-success fw-600') }}">{{ $sm }}%</span>
          </td>
          <td class="text-center" id="schange_{{ $j }}"></td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-muted">No available vehicle stock. Add chassis first.</td></tr>
        @endforelse
        </tbody>
      </table>
      </div>
      @if($stock->hasPages())
      <div class="card-footer py-2">{{ $stock->appends(request()->query())->links() }}</div>
      @endif
    </div>
  </form>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{--  SECTION 1b: GROUP EDIT (same variant+color = bulk price)        --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div id="groupEditSection" style="display:none" class="mb-4">
  <div class="d-flex align-items-center mb-2 gap-2">
    <h6 class="mb-0 fw-700 text-primary"><i class="bi bi-layers me-2"></i>Group Edit Mode</h6>
    <span class="badge bg-primary-subtle text-primary-emphasis">{{ $stockGrouped->count() }} groups</span>
    <span class="badge bg-info-subtle text-info-emphasis">One price → all matching chassis</span>
  </div>
  <div class="alert alert-primary py-2 small mb-2">
    <i class="bi bi-info-circle me-1"></i>
    <strong>Group Edit:</strong> Chassis with the same <strong>Model · Variant · Colour</strong> are merged into one row.
    Setting a price here will update <em>every</em> matching available chassis at once.
  </div>
  <form method="POST" action="{{ route('admin.price-management.update-group') }}" id="groupForm">
    @csrf @method('PUT')
    <div class="card border-primary">
      <div class="card-header d-flex align-items-center justify-content-between py-2 bg-primary-subtle">
        <span class="small fw-600 text-primary-emphasis">
          <i class="bi bi-layers me-1"></i>Grouped by Model · Variant · Colour
        </span>
        <div class="d-flex gap-2 align-items-center">
          <input type="text" name="reason" class="form-control form-control-sm" style="width:200px" placeholder="Reason for change">
          <button type="submit" class="btn btn-primary btn-sm fw-600">
            <i class="bi bi-check-circle me-1"></i>Save Group Prices
            <span class="badge bg-white text-primary ms-1" id="groupChangedCount" style="display:none">0</span>
          </button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 table-hover">
          <thead class="table-light">
            <tr>
              <th class="px-3">Model / Variant</th>
              <th>Colour</th>
              <th class="text-center" style="width:80px">Count</th>
              <th>Sample Chassis</th>
              <th class="text-end" style="width:145px">Purchase (&#8377;)</th>
              <th class="text-end" style="width:145px">Selling (&#8377;)</th>
              <th class="text-center" style="width:90px">Margin %</th>
              <th class="text-center" style="width:80px">Change</th>
            </tr>
          </thead>
          <tbody>
          @forelse($stockGrouped as $gi => $g)
          @php
            $gm = $g->purchase_price > 0
              ? round((($g->selling_price - $g->purchase_price) / $g->purchase_price) * 100, 1)
              : 0;
          @endphp
          <input type="hidden" name="groups[{{ $gi }}][variant_id]" value="{{ $g->variant_id }}">
          <input type="hidden" name="groups[{{ $gi }}][color]" value="{{ $g->color }}">
          <tr class="group-row"
              data-orig-p="{{ $g->purchase_price }}"
              data-orig-s="{{ $g->selling_price }}"
              data-gidx="{{ $gi }}">
            <td class="px-3 small">
              <div class="fw-600">{{ $g->variant?->vehicleModel?->brand?->name }} {{ $g->variant?->vehicleModel?->name }}</div>
              <div class="text-muted" style="font-size:.8rem">{{ $g->variant?->name }}</div>
            </td>
            <td class="small text-muted">{{ $g->color ?: '&mdash;' }}</td>
            <td class="text-center">
              <span class="badge bg-success rounded-pill">{{ $g->count }}</span>
            </td>
            <td class="small text-muted" style="max-width:180px;white-space:normal;font-size:.75rem">{{ $g->chassis_sample }}{{ $g->count > 3 ? '&hellip;' : '' }}</td>
            <td class="text-end">
              <div class="input-group input-group-sm justify-content-end">
                <span class="input-group-text px-1">&#8377;</span>
                <input type="number" name="groups[{{ $gi }}][purchase_price]"
                       class="form-control text-end group-price-input"
                       value="{{ number_format($g->purchase_price, 2, '.', '') }}"
                       min="0" step="0.01" style="max-width:90px"
                       data-field="p" data-gidx="{{ $gi }}"
                       data-orig="{{ $g->purchase_price }}">
              </div>
            </td>
            <td class="text-end">
              <div class="input-group input-group-sm justify-content-end">
                <span class="input-group-text px-1">&#8377;</span>
                <input type="number" name="groups[{{ $gi }}][selling_price]"
                       class="form-control text-end group-price-input"
                       value="{{ number_format($g->selling_price, 2, '.', '') }}"
                       min="0" step="0.01" style="max-width:90px"
                       data-field="s" data-gidx="{{ $gi }}"
                       data-orig="{{ $g->selling_price }}">
              </div>
            </td>
            <td class="text-center" id="gmargin_{{ $gi }}">
              <span class="{{ $gm < 0 ? 'text-danger fw-700' : ($gm < 10 ? 'text-warning fw-600' : 'text-success fw-600') }}">{{ $gm }}%</span>
            </td>
            <td class="text-center" id="gchange_{{ $gi }}"></td>
          </tr>
          @empty
          <tr><td colspan="8" class="text-center py-4 text-muted">No available stock.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </form>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{--  SECTION 2: SPARE PARTS (behind toggle)                       --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if($products->isNotEmpty())
<div class="mb-4">
  <div class="d-flex align-items-center mb-2">
    <h6 class="mb-0 fw-700 text-warning"><i class="bi bi-box-seam me-2"></i>Spare Parts Prices</h6>
    <span class="badge bg-warning-subtle text-warning-emphasis ms-2">{{ $products->total() }} products</span>
    <span class="badge bg-secondary ms-2">Optional</span>
  </div>
  <form method="POST" action="{{ route('admin.price-management.update') }}" id="productsForm">
    @csrf @method('PUT')
    <input type="hidden" name="section" value="products">

    <div class="card border-warning">
      <div class="card-header d-flex align-items-center justify-content-between py-2 bg-warning-subtle">
        <span class="small fw-600 text-warning-emphasis"><i class="bi bi-box-seam me-1"></i>Edit purchase &amp; selling prices for spare parts</span>
        <div class="d-flex gap-2 align-items-center">
          <input type="text" name="reason" class="form-control form-control-sm" style="width:200px" placeholder="Reason">
          <button type="submit" class="btn btn-warning btn-sm fw-600">
            <i class="bi bi-check-circle me-1"></i>Save Parts Prices
          </button>
        </div>
      </div>
      <div class="table-responsive">
      <table class="table table-sm align-middle mb-0 table-hover">
        <thead class="table-light">
          <tr>
            <th class="px-3">Product</th><th>SKU</th><th>Category</th>
            <th class="text-end" style="width:130px">Purchase (₹)</th>
            <th class="text-end" style="width:130px">Selling (₹)</th>
            <th class="text-center" style="width:90px">Margin %</th>
          </tr>
        </thead>
        <tbody>
        @foreach($products as $k => $p)
        @php
          $pm = $p->purchase_price > 0
            ? round((($p->selling_price - $p->purchase_price) / $p->purchase_price) * 100, 1) : 0;
        @endphp
        <input type="hidden" name="products[{{ $k }}][product_id]" value="{{ $p->id }}">
        <tr class="product-row" data-orig-p="{{ $p->purchase_price }}" data-orig-s="{{ $p->selling_price }}" data-idx="{{ $k }}">
          <td class="px-3 fw-500 small">{{ $p->name }}</td>
          <td class="small text-muted">{{ $p->sku ?? '—' }}</td>
          <td class="small text-muted">{{ $p->category?->name ?? '—' }}</td>
          <td class="text-end">
            <div class="input-group input-group-sm justify-content-end">
              <span class="input-group-text px-1">₹</span>
              <input type="number" name="products[{{ $k }}][purchase_price]"
                     class="form-control text-end product-price-input"
                     value="{{ number_format($p->purchase_price, 2, '.', '') }}"
                     min="0" step="0.01" style="max-width:90px"
                     data-field="p" data-idx="{{ $k }}" data-orig="{{ $p->purchase_price }}">
            </div>
          </td>
          <td class="text-end">
            <div class="input-group input-group-sm justify-content-end">
              <span class="input-group-text px-1">₹</span>
              <input type="number" name="products[{{ $k }}][selling_price]"
                     class="form-control text-end product-price-input"
                     value="{{ number_format($p->selling_price, 2, '.', '') }}"
                     min="0" step="0.01" style="max-width:90px"
                     data-field="s" data-idx="{{ $k }}" data-orig="{{ $p->selling_price }}">
            </div>
          </td>
          <td class="text-center" id="pmargin_{{ $k }}">
            <span class="{{ $pm < 0 ? 'text-danger fw-700' : ($pm < 10 ? 'text-warning fw-600' : 'text-success fw-600') }}">{{ $pm }}%</span>
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
      </div>
      @if($products->hasPages())
      <div class="card-footer py-2">{{ $products->appends(request()->query())->links() }}</div>
      @endif
    </div>
  </form>
</div>
@endif

@endsection

@push('scripts')
<script>
// ── Spare parts toggle ──────────────────────────────────────────
$('#toggleParts').on('click', function () {
    $('#partsHidden').val($('#partsHidden').val() === '1' ? '0' : '1');
    $('#filterForm').submit();
});

// ── Group Edit toggle (localStorage persisted) ──────────────────
var GE_KEY = 'pm_groupEdit_v1';
var groupEditOn = localStorage.getItem(GE_KEY) === '1';

function applyGroupEditState(on, animate) {
    groupEditOn = on;
    localStorage.setItem(GE_KEY, on ? '1' : '0');
    var dur = animate ? 250 : 0;
    if (on) {
        $('#groupEditSection').slideDown(dur);
        $('#stockSection').slideUp(dur);
        $('#toggleGroupEdit').removeClass('btn-outline-primary').addClass('btn-primary');
        $('#groupEditLabel').text('Group Edit ON ✓');
    } else {
        $('#groupEditSection').slideUp(dur);
        $('#stockSection').slideDown(dur);
        $('#toggleGroupEdit').removeClass('btn-primary').addClass('btn-outline-primary');
        $('#groupEditLabel').text('Group Edit OFF');
    }
}

// Apply stored state immediately (no animation on load)
applyGroupEditState(groupEditOn, false);

$('#toggleGroupEdit').on('click', function () {
    applyGroupEditState(!groupEditOn, true);
});

// ── Stock (individual) price change tracking + live margin ──────
let stockChanged = new Set();
$('.stock-price-input').on('input', function () {
    const idx   = $(this).data('idx');
    const $row  = $(this).closest('tr.stock-row');
    const origP = parseFloat($row.data('orig-p')) || 0;
    const origS = parseFloat($row.data('orig-s')) || 0;
    const newP  = parseFloat($row.find('[data-field="p"]').val()) || 0;
    const newS  = parseFloat($row.find('[data-field="s"]').val()) || 0;

    const margin = newP > 0 ? ((newS - newP) / newP * 100).toFixed(1) : 0;
    $(`#smargin_${idx}`).html(
        `<span class="${margin < 0 ? 'text-danger fw-700' : (margin < 10 ? 'text-warning fw-600' : 'text-success fw-600')}">${margin}%</span>`
    );

    const pChg = Math.abs(newP - origP) > 0.01;
    const sChg = Math.abs(newS - origS) > 0.01;
    if (pChg || sChg) {
        stockChanged.add(idx); $row.addClass('table-warning');
        let pills = '';
        if (pChg) { const d = newP - origP; pills += `<span class="badge ${d>0?'bg-danger-subtle text-danger-emphasis':'bg-success-subtle text-success-emphasis'} d-block">P ${d>0?'+':''}${d.toFixed(0)}</span>`; }
        if (sChg) { const d = newS - origS; pills += `<span class="badge ${d>0?'bg-danger-subtle text-danger-emphasis':'bg-success-subtle text-success-emphasis'} d-block">S ${d>0?'+':''}${d.toFixed(0)}</span>`; }
        $(`#schange_${idx}`).html(pills);
    } else {
        stockChanged.delete(idx); $row.removeClass('table-warning'); $(`#schange_${idx}`).html('');
    }
    const cnt = stockChanged.size;
    $('#stockChangedCount').text(cnt).toggle(cnt > 0);
});

// ── Group price change tracking + live margin ───────────────────
let groupChanged = new Set();
$('.group-price-input').on('input', function () {
    const gidx  = $(this).data('gidx');
    const $row  = $(this).closest('tr.group-row');
    const origP = parseFloat($row.data('orig-p')) || 0;
    const origS = parseFloat($row.data('orig-s')) || 0;
    const newP  = parseFloat($row.find('[data-field="p"]').val()) || 0;
    const newS  = parseFloat($row.find('[data-field="s"]').val()) || 0;

    const margin = newP > 0 ? ((newS - newP) / newP * 100).toFixed(1) : 0;
    $(`#gmargin_${gidx}`).html(
        `<span class="${margin < 0 ? 'text-danger fw-700' : (margin < 10 ? 'text-warning fw-600' : 'text-success fw-600')}">${margin}%</span>`
    );

    const pChg = Math.abs(newP - origP) > 0.01;
    const sChg = Math.abs(newS - origS) > 0.01;
    if (pChg || sChg) {
        groupChanged.add(gidx); $row.addClass('table-warning');
        let pills = '';
        if (pChg) { const d = newP - origP; pills += `<span class="badge ${d>0?'bg-danger-subtle text-danger-emphasis':'bg-success-subtle text-success-emphasis'} d-block">P ${d>0?'+':''}${d.toFixed(0)}</span>`; }
        if (sChg) { const d = newS - origS; pills += `<span class="badge ${d>0?'bg-danger-subtle text-danger-emphasis':'bg-success-subtle text-success-emphasis'} d-block">S ${d>0?'+':''}${d.toFixed(0)}</span>`; }
        $(`#gchange_${gidx}`).html(pills);
    } else {
        groupChanged.delete(gidx); $row.removeClass('table-warning'); $(`#gchange_${gidx}`).html('');
    }
    const cnt = groupChanged.size;
    $('#groupChangedCount').text(cnt).toggle(cnt > 0);
});

// ── Product price live margin ───────────────────────────────────
$('.product-price-input').on('input', function () {
    const idx   = $(this).data('idx');
    const $row  = $(this).closest('tr.product-row');
    const newP  = parseFloat($row.find('[data-field="p"]').val()) || 0;
    const newS  = parseFloat($row.find('[data-field="s"]').val()) || 0;
    const margin = newP > 0 ? ((newS - newP) / newP * 100).toFixed(1) : 0;
    $(`#pmargin_${idx}`).html(
        `<span class="${margin < 0 ? 'text-danger fw-700' : (margin < 10 ? 'text-warning fw-600' : 'text-success fw-600')}">${margin}%</span>`
    );
    const origP = parseFloat($row.data('orig-p')) || 0;
    const origS = parseFloat($row.data('orig-s')) || 0;
    $row.toggleClass('table-warning', Math.abs(newP-origP) > 0.01 || Math.abs(newS-origS) > 0.01);
});

// ── Warn before leaving with unsaved changes ────────────────────
window.addEventListener('beforeunload', e => {
    if (stockChanged.size > 0 || groupChanged.size > 0) { e.preventDefault(); e.returnValue = ''; }
});
$('#stockForm, #productsForm, #groupForm').on('submit', () => { stockChanged.clear(); groupChanged.clear(); });
</script>
@endpush
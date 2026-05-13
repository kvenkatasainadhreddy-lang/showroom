@extends('layouts.admin')
@section('title','Chassis Registry')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h5 class="mb-0 fw-700"><i class="bi bi-car-front me-2 text-success"></i>Chassis Registry</h5>
    <div class="small text-muted">Add and track individual chassis numbers per variant</div>
  </div>
  <a href="{{ route('admin.catalogue.setup') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back to Setup
  </a>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show py-2"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="row g-3">

  {{-- ── LEFT: Add Chassis Form ───────────────────────────── --}}
  <div class="col-lg-4">
    <div class="card border-success border-top border-top-3 sticky-top" style="top:70px">
      <div class="card-header py-2 bg-success-subtle">
        <span class="fw-700 text-success-emphasis"><i class="bi bi-plus-circle me-1"></i>Add Chassis Number</span>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <label class="form-label small fw-600 mb-1">Brand</label>
          <select id="formBrand" class="form-select form-select-sm">
            <option value="">— Brand —</option>
            @foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small fw-600 mb-1">Model</label>
          <select id="formModel" class="form-select form-select-sm" disabled>
            <option value="">— Select Brand first —</option>
            @foreach($models as $m)<option value="{{ $m->id }}" data-brand="{{ $m->brand_id }}">{{ $m->name }}</option>@endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small fw-600 mb-1">Variant <span class="text-danger">*</span></label>
          <select id="formVariant" name="variant_id" class="form-select form-select-sm" disabled>
            <option value="">— Select Model first —</option>
            @foreach($variants as $v)<option value="{{ $v->id }}" data-model="{{ $v->model_id }}">{{ $v->name }}{{ $v->color ? ' ('.$v->color.')' : '' }}</option>@endforeach
          </select>
        </div>
        <hr class="my-2">
        <div class="mb-2">
          <label class="form-label small fw-600 mb-1">Chassis No. <span class="text-danger">*</span></label>
          <input type="text" id="formChassis" class="form-control form-control-sm text-uppercase" placeholder="e.g. ME4KC131MR8001234">
        </div>
        <div class="mb-2">
          <label class="form-label small fw-600 mb-1">Engine No.</label>
          <input type="text" id="formEngine" class="form-control form-control-sm text-uppercase" placeholder="Engine number">
        </div>
        <div class="mb-2">
          <label class="form-label small fw-600 mb-1">Color</label>
          <input type="text" id="formColor" class="form-control form-control-sm" placeholder="e.g. Matte Blue Metallic">
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label small fw-600 mb-1">Purchase Price <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm"><span class="input-group-text">₹</span>
              <input type="number" id="formPurchase" class="form-control" placeholder="0" min="0" step="0.01">
            </div>
          </div>
          <div class="col-6">
            <label class="form-label small fw-600 mb-1">Selling Price <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm"><span class="input-group-text">₹</span>
              <input type="number" id="formSelling" class="form-control" placeholder="0" min="0" step="0.01">
            </div>
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label small fw-600 mb-1">Branch</label>
          <select id="formBranch" class="form-select form-select-sm">
            <option value="">— No Branch —</option>
            @foreach($branches as $br)<option value="{{ $br->id }}">{{ $br->name }}</option>@endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-600 mb-1">Received Date</label>
          <input type="date" id="formDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
        </div>
        <div id="chassisError" class="alert alert-danger py-2 small" style="display:none"></div>
        <div id="chassisSuccess" class="alert alert-success py-2 small" style="display:none"></div>
        <button class="btn btn-success w-100 fw-600" id="saveChassisBtn" type="button">
          <i class="bi bi-check-circle me-1"></i>Add Chassis
        </button>
      </div>
    </div>
  </div>

  {{-- ── RIGHT: Chassis Table ─────────────────────────────── --}}
  <div class="col-lg-8">

    {{-- Filter bar --}}
    <form method="GET" class="card mb-3">
      <div class="card-body py-2">
        <div class="row g-2 align-items-end">
          <div class="col-sm-3">
            <select name="brand_id" class="form-select form-select-sm">
              <option value="">All Brands</option>
              @foreach($brands as $b)<option value="{{ $b->id }}" {{ request('brand_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
            </select>
          </div>
          <div class="col-sm-3">
            <select name="model_id" class="form-select form-select-sm">
              <option value="">All Models</option>
              @foreach($models as $m)<option value="{{ $m->id }}" {{ request('model_id')==$m->id?'selected':'' }}>{{ $m->name }}</option>@endforeach
            </select>
          </div>
          <div class="col-sm-3">
            <select name="variant_id" class="form-select form-select-sm">
              <option value="">All Variants</option>
              @foreach($variants as $v)<option value="{{ $v->id }}" {{ request('variant_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>@endforeach
            </select>
          </div>
          <div class="col-sm-2">
            <select name="status" class="form-select form-select-sm">
              <option value="">All Status</option>
              <option value="available" {{ request('status')=='available'?'selected':'' }}>Available</option>
              <option value="sold"      {{ request('status')=='sold'?'selected':'' }}>Sold</option>
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
            <a href="{{ route('admin.catalogue.chassis') }}" class="btn btn-light btn-sm">Reset</a>
          </div>
        </div>
      </div>
    </form>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between py-2">
        <span class="fw-600 small"><i class="bi bi-car-front me-1 text-success"></i>Chassis List</span>
        <span class="badge bg-success-subtle text-success-emphasis">{{ $stockQuery->total() }} entries</span>
      </div>
      <div class="table-responsive">
      <table class="table table-sm align-middle mb-0 table-hover" id="chassisTable">
        <thead class="table-light"><tr>
          <th class="px-3">Chassis No.</th>
          <th>Variant / Model</th>
          <th>Color</th>
          <th class="text-end">Purchase</th>
          <th class="text-end">Selling</th>
          <th class="text-center">Margin</th>
          <th class="text-center">Status</th>
          <th>Branch</th>
          <th></th>
        </tr></thead>
        <tbody id="chassisTableBody">
        @forelse($stockQuery as $s)
        @php $margin = $s->purchase_price > 0 ? round((($s->selling_price-$s->purchase_price)/$s->purchase_price)*100,1) : 0; @endphp
        <tr>
          <td class="px-3"><code class="small fw-600">{{ $s->chassis_number }}</code>
            @if($s->engine_number)<div class="text-muted" style="font-size:10px">E: {{ $s->engine_number }}</div>@endif
          </td>
          <td class="small">
            <div class="fw-500">{{ $s->variant?->name }}</div>
            <div class="text-muted" style="font-size:11px">{{ $s->variant?->vehicleModel?->brand?->name }} {{ $s->variant?->vehicleModel?->name }}</div>
          </td>
          <td class="small text-muted">{{ $s->color ?? '—' }}</td>
          <td class="text-end small">₹{{ number_format($s->purchase_price,0) }}</td>
          <td class="text-end small fw-600">₹{{ number_format($s->selling_price,0) }}</td>
          <td class="text-center small {{ $margin < 5 ? 'text-danger' : ($margin < 15 ? 'text-warning' : 'text-success') }}">{{ $margin }}%</td>
          <td class="text-center">
            <span class="badge {{ $s->status === 'available' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($s->status) }}</span>
          </td>
          <td class="small text-muted">{{ $s->branch?->name ?? '—' }}</td>
          <td>
            <a href="{{ route('admin.vehicle-stock.edit', $s) }}" class="btn btn-xs btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center py-4 text-muted">No chassis found. Add one using the form on the left.</td></tr>
        @endforelse
        </tbody>
      </table>
      </div>
      @if($stockQuery->hasPages())
      <div class="card-footer py-2">{{ $stockQuery->links() }}</div>
      @endif
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

// ── Brand → Model cascade ─────────────────────────────────────
$('#formBrand').on('change', function () {
    const brandId = $(this).val();
    const $model = $('#formModel');
    $model.prop('disabled', !brandId).val('');
    $('#formVariant').prop('disabled', true).val('');
    $model.find('option').each((i, el) => {
        if (!$(el).val()) return;
        $(el).toggle(!brandId || $(el).data('brand') == brandId);
    });
    $model.prop('disabled', false);
});

// ── Model → Variant cascade ───────────────────────────────────
$('#formModel').on('change', function () {
    const modelId = $(this).val();
    const $variant = $('#formVariant');
    $variant.prop('disabled', !modelId).val('');
    $variant.find('option').each((i, el) => {
        if (!$(el).val()) return;
        $(el).toggle(!modelId || $(el).data('model') == modelId);
    });
    if (modelId) $variant.prop('disabled', false);
});

// ── Save chassis via AJAX ─────────────────────────────────────
$('#saveChassisBtn').on('click', function () {
    const $btn = $(this);
    const data = {
        _token:         CSRF,
        variant_id:     $('#formVariant').val(),
        chassis_number: $('#formChassis').val().trim().toUpperCase(),
        engine_number:  $('#formEngine').val().trim().toUpperCase(),
        color:          $('#formColor').val().trim(),
        purchase_price: $('#formPurchase').val(),
        selling_price:  $('#formSelling').val(),
        branch_id:      $('#formBranch').val(),
        received_date:  $('#formDate').val(),
    };

    $('#chassisError, #chassisSuccess').hide();

    if (!data.variant_id) { $('#chassisError').text('Please select a Variant.').show(); return; }
    if (!data.chassis_number) { $('#chassisError').text('Chassis number is required.').show(); return; }
    if (!data.purchase_price || !data.selling_price) { $('#chassisError').text('Purchase and selling price are required.').show(); return; }

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving…');

    $.post('{{ route("admin.catalogue.chassis.store") }}', data)
    .done(res => {
        if (!res.success) return;
        const s = res.stock;
        const margin = s.purchase_price > 0 ? (((s.selling_price - s.purchase_price) / s.purchase_price) * 100).toFixed(1) : 0;
        const row = `<tr class="table-success">
          <td class="px-3"><code class="small fw-600">${s.chassis_number}</code>
            ${s.engine_number ? '<div class="text-muted" style="font-size:10px">E: ' + s.engine_number + '</div>' : ''}
          </td>
          <td class="small"><div class="fw-500">${s.variant}</div><div class="text-muted" style="font-size:11px">${s.brand} ${s.model}</div></td>
          <td class="small text-muted">${s.color || '—'}</td>
          <td class="text-end small">₹${parseInt(s.purchase_price).toLocaleString('en-IN')}</td>
          <td class="text-end small fw-600">₹${parseInt(s.selling_price).toLocaleString('en-IN')}</td>
          <td class="text-center small text-success">${margin}%</td>
          <td class="text-center"><span class="badge bg-success">Available</span></td>
          <td class="small text-muted">${s.branch}</td>
          <td></td>
        </tr>`;
        $('#chassisTableBody').prepend(row);
        $('#chassisSuccess').text(`Chassis ${s.chassis_number} added successfully!`).show();
        $('#formChassis, #formEngine, #formColor').val('');
        $('#formPurchase, #formSelling').val('');
        setTimeout(() => $('#chassisSuccess').fadeOut(), 3000);
    })
    .fail(err => {
        const errs = err.responseJSON?.errors;
        const msg = errs ? Object.values(errs).flat().join(' | ') : 'Failed to add chassis.';
        $('#chassisError').text(msg).show();
    })
    .always(() => $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Add Chassis'));
});
</script>
@endpush

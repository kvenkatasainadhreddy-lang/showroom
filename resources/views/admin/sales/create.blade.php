@extends('layouts.admin')
@section('title', 'New Vehicle Sale')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 class="mb-0 fw-700"><i class="bi bi-car-front me-2 text-primary"></i>Sales Area — Cashier Bill Form</h5>
  <span class="badge bg-primary-subtle text-primary-emphasis fs-6 px-3 py-2">
    <i class="bi bi-hash me-1"></i><span id="displayBillNo">{{ $billNo }}</span>
    <span class="text-muted ms-1 small fw-400">Auto Bill No.</span>
  </span>
</div>

@if($errors->has('payment_sum'))
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
  <i class="bi bi-exclamation-triangle-fill"></i>
  <div>{{ $errors->first('payment_sum') }}</div>
</div>
@endif

<form method="POST" action="{{ route('admin.vehicle-sales.store') }}" id="vehicleSaleForm">
@csrf
<input type="hidden" name="invoice_no"       id="invoice_no"       value="{{ old('invoice_no', $billNo) }}">
<input type="hidden" name="discount_type"   id="discountType"   value="{{ old('discount_type', 'amount') }}">
<input type="hidden" name="discount_value"  id="discountValue"  value="{{ old('discount_value', 0) }}">
<input type="hidden" name="discount_amount" id="discountAmount" value="{{ old('discount_amount', 0) }}">

<div class="card shadow-sm">
  <div class="card-body p-4">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{--  ROW 1: Date | Customer Name | Model (cascade starts here) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3">
      <div class="col-md-2">
        <label class="form-label fw-600 small">Date</label>
        <input type="date" name="sale_date" class="form-control"
               value="{{ old('sale_date', date('Y-m-d')) }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-600 small">Customer Name <span class="text-danger">*</span></label>
        <input type="text" name="customer_name" class="form-control"
               value="{{ old('customer_name') }}" placeholder="Enter customer full name" required>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-600 small">Model <span class="text-danger">*</span>
          <span class="text-muted fw-400">(dropdown — all models)</span>
        </label>
        <select id="selectModel" class="form-select">
          <option value="">— Select Model —</option>
          @foreach($vehicleModels as $m)
          <option value="{{ $m->id }}">{{ $m->brand?->name }} {{ $m->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-600 small">Variants — All
          <span id="variantCount" class="text-muted fw-400"></span>
        </label>
        <select id="selectVariant" class="form-select" disabled>
          <option value="">— Select Model first —</option>
        </select>
      </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{--  ROW 2: Salesman | Branch | Chassis (depending on selected model)  --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label fw-600 small">Salesman Name</label>
        @if(auth()->user()->hasRole('admin'))
          {{-- Admin: full dropdown --}}
          <select name="salesman_id" class="form-select">
            <option value="">— Select Salesman —</option>
            @foreach($salesmen as $u)
            <option value="{{ $u->id }}" {{ old('salesman_id', auth()->id()) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
          </select>
        @else
          {{-- Non-admin: auto-set to current user, readonly --}}
          <input type="hidden" name="salesman_id" value="{{ auth()->id() }}">
          <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}" disabled>
          <div class="form-text text-muted small"><i class="bi bi-lock-fill me-1"></i>Auto-assigned to you</div>
        @endif
      </div>

      <div class="col-md-2">
        <label class="form-label fw-600 small">Branch <span class="text-danger">*</span></label>
        <select name="branch_id" class="form-select" required>
          @foreach($branches as $b)
          <option value="{{ $b->id }}" {{ old('branch_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-600 small">Chassis Number
          <span class="text-muted fw-400 small">— depending on selected model</span>
        </label>
        <select id="selectChassis" name="vehicle_stock_id" class="form-select" disabled required>
          <option value="">— Select Variant first —</option>
        </select>
      </div>
      <div class="col-md-3">
        {{-- Link to existing customer record (optional) --}}
        <label class="form-label fw-600 small">Link Customer Record <span class="text-muted fw-400">(optional)</span></label>
        <select name="customer_id" class="form-select">
          <option value="">— Walk-in —</option>
          @foreach($customers as $c)
          <option value="{{ $c->id }}" {{ old('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <hr class="my-3">

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--  PAYMENTS SECTION                                      --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="row g-3">
      {{-- Left: payment inputs --}}
      <div class="col-lg-7">
        <div class="fw-600 mb-2 text-muted small text-uppercase letter-spacing-1">Payments</div>

        {{-- Total amount of model row --}}
        <div class="row g-2 mb-2 align-items-end">
          <div class="col-6">
            <label class="form-label small fw-600">Total Amount of Model (₹) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">₹</span>
              <input type="number" name="total" id="totalAmount" class="form-control fw-600 fs-5"
                     value="{{ old('total', 0) }}" min="0" step="0.01" required placeholder="0.00">
            </div>
            <div class="form-text" id="vehiclePriceHint" style="display:none">
              <i class="bi bi-check-circle-fill text-success me-1"></i>Auto-filled from chassis
            </div>
          </div>
          <div class="col-6">
            <label class="form-label small fw-600">Exchange Amount (₹) <span class="text-muted fw-400">(trade-in)</span></label>
            <div class="input-group">
              <span class="input-group-text text-secondary"><i class="bi bi-arrow-left-right"></i></span>
              <input type="number" name="exchange_amount" id="exchangeAmountInput" class="form-control"
                     value="{{ old('exchange_amount', 0) }}" min="0" step="0.01" placeholder="0.00">
            </div>
          </div>
        </div>

        {{-- ── DISCOUNT ROW ── --}}
        <div class="row g-2 mb-3 align-items-end">
          <div class="col-6">
            <label class="form-label small fw-600">
              <i class="bi bi-tag-fill text-danger me-1"></i>Discount
              <span class="text-muted fw-400 ms-1" id="discountBadge">₹ Flat</span>
            </label>
            <div class="input-group input-group-sm">
              {{-- Toggle button: switches between % and ₹ --}}
              <button type="button" id="discountToggle" class="btn btn-outline-danger px-2"
                      title="Click to switch between % and flat amount">
                <i class="bi bi-currency-rupee" id="discountToggleIcon"></i>
              </button>
              <input type="number" id="discountInput" class="form-control"
                     value="{{ old('discount_value', 0) }}" min="0" step="0.01" placeholder="0.00">
              <span class="input-group-text fw-600 text-danger" id="discountSuffix">₹</span>
            </div>
            <div class="form-text" id="discountCalcHint"></div>
          </div>
          <div class="col-6">
            <label class="form-label small fw-600">Net After Discount &amp; Exchange <span class="text-muted fw-400">auto</span></label>
            <div class="input-group input-group-sm">
              <span class="input-group-text text-success"><i class="bi bi-check2-circle"></i></span>
              <input type="text" id="netPayableDisplay" class="form-control fw-600 text-success" readonly placeholder="0.00">
            </div>
          </div>
        </div>

        {{-- Cash | ADV | Finance Name --}}
        <div class="row g-2 mb-2">
          <div class="col-4">
            <label class="form-label small fw-600">Cash (₹)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text text-success"><i class="bi bi-cash"></i></span>
              <input type="number" name="cash_amount" id="cashAmount" class="form-control"
                     value="{{ old('cash_amount', 0) }}" min="0" step="0.01" placeholder="0.00">
            </div>
          </div>
          <div class="col-4">
            <label class="form-label small fw-600">ADV (₹)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text text-warning"><i class="bi bi-wallet2"></i></span>
              <input type="number" name="advance_amount" id="advanceAmount" class="form-control"
                     value="{{ old('advance_amount', 0) }}" min="0" step="0.01" placeholder="0.00">
            </div>
          </div>
          <div class="col-4">
            <label class="form-label small fw-600">Finance Name</label>
            <input type="text" name="finance_name" class="form-control form-control-sm"
                   value="{{ old('finance_name') }}" placeholder="e.g. HDFC Bank">
          </div>
        </div>

        {{-- Finance Amount | Balance | Amount Verification --}}
        <div class="row g-2 mb-3">
          <div class="col-4">
            <label class="form-label small fw-600">Finance Amount (₹)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text text-info"><i class="bi bi-bank"></i></span>
              <input type="number" name="finance_amount" id="financeAmount" class="form-control"
                     value="{{ old('finance_amount', 0) }}" min="0" step="0.01" placeholder="0.00">
            </div>
          </div>
          <div class="col-4">
            <label class="form-label small fw-600">Balance (₹) <span class="text-muted small fw-400">auto</span></label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bi bi-calculator"></i></span>
              <input type="text" id="balanceDisplay" class="form-control fw-600" readonly placeholder="0.00">
            </div>
          </div>
          <div class="col-4">
            <label class="form-label small fw-600">Amount Verification</label>
            <div class="input-group input-group-sm">
              <input type="number" id="amountVerification" class="form-control" placeholder="Re-enter total">
              <span class="input-group-text" id="verifyIcon"><i class="bi bi-question-circle text-muted"></i></span>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-600">Notes</label>
          <textarea name="notes" class="form-control form-control-sm" rows="2"
                    placeholder="Any remarks…">{{ old('notes') }}</textarea>
        </div>
      </div>

      {{-- Right: running calculation table + status --}}
      <div class="col-lg-5">
        <div class="fw-600 mb-2 text-muted small text-uppercase">Table — All types of amounts should match total amount of vehicle</div>
        <div class="card bg-light border-0">
          <div class="card-body p-3">
            <table class="table table-sm mb-3">
              <tbody>
                <tr><td class="text-muted small">Total Vehicle Amount</td>
                    <td class="text-end fw-600" id="tblTotal">₹0.00</td></tr>
                <tr><td class="text-muted small"><i class="bi bi-tag-fill me-1 text-danger"></i>Discount</td>
                    <td class="text-end text-danger" id="tblDiscount">₹0.00</td></tr>
                <tr><td class="text-muted small"><i class="bi bi-arrow-left-right me-1"></i>Exchange</td>
                    <td class="text-end text-secondary" id="tblExchange">₹0.00</td></tr>
                <tr class="border-top"><td class="text-muted small fw-600">Net Payable</td>
                    <td class="text-end fw-700" id="tblNetPayable">₹0.00</td></tr>
                <tr><td class="text-muted small"><i class="bi bi-cash me-1 text-success"></i>Cash</td>
                    <td class="text-end" id="tblCash">₹0.00</td></tr>
                <tr><td class="text-muted small"><i class="bi bi-wallet2 me-1 text-warning"></i>Advance</td>
                    <td class="text-end" id="tblAdv">₹0.00</td></tr>
                <tr><td class="text-muted small"><i class="bi bi-bank me-1 text-info"></i>Finance</td>
                    <td class="text-end" id="tblFin">₹0.00</td></tr>
                <tr class="border-top"><td class="fw-600 small">Sum Collected</td>
                    <td class="text-end fw-700" id="tblSum">₹0.00</td></tr>
              </tbody>
            </table>

            {{-- Status indicator --}}
            <div id="paymentStatus" class="alert alert-secondary py-2 px-3 small mb-0 text-center">
              <i class="bi bi-info-circle me-1"></i>Enter amounts to verify
            </div>

            {{-- Balance display --}}
            <div class="d-flex justify-content-between align-items-center mt-3 px-1">
              <span class="fw-600">Balance Due</span>
              <span id="tblBalance" class="fw-700 fs-5" style="color:#dc3545">₹0.00</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr class="my-3">

    {{-- Buttons --}}
    <div class="d-flex gap-2 justify-content-center">
      <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary px-4">
        <i class="bi bi-x-circle me-1"></i>Cancel
      </a>
      <button type="submit" class="btn btn-success px-5 fw-600" id="submitBtn">
        <i class="bi bi-check-circle me-1"></i>Submit
      </button>
    </div>

  </div>{{-- /card-body --}}
</div>{{-- /card --}}
</form>
@endsection

@push('scripts')
<script>
// ── AJAX: Model → Variants ──────────────────────────────────────
$('#selectModel').on('change', function () {
    const modelId = $(this).val();
    $('#selectVariant').prop('disabled', true).html('<option value="">Loading…</option>');
    $('#selectChassis').prop('disabled', true).html('<option value="">— Select Variant first —</option>').val('');
    resetVehiclePrice();

    if (!modelId) { $('#selectVariant').html('<option value="">— Select Model first —</option>'); return; }

    $.getJSON('{{ url("admin/vehicle-variants/by-model") }}/' + modelId, function (variants) {
        let opts = '<option value="">— Select Variant —</option>';
        variants.forEach(v => {
            opts += `<option value="${v.id}" data-price="${v.base_price}">
                ${v.name}${v.color ? ' (' + v.color + ')' : ''}
                [${v.available_count ?? 0} avail]
                </option>`;
        });
        $('#selectVariant').html(opts).prop('disabled', false);
        $('#variantCount').text('(' + variants.length + ')');
    });
});

// ── AJAX: Variant → Chassis ─────────────────────────────────────
$('#selectVariant').on('change', function () {
    const variantId = $(this).val();
    const basePrice = parseFloat($(this).find(':selected').data('price')) || 0;
    $('#selectChassis').prop('disabled', true).html('<option value="">Loading…</option>').val('');
    resetVehiclePrice();

    if (!variantId) { $('#selectChassis').html('<option value="">— Select Variant first —</option>'); return; }

    $.getJSON('{{ url("admin/vehicle-stock/by-variant") }}/' + variantId, function (stock) {
        let opts = '<option value="">— Select Chassis —</option>';
        stock.forEach(s => {
            const price = parseFloat(s.effective_price || basePrice);
            opts += `<option value="${s.id}" data-price="${price}" data-chassis="${s.chassis_number}" data-color="${s.color||''}">
                ${s.chassis_number}${s.color ? ' — ' + s.color : ''} (₹${price.toLocaleString('en-IN')})
                </option>`;
        });
        if (!stock.length) opts = '<option value="">— No stock available —</option>';
        $('#selectChassis').html(opts).prop('disabled', stock.length > 0);
        if (stock.length) $('#selectChassis').prop('disabled', false);
    });
});

// ── Chassis selected: auto-fill total ──────────────────────────
$('#selectChassis').on('change', function () {
    const opt = $(this).find(':selected');
    const price = parseFloat(opt.data('price')) || 0;
    if (!$(this).val()) { resetVehiclePrice(); return; }

    $('#totalAmount').val(price.toFixed(2)).trigger('input');
    $('#vehiclePriceHint').show();
});

function resetVehiclePrice() {
    $('#vehiclePriceHint').hide();
}

// ── Discount toggle: % ↔ ₹ ─────────────────────────────────────
let discountMode = '{{ old("discount_type", "amount") }}'; // 'amount' or 'percent'

function syncDiscountUI() {
    if (discountMode === 'percent') {
        $('#discountToggleIcon').attr('class', 'bi bi-percent');
        $('#discountSuffix').text('%');
        $('#discountBadge').text('% Percentage');
        $('#discountToggle').attr('title', 'Switch to flat ₹ amount');
    } else {
        $('#discountToggleIcon').attr('class', 'bi bi-currency-rupee');
        $('#discountSuffix').text('₹');
        $('#discountBadge').text('₹ Flat');
        $('#discountToggle').attr('title', 'Switch to percentage %');
    }
    $('#discountType').val(discountMode);
}

$('#discountToggle').on('click', function () {
    discountMode = (discountMode === 'amount') ? 'percent' : 'amount';
    // Convert current value to opposite mode on switch
    const total = parseFloat($('#totalAmount').val()) || 0;
    const cur   = parseFloat($('#discountInput').val()) || 0;
    if (total > 0) {
        if (discountMode === 'percent') {
            // was flat → convert to %
            $('#discountInput').val(((cur / total) * 100).toFixed(2));
        } else {
            // was % → convert to flat
            $('#discountInput').val(((cur / 100) * total).toFixed(2));
        }
    }
    syncDiscountUI();
    recalc();
});

function getDiscountAmount() {
    const total = parseFloat($('#totalAmount').val()) || 0;
    const val   = parseFloat($('#discountInput').val()) || 0;
    if (discountMode === 'percent') {
        return Math.min((val / 100) * total, total);
    }
    return Math.min(val, total);
}

// ── Live calculation ────────────────────────────────────────────
function recalc() {
    const total    = parseFloat($('#totalAmount').val())       || 0;
    const discount = getDiscountAmount();
    const exchange = parseFloat($('#exchangeAmountInput').val()) || 0;
    const cash     = parseFloat($('#cashAmount').val())        || 0;
    const advance  = parseFloat($('#advanceAmount').val())     || 0;
    const finance  = parseFloat($('#financeAmount').val())     || 0;

    const afterDiscount = Math.max(0, total - discount);
    const netPayable    = Math.max(0, afterDiscount - exchange);
    const collected     = cash + advance + finance;
    const balance       = netPayable - collected;

    const fmt = n => '₹' + Math.abs(n).toLocaleString('en-IN', {minimumFractionDigits:2});

    // Sync hidden fields
    $('#discountValue').val(parseFloat($('#discountInput').val()) || 0);
    $('#discountAmount').val(discount.toFixed(2));

    // Discount hint
    if (discount > 0 && discountMode === 'percent') {
        const pct = parseFloat($('#discountInput').val()) || 0;
        $('#discountCalcHint').html(`<i class="bi bi-info-circle me-1"></i>${pct}% = ${fmt(discount)} off`);
    } else if (discount > 0) {
        const pct = total > 0 ? ((discount / total) * 100).toFixed(1) : 0;
        $('#discountCalcHint').html(`<i class="bi bi-info-circle me-1"></i>${pct}% of total`);
    } else {
        $('#discountCalcHint').html('');
    }

    // Net payable display
    $('#netPayableDisplay').val(fmt(netPayable).replace('₹', ''));

    // Update summary table
    $('#tblTotal').text(fmt(total));
    $('#tblDiscount').text(discount > 0 ? '− ' + fmt(discount) : '₹0.00');
    $('#tblExchange').text(fmt(exchange));
    $('#tblNetPayable').text(fmt(netPayable));
    $('#tblCash').text(fmt(cash));
    $('#tblAdv').text(fmt(advance));
    $('#tblFin').text(fmt(finance));
    $('#tblSum').text(fmt(collected));
    $('#tblBalance').text(balance !== 0 ? fmt(balance) : '₹0.00')
        .css('color', balance > 1 ? '#dc3545' : '#198754');

    // Balance field
    $('#balanceDisplay').val(fmt(balance).replace('₹',''))
        .css('color', balance > 1 ? '#dc3545' : '#198754');

    // Status badge
    const diff = Math.abs(collected - netPayable);
    const $ps  = $('#paymentStatus');
    if (netPayable === 0) {
        $ps.attr('class','alert alert-secondary py-2 px-3 small mb-0 text-center')
           .html('<i class="bi bi-info-circle me-1"></i>Enter total amount to verify');
    } else if (diff <= 1) {
        $ps.attr('class','alert alert-success py-2 px-3 small mb-0 text-center')
           .html('<i class="bi bi-check-circle me-1"></i><strong>Amounts balanced ✓</strong>');
    } else {
        const short = balance > 0 ? `Short by ${fmt(balance)}` : `Over by ${fmt(-balance)}`;
        $ps.attr('class','alert alert-danger py-2 px-3 small mb-0 text-center')
           .html(`<i class="bi bi-exclamation-triangle me-1"></i>Mismatch — ${short}`);
    }
}

// ── Amount verification field ──────────────────────────────────
$('#amountVerification').on('input', function() {
    const entered = parseFloat($(this).val()) || 0;
    const total   = parseFloat($('#totalAmount').val()) || 0;
    const $icon   = $('#verifyIcon i');
    if (!entered) { $icon.attr('class', 'bi bi-question-circle text-muted'); return; }
    if (Math.abs(entered - total) <= 1) {
        $icon.attr('class', 'bi bi-check-circle-fill text-success');
    } else {
        $icon.attr('class', 'bi bi-x-circle-fill text-danger');
    }
});

$('#totalAmount, #exchangeAmountInput, #cashAmount, #advanceAmount, #financeAmount, #discountInput').on('input', recalc);

// Init UI
syncDiscountUI();
recalc();

// ── Prevent submit if amounts mismatched ───────────────────────
$('#vehicleSaleForm').on('submit', function(e) {
    const total    = parseFloat($('#totalAmount').val())       || 0;
    const discount = getDiscountAmount();
    const exchange = parseFloat($('#exchangeAmountInput').val()) || 0;
    const cash     = parseFloat($('#cashAmount').val())        || 0;
    const advance  = parseFloat($('#advanceAmount').val())     || 0;
    const finance  = parseFloat($('#financeAmount').val())     || 0;
    const net      = Math.max(0, Math.max(0, total - discount) - exchange);
    const sum      = cash + advance + finance;
    if (total > 0 && Math.abs(sum - net) > 1) {
        e.preventDefault();
        $('#paymentStatus')
            .attr('class','alert alert-danger py-2 px-3 small mb-0 text-center')
            .html('<i class="bi bi-exclamation-triangle me-1"></i><strong>Cannot submit — amounts do not match total!</strong>');
        $('html, body').animate({scrollTop: $('#paymentStatus').offset().top - 100}, 300);
    }
});
</script>
@endpush

@extends('layouts.admin')
@section('title', 'New Sale')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600">Create New Sale</h5>
</div>

<form method="POST" action="{{ route('admin.sales.store') }}" id="saleForm">
@csrf
<div class="row g-3">
    <!-- Left: Product Search + Cart -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Add Products</div>
            <div class="card-body">
                <!-- Barcode / Name Search -->
                <div class="input-group mb-3">
                    <span class="input-group-text" title="Primary: Hardware scanner – just focus and scan">
                        <i class="bi bi-upc-scan"></i>
                    </span>
                    <input type="text" id="productSearch" class="form-control"
                        placeholder="Scan barcode (hardware) or type name / SKU…" autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="searchBtn" title="Search"><i class="bi bi-search"></i></button>
                    <!-- Secondary: Camera Scanner -->
                    <button class="btn btn-outline-primary" type="button" id="cameraScanBtn" title="Scan with camera">
                        <i class="bi bi-camera"></i> Camera
                    </button>
                </div>
                <div id="barcodeAlert" class="alert alert-success alert-sm py-1 px-2 small mb-2" style="display:none">
                    <i class="bi bi-upc-scan me-1"></i><span id="barcodeMsg"></span>
                </div>
                <!-- Search Results Dropdown -->
                <div id="searchResults" class="list-group mb-3" style="display:none; max-height:220px; overflow-y:auto;"></div>

                <!-- Cart Table -->
                <div class="table-responsive">
                <table class="table table-bordered align-middle" id="cartTable">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:160px">Product</th>
                            <th style="width:90px">Qty</th>
                            <th style="width:120px">Unit Price</th>
                            <th style="width:100px">Discount</th>
                            <th style="width:110px">Total</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        <tr id="emptyRow"><td colspan="6" class="text-center text-muted py-3 small">No items added yet</td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Sale Details -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Sale Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">Walk-in Customer</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select" required>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sale Date <span class="text-danger">*</span></label>
                    <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Overall Discount (₹)</label>
                    <input type="number" name="discount" class="form-control" id="discountInput" value="0" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tax (₹)</label>
                    <input type="number" name="tax" class="form-control" id="taxInput" value="0" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount Paid (₹)</label>
                    <input type="number" name="amount_paid" class="form-control" id="amountPaidInput" value="0" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="card">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Subtotal</td><td class="text-end fw-500" id="showSubtotal">₹0.00</td></tr>
                    <tr><td>Discount</td><td class="text-end text-danger" id="showDiscount">- ₹0.00</td></tr>
                    <tr><td>Tax</td><td class="text-end text-success" id="showTax">+ ₹0.00</td></tr>
                    <tr class="border-top"><td class="fw-700 fs-5">Total</td><td class="text-end fw-700 fs-5 text-primary" id="showTotal">₹0.00</td></tr>
                    <tr><td class="text-muted small">Balance Due</td><td class="text-end fw-500 text-danger" id="showBalance">₹0.00</td></tr>
                </table>
                <button type="submit" class="btn btn-success w-100 mt-2" id="submitBtn" disabled>
                    <i class="bi bi-check-circle me-1"></i> Create Sale
                </button>
            </div>
        </div>
    </div>
</div>
</form>
<!-- ═══════════════════════════════════════════════════════════
     Camera Barcode Scanner Modal (Secondary)
     Uses html5-qrcode CDN library – no npm/node required
═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="cameraScanModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-camera me-1"></i>Camera Barcode Scanner</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-2">
        <!-- Camera viewfinder -->
        <div id="qrReaderContainer" style="position:relative;background:#000;border-radius:6px;overflow:hidden;">
          <div id="qr-reader" style="width:100%;"></div>
          <!-- Aim overlay -->
          <div id="scanAim" style="
            position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
            width:200px;height:120px;
            border:2px solid #0d6efd;
            border-radius:6px;
            box-shadow:0 0 0 9999px rgba(0,0,0,.45);
            pointer-events:none;
          "></div>
        </div>
        <!-- Status -->
        <div class="mt-2 text-center">
          <div id="camStatus" class="small text-muted"><i class="bi bi-hourglass-split me-1"></i>Starting camera…</div>
          <div id="camResult" class="badge bg-primary mt-1" style="display:none;font-size:.85rem"></div>
        </div>
        <!-- Manual fallback -->
        <div class="mt-2">
          <div class="input-group input-group-sm">
            <input type="text" id="camManualInput" class="form-control" placeholder="Or type / paste barcode here…">
            <button class="btn btn-primary" type="button" id="camManualAdd"><i class="bi bi-check"></i> Add</button>
          </div>
        </div>
      </div>
      <div class="modal-footer py-2">
        <small class="text-muted me-auto"><i class="bi bi-info-circle me-1"></i>Point camera at barcode · auto-adds to cart</small>
        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* html5-qrcode: suppress noisy default UI elements */
  #qr-reader__header_message,
  #qr-reader__status_span,
  #qr-reader__camera_selection,
  #qr-reader__dashboard_section_swaplink,
  #qr-reader img { display:none !important; }
  #qr-reader__scan_region { border:none !important; }
  #qr-reader video { border-radius:4px; width:100% !important; }
  #qr-reader { background:#000; }
</style>
@endpush

@push('scripts')
{{-- html5-qrcode v2.3.8 via jsDelivr – correct root-level path, no /minified/ subdir --}}
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════
// COMPLETE SALE FORM JS
// Fixes: auto-focus, duplicate scan prevention, re-focus after add
// ═══════════════════════════════════════════════════════════════

let cartItems = [];
let searchTimeout;

// ── Barcode Scanner State ──────────────────────────────────────
let lastKeyTime       = 0;
let isBarcodeScan     = false;
let barcodeProcessing = false;   // ← prevents duplicate AJAX calls

// ── Auto-focus on page load ────────────────────────────────────
$(function () {
    $('#productSearch').focus();
});

// Re-focus search input whenever user clicks on a neutral area
$(document).on('click', function (e) {
    const $t = $(e.target);

    // Hide dropdown if clicked outside search area
    if (!$t.closest('#productSearch,#searchResults,#searchBtn').length) {
        $('#searchResults').hide();
    }

    // Re-focus if the click was NOT on any interactive element or modal
    if (!$t.closest('input,select,textarea,button,a,.modal,label').length) {
        $('#productSearch').focus();
    }
});

// ── Hardware Barcode Scanner Detection ────────────────────────
// Scanners send all characters < 30ms apart, then fire Enter.
$('#productSearch')
    .on('keydown', function (e) {
        const now      = Date.now();
        const timeDiff = now - lastKeyTime;
        lastKeyTime    = now;

        // Tighten threshold to 30ms (real scanners are < 20ms per char)
        if (timeDiff < 30) isBarcodeScan = true;

        if (e.key === 'Enter') {
            e.preventDefault();
            const q = $(this).val().trim();
            if (!q) return;

            if (isBarcodeScan) {
                searchAndAutoAdd(q);
            } else {
                searchProducts(q);
            }
            isBarcodeScan = false;
        }
    })
    .on('input', function () {
        // If typing slowly, cancel barcode mode
        if (Date.now() - lastKeyTime > 100) isBarcodeScan = false;

        clearTimeout(searchTimeout);
        const q = $(this).val().trim();
        if (q.length < 2) { $('#searchResults').hide(); return; }

        searchTimeout = setTimeout(() => {
            if (!isBarcodeScan) searchProducts(q);
        }, 300);
    });

$('#searchBtn').on('click', () => {
    isBarcodeScan = false;
    searchProducts($('#productSearch').val().trim());
});

// ── Normal Search → Show Dropdown ─────────────────────────────
function searchProducts(q) {
    if (!q) return;
    $.getJSON('{{ route("admin.products.search") }}', { q }, function (products) {
        const $res = $('#searchResults').empty().show();
        if (!products.length) {
            $res.append('<a class="list-group-item disabled small">No products found for "' + q + '"</a>');
        } else {
            products.forEach(p => {
                const jsonStr = JSON.stringify(p).replace(/'/g, '&apos;');
                $res.append(`
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center small"
                       href="#" data-pjson='${jsonStr}' onclick="addToCart(event, this)">
                        <span><strong>${p.name}</strong> <span class="text-muted">${p.sku || ''}</span></span>
                        <span class="text-end">
                            <span class="badge bg-light text-dark me-1">Stock: ${p.stock}</span>
                            <span class="fw-600">₹${parseFloat(p.selling_price).toFixed(2)}</span>
                        </span>
                    </a>`);
            });
        }
    });
}

// ── Barcode Auto-Add (with duplicate lock) ─────────────────────
function searchAndAutoAdd(barcode) {
    if (barcodeProcessing) return;          // ← block duplicate calls
    barcodeProcessing = true;

    $.getJSON('{{ route("admin.products.search") }}', { q: barcode }, function (products) {
        if (products.length === 1) {
            addProductToCart(products[0]);
            showBarcodeMsg('✓ Added: ' + products[0].name, 'success');
            $('#productSearch').val('');
            $('#searchResults').hide();
        } else if (products.length > 1) {
            searchProducts(barcode);
            showBarcodeMsg('Multiple matches found – please select below', 'warning');
        } else {
            showBarcodeMsg('✗ No product found for barcode: ' + barcode, 'danger');
            $('#productSearch').select();
        }
    }).always(function () {
        // Release lock after 1 second cooldown to prevent re-scan duplicates
        setTimeout(() => { barcodeProcessing = false; }, 1000);
    });
}

function showBarcodeMsg(msg, type) {
    $('#barcodeAlert')
        .removeClass('alert-success alert-warning alert-danger')
        .addClass('alert-' + type);
    $('#barcodeMsg').text(msg);
    $('#barcodeAlert').fadeIn(150);
    setTimeout(() => $('#barcodeAlert').fadeOut(500), 3000);
}

// ── Add to Cart (from dropdown click) ─────────────────────────
function addToCart(e, el) {
    e.preventDefault();
    const p = JSON.parse(el.getAttribute('data-pjson'));
    addProductToCart(p);
    $('#searchResults').hide();
    $('#productSearch').val('');
}

// ── Shared: Add Product to Cart ────────────────────────────────
function addProductToCart(p) {
    const existing = cartItems.find(i => i.product_id == p.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cartItems.push({
            product_id: p.id,
            name:       p.name,
            quantity:   1,
            unit_price: parseFloat(p.selling_price),
            cost_price: parseFloat(p.purchase_price),
            discount:   0
        });
    }
    renderCart();

    // Re-focus search input after every add so next scan works immediately
    setTimeout(() => $('#productSearch').focus(), 50);
}

function removeFromCart(idx) {
    cartItems.splice(idx, 1);
    renderCart();
    setTimeout(() => $('#productSearch').focus(), 50);
}

// ── Render Cart ────────────────────────────────────────────────
function renderCart() {
    const $body = $('#cartBody').empty();
    if (!cartItems.length) {
        $body.append('<tr id="emptyRow"><td colspan="6" class="text-center text-muted py-3 small">No items added yet</td></tr>');
        $('#submitBtn').prop('disabled', true);
    } else {
        cartItems.forEach((item, idx) => {
            const lineTotal = (item.unit_price * item.quantity - item.discount).toFixed(2);
            $body.append(`
                <tr>
                    <td class="small">${item.name}
                        <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${idx}][cost_price]"   value="${item.cost_price}">
                    </td>
                    <td><input type="number" name="items[${idx}][quantity]"   class="form-control form-control-sm cart-qty"   data-idx="${idx}" value="${item.quantity}"   min="0.01" step="0.01" style="width:75px"></td>
                    <td><input type="number" name="items[${idx}][unit_price]" class="form-control form-control-sm cart-price" data-idx="${idx}" value="${item.unit_price}" min="0"    step="0.01" style="width:100px"></td>
                    <td><input type="number" name="items[${idx}][discount]"   class="form-control form-control-sm cart-disc"  data-idx="${idx}" value="${item.discount}"   min="0"    step="0.01" style="width:85px"></td>
                    <td class="fw-500 cart-line-total">₹${lineTotal}</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${idx})"><i class="bi bi-trash"></i></button></td>
                </tr>`);
        });
        $('#submitBtn').prop('disabled', false);
    }
    recalcTotals();
}

// ── Live update on qty / price / discount change ───────────────
$(document).on('input', '.cart-qty,.cart-price,.cart-disc', function () {
    const idx = parseInt($(this).data('idx'));
    if ($(this).hasClass('cart-qty'))   cartItems[idx].quantity   = parseFloat($(this).val()) || 0;
    if ($(this).hasClass('cart-price')) cartItems[idx].unit_price = parseFloat($(this).val()) || 0;
    if ($(this).hasClass('cart-disc'))  cartItems[idx].discount   = parseFloat($(this).val()) || 0;
    const lt = (cartItems[idx].unit_price * cartItems[idx].quantity - cartItems[idx].discount).toFixed(2);
    $(this).closest('tr').find('.cart-line-total').text('₹' + lt);
    recalcTotals();
});

// Re-focus search when a cart input loses focus (tab/click away)
$(document).on('blur', '.cart-qty,.cart-price,.cart-disc', function () {
    // Small delay so clicks on other inputs still work
    setTimeout(() => {
        if (!$('input:focus,select:focus,textarea:focus').length) {
            $('#productSearch').focus();
        }
    }, 150);
});

// ── Recalculate Totals ─────────────────────────────────────────
function recalcTotals() {
    const subtotal = cartItems.reduce((s, i) => s + (i.unit_price * i.quantity - i.discount), 0);
    const discount = parseFloat($('#discountInput').val())    || 0;
    const tax      = parseFloat($('#taxInput').val())         || 0;
    const total    = subtotal - discount + tax;
    const paid     = parseFloat($('#amountPaidInput').val())  || 0;
    const balance  = Math.max(0, total - paid);
    $('#showSubtotal').text('₹' + subtotal.toFixed(2));
    $('#showDiscount').text('- ₹' + discount.toFixed(2));
    $('#showTax').text('+  ₹' + tax.toFixed(2));
    $('#showTotal').text('₹' + total.toFixed(2));
    $('#showBalance').text('₹' + balance.toFixed(2));
}

$('#discountInput,#taxInput,#amountPaidInput').on('input', recalcTotals);

// ── Camera Barcode Scanner (Secondary) ────────────────────────
const camModal    = document.getElementById('cameraScanModal');
let html5QrCode   = null;
let lastScanTime  = 0;
const scanCooldown = 3000;

$('#cameraScanBtn').on('click', function () {
    bootstrap.Modal.getOrCreateInstance(camModal).show();
});

camModal.addEventListener('shown.bs.modal', function () {
    startCameraScanner();
});

camModal.addEventListener('hide.bs.modal', function () {
    stopCameraScanner();
    // Re-focus search after modal closes
    setTimeout(() => $('#productSearch').focus(), 300);
});

function startCameraScanner() {
    $('#camStatus').html('<i class="bi bi-hourglass-split me-1"></i>Starting camera…');
    $('#camResult').hide();

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode('qr-reader');
    }

    Html5Qrcode.getCameras().then(devices => {
        if (!devices || !devices.length) {
            $('#camStatus').html('<i class="bi bi-exclamation-triangle me-1 text-warning"></i>No camera found');
            return;
        }

        let camId   = devices[0].id;
        const backCam = devices.find(d =>
            d.label.toLowerCase().includes('back') ||
            d.label.toLowerCase().includes('rear') ||
            d.label.toLowerCase().includes('environment')
        );
        if (backCam) camId = backCam.id;

        html5QrCode.start(
            camId,
            {
                fps: 15,
                qrbox: { width: 250, height: 150 },
                aspectRatio: 1.6,
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.QR_CODE,
                ]
            },
            onBarcodeDetected,
            () => {}   // silent per-frame errors
        ).then(() => {
            $('#camStatus').html('<i class="bi bi-camera-video me-1 text-success"></i>Camera active – point at barcode');
        }).catch(err => {
            $('#camStatus').html('<i class="bi bi-exclamation-triangle me-1 text-danger"></i>Camera error: ' + err);
        });

    }).catch(() => {
        $('#camStatus').html('<i class="bi bi-exclamation-triangle me-1 text-danger"></i>Camera access denied.');
    });
}

function stopCameraScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().catch(() => {});
    }
}

function onBarcodeDetected(decodedText) {
    const now = Date.now();
    if (now - lastScanTime < scanCooldown) return;   // cooldown between scans
    lastScanTime = now;

    $('#camResult').text(decodedText).show();
    $('#camStatus').html('<i class="bi bi-check-circle me-1 text-success"></i>Detected: ' + decodedText);

    $.getJSON('{{ route("admin.products.search") }}', { q: decodedText }, function (products) {
        if (products.length === 1) {
            addProductToCart(products[0]);
            showBarcodeMsg('✓ Camera added: ' + products[0].name, 'success');
            setTimeout(() => bootstrap.Modal.getInstance(camModal).hide(), 800);
        } else if (products.length > 1) {
            bootstrap.Modal.getInstance(camModal).hide();
            searchProducts(decodedText);
            showBarcodeMsg('Multiple matches – please select below', 'warning');
        } else {
            $('#camStatus').html('<i class="bi bi-x-circle me-1 text-danger"></i>Not found: ' + decodedText + ' – try again');
            lastScanTime = 0;   // reset so they can retry immediately
        }
    });
}

// ── Camera Modal: Manual Input Fallback ───────────────────────
$('#camManualAdd').on('click', function () {
    const val = $('#camManualInput').val().trim();
    if (!val) return;
    onBarcodeDetected(val);
    $('#camManualInput').val('');
});

$('#camManualInput').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); $('#camManualAdd').trigger('click'); }
});
</script>
@endpush

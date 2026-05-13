@extends('layouts.admin')
@section('title', 'New Purchase')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-600">New Purchase Order</h5>
</div>
<form method="POST" action="{{ route('admin.purchases.store') }}" id="poForm">
@csrf
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Add Products</div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="poSearch" class="form-control" placeholder="Search product name or barcode…" autocomplete="off">
                </div>
                <div id="poResults" class="list-group mb-3" style="display:none;max-height:200px;overflow-y:auto;"></div>
                <div class="table-responsive">
                <table class="table table-bordered" id="poTable">
                    <thead class="table-light"><tr><th>Product</th><th style="width:90px">Qty</th><th style="width:130px">Cost Price (₹)</th><th style="width:110px">Total</th><th style="width:40px"></th></tr></thead>
                    <tbody id="poBody"><tr id="emptyPoRow"><td colspan="5" class="text-center text-muted py-3 small">No items added</td></tr></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">PO Details</div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Branch <span class="text-danger">*</span></label><select name="branch_id" class="form-select" required>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Date <span class="text-danger">*</span></label><input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select"><option value="received">Received</option><option value="pending">Pending</option></select>
                </div>
                <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body"><div class="d-flex justify-content-between fw-700 fs-5"><span>Total</span><span class="text-primary" id="poTotal">₹0.00</span></div></div>
        </div>
        <button type="submit" class="btn btn-success w-100" id="poSubmit" disabled><i class="bi bi-check-circle me-1"></i>Save Purchase</button>
    </div>
</div>
</form>
@endsection
@push('scripts')
<script>
let poItems = []; let poTimeout;
$('#poSearch').on('input', function() {
    clearTimeout(poTimeout);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#poResults').hide(); return; }
    poTimeout = setTimeout(() => {
        $.getJSON('{{ route("admin.products.search") }}', { q }, products => {
            const $r = $('#poResults').empty().show();
            if (!products.length) { $r.append('<a class="list-group-item disabled small">No products found</a>'); return; }
            products.forEach(p => $r.append(`<a class="list-group-item list-group-item-action small d-flex justify-content-between" href="#" data-p='${JSON.stringify(p)}' onclick="addPoItem(event,this)"><span>${p.name} <span class="text-muted">${p.sku||''}</span></span><span>CP: ₹${parseFloat(p.purchase_price).toFixed(2)}</span></a>`));
        });
    }, 300);
});
$(document).on('click', e => { if (!$(e.target).closest('#poSearch,#poResults').length) $('#poResults').hide(); });
function addPoItem(e, el) {
    e.preventDefault();
    const p = JSON.parse($(el).data('p'));
    const ex = poItems.find(i => i.product_id == p.id);
    if (ex) { ex.quantity += 1; } else poItems.push({ product_id: p.id, name: p.name, quantity: 1, cost_price: parseFloat(p.purchase_price) });
    $('#poResults').hide(); $('#poSearch').val('');
    renderPo();
}
function removePoItem(idx) { poItems.splice(idx, 1); renderPo(); }
function renderPo() {
    const $b = $('#poBody').empty();
    if (!poItems.length) { $b.append('<tr id="emptyPoRow"><td colspan="5" class="text-center text-muted py-3 small">No items added</td></tr>'); $('#poSubmit').prop('disabled', true); return; }
    $('#poSubmit').prop('disabled', false);
    let tot = 0;
    poItems.forEach((item, idx) => {
        const lt = (item.cost_price * item.quantity).toFixed(2); tot += parseFloat(lt);
        $b.append(`<tr><td class="small">${item.name}<input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}"></td>
            <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm po-qty" data-idx="${idx}" value="${item.quantity}" min="0.01" step="0.01"></td>
            <td><input type="number" name="items[${idx}][cost_price]" class="form-control form-control-sm po-cp" data-idx="${idx}" value="${item.cost_price}" min="0" step="0.01"></td>
            <td class="fw-500 po-lt">₹${lt}</td>
            <td><button type="button" class="btn btn-xs btn-outline-danger" onclick="removePoItem(${idx})"><i class="bi bi-trash"></i></button></td>
        </tr>`);
    });
    $('#poTotal').text('₹' + tot.toFixed(2));
}
$(document).on('input', '.po-qty,.po-cp', function() {
    const idx = $(this).data('idx');
    if ($(this).hasClass('po-qty')) poItems[idx].quantity = parseFloat($(this).val()) || 0;
    if ($(this).hasClass('po-cp')) poItems[idx].cost_price = parseFloat($(this).val()) || 0;
    const lt = (poItems[idx].cost_price * poItems[idx].quantity).toFixed(2);
    $(this).closest('tr').find('.po-lt').text('₹' + lt);
    const tot = poItems.reduce((s, i) => s + i.cost_price * i.quantity, 0);
    $('#poTotal').text('₹' + tot.toFixed(2));
});
</script>
@endpush

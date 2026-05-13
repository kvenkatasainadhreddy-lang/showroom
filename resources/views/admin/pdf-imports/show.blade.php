@extends('layouts.admin')
@section('title', 'Review Import: ' . $pdfImport->filename)
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="{{ route('admin.pdf-imports.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-600"><i class="bi bi-file-pdf me-2 text-danger"></i>{{ $pdfImport->filename }}</h5>
  {!! $pdfImport->status_badge !!}
</div>

@if($pdfImport->notes)
<div class="alert {{ str_contains($pdfImport->notes, 'error') || str_contains($pdfImport->notes, 'image-based') ? 'alert-warning' : 'alert-info' }} d-flex align-items-start gap-2 mb-3">
  <i class="bi bi-info-circle-fill mt-1"></i>
  <div>{{ $pdfImport->notes }}</div>
</div>
@endif

@if($pdfImport->status === 'confirmed')
<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>This import has already been confirmed and stock was updated.</div>
@elseif(count($pdfImport->parsed_items ?? []) > 0)

<form method="POST" action="{{ route('admin.pdf-imports.confirm', $pdfImport) }}" id="confirmForm">
@csrf
<div class="card mb-3">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span><i class="bi bi-table me-1"></i>Extracted Items — <strong>{{ count($pdfImport->parsed_items) }}</strong> rows</span>
    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Confirm and add to stock?')">
      <i class="bi bi-check-circle me-1"></i>Confirm & Add to Stock
    </button>
  </div>
  <div class="table-responsive">
  <table class="table table-sm align-middle mb-0">
    <thead class="table-light"><tr>
      <th class="px-3">#</th>
      <th>Product Name</th>
      <th style="width:120px">SKU</th>
      <th style="width:80px">Qty</th>
      <th style="width:110px">Unit Price (₹)</th>
      <th style="width:180px">Category</th>
      <th style="width:160px">Branch</th>
    </tr></thead>
    <tbody>
    @foreach($pdfImport->parsed_items as $i => $item)
    <tr>
      <td class="px-3 text-muted small">{{ $i + 1 }}</td>
      <td><input type="text" name="items[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $item['name'] }}" required></td>
      <td><input type="text" name="items[{{ $i }}][sku]"  class="form-control form-control-sm" value="{{ $item['sku'] ?? '' }}" placeholder="Auto"></td>
      <td><input type="number" name="items[{{ $i }}][qty]" class="form-control form-control-sm" value="{{ $item['qty'] }}" min="0.01" step="0.01" required></td>
      <td><input type="number" name="items[{{ $i }}][price]" class="form-control form-control-sm" value="{{ $item['price'] }}" min="0" step="0.01" required></td>
      <td>
        <select name="items[{{ $i }}][category_id]" class="form-select form-select-sm">
          <option value="">— Category —</option>
          @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
        </select>
      </td>
      <td>
        <select name="items[{{ $i }}][branch_id]" class="form-select form-select-sm">
          <option value="">— Branch —</option>
          @foreach(\App\Models\Branch::where('is_active',true)->get() as $b)
          <option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
        </select>
      </td>
    </tr>
    @endforeach
    </tbody>
  </table>
  </div>
  <div class="card-footer d-flex justify-content-between align-items-center">
    <span class="small text-muted">Review all rows before confirming. You can edit names, quantities, and prices.</span>
    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Confirm and add to stock?')">
      <i class="bi bi-check-circle me-1"></i>Confirm & Add to Stock
    </button>
  </div>
</div>
</form>

@else
<div class="card"><div class="card-body text-center py-5">
  <i class="bi bi-file-x fs-1 text-muted mb-3 d-block"></i>
  <h6>No items could be extracted from this PDF.</h6>
  <p class="text-muted small">The PDF may be image-based or use a non-standard layout. Please add inventory manually.</p>
  <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Add Purchase Manually
  </a>
</div></div>
@endif
@endsection

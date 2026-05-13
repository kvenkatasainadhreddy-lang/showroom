@extends('layouts.admin')
@section('title','Upload Purchase PDF')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="{{ route('admin.pdf-imports.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-600">Upload Purchase PDF</h5>
</div>

<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.pdf-imports.store') }}" enctype="multipart/form-data">
@csrf
<div class="mb-4">
  <label class="form-label fw-600">PDF File <span class="text-danger">*</span></label>
  <input type="file" name="pdf" class="form-control" accept=".pdf" required>
  <div class="form-text">Max 10MB. Must be a text-based PDF (not scanned image).</div>
</div>
<div class="mb-3">
  <label class="form-label">Supplier <span class="text-muted small">(optional)</span></label>
  <select name="supplier_id" class="form-select">
    <option value="">— Select Supplier —</option>
    @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
  </select>
</div>
<div class="mb-3">
  <label class="form-label">Notes</label>
  <textarea name="notes" class="form-control" rows="2" placeholder="e.g. March 2026 Honda consignment"></textarea>
</div>
<button type="submit" class="btn btn-success w-100">
  <i class="bi bi-upload me-1"></i>Upload & Parse PDF
</button>
</form>
</div></div>
</div>

<div class="col-lg-6">
<div class="card bg-light border-0"><div class="card-body">
  <h6 class="fw-600 mb-3"><i class="bi bi-lightbulb text-warning me-1"></i>How PDF Import Works</h6>
  <ol class="small mb-0">
    <li class="mb-2"><strong>Upload</strong> a purchase invoice PDF from your supplier/distributor</li>
    <li class="mb-2">System <strong>extracts text</strong> and looks for rows with:<br>
      <code>Product Name · Qty · Price</code></li>
    <li class="mb-2"><strong>Review</strong> the extracted items — you can edit name, qty, price, and category</li>
    <li class="mb-2"><strong>Confirm</strong> → items are added to inventory automatically with stock movement logs</li>
  </ol>
  <div class="alert alert-warning py-2 px-3 small mt-3 mb-0">
    <i class="bi bi-exclamation-triangle me-1"></i>
    <strong>Scanned PDFs won't work</strong> — only text-based (digitally generated) PDFs can be parsed.
  </div>
</div></div>
</div>
</div>
@endsection

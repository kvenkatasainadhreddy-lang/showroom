@extends('layouts.admin')
@section('title', 'Vehicle Stock Import')

@push('styles')
<style>
/* ── Drop Zone ───────────────────────────────────────────── */
.drop-zone {
  border: 2.5px dashed #4361ee;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(67,97,238,0.04), rgba(114,9,183,0.04));
  transition: all .25s ease;
  cursor: pointer;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
}
.drop-zone.dragover {
  border-color: #06d6a0;
  background: rgba(6,214,160,0.08);
  transform: scale(1.01);
}
.drop-zone .drop-icon { font-size: 3rem; color: #4361ee; transition: transform .2s; }
.drop-zone.dragover .drop-icon { transform: translateY(-6px); }
.drop-zone .file-name { font-size: .85rem; color: #6c757d; max-width: 280px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }

/* ── Step badges ─────────────────────────────────────────── */
.step-badge {
  width: 32px; height: 32px; border-radius: 50%;
  display: inline-flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: .8rem; flex-shrink: 0;
}

/* ── Preview table ───────────────────────────────────────── */
#previewSection { display: none; }
.preview-table thead th { background: #1e3a5f; color: #fff; font-size: .78rem; white-space: nowrap; }
.preview-table tbody tr:hover { background: rgba(67,97,238,.04); }
.row-ok   { border-left: 3px solid #06d6a0; }
.row-err  { border-left: 3px solid #ef233c; background: rgba(239,35,60,.04); }
.row-new  .badge-status { background: #06d6a0; }
.row-dup  .badge-status { background: #f8961e; }

/* ── Progress bar ────────────────────────────────────────── */
#progressWrap { display: none; }
.progress-bar-animated { animation: progress-bar-stripes 1s linear infinite; }

/* ── History card ────────────────────────────────────────── */
.history-item { border-left: 3px solid #4361ee; } 

/* ── Stat pill ───────────────────────────────────────────── */
.stat-pill {
  border-radius: 10px; padding: 2px 10px; font-size: .75rem; font-weight: 600;
}
</style>
@endpush

@section('content')

{{-- ── Page Header ── --}}
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h5 class="mb-0 fw-700">
      <i class="bi bi-cloud-upload me-2 text-primary"></i>Vehicle Stock Import
    </h5>
    <div class="small text-muted">Upload an Excel / CSV sheet to bulk-import chassis numbers into the system</div>
  </div>
  <a href="{{ route('admin.vehicle-import.template') }}" class="btn btn-outline-success btn-sm">
    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Download Sample Template
  </a>
</div>

{{-- ── Flash Messages ── --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif
@if(session('warning'))
  <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>{{ session('warning') }}</div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif
@if(session('import_errors'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong><i class="bi bi-bug me-1"></i>Row Errors:</strong>
    <ul class="mb-0 mt-1">
      @foreach(session('import_errors') as $err)
        <li>Row #{{ $err['row'] }} [{{ $err['chassis'] }}] — {{ $err['message'] }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="row g-4">

  {{-- ── LEFT: Upload Form ── --}}
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">

        {{-- Steps indicator --}}
        <div class="d-flex align-items-start gap-3 mb-4">
          <div class="text-center" style="min-width:60px">
            <div class="step-badge bg-primary text-white">1</div>
            <div class="small mt-1 fw-600 text-primary">Upload</div>
          </div>
          <div class="flex-fill border-top mt-3 mx-1" style="border-color:#dee2e6!important"></div>
          <div class="text-center" style="min-width:60px">
            <div class="step-badge bg-light text-muted border" id="step2badge">2</div>
            <div class="small mt-1 text-muted" id="step2label">Preview</div>
          </div>
          <div class="flex-fill border-top mt-3 mx-1"></div>
          <div class="text-center" style="min-width:60px">
            <div class="step-badge bg-light text-muted border" id="step3badge">3</div>
            <div class="small mt-1 text-muted" id="step3label">Confirm</div>
          </div>
        </div>

        {{-- ── Step 1: File + Options ── --}}
        <div id="step1">
          <form id="previewForm" enctype="multipart/form-data">
            @csrf

            {{-- Drag & drop zone --}}
            <div class="drop-zone mb-3" id="dropZone" onclick="document.getElementById('fileInput').click()">
              <i class="bi bi-cloud-arrow-up drop-icon"></i>
              <div class="fw-600 text-dark">Drag & drop your Excel / CSV file here</div>
              <div class="small text-muted">or click to browse</div>
              <div class="text-muted file-name" id="fileLabel">Supported: .xlsx, .xls, .csv &nbsp;·&nbsp; Max 5 MB</div>
              <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" class="d-none">
            </div>

            <div class="row g-3 mt-1">
              {{-- Brand --}}
              <div class="col-md-4">
                <label class="form-label fw-600 small">
                  Brand <span class="text-danger">*</span>
                </label>
                <select name="brand_id" class="form-select" required>
                  <option value="">-- Select Brand --</option>
                  @foreach($brands as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                  @endforeach
                </select>
                <div class="form-text">Models will be linked to this brand</div>
              </div>

              {{-- Branch --}}
              <div class="col-md-4">
                <label class="form-label fw-600 small">Branch / Location</label>
                <select name="branch_id" class="form-select">
                  <option value="">-- No branch --</option>
                  @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Received Date --}}
              <div class="col-md-4">
                <label class="form-label fw-600 small">Received Date</label>
                <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}">
              </div>
            </div>

            <div class="d-flex gap-2 mt-4">
              <button type="button" id="previewBtn" class="btn btn-primary px-4" disabled>
                <i class="bi bi-eye me-1"></i>Preview Data
              </button>
              <span class="text-muted small align-self-center">Preview rows before confirming import</span>
            </div>
          </form>
        </div>

        {{-- ── Step 2: Preview Table ── --}}
        <div id="previewSection" class="mt-4">

          <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0 fw-700">
              <i class="bi bi-table me-1 text-primary"></i>Data Preview
            </h6>
            <div class="d-flex gap-2" id="previewStats">
              <span class="stat-pill bg-success-subtle text-success-emphasis" id="countValid">0 valid</span>
              <span class="stat-pill bg-danger-subtle text-danger-emphasis" id="countErrors">0 errors</span>
            </div>
          </div>

          {{-- Legend --}}
          <div class="d-flex gap-3 mb-2 small text-muted flex-wrap">
            <span><span class="badge bg-success me-1">&nbsp;</span>New chassis (will be created)</span>
            <span><span class="badge bg-warning me-1">&nbsp;</span>Existing (will be updated)</span>
            <span><span class="badge bg-danger me-1">&nbsp;</span>Error (will be skipped)</span>
          </div>

          <div class="table-responsive border rounded" style="max-height:340px;overflow-y:auto">
            <table class="table table-sm preview-table mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Chassis No.</th>
                  <th>Engine No.</th>
                  <th>Model</th>
                  <th>Variant</th>
                  <th>Colour</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="previewBody"></tbody>
            </table>
          </div>

          {{-- Error rows --}}
          <div id="errorBlock" class="mt-3" style="display:none">
            <div class="alert alert-danger py-2 mb-0">
              <strong><i class="bi bi-exclamation-triangle me-1"></i>Rows with errors (will be skipped):</strong>
              <ul class="mb-0 mt-1 small" id="errorList"></ul>
            </div>
          </div>

          {{-- Confirm button (real form) --}}
          <form id="importForm" method="POST" action="{{ route('admin.vehicle-import.store') }}" enctype="multipart/form-data" class="mt-3">
            @csrf
            <input type="hidden" name="brand_id"      id="fx_brand_id">
            <input type="hidden" name="branch_id"     id="fx_branch_id">
            <input type="hidden" name="received_date" id="fx_received_date">
            <input type="file"   name="file" id="fx_file" class="d-none">

            <div id="progressWrap" class="mb-3">
              <div class="progress" style="height:6px">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width:100%"></div>
              </div>
              <div class="small text-muted text-center mt-1">Importing — please wait…</div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" id="confirmBtn" class="btn btn-success px-4">
                <i class="bi bi-check2-circle me-1"></i>Confirm & Import
              </button>
              <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Start Over
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

  {{-- ── RIGHT: Info + History ── --}}
  <div class="col-lg-4">

    {{-- Column guide --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-primary text-white py-2 small fw-600">
        <i class="bi bi-info-circle me-1"></i>Expected Column Format
      </div>
      <div class="card-body p-3">
        <table class="table table-sm table-borderless mb-0 small">
          <tbody>
            <tr><td class="fw-600 text-nowrap">S.NO</td>       <td class="text-muted">Row number (ignored)</td></tr>
            <tr><td class="fw-600 text-nowrap">CHASIS NO</td>  <td class="text-muted"><span class="badge bg-danger-subtle text-danger-emphasis">Required</span> Chassis number</td></tr>
            <tr><td class="fw-600 text-nowrap">ENGINE NO</td>  <td class="text-muted">Engine number</td></tr>
            <tr><td class="fw-600 text-nowrap">MODEL</td>      <td class="text-muted"><span class="badge bg-danger-subtle text-danger-emphasis">Required</span> e.g. SP125 OBD2B</td></tr>
            <tr><td class="fw-600 text-nowrap">VARIANT</td>    <td class="text-muted"><span class="badge bg-danger-subtle text-danger-emphasis">Required</span> e.g. SP125 DLX DISK</td></tr>
            <tr><td class="fw-600 text-nowrap">COLOUR</td>     <td class="text-muted">Paint colour</td></tr>
          </tbody>
        </table>
      </div>
      <div class="card-footer py-2">
        <div class="small text-muted">
          <i class="bi bi-lightbulb me-1 text-warning"></i>
          Existing chassis numbers are <strong>updated</strong> (engine/colour). New ones are <strong>created</strong> as Available.
        </div>
      </div>
    </div>

    {{-- Import History --}}
    <div class="card border-0 shadow-sm">
      <div class="card-header py-2 small fw-600 d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-1 text-primary"></i>Recent Imports</span>
        <span class="badge bg-secondary">{{ count($history) }}</span>
      </div>
      <div class="card-body p-0">
        @if(count($history))
          <div class="list-group list-group-flush">
            @foreach($history as $h)
            <div class="list-group-item history-item px-3 py-2">
              <div class="d-flex align-items-center justify-content-between">
                <div class="small fw-600 text-truncate" style="max-width:180px" title="{{ $h['file'] }}">
                  <i class="bi bi-file-earmark-spreadsheet me-1 text-success"></i>{{ $h['file'] }}
                </div>
                <div class="text-muted" style="font-size:10px">{{ $h['at'] }}</div>
              </div>
              <div class="d-flex gap-1 mt-1 flex-wrap">
                <span class="stat-pill bg-success-subtle text-success-emphasis">+{{ $h['imported'] }} new</span>
                @if($h['updated'] ?? 0)
                <span class="stat-pill bg-warning-subtle text-warning-emphasis">{{ $h['updated'] }} updated</span>
                @endif
                @if($h['errors'] ?? 0)
                <span class="stat-pill bg-danger-subtle text-danger-emphasis">{{ $h['errors'] }} err</span>
                @endif
              </div>
              <div class="text-muted mt-1" style="font-size:10px">{{ $h['brand'] }} · {{ $h['by'] }}</div>
            </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-4 text-muted small">
            <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>No imports yet
          </div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── All element refs declared ONCE at top — no getElementById inside callbacks ──
  var el = {
    dropZone:     document.getElementById('dropZone'),
    fileInput:    document.getElementById('fileInput'),
    fileLabel:    document.getElementById('fileLabel'),
    previewBtn:   document.getElementById('previewBtn'),
    previewForm:  document.getElementById('previewForm'),
    previewSec:   document.getElementById('previewSection'),
    previewBody:  document.getElementById('previewBody'),
    errorBlock:   document.getElementById('errorBlock'),
    errorList:    document.getElementById('errorList'),
    countValid:   document.getElementById('countValid'),
    countErrors:  document.getElementById('countErrors'),
    importForm:   document.getElementById('importForm'),
    progressWrap: document.getElementById('progressWrap'),
    confirmBtn:   document.getElementById('confirmBtn'),
    fxBrand:      document.getElementById('fx_brand_id'),
    fxBranch:     document.getElementById('fx_branch_id'),
    fxDate:       document.getElementById('fx_received_date'),
    fxFile:       document.getElementById('fx_file'),
  };

  // Log any missing elements to console for debugging
  var missing = Object.keys(el).filter(function(k) { return !el[k]; });
  if (missing.length) {
    console.warn('Vehicle import: missing elements:', missing.join(', '));
  }

  if (!el.dropZone || !el.fileInput || !el.previewBtn || !el.previewForm) {
    console.error('Core elements missing — import UI disabled');
    return;
  }

  // ── Drag & Drop ──────────────────────────────────────────────
  ['dragenter','dragover'].forEach(function(ev) {
    el.dropZone.addEventListener(ev, function(e) {
      e.preventDefault();
      el.dropZone.classList.add('dragover');
    });
  });
  ['dragleave','drop'].forEach(function(ev) {
    el.dropZone.addEventListener(ev, function(e) {
      e.preventDefault();
      el.dropZone.classList.remove('dragover');
    });
  });
  el.dropZone.addEventListener('drop', function(e) {
    var f = e.dataTransfer.files[0];
    if (f) setFile(f);
  });
  el.fileInput.addEventListener('change', function() {
    if (el.fileInput.files[0]) setFile(el.fileInput.files[0]);
  });

  function setFile(f) {
    if (!/\.(xlsx|xls|csv)$/i.test(f.name)) {
      showToast('Only .xlsx, .xls and .csv files are allowed.', 'danger');
      return;
    }
    if (el.fileLabel) {
      el.fileLabel.textContent = f.name + '  (' + (f.size/1024).toFixed(1) + ' KB)';
      el.fileLabel.style.color = '#198754';
    }
    el.dropZone.style.borderColor = '#198754';
    el.previewBtn.disabled = false;
  }

  function resetBtn() {
    el.previewBtn.disabled = false;
    el.previewBtn.innerHTML = '<i class="bi bi-eye me-1"></i>Preview Data';
  }

  // ── Preview click ────────────────────────────────────────────
  el.previewBtn.addEventListener('click', function() {
    var fd = new FormData(el.previewForm);
    if (!el.fileInput.files[0])  { showToast('Please select a file first.', 'warning'); return; }
    if (!fd.get('brand_id'))     { showToast('Please select a brand.', 'warning'); return; }

    el.previewBtn.disabled = true;
    el.previewBtn.textContent = 'Analysing...';

    fetch('{{ route("admin.vehicle-import.preview") }}', {
      method:  'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body:    fd,
    })
    .then(function(res) {
      return res.text().then(function(text) {
        var parsed;
        try { parsed = JSON.parse(text); } catch(e) {
          showToast('Server error - the response was not JSON. Check Laravel logs.', 'danger');
          resetBtn();
          return null;
        }
        if (!res.ok) {
          var msg = 'Validation error.';
          if (parsed && parsed.errors && parsed.errors.file) msg = parsed.errors.file[0];
          else if (parsed && parsed.message) msg = parsed.message;
          showToast(msg, 'danger');
          resetBtn();
          return null;
        }
        return parsed;
      });
    })
    .then(function(data) {
      if (!data) return;

      renderPreview(data);

      // Copy values into real submit form
      if (el.fxBrand)  el.fxBrand.value  = fd.get('brand_id')      || '';
      if (el.fxBranch) el.fxBranch.value = fd.get('branch_id')     || '';
      if (el.fxDate)   el.fxDate.value   = fd.get('received_date') || '';

      // Try to copy file via DataTransfer
      try {
        var dt = new DataTransfer();
        dt.items.add(el.fileInput.files[0]);
        if (el.fxFile) el.fxFile.files = dt.files;
      } catch(e) { /* DataTransfer not supported in this browser */ }

      stepDone(2);
      resetBtn();
    })
    .catch(function(err) {
      showToast('Fetch failed: ' + err.message, 'danger');
      resetBtn();
    });
  });

  // ── Render Preview (uses closure refs, no getElementById) ────
  function renderPreview(data) {
    var rows = Array.isArray(data.rows)   ? data.rows   : [];
    var errs = Array.isArray(data.errors) ? data.errors : [];

    // Render rows into table
    if (el.previewBody) {
      el.previewBody.innerHTML = '';
      rows.forEach(function(r) {
        var tr = document.createElement('tr');
        tr.className = 'row-ok';
        tr.innerHTML =
          '<td class="text-muted small">' + (r.row||'') + '</td>' +
          '<td class="fw-600 small"><code>' + (r.chassis||'') + '</code></td>' +
          '<td class="small text-muted">' + (r.engine||'&mdash;') + '</td>' +
          '<td class="small">' + (r.model||'') + '</td>' +
          '<td class="small">' + (r.variant||'') + '</td>' +
          '<td class="small">' + (r.color||'&mdash;') + '</td>' +
          '<td><span class="badge bg-success" style="font-size:10px">New</span></td>';
        el.previewBody.appendChild(tr);
      });
    }

    // Render errors
    if (el.errorList) {
      el.errorList.innerHTML = '';
      errs.forEach(function(e) {
        var li = document.createElement('li');
        li.textContent = 'Row #' + e.row + ' [' + e.chassis + '] - ' + e.message;
        el.errorList.appendChild(li);
      });
    }
    if (el.errorBlock) el.errorBlock.style.display = errs.length ? 'block' : 'none';

    // Update counts
    if (el.countValid)  el.countValid.textContent  = rows.length + ' valid';
    if (el.countErrors) el.countErrors.textContent = errs.length + ' errors';

    // Show preview section
    if (el.previewSec) el.previewSec.style.display = 'block';
  }

  // ── Confirm form submit ──────────────────────────────────────
  if (el.importForm) {
    el.importForm.addEventListener('submit', function() {
      if (el.progressWrap) el.progressWrap.style.display = 'block';
      if (el.confirmBtn) {
        el.confirmBtn.disabled = true;
        el.confirmBtn.textContent = 'Importing...';
      }
      stepDone(3);
    });
  }

  // ── Reset ────────────────────────────────────────────────────
  window.resetForm = function() {
    el.fileInput.value = '';
    if (el.fileLabel) { el.fileLabel.textContent = 'Supported: .xlsx, .xls, .csv · Max 5 MB'; el.fileLabel.style.color = ''; }
    el.dropZone.style.borderColor = '';
    el.previewBtn.disabled = true;
    el.previewBtn.innerHTML = '<i class="bi bi-eye me-1"></i>Preview Data';
    if (el.previewSec)  el.previewSec.style.display = 'none';
    if (el.previewBody) el.previewBody.innerHTML = '';
    resetSteps();
  };

  // ── Step helpers ─────────────────────────────────────────────
  function stepDone(n) {
    var b = document.getElementById('step' + n + 'badge');
    var l = document.getElementById('step' + n + 'label');
    if (b) { b.className = 'step-badge bg-primary text-white'; b.textContent = n; }
    if (l)   l.className = 'small mt-1 fw-600 text-primary';
  }
  function resetSteps() {
    [2,3].forEach(function(n) {
      var b = document.getElementById('step' + n + 'badge');
      var l = document.getElementById('step' + n + 'label');
      if (b) b.className = 'step-badge bg-light text-muted border';
      if (l) l.className = 'small mt-1 text-muted';
    });
  }

}); // end DOMContentLoaded

function showToast(msg, type) {
  type = type || 'info';
  var t = document.createElement('div');
  t.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed shadow-sm';
  t.style.cssText = 'bottom:20px;right:20px;z-index:9999;min-width:280px;max-width:400px';
  t.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
  document.body.appendChild(t);
  setTimeout(function() { if (t.parentNode) t.remove(); }, 5000);
}
</script>
@endpush
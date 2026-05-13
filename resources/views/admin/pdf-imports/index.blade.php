@extends('layouts.admin')
@section('title','PDF Imports – Inventory Auto-Import')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-600"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>PDF Inventory Imports</h5>
  <a href="{{ route('admin.pdf-imports.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Upload PDF</a>
</div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead><tr>
    <th class="px-3">Filename</th><th>Supplier</th><th class="text-center">Items</th>
    <th class="text-center">Status</th><th>Uploaded By</th><th>Date</th><th></th>
  </tr></thead>
  <tbody>
  @forelse($imports as $imp)
  <tr>
    <td class="px-3 fw-500"><i class="bi bi-file-pdf me-1 text-danger"></i>{{ $imp->filename }}</td>
    <td class="small">{{ $imp->supplier?->name ?? '—' }}</td>
    <td class="text-center">{{ $imp->item_count }}</td>
    <td class="text-center">{!! $imp->status_badge !!}</td>
    <td class="small">{{ $imp->creator?->name ?? '—' }}</td>
    <td class="small text-muted">{{ $imp->created_at->format('d M Y, H:i') }}</td>
    <td>
      <a href="{{ route('admin.pdf-imports.show', $imp) }}" class="btn btn-xs btn-primary">
        {{ $imp->status === 'confirmed' ? 'View' : 'Review' }}
      </a>
    </td>
  </tr>
  @empty<tr><td colspan="7" class="text-center py-4 text-muted">No imports yet. <a href="{{ route('admin.pdf-imports.create') }}">Upload a PDF</a></td></tr>@endforelse
  </tbody>
</table></div>
<div class="px-3 py-2">{{ $imports->links() }}</div>
</div>
@endsection

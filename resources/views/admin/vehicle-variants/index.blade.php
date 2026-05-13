@extends('layouts.admin')
@section('title','Vehicle Variants')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-600"><i class="bi bi-palette me-2 text-primary"></i>Vehicle Variants</h5>
  <a href="{{ route('admin.vehicle-variants.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Variant</a>
</div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead><tr>
    <th class="px-3">Variant Name</th><th>Model / Brand</th><th>Color</th>
    <th class="text-center">Total Stock</th><th class="text-center">Available</th><th></th>
  </tr></thead>
  <tbody>
  @forelse($variants as $v)
  <tr>
    <td class="px-3 fw-500">{{ $v->name }}</td>
    <td class="small">{{ $v->vehicleModel?->brand?->name }} {{ $v->vehicleModel?->name }}</td>
    <td class="small">{{ $v->color ?? '—' }}</td>
    <td class="text-center">{{ $v->stock_count }}</td>
    <td class="text-center"><span class="badge {{ $v->available_count > 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }}">{{ $v->available_count }}</span></td>
    <td>
      <a href="{{ route('admin.vehicle-variants.edit', $v) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>
      <form class="d-inline" method="POST" action="{{ route('admin.vehicle-variants.destroy', $v) }}" onsubmit="return confirm('Delete variant?')">@csrf @method('DELETE')
        <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
      </form>
    </td>
  </tr>
  @empty<tr><td colspan="6" class="text-center py-4 text-muted">No variants yet</td></tr>@endforelse
  </tbody>
</table></div>
<div class="px-3 py-2">{{ $variants->links() }}</div>
</div>
@endsection

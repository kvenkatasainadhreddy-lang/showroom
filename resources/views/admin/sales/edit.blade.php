@extends('layouts.admin')
@section('title','Edit Sale ' . $sale->invoice_no)
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Edit Sale: {{ $sale->invoice_no }}</h5></div>
<div class="card" style="max-width:550px"><div class="card-body">
    <form method="POST" action="{{ route('admin.sales.update', $sale) }}">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Amount Paid (₹)</label><input type="number" name="amount_paid" class="form-control" value="{{ old('amount_paid', $sale->amount_paid) }}" step="0.01" min="0"></div>
    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $sale->notes) }}</textarea></div>
    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Update Sale</button>
    <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-light ms-2">Cancel</a>
    </form>
</div></div>
@endsection

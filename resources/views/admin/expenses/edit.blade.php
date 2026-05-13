@extends('layouts.admin')
@section('title','Edit Expense')
@section('content')
<div class="d-flex align-items-center gap-2 mb-3"><a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-600">Edit Expense</h5></div>
<div class="card" style="max-width:550px"><div class="card-body">
    <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">@csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" value="{{ old('title', $expense->title) }}" required></div>
    <div class="row g-3 mb-3">
        <div class="col-sm-6"><label class="form-label">Amount (₹)</label><input type="number" name="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0"></div>
        <div class="col-sm-6"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
            <option value="">— Select —</option>
            @foreach(['Rent','Salaries','Utilities','Fuel','Maintenance','Marketing','Office Supplies','Other'] as $cat)
            <option {{ $expense->category==$cat?'selected':'' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3"><label class="form-label">Branch</label><select name="branch_id" class="form-select"><option value="">All Branches</option>@foreach($branches as $b)<option value="{{ $b->id }}" {{ $expense->branch_id==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes) }}</textarea></div>
    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-light ms-2">Cancel</a>
    </form>
</div></div>
@endsection

@extends('layouts.admin')
@section('title','Expenses')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-600">Expenses</h5>
    @can('expenses.create')<a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Expense</a>@endcan
</div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2 align-items-end">
    <div class="col-sm-4 col-lg-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Title…" value="{{ request('search') }}"></div>
    <div class="col-sm-3 col-lg-2"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}"></div>
    <div class="col-sm-3 col-lg-2"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}"></div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button> <a href="{{ route('admin.expenses.index') }}" class="btn btn-light btn-sm">Reset</a></div>
</form></div></div>
<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Date</th><th>Title</th><th>Category</th><th>Branch</th><th class="text-end">Amount</th><th>Added By</th><th></th></tr></thead>
        <tbody>
        @forelse($expenses as $exp)
        <tr>
            <td class="px-3 small">{{ $exp->expense_date->format('d M Y') }}</td>
            <td class="fw-500">{{ $exp->title }}</td>
            <td class="small text-muted">{{ $exp->category ?? '—' }}</td>
            <td class="small">{{ $exp->branch->name ?? 'All' }}</td>
            <td class="text-end fw-500 text-danger">₹{{ number_format($exp->amount,2) }}</td>
            <td class="small">{{ $exp->creator->name ?? '—' }}</td>
            <td>
                @can('expenses.edit')<a href="{{ route('admin.expenses.edit', $exp) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>@endcan
                @can('expenses.delete')<form class="d-inline" method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endcan
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-muted">No expenses</td></tr>
        @endforelse
        </tbody>
        <tfoot class="table-light fw-600"><tr><td colspan="4" class="px-3 text-end">Total Filtered</td><td class="text-end text-danger">₹{{ number_format($totalFiltered,2) }}</td><td colspan="2"></td></tr></tfoot>
    </table>
    </div>
    <div class="card-footer">{{ $expenses->links() }}</div>
</div>
@endsection

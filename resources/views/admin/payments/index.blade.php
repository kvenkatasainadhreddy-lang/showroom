@extends('layouts.admin')
@section('title','Payments')
@section('content')
<h5 class="mb-3 fw-600">Payment History</h5>
<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Date</th><th>Invoice</th><th>Customer</th><th>Method</th><th>Reference</th><th class="text-end">Amount</th><th>By</th></tr></thead>
        <tbody>
        @forelse($payments as $pay)
        <tr>
            <td class="px-3 small">{{ $pay->payment_date->format('d M Y') }}</td>
            <td><a href="{{ route('admin.sales.show', $pay->sale) }}" class="text-decoration-none small fw-500">{{ $pay->sale->invoice_no ?? '—' }}</a></td>
            <td class="small">{{ $pay->sale->customer->name ?? 'Walk-in' }}</td>
            <td class="small">{{ ucfirst(str_replace('_',' ',$pay->method)) }}</td>
            <td class="small text-muted">{{ $pay->reference ?? '—' }}</td>
            <td class="text-end fw-600 text-success">₹{{ number_format($pay->amount,2) }}</td>
            <td class="small">{{ $pay->receivedBy->name ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-muted">No payments yet</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="card-footer">{{ $payments->links() }}</div>
</div>
@endsection

@extends('layouts.admin')
@section('title','Profit & Loss Report')
@section('content')
<h5 class="mb-3 fw-600"><i class="bi bi-bar-chart-line me-2"></i>Profit & Loss Report</h5>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-sm-4 col-lg-3">
                <label class="form-label small fw-500">From Date</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-sm-4 col-lg-3">
                <label class="form-label small fw-500">To Date</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-sm-4 col-lg-3">
                <label class="form-label small fw-500">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}" {{ $branchId==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-sm-4 col-lg-2">
                <button class="btn btn-primary w-100">Generate</button>
            </div>
        </form>
    </div>
</div>

<!-- Stat Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#0d6efd,#0a58ca)">
            <div class="stat-label">Revenue</div>
            <div class="stat-value">₹{{ number_format($report['revenue']) }}</div>
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="small opacity-75 mt-1">{{ $report['sales_count'] }} sales</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#dc3545,#b02a37)">
            <div class="stat-label">COGS</div>
            <div class="stat-value">₹{{ number_format($report['cogs']) }}</div>
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#fd7e14,#e35d0a)">
            <div class="stat-label">Expenses</div>
            <div class="stat-value">₹{{ number_format($report['expenses']) }}</div>
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:{{ $report['net_profit']>=0 ? 'linear-gradient(135deg,#198754,#146c43)' : 'linear-gradient(135deg,#dc3545,#b02a37)' }}">
            <div class="stat-label">Net Profit</div>
            <div class="stat-value">₹{{ number_format($report['net_profit']) }}</div>
            <div class="stat-icon"><i class="bi bi-graph-{{ $report['net_profit']>=0 ? 'up' : 'down' }}-arrow"></i></div>
        </div>
    </div>
</div>

<!-- Breakdown Table -->
<div class="card">
    <div class="card-header">P&L Breakdown: {{ $report['from'] }} to {{ $report['to'] }}</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="table-light"><tr><th class="px-4">Item</th><th class="text-end px-4">Amount (₹)</th></tr></thead>
            <tbody>
                <tr><td class="px-4">Total Revenue</td><td class="text-end px-4 text-primary fw-600">₹{{ number_format($report['revenue'],2) }}</td></tr>
                <tr><td class="px-4 text-danger">Less: Cost of Goods Sold (COGS)</td><td class="text-end px-4 text-danger">- ₹{{ number_format($report['cogs'],2) }}</td></tr>
                <tr class="table-light"><td class="px-4 fw-600">Gross Profit</td><td class="text-end px-4 fw-600">₹{{ number_format($report['gross_profit'],2) }}</td></tr>
                <tr><td class="px-4 text-danger">Less: Operating Expenses</td><td class="text-end px-4 text-danger">- ₹{{ number_format($report['expenses'],2) }}</td></tr>
                <tr class="table-{{ $report['net_profit']>=0?'success':'danger' }}">
                    <td class="px-4 fw-700 fs-5">Net Profit / Loss</td>
                    <td class="text-end px-4 fw-700 fs-5 {{ $report['net_profit']>=0?'text-success':'text-danger' }}">₹{{ number_format($report['net_profit'],2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

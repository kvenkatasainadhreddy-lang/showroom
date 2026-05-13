<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\ProfitService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ProfitService $profitService) {}

    public function profitLoss(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $branchId = $request->get('branch_id');

        $report = $this->profitService->calculate($from, $to, $branchId ?: null);
        $branches = Branch::where('is_active', true)->get();

        return view('admin.reports.profit-loss', compact('report', 'branches', 'from', 'to', 'branchId'));
    }
}

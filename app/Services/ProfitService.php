<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;

class ProfitService
{
    /**
     * Calculate Profit & Loss for a given date range and optional branch.
     *
     * @return array{revenue, cogs, gross_profit, expenses, net_profit, sales_count, from, to}
     */
    public function calculate(string $from, string $to, ?int $branchId = null): array
    {
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();

        // ── Revenue ──────────────────────────────────────────────────
        $salesQuery = Sale::whereBetween('sale_date', [$fromDate, $toDate]);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        $salesIds  = $salesQuery->pluck('id');
        $revenue   = $salesQuery->sum('total');
        $salesCount = $salesQuery->count();

        // ── COGS ─────────────────────────────────────────────────────
        $cogs = SaleItem::whereIn('sale_id', $salesIds)
            ->selectRaw('SUM(cost_price * quantity) as total_cogs')
            ->value('total_cogs') ?? 0;

        // ── Gross Profit ──────────────────────────────────────────────
        $grossProfit = $revenue - $cogs;

        // ── Expenses ─────────────────────────────────────────────────
        $expensesQuery = Expense::whereBetween('expense_date', [$fromDate, $toDate]);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        $expenses = $expensesQuery->sum('amount');

        // ── Net Profit ────────────────────────────────────────────────
        $netProfit = $grossProfit - $expenses;

        return [
            'from'         => $fromDate->toDateString(),
            'to'           => $toDate->toDateString(),
            'revenue'      => round($revenue, 2),
            'cogs'         => round($cogs, 2),
            'gross_profit' => round($grossProfit, 2),
            'expenses'     => round($expenses, 2),
            'net_profit'   => round($netProfit, 2),
            'sales_count'  => $salesCount,
        ];
    }

    /**
     * Get sales revenue per day for the last N days (for charts).
     */
    public function dailyRevenue(int $days = 7, ?int $branchId = null): array
    {
        $query = Sale::selectRaw('DATE(sale_date) as date, SUM(total) as revenue')
            ->where('sale_date', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->orderBy('date');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get()->keyBy('date')->map(fn($r) => $r->revenue)->toArray();
    }
}

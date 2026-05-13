<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\VehicleStock;
use App\Models\VehicleVariant;
use App\Services\ProfitService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ProfitService $profitService) {}

    public function index(Request $request)
    {
        $branchId = $request->get('branch_id');
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        // P&L stats
        $stats = $this->profitService->calculate($from, $to, $branchId ?: null);

        // Daily revenue for chart (last 7 days)
        $dailyRevenue = $this->profitService->dailyRevenue(7, $branchId ?: null);

        // Fill missing days with 0
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartData[] = $dailyRevenue[$date] ?? 0;
        }

        // Low stock spare parts (inventory table)
        $lowStock = Inventory::with('product', 'branch')
            ->whereRaw('quantity <= min_quantity')
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        // Vehicle variants with 0 available stock
        $zeroVehicleStock = VehicleVariant::with('vehicleModel.brand')
            ->withCount(['stock as available_count' => fn($q) => $q->where('status', 'available')])
            ->having('available_count', 0)
            ->orderBy('name')
            ->limit(8)
            ->get();

        // Recent sales
        $recentSales = Sale::with('customer', 'branch')
            ->latest()
            ->limit(8)
            ->get();

        // Summary counts
        $totalCustomers = Customer::count();

        return view('admin.dashboard', compact(
            'stats', 'chartLabels', 'chartData',
            'lowStock', 'zeroVehicleStock', 'recentSales', 'totalCustomers',
            'from', 'to'
        ));
    }
}

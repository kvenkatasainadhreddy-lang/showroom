<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\VehicleStock;
use App\Models\Branch;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $movements = StockMovement::with('product', 'branch', 'creator')
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b))
            ->when($request->from, fn($q, $f) => $q->whereDate('created_at', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('created_at', '<=', $t))
            ->latest()->paginate(30)->withQueryString();

        // Vehicle stock movements (sold chassis)
        $vehicleSales = VehicleStock::with('variant.vehicleModel.brand', 'branch', 'sale')
            ->where('status', 'sold')
            ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b))
            ->when($request->from, fn($q, $f) => $q->whereDate('updated_at', '>=', $f))
            ->when($request->to,   fn($q, $t) => $q->whereDate('updated_at', '<=', $t))
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $branches = Branch::where('is_active', true)->get();
        return view('admin.stock-movements.index', compact('movements', 'vehicleSales', 'branches'));
    }
}

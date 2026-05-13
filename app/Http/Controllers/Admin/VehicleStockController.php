<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\VehicleStock;
use App\Models\VehicleVariant;
use Illuminate\Http\Request;

class VehicleStockController extends Controller
{
    public function index(Request $request)
    {
        $stock = VehicleStock::with('variant.vehicleModel.brand', 'branch')
            ->when($request->status,    fn($q, $s) => $q->where('status', $s))
            ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b))
            ->when($request->search,    fn($q, $s) => $q->where('chassis_number', 'like', "%$s%")
                ->orWhere('engine_number', 'like', "%$s%"))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $branches = Branch::where('is_active', true)->get();
        return view('admin.vehicle-stock.index', compact('stock', 'branches'));
    }

    public function create()
    {
        $variants = VehicleVariant::with('vehicleModel.brand')
            ->where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.vehicle-stock.create', compact('variants', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'variant_id'     => 'required|exists:vehicle_variants,id',
            'chassis_number' => 'required|string|unique:vehicle_stock,chassis_number',
            'engine_number'  => 'nullable|string',
            'color'          => 'nullable|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'branch_id'      => 'nullable|exists:branches,id',
            'received_date'  => 'nullable|date',
        ]);
        VehicleStock::create($request->all());
        return redirect()->route('admin.vehicle-stock.index')
            ->with('success', 'Vehicle stock added: ' . $request->chassis_number);
    }

    public function edit(VehicleStock $vehicleStock)
    {
        $variants = VehicleVariant::with('vehicleModel.brand')
            ->where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.vehicle-stock.edit', compact('vehicleStock', 'variants', 'branches'));
    }

    public function update(Request $request, VehicleStock $vehicleStock)
    {
        $request->validate([
            'chassis_number' => 'required|string|unique:vehicle_stock,chassis_number,' . $vehicleStock->id,
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'status'         => 'required|in:available,reserved,sold',
        ]);
        $vehicleStock->update($request->all());
        return redirect()->route('admin.vehicle-stock.index')->with('success', 'Vehicle stock updated.');
    }

    public function destroy(VehicleStock $vehicleStock)
    {
        $vehicleStock->delete();
        return redirect()->route('admin.vehicle-stock.index')->with('success', 'Vehicle stock deleted.');
    }
}

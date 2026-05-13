<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use Illuminate\Http\Request;

class VehicleVariantController extends Controller
{
    public function index()
    {
        $variants = VehicleVariant::with('vehicleModel.brand')
            ->withCount(['stock', 'stock as available_count' => fn($q) => $q->where('status', 'available')])
            ->latest()
            ->paginate(25);
        return view('admin.vehicle-variants.index', compact('variants'));
    }

    public function create()
    {
        $models = VehicleModel::with('brand')->orderBy('name')->get();
        return view('admin.vehicle-variants.create', compact('models'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_id'   => 'required|exists:vehicle_models,id',
            'name'       => 'required|string|max:255',
            'color'      => 'nullable|string|max:100',
        ]);
        VehicleVariant::create($request->only('model_id', 'name', 'color', 'is_active'));
        return redirect()->route('admin.vehicle-variants.index')->with('success', 'Variant created.');
    }

    public function edit(VehicleVariant $vehicleVariant)
    {
        $models = VehicleModel::with('brand')->orderBy('name')->get();
        return view('admin.vehicle-variants.edit', compact('vehicleVariant', 'models'));
    }

    public function update(Request $request, VehicleVariant $vehicleVariant)
    {
        $request->validate([
            'model_id' => 'required|exists:vehicle_models,id',
            'name'     => 'required|string|max:255',
        ]);
        $vehicleVariant->update($request->only('model_id', 'name', 'color', 'is_active'));
        return redirect()->route('admin.vehicle-variants.index')->with('success', 'Variant updated.');
    }

    public function destroy(VehicleVariant $vehicleVariant)
    {
        $vehicleVariant->delete();
        return redirect()->route('admin.vehicle-variants.index')->with('success', 'Variant deleted.');
    }
}

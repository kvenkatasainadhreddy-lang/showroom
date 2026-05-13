<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use App\Models\Brand;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{
    public function index() { return view('admin.vehicle-models.index', ['models' => VehicleModel::with('brand')->paginate(30)]); }
    public function create() { $brands = Brand::all(); return view('admin.vehicle-models.create', compact('brands')); }

    public function store(Request $request)
    {
        $request->validate(['brand_id' => 'required|exists:brands,id', 'name' => 'required']);
        VehicleModel::create($request->all());
        return redirect()->route('admin.vehicle-models.index')->with('success', 'Model created.');
    }

    public function edit(VehicleModel $vehicleModel) { $brands = Brand::all(); return view('admin.vehicle-models.edit', compact('vehicleModel', 'brands')); }
    public function update(Request $request, VehicleModel $vehicleModel) { $request->validate(['brand_id' => 'required|exists:brands,id', 'name' => 'required']); $vehicleModel->update($request->all()); return redirect()->route('admin.vehicle-models.index')->with('success', 'Model updated.'); }
    public function destroy(VehicleModel $vehicleModel) { $vehicleModel->delete(); return redirect()->route('admin.vehicle-models.index')->with('success', 'Model deleted.'); }
}

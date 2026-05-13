<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index() { return view('admin.brands.index', ['brands' => Brand::withCount(['vehicleModels', 'products'])->paginate(20)]); }
    public function create() { return view('admin.brands.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:brands']);
        Brand::create($request->all());
        return redirect()->route('admin.brands.index')->with('success', 'Brand created.');
    }

    public function edit(Brand $brand) { return view('admin.brands.edit', compact('brand')); }
    public function update(Request $request, Brand $brand) { $request->validate(['name' => 'required|unique:brands,name,' . $brand->id]); $brand->update($request->all()); return redirect()->route('admin.brands.index')->with('success', 'Brand updated.'); }
    public function destroy(Brand $brand) { $brand->delete(); return redirect()->route('admin.brands.index')->with('success', 'Brand deleted.'); }
}

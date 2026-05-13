<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Branch;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use App\Models\VehicleStock;
use Illuminate\Http\Request;

class CatalogueController extends Controller
{
    // ── Unified Setup: Brands → Models → Variants ────────────────
    public function setup()
    {
        $brands   = Brand::withCount('vehicleModels')->orderBy('name')->get();
        $models   = VehicleModel::with('brand')
                        ->withCount(['variants'])
                        ->orderBy('name')->get();
        $variants = VehicleVariant::with('vehicleModel.brand')
                        ->withCount(['stock', 'stock as available_count' => fn($q) => $q->where('status', 'available')])
                        ->orderBy('name')->get();

        return view('admin.catalogue.setup', compact('brands', 'models', 'variants'));
    }

    // ── AJAX: Quick-add Brand ─────────────────────────────────────
    public function storeBrand(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:brands,name']);
        $brand = Brand::create(['name' => $request->name, 'country' => $request->country]);
        return response()->json([
            'success' => true,
            'brand'   => ['id' => $brand->id, 'name' => $brand->name],
        ]);
    }

    // ── AJAX: Quick-add Model ─────────────────────────────────────
    public function storeModel(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
        ]);
        $model = VehicleModel::create(['name' => $request->name, 'brand_id' => $request->brand_id]);
        $model->load('brand');
        return response()->json([
            'success' => true,
            'model'   => [
                'id'         => $model->id,
                'name'       => $model->name,
                'brand_id'   => $model->brand_id,
                'brand_name' => $model->brand->name,
            ],
        ]);
    }

    // ── AJAX: Quick-add Variant ───────────────────────────────────
    public function storeVariant(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'model_id' => 'required|exists:vehicle_models,id',
        ]);
        $variant = VehicleVariant::create([
            'name'      => $request->name,
            'model_id'  => $request->model_id,
            'color'     => $request->color,
            'is_active' => true,
        ]);
        $variant->load('vehicleModel.brand');
        return response()->json([
            'success' => true,
            'variant' => [
                'id'         => $variant->id,
                'name'       => $variant->name,
                'color'      => $variant->color,
                'model_id'   => $variant->model_id,
                'model_name' => $variant->vehicleModel->name,
                'brand_name' => $variant->vehicleModel->brand->name,
            ],
        ]);
    }

    // ── Chassis Registry ─────────────────────────────────────────
    public function chassis(Request $request)
    {
        $brands   = Brand::orderBy('name')->get();
        $models   = VehicleModel::with('brand')->orderBy('name')->get();
        $variants = VehicleVariant::with('vehicleModel.brand')->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        $stockQuery = VehicleStock::with('variant.vehicleModel.brand', 'branch')
            ->when($request->brand_id,   fn($q, $b) => $q->whereHas('variant.vehicleModel', fn($q2) => $q2->where('brand_id', $b)))
            ->when($request->model_id,   fn($q, $m) => $q->whereHas('variant', fn($q2) => $q2->where('model_id', $m)))
            ->when($request->variant_id, fn($q, $v) => $q->where('variant_id', $v))
            ->when($request->status,     fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.catalogue.chassis', compact(
            'brands', 'models', 'variants', 'branches', 'stockQuery'
        ));
    }

    // ── AJAX: Quick-add Chassis ───────────────────────────────────
    public function storeChassis(Request $request)
    {
        $request->validate([
            'variant_id'     => 'required|exists:vehicle_variants,id',
            'chassis_number' => 'required|string|unique:vehicle_stock,chassis_number',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
        ]);

        $stock = VehicleStock::create([
            'variant_id'      => $request->variant_id,
            'branch_id'       => $request->branch_id ?: null,
            'chassis_number'  => strtoupper(trim($request->chassis_number)),
            'engine_number'   => $request->engine_number ? strtoupper(trim($request->engine_number)) : null,
            'color'           => $request->color,
            'purchase_price'  => $request->purchase_price,
            'selling_price'   => $request->selling_price,
            'status'          => 'available',
            'received_date'   => $request->received_date ?: now()->toDateString(),
            'notes'           => $request->notes,
        ]);
        $stock->load('variant.vehicleModel.brand', 'branch');

        return response()->json([
            'success' => true,
            'stock'   => [
                'id'             => $stock->id,
                'chassis_number' => $stock->chassis_number,
                'engine_number'  => $stock->engine_number,
                'color'          => $stock->color,
                'purchase_price' => $stock->purchase_price,
                'selling_price'  => $stock->selling_price,
                'status'         => $stock->status,
                'variant'        => $stock->variant->name,
                'model'          => $stock->variant->vehicleModel->name,
                'brand'          => $stock->variant->vehicleModel->brand->name,
                'branch'         => $stock->branch?->name ?? '—',
                'received_date'  => $stock->received_date?->format('d M Y'),
            ],
        ]);
    }
}

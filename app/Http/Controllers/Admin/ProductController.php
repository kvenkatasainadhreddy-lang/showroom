<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'brand', 'vehicleModel')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")->orWhere('barcode', 'like', "%$s%"))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c))
            ->latest();
        $products = $query->paginate(20)->withQueryString();
        $categories = Category::all();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $vehicleModels = VehicleModel::with('brand')->get();
        return view('admin.products.create', compact('categories', 'brands', 'vehicleModels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products',
            'barcode' => 'nullable|string|unique:products',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:vehicle,part',
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:vehicle_models,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Auto-generate SKU if empty
        if (empty($data['sku'])) {
            $data['sku'] = 'SKU-' . strtoupper(substr(md5(uniqid()), 0, 8));
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'brand', 'vehicleModel', 'inventories.branch', 'saleItems.sale');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $vehicleModels = VehicleModel::with('brand')->get();
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'vehicleModels'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:vehicle,part',
            'brand_id' => 'nullable|exists:brands,id',
            'model_id' => 'nullable|exists:vehicle_models,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /**
     * AJAX: Search product by barcode or name (used in sale/purchase cart)
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $products = Product::with('inventories')
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                $query->where('barcode', $term)
                    ->orWhere('name', 'like', "%$term%")
                    ->orWhere('sku', 'like', "%$term%");
            })
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'selling_price' => $p->selling_price,
                'purchase_price' => $p->purchase_price,
                'stock' => $p->inventories->sum('quantity'),
            ]);
        return response()->json($products);
    }
}

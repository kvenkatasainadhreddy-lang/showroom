<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"))
            ->withCount('purchases')->latest()->paginate(20)->withQueryString();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create() { return view('admin.suppliers.create'); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'phone' => 'nullable', 'email' => 'nullable|email']);
        Supplier::create($request->all());
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('purchases.items');
        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier) { return view('admin.suppliers.edit', compact('supplier')); }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate(['name' => 'required', 'email' => 'nullable|email']);
        $supplier->update($request->all());
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted.');
    }
}

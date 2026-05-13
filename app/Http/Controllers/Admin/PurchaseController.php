<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Branch;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchaseService) {}

    public function index(Request $request)
    {
        $query = Purchase::with('supplier', 'branch')
            ->when($request->search, fn($q, $s) => $q->where('reference_no', 'like', "%$s%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->from, fn($q, $f) => $q->whereDate('purchase_date', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('purchase_date', '<=', $t))
            ->latest();
        $purchases = $query->paginate(20)->withQueryString();
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.purchases.create', compact('suppliers', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'purchase_date' => 'required|date',
            'status' => 'required|in:pending,received,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        $purchase = $this->purchaseService->createPurchase(
            $request->only('supplier_id', 'status', 'purchase_date', 'notes'),
            $request->input('items'),
            $request->input('branch_id'),
            auth()->id()
        );

        return redirect()->route('admin.purchases.show', $purchase)->with('success', 'Purchase recorded: ' . $purchase->reference_no);
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'branch', 'items.product');
        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'branches'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'status' => 'required|in:pending,received,cancelled',
            'notes' => 'nullable|string',
        ]);
        $purchase->update($request->only('status', 'notes'));
        return redirect()->route('admin.purchases.show', $purchase)->with('success', 'Purchase updated.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->route('admin.purchases.index')->with('success', 'Purchase deleted.');
    }
}

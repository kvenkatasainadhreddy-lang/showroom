<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Branch;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $inventory = Inventory::with('product.category', 'product.brand', 'branch')
            ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b))
            ->when($request->low_stock, fn($q) => $q->whereRaw('quantity <= min_quantity'))
            ->when($request->search, fn($q, $s) => $q->whereHas('product', fn($p) => $p->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")))
            ->paginate(25)->withQueryString();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.inventory.index', compact('inventory', 'branches'));
    }

    public function adjust(Request $request, Inventory $inventory)
    {
        $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $before = $inventory->quantity;
        if ($request->type === 'in') {
            $inventory->increment('quantity', $request->quantity);
        } elseif ($request->type === 'out') {
            $inventory->decrement('quantity', $request->quantity);
        } else {
            $inventory->update(['quantity' => $request->quantity]);
        }
        $after = $inventory->fresh()->quantity;

        StockMovement::create([
            'product_id' => $inventory->product_id,
            'branch_id' => $inventory->branch_id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'before_quantity' => $before,
            'after_quantity' => $after,
            'reference_type' => 'adjustment',
            'notes' => $request->notes ?? 'Manual adjustment',
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }
}

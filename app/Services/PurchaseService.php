<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Create a purchase, increase inventory, and log stock movements.
     *
     * @param array $purchaseData  Main purchase fields
     * @param array $items         [{product_id, quantity, cost_price}]
     * @param int   $branchId      Branch receiving the stock
     * @param int   $userId        Who is recording the purchase
     */
    public function createPurchase(array $purchaseData, array $items, int $branchId, int $userId): Purchase
    {
        return DB::transaction(function () use ($purchaseData, $items, $branchId, $userId) {
            // 1. Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['cost_price'] * $item['quantity'];
            }

            // 2. Create Purchase
            $purchase = Purchase::create([
                'reference_no'  => Purchase::generateReferenceNo(),
                'supplier_id'   => $purchaseData['supplier_id'] ?? null,
                'branch_id'     => $branchId,
                'subtotal'      => $subtotal,
                'total'         => $subtotal,
                'status'        => $purchaseData['status'] ?? 'received',
                'purchase_date' => $purchaseData['purchase_date'] ?? now()->toDateString(),
                'notes'         => $purchaseData['notes'] ?? null,
            ]);

            // 3. Create PurchaseItems, increase inventory, log stock movements
            foreach ($items as $item) {
                $lineTotal = $item['cost_price'] * $item['quantity'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'cost_price'  => $item['cost_price'],
                    'total'       => $lineTotal,
                ]);

                // Update inventory
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $item['product_id'], 'branch_id' => $branchId],
                    ['quantity' => 0, 'min_quantity' => 5]
                );

                $beforeQty = $inventory->quantity;
                $inventory->increment('quantity', $item['quantity']);
                $afterQty = $inventory->fresh()->quantity;

                // Log stock movement
                StockMovement::create([
                    'product_id'      => $item['product_id'],
                    'branch_id'       => $branchId,
                    'type'            => 'in',
                    'quantity'        => $item['quantity'],
                    'before_quantity' => $beforeQty,
                    'after_quantity'  => $afterQty,
                    'reference_type'  => 'purchase',
                    'reference_id'    => $purchase->id,
                    'notes'           => 'Purchase: ' . $purchase->reference_no,
                    'created_by'      => $userId,
                ]);

                // Update product's purchase_price to the latest cost
                \App\Models\Product::where('id', $item['product_id'])
                    ->update(['purchase_price' => $item['cost_price']]);
            }

            return $purchase->load('items', 'supplier');
        });
    }
}

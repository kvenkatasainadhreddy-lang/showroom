<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Create a sale with items, reduce inventory, and log stock movements.
     *
     * @param array $saleData   Main sale fields
     * @param array $items      [{product_id, quantity, unit_price, cost_price, discount}]
     * @param int   $branchId   Branch where sale occurs
     * @param int   $userId     Who is making the sale
     */
    public function createSale(array $saleData, array $items, int $branchId, int $userId): Sale
    {
        return DB::transaction(function () use ($saleData, $items, $branchId, $userId) {
            // 1. Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $discount = $saleData['discount'] ?? 0;
            $tax = $saleData['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;
            $amountPaid = $saleData['amount_paid'] ?? 0;

            $paymentStatus = 'unpaid';
            if ($amountPaid >= $total) {
                $paymentStatus = 'paid';
            } elseif ($amountPaid > 0) {
                $paymentStatus = 'partial';
            }

            // 2. Create Sale
            $sale = Sale::create([
                'invoice_no'     => Sale::generateInvoiceNo(),
                'customer_id'    => $saleData['customer_id'] ?? null,
                'branch_id'      => $branchId,
                'sold_by'        => $userId,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'amount_paid'    => $amountPaid,
                'payment_status' => $paymentStatus,
                'notes'          => $saleData['notes'] ?? null,
                'sale_date'      => $saleData['sale_date'] ?? now()->toDateString(),
            ]);

            // 3. Create SaleItems, reduce inventory, log stock movements
            foreach ($items as $item) {
                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'],
                    'discount'   => $item['discount'] ?? 0,
                    'total'      => $lineTotal,
                ]);

                // Update inventory
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $item['product_id'], 'branch_id' => $branchId],
                    ['quantity' => 0, 'min_quantity' => 5]
                );

                $beforeQty = $inventory->quantity;
                $inventory->decrement('quantity', $item['quantity']);
                $afterQty = $inventory->fresh()->quantity;

                // Log stock movement
                StockMovement::create([
                    'product_id'      => $item['product_id'],
                    'branch_id'       => $branchId,
                    'type'            => 'out',
                    'quantity'        => $item['quantity'],
                    'before_quantity' => $beforeQty,
                    'after_quantity'  => $afterQty,
                    'reference_type'  => 'sale',
                    'reference_id'    => $sale->id,
                    'notes'           => 'Sale: ' . $sale->invoice_no,
                    'created_by'      => $userId,
                ]);

                // Update product's purchase_price to latest cost_price for future sales
                \App\Models\Product::where('id', $item['product_id'])
                    ->update(['purchase_price' => $item['cost_price']]);
            }

            return $sale->load('items', 'customer');
        });
    }
}

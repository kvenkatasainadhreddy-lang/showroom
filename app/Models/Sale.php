<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_no',
        'sale_type',
        'vehicle_stock_id',
        'customer_id',
        'customer_name',
        'branch_id',
        'sold_by',
        'salesman_id',
        'subtotal',
        'discount',
        'exchange',
        'tax',
        'total',
        'amount_paid',
        'cash_amount',
        'advance_amount',
        'finance_name',
        'finance_amount',
        'balance_amount',
        'payment_status',
        'notes',
        'sale_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function vehicleStock(): BelongsTo
    {
        return $this->belongsTo(VehicleStock::class, 'vehicle_stock_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceDueAttribute(): float
    {
        return $this->total - $this->amount_paid;
    }

    public function getCogsTotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->cost_price * $item->quantity);
    }

    public function getGrossProfitAttribute(): float
    {
        return $this->total - $this->cogs_total;
    }

    /**
     * Generate next invoice number
     */
    public static function generateInvoiceNo(): string
    {
        $latest = static::latest('id')->value('invoice_no');
        if (!$latest) {
            return 'INV-00001';
        }
        $num = (int) substr($latest, 4) + 1;
        return 'INV-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}

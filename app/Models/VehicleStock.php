<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleStock extends Model
{
    protected $table = 'vehicle_stock';

    protected $fillable = [
        'variant_id', 'branch_id', 'chassis_number', 'engine_number',
        'color', 'purchase_price', 'selling_price', 'status',
        'received_date', 'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'received_date'  => 'date',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(VehicleVariant::class, 'variant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'vehicle_stock_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /** Effective selling price — from chassis record only */
    public function getEffectivePriceAttribute(): float
    {
        return (float) $this->selling_price;
    }
}

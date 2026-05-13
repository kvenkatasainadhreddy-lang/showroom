<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceLog extends Model
{
    protected $fillable = [
        'entity_type', 'price_field',
        'product_id', 'vehicle_variant_id', 'vehicle_stock_id',
        'price_type',
        'old_purchase_price', 'new_purchase_price',
        'old_selling_price',  'new_selling_price',
        'old_margin_percent', 'new_margin_percent',
        'reason', 'changed_by',
    ];

    protected $casts = [
        'old_purchase_price' => 'decimal:2',
        'new_purchase_price' => 'decimal:2',
        'old_selling_price'  => 'decimal:2',
        'new_selling_price'  => 'decimal:2',
        'old_margin_percent' => 'decimal:2',
        'new_margin_percent' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vehicleVariant(): BelongsTo
    {
        return $this->belongsTo(VehicleVariant::class, 'vehicle_variant_id');
    }

    public function vehicleStock(): BelongsTo
    {
        return $this->belongsTo(VehicleStock::class, 'vehicle_stock_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /** Human-readable entity name for display */
    public function getEntityNameAttribute(): string
    {
        return match ($this->entity_type) {
            'vehicle_variant' => $this->vehicleVariant?->name ?? 'Variant #'.$this->vehicle_variant_id,
            'vehicle_stock'   => $this->vehicleStock?->chassis_number ?? 'Chassis #'.$this->vehicle_stock_id,
            default           => $this->product?->name ?? 'Product #'.$this->product_id,
        };
    }

    public function getEntityTypeIconAttribute(): string
    {
        return match ($this->entity_type) {
            'vehicle_variant' => 'bi-palette text-primary',
            'vehicle_stock'   => 'bi-car-front text-success',
            default           => 'bi-box-seam text-warning',
        };
    }

    public function getSellingDirectionAttribute(): string
    {
        if ((float)$this->new_selling_price > (float)$this->old_selling_price) return 'up';
        if ((float)$this->new_selling_price < (float)$this->old_selling_price) return 'down';
        return 'same';
    }

    public function getSellingChangeAttribute(): float
    {
        return (float)$this->new_selling_price - (float)$this->old_selling_price;
    }
}

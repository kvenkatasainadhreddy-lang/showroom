<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'barcode', 'category_id', 'type',
        'brand_id', 'model_id', 'purchase_price', 'selling_price',
        'description', 'image', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get total stock across all branches
     */
    public function getTotalStockAttribute(): float
    {
        return $this->inventories->sum('quantity');
    }

    /**
     * Get profit margin percentage
     */
    public function getMarginPercent(): float
    {
        if ($this->purchase_price <= 0) {
            return 0;
        }
        return round((($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100, 2);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleVariant extends Model
{
    protected $fillable = ['model_id', 'name', 'color', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function stock(): HasMany
    {
        return $this->hasMany(VehicleStock::class, 'variant_id');
    }

    public function availableStock(): HasMany
    {
        return $this->hasMany(VehicleStock::class, 'variant_id')->where('status', 'available');
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(\App\Models\PriceLog::class, 'vehicle_variant_id');
    }

    public function getAvailableCountAttribute(): int
    {
        // Use eager-loaded count if available (via withCount), else query
        return $this->available_count_count ?? $this->availableStock()->count();
    }
}

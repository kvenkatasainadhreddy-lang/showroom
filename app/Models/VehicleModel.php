<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    protected $table = 'vehicle_models';

    protected $fillable = ['brand_id', 'name', 'year', 'description'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(VehicleVariant::class, 'model_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'model_id');
    }
}

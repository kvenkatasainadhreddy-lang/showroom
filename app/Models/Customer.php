<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address',
        'type', 'company_name', 'credit_limit', 'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalPurchasedAttribute(): float
    {
        return $this->sales->sum('total');
    }
}

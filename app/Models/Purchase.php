<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'reference_no', 'supplier_id', 'branch_id',
        'subtotal', 'total', 'status', 'purchase_date', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public static function generateReferenceNo(): string
    {
        $latest = static::latest('id')->value('reference_no');
        if (!$latest) {
            return 'PO-00001';
        }
        $num = (int) substr($latest, 3) + 1;
        return 'PO-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}

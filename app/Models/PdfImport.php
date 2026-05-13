<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfImport extends Model
{
    protected $fillable = [
        'filename', 'path', 'status', 'parsed_items',
        'supplier_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'parsed_items' => 'array',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => '<span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>',
            'processed' => '<span class="badge bg-info-subtle text-info-emphasis">Review</span>',
            'confirmed' => '<span class="badge bg-success-subtle text-success-emphasis">Confirmed</span>',
            'failed'    => '<span class="badge bg-danger-subtle text-danger-emphasis">Failed</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getItemCountAttribute(): int
    {
        return count($this->parsed_items ?? []);
    }
}

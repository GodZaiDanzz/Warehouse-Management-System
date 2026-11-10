<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'quantity_ordered',
        'quantity_received',
        'unit',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'float',
        'quantity_received' => 'float',
        'unit_price' => 'float',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function materialIns(): HasMany
    {
        return $this->hasMany(MaterialIn::class, 'purchase_order_item_id');
    }

    /**
     * Get remaining quantity that has not yet arrived
     */
    public function getQuantityRemainingAttribute(): float
    {
        return (float) max(0, $this->quantity_ordered - $this->quantity_received);
    }

    /**
     * Check if this item is completely fulfilled
     */
    public function getIsFulfilledAttribute(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }
}


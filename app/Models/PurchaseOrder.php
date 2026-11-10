<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'po_date',
        'supplier',
        'project_id',
        'status',
        'eta_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'eta_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function materialIns(): HasMany
    {
        return $this->hasMany(MaterialIn::class, 'purchase_order_id');
    }

    /**
     * Check if PO is fully received / completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if PO is partially received
     */
    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    /**
     * Check if PO is open / awaiting first delivery
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if PO is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Calculate percentage of total items received
     */
    public function getProgressPercentageAttribute(): int
    {
        $totalOrdered = (float) $this->items()->sum('quantity_ordered');
        if ($totalOrdered <= 0) {
            return 0;
        }

        $totalReceived = (float) $this->items()->sum('quantity_received');
        return (int) min(100, round(($totalReceived / $totalOrdered) * 100));
    }

    /**
     * Auto recalculate status based on current items fulfillment
     */
    public function recalculateStatus(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        $items = $this->items;
        if ($items->isEmpty()) {
            return $this->status;
        }

        $allFulfilled = true;
        $anyReceived = false;

        foreach ($items as $item) {
            $ordered = (float) $item->quantity_ordered;
            $received = (float) $item->quantity_received;

            if ($received > 0) {
                $anyReceived = true;
            }

            if ($received < $ordered) {
                $allFulfilled = false;
            }
        }

        $newStatus = 'open';
        if ($allFulfilled) {
            $newStatus = 'completed';
        } elseif ($anyReceived) {
            $newStatus = 'partial';
        }

        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }

        return $newStatus;
    }

    /**
     * Auto generate unique PO number (e.g. PO-202611-001)
     */
    public static function generateUniquePoNumber(): string
    {
        $prefix = 'PO-' . date('Ym');
        $latest = static::withTrashed()
            ->where('po_number', 'like', "{$prefix}-%")
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($latest && preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/', $latest->po_number, $matches)) {
            $seq = (int) $matches[1] + 1;
        }

        $code = sprintf('%s-%03d', $prefix, $seq);
        while (static::withTrashed()->where('po_number', $code)->exists()) {
            $seq++;
            $code = sprintf('%s-%03d', $prefix, $seq);
        }

        return $code;
    }
}


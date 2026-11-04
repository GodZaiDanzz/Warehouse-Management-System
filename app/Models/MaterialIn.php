<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialIn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'date',
        'item_id',
        'unit_id',
        'quantity',
        'unit',
        'is_offcut',
        'parent_item_id',
        'dimension',
        'origin_project',
        'heat_number',
        'supplier',
        'po_number',
        'received_by',
        'notes',
        'user_id',
        'qc_status',
        'qc_by',
        'qc_at',
        'qc_notes',
        'manufacturing_date',
        'expiry_date',
        'wholesale_qty',
        'wholesale_unit',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'float',
        'wholesale_qty' => 'float',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'qc_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = 'IN-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('wms_header_notifications_data');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('wms_header_notifications_data');
        });
    }

    /**
     * Relationship to Item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relationship to parent virgin raw material (if off-cut).
     */
    public function parentItem()
    {
        return $this->belongsTo(Item::class, 'parent_item_id');
    }

    /**
     * Relationship to User (Staff who recorded the receipt).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to QC Inspector.
     */
    public function qcUser()
    {
        return $this->belongsTo(User::class, 'qc_by');
    }

    public function isPendingQc(): bool
    {
        return empty($this->qc_status) || $this->qc_status === 'pending_qc';
    }

    public function isApproved(): bool
    {
        return $this->qc_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->qc_status === 'rejected';
    }

    public function isConsumable(): bool
    {
        return $this->item && $this->item->isConsumable();
    }

    /**
     * Relationship to Material Rejects.
     */
    public function materialRejects()
    {
        return $this->hasMany(MaterialReject::class);
    }

    /**
     * Relationship to Master Unit.
     */
    public function unitRelation()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Centralized Unit display attribute.
     */
    public function getUnitAttribute($value)
    {
        if ($this->relationLoaded('unitRelation') && $this->unitRelation) {
            return $this->unitRelation->symbol;
        }
        if ($this->relationLoaded('item') && $this->item?->unitRelation) {
            return $this->item->unitRelation->symbol;
        }
        return $value ?? 'PCS';
    }
}

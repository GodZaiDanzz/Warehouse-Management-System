<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialReject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'material_in_id',
        'item_id',
        'date',
        'supplier',
        'po_number',
        'quantity',
        'unit_id',
        'unit',
        'damage_type',
        'disposition',
        'disposition_status',
        'description',
        'evidence_photo',
        'reported_by',
        'inspected_by',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'float',
        'resolved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = 'REJ-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
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
     * Relationship to Material In.
     */
    public function materialIn()
    {
        return $this->belongsTo(MaterialIn::class);
    }

    /**
     * Relationship to User who reported the defect.
     */
    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Relationship to QC Inspector.
     */
    public function inspectedBy()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    /**
     * Relationship to User who resolved the disposition.
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Helper for disposition readable label.
     */
    public function getDispositionLabelAttribute(): string
    {
        return match ($this->disposition) {
            'retur_supplier' => 'Retur ke Supplier / Vendor',
            'replacement' => 'Klaim Garansi / Penggantian Baru',
            'scrap' => 'Pemusnahan / Scrap',
            default => 'Karantina Gudang',
        };
    }

    /**
     * Helper for disposition status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->disposition_status) {
            'completed' => 'Selesai (Tuntas)',
            'in_progress' => 'Dalam Proses Retur/Klaim',
            default => 'Menunggu Tindak Lanjut',
        };
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

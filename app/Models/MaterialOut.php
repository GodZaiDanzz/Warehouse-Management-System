<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialOut extends Model
{
    use HasFactory, SoftDeletes;

    public const STAGE_CUTTING = 'Cutting';
    public const STAGE_WELDING = 'Welding';
    public const STAGE_PAINTING = 'Painting';
    public const STAGE_PACKING = 'Packing';

    public static function getWipStages(): array
    {
        return [
            self::STAGE_CUTTING => 'Cutting (Pemotongan Bahan)',
            self::STAGE_WELDING => 'Welding & Assembling (Pengelasan)',
            self::STAGE_PAINTING => 'Blasting & Painting (Pengecatan)',
            self::STAGE_PACKING => 'Packing & Wrapping (Pengemasan)',
        ];
    }

    protected $fillable = [
        'reference_number',
        'date',
        'item_id',
        'unit_id',
        'quantity',
        'unit',
        'project',
        'project_id',
        'pic',
        'notes',
        'user_id',
        'wholesale_qty',
        'wholesale_unit',
        'wip_stage',
        'manpower_hours',
        'manpower_rate',
        'manpower_cost',
        'finish_good_id',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'float',
        'wholesale_qty' => 'float',
        'manpower_hours' => 'float',
        'manpower_rate' => 'float',
        'manpower_cost' => 'float',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = 'OUT-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
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
     * Relationship to Project.
     */
    public function projectRelation()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Relationship to User (Admin/Staff who created the transaction).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

    /**
     * Relationship to FinishGood (if linked).
     */
    public function finishGood()
    {
        return $this->belongsTo(FinishGood::class, 'finish_good_id');
    }

    /**
     * Computed Material Part Cost for this transaction.
     */
    public function getMaterialCostAttribute(): float
    {
        return (float) $this->quantity * (float) ($this->item?->unit_price ?? 0);
    }

    /**
     * Computed Total WIP Cost (Material + Manpower).
     */
    public function getTotalWipCostAttribute(): float
    {
        return $this->material_cost + (float) ($this->manpower_cost ?? 0);
    }
}

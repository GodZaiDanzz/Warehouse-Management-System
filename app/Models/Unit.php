<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'symbol',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        // When symbol is updated, sync string unit in related tables for backward-compatibility with raw SQL & reports
        static::updated(function ($unit) {
            if ($unit->wasChanged('symbol')) {
                Item::where('unit_id', $unit->id)->update(['unit' => $unit->symbol]);
                MaterialIn::where('unit_id', $unit->id)->update(['unit' => $unit->symbol]);
                MaterialOut::where('unit_id', $unit->id)->update(['unit' => $unit->symbol]);
                if (\Illuminate\Support\Facades\Schema::hasTable('material_rejects')) {
                    MaterialReject::where('unit_id', $unit->id)->update(['unit' => $unit->symbol]);
                }
            }
        });
    }

    /**
     * Relationship to Items.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Relationship to Material In records.
     */
    public function materialIns()
    {
        return $this->hasMany(MaterialIn::class);
    }

    /**
     * Relationship to Material Out records.
     */
    public function materialOuts()
    {
        return $this->hasMany(MaterialOut::class);
    }

    /**
     * Relationship to Material Rejects.
     */
    public function materialRejects()
    {
        return $this->hasMany(MaterialReject::class);
    }
}

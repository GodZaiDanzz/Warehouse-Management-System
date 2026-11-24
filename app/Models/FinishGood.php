<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinishGood extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'item_name',
        'item_code',
        'quantity',
        'unit',
        'material_cost',
        'manpower_cost',
        'total_hpp',
        'hpp_per_unit',
        'production_date',
        'qc_status',
        'status',
        'delivery_date',
        'delivery_order_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'delivery_date' => 'date',
        'quantity' => 'decimal:2',
        'material_cost' => 'float',
        'manpower_cost' => 'float',
        'total_hpp' => 'float',
        'hpp_per_unit' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function materialOuts()
    {
        return $this->hasMany(MaterialOut::class, 'finish_good_id');
    }

    public function getFormattedTotalHppAttribute(): string
    {
        return format_currency($this->total_hpp ?? 0);
    }

    public function getFormattedHppPerUnitAttribute(): string
    {
        return format_currency($this->hpp_per_unit ?? 0);
    }

    public function scopeInStorage($query)
    {
        return $query->where('status', 'In Storage');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'Delivered');
    }

    public function scopeQcPassed($query)
    {
        return $query->where('qc_status', 'Passed');
    }
}

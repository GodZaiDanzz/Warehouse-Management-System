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
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'client',
        'location',
        'status',
        'start_date',
        'end_date',
        'budget',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($project) {
            if (empty($project->code)) {
                $project->code = 'PRJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
        });
    }

    public function materialOuts()
    {
        return $this->hasMany(MaterialOut::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for fabrication location (Tempat Fabrikasi)
     */
    public function getFabricationLocationAttribute()
    {
        return $this->location;
    }

    public function setFabricationLocationAttribute($value)
    {
        $this->attributes['location'] = $value;
    }

    /**
     * Get total quantity of materials consumed by this project
     */
    public function getTotalMaterialQuantityAttribute()
    {
        return $this->materialOuts()->sum('quantity');
    }

    /**
     * Get count of unique item types used in this project
     */
    public function getUniqueItemsCountAttribute()
    {
        return $this->materialOuts()->distinct('item_id')->count('item_id');
    }
}

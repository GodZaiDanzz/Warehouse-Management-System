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
        'po_number',
        'name',
        'client',
        'location',
        'status',
        'start_date',
        'end_date',
        'budget',
        'tonnage',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'tonnage' => 'float',
    ];

    protected static function booted()
    {
        static::creating(function ($project) {
            if (empty($project->code)) {
                $project->code = 'PRJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
        });

        static::saved(function () {
            static::clearProjectCache();
        });

        static::deleted(function () {
            static::clearProjectCache();
        });
    }

    /**
     * Clear cached project tonnage and listing.
     */
    public static function clearProjectCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('wms_total_active_tonnage');
        \Illuminate\Support\Facades\Cache::forget('wms_active_projects_list');
        \Illuminate\Support\Facades\Cache::forget('wms_header_notifications_data');
    }

    /**
     * Scope query to active projects only (excludes Completed, Cancelled, Batal).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['Completed', 'Cancelled', 'Batal']);
    }

    /**
     * Get total tonnage from all active projects.
     */
    public static function getTotalActiveTonnage(): float
    {
        return (float) \Illuminate\Support\Facades\Cache::remember('wms_total_active_tonnage', 300, function () {
            return (float) static::active()->sum('tonnage');
        });
    }

    /**
     * Get list of all active projects for stock requirement breakdowns.
     */
    public static function getActiveProjects()
    {
        return \Illuminate\Support\Facades\Cache::remember('wms_active_projects_list', 300, function () {
            return static::active()
                ->select(['id', 'code', 'name', 'status', 'tonnage', 'location'])
                ->orderBy('name')
                ->get();
        });
    }

    public function materialOuts()
    {
        return $this->hasMany(MaterialOut::class, 'project_id');
    }

    public function finishGoods()
    {
        return $this->hasMany(FinishGood::class, 'project_id');
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

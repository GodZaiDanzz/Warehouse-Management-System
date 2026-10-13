<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Check if category is the master/umbrella Cat category.
     */
    public function isCatUmbrella(): bool
    {
        return strcasecmp(trim($this->name), 'Cat') === 0;
    }

    /**
     * Check if category is a paint subcategory (e.g. Cat - Primer).
     */
    public function isCatSubcategory(): bool
    {
        return str_starts_with(strtolower(trim($this->name)), 'cat -');
    }

    /**
     * Get IDs of all paint-related categories (umbrella + subcategories).
     *
     * @return array<int>
     */
    public static function getCatCategoryIds(): array
    {
        return static::where('name', 'Cat')
            ->orWhere('name', 'like', 'Cat - %')
            ->pluck('id')
            ->toArray();
    }
}

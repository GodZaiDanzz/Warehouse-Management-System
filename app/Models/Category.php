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
        $name = strtolower(trim($this->name));
        return (str_starts_with($name, 'cat -') || str_starts_with($name, 'cat ')) && $name !== 'cat';
    }

    /**
     * Check if category is Raw Material or Raw Material subcategory.
     */
    public function isRawMaterial(): bool
    {
        $name = strtolower(trim($this->name));
        return str_contains($name, 'raw material') || str_contains($name, 'raw');
    }

    /**
     * Check if category is specifically for Off-Cut (Sisa Potongan).
     */
    public function isOffCut(): bool
    {
        $name = strtolower(trim($this->name));
        return str_contains($name, 'off cut') || str_contains($name, 'offcut') || str_contains($name, 'sisa');
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
            ->orWhere('name', 'like', 'Cat %')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get IDs of all raw material related categories (including off-cut).
     *
     * @return array<int>
     */
    public static function getRawMaterialCategoryIds(): array
    {
        return static::where('name', 'like', '%raw%')->pluck('id')->toArray();
    }
}

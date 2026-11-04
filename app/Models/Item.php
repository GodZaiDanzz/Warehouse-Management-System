<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'unit_id',
        'code_item',
        'name',
        'brand',
        'is_offcut',
        'parent_item_id',
        'dimension',
        'origin_project',
        'heat_number',
        'stock',
        'unit',
        'unit_price',
        'min_stock',
        'requirement_per_ton',
        'location',
        'manufacturing_date',
        'expiry_date',
        'wholesale_unit',
        'conversion_factor',
    ];

    protected $casts = [
        'is_offcut' => 'boolean',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'stock' => 'float',
        'unit_price' => 'float',
        'min_stock' => 'float',
        'requirement_per_ton' => 'float',
        'conversion_factor' => 'float',
    ];

    /**
     * Get computed total asset valuation for this item (Stock x Unit Price).
     */
    public function getTotalAssetValueAttribute(): float
    {
        return (float) $this->stock * (float) ($this->unit_price ?? 0);
    }

    /**
     * Get formatted unit price in Rupiah.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return format_currency($this->unit_price ?? 0);
    }

    /**
     * Get formatted total asset value in Rupiah.
     */
    public function getFormattedTotalAssetAttribute(): string
    {
        return format_currency($this->total_asset_value);
    }

    /**
     * Check if item has valid wholesale conversion packaging.
     */
    public function hasWholesaleConversion(): bool
    {
        return !empty($this->wholesale_unit) && (float) ($this->conversion_factor ?? 0) > 1;
    }

    /**
     * Get computed wholesale package price (Unit Price x Conversion Factor).
     */
    public function getWholesalePriceAttribute(): float
    {
        return (float) ($this->unit_price ?? 0) * (float) ($this->conversion_factor ?? 1);
    }

    /**
     * Get formatted wholesale package price in Rupiah.
     */
    public function getFormattedWholesalePriceAttribute(): string
    {
        return format_currency($this->wholesale_price);
    }

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->code_item)) {
                $item->code_item = static::generateUniqueCodeItem($item->category_id);
            }
        });

        static::saving(function ($item) {
            if ($item->unit_id && empty($item->unit)) {
                $unit = Unit::find($item->unit_id);
                if ($unit) {
                    $item->unit = $unit->symbol;
                }
            }

            if ($item->conversion_factor === null || (float)$item->conversion_factor <= 0) {
                $item->conversion_factor = 1;
            }

            if ($item->unit_price === null) {
                $item->unit_price = 0;
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
     * Generate unique Kode Material based on category.
     * Consumable -> CSM-001
     * Secondary Part -> SEC-001
     * Cat / Painting -> CAT-001
     * Other / Fallback -> MAT-001
     */
    public static function generateUniqueCodeItem(?int $categoryId = null): string
    {
        $prefix = 'MAT';
        if ($categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                $catLower = strtolower($category->name);
                if (str_contains($catLower, 'consumable')) {
                    $prefix = 'CSM';
                } elseif (str_contains($catLower, 'secondary')) {
                    $prefix = 'SEC';
                } elseif (str_contains($catLower, 'cat') || str_contains($catLower, 'paint') || str_contains($catLower, 'thinner')) {
                    $prefix = 'CAT';
                } elseif (str_contains($catLower, 'off cut') || str_contains($catLower, 'offcut') || str_contains($catLower, 'sisa')) {
                    $prefix = 'OFF';
                } elseif (str_contains($catLower, 'raw')) {
                    $prefix = 'RAW';
                } elseif (str_contains($catLower, 'gas')) {
                    $prefix = 'GAS';
                } else {
                    $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $category->name));
                    $prefix = strlen($clean) >= 3 ? substr($clean, 0, 3) : 'MAT';
                }
            }
        }

        // Find the highest number for this prefix
        $existingCodes = static::where('code_item', 'like', "{$prefix}-%")
            ->pluck('code_item');

        $maxNum = 0;
        foreach ($existingCodes as $code) {
            if (preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/', (string)$code, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        $code = sprintf('%s-%03d', $prefix, $nextNum);

        // Ensure uniqueness
        while (static::where('code_item', $code)->exists()) {
            $nextNum++;
            $code = sprintf('%s-%03d', $prefix, $nextNum);
        }

        return $code;
    }

    /**
     * Get effective expiry date (explicit expiry_date or computed from manufacturing_date + shelf life for Cat items).
     */
    public function getEffectiveExpiryDateAttribute()
    {
        if ($this->expiry_date !== null) {
            return $this->expiry_date;
        }
        if ($this->isCat() && $this->manufacturing_date !== null) {
            $shelfMonths = CompanyProfile::getCatShelfLifeMonths();
            return $this->manufacturing_date->copy()->addMonths($shelfMonths);
        }
        return null;
    }

    /**
     * Check if item has expired.
     */
    public function isExpired(): bool
    {
        $exp = $this->effective_expiry_date;
        return $exp !== null && $exp->isPast();
    }

    /**
     * Check if item is nearing expiration (within $days, fallback to system cat_fefo_alert_days).
     */
    public function isNearExpiry(?int $days = null): bool
    {
        $exp = $this->effective_expiry_date;
        if ($exp === null) {
            return false;
        }
        $thresholdDays = $days ?? CompanyProfile::getCatFefoAlertDays();
        return !$this->isExpired() && now()->startOfDay()->diffInDays($exp->startOfDay(), false) <= $thresholdDays;
    }

    /**
     * Get expiry status: 'expired', 'near_expiry', 'safe', or null.
     */
    public function getExpiryStatusAttribute(): ?string
    {
        $exp = $this->effective_expiry_date;
        if ($exp === null) {
            return null;
        }
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isNearExpiry()) {
            return 'near_expiry';
        }
        return 'safe';
    }

    /**
     * Get remaining days until expiration (positive if future, negative if past).
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        $exp = $this->effective_expiry_date;
        if ($exp === null) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($exp->startOfDay(), false);
    }

    /**
     * Check if item is in Cat / Painting category.
     */
    public function isCat(): bool
    {
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }
        return ($this->category && str_contains(strtolower($this->category->name), 'cat'))
            || str_contains(strtoupper($this->name), 'CAT ')
            || str_contains(strtoupper($this->name), 'PAINT')
            || str_contains(strtoupper($this->name), 'THINNER');
    }

    public const STORAGE_1 = 'STORAGE 1';
    public const STORAGE_2 = 'STORAGE 2';

    /**
     * Check if item is in Raw Material category (including off-cut).
     */
    public function isRawMaterial(): bool
    {
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }
        return ($this->category && (str_contains(strtolower($this->category->name), 'raw') || str_contains(strtolower($this->category->name), 'off cut')))
            || str_starts_with(strtoupper($this->code_item ?? ''), 'RAW')
            || str_starts_with(strtoupper($this->code_item ?? ''), 'OFF-')
            || str_contains(strtolower($this->name), 'raw material')
            || (bool) $this->is_offcut;
    }

    /**
     * Check if item is an Off-Cut (Sisa Potongan).
     */
    public function isOffCut(): bool
    {
        if ((bool) $this->is_offcut) {
            return true;
        }
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }
        return ($this->category && (str_contains(strtolower($this->category->name), 'off cut') || str_contains(strtolower($this->category->name), 'offcut') || str_contains(strtolower($this->category->name), 'sisa')))
            || str_starts_with(strtoupper($this->code_item ?? ''), 'OFF-')
            || str_contains(strtolower($this->name), 'off-cut')
            || str_contains(strtolower($this->name), 'offcut');
    }

    /**
     * Get designated storage name ('STORAGE 1' or 'STORAGE 2').
     * STORAGE 2 is designated for Raw Material.
     * STORAGE 1 is designated for all other materials.
     */
    public function getStorageLocationAttribute(): string
    {
        if (!empty($this->location)) {
            $locUpper = strtoupper($this->location);
            if (str_contains($locUpper, 'STORAGE 2') || str_contains($locUpper, 'GUDANG 2')) {
                return self::STORAGE_2;
            }
            if (str_contains($locUpper, 'STORAGE 1') || str_contains($locUpper, 'GUDANG 1')) {
                return self::STORAGE_1;
            }
        }

        return $this->isRawMaterial() ? self::STORAGE_2 : self::STORAGE_1;
    }

    /**
     * Alias attribute for storage name ('STORAGE 1' or 'STORAGE 2').
     */
    public function getStorageAttribute(): string
    {
        return $this->storage_location;
    }

    /**
     * Get clean display location with storage and sub-rack info.
     */
    public function getDisplayLocationAttribute(): string
    {
        $storage = $this->storage_location;
        if (!empty($this->location)) {
            $loc = trim($this->location);
            if (strcasecmp($loc, $storage) === 0) {
                return $storage;
            }
            if (stripos($loc, $storage) === 0) {
                return $loc;
            }
            return "{$storage} ({$loc})";
        }
        return $storage;
    }

    /**
     * Scope a query to filter by storage ('STORAGE 1' or 'STORAGE 2').
     */
    public function scopeInStorage($query, string $storage)
    {
        $storage = strtoupper(trim($storage));
        $rawCatIds = Category::where('name', 'like', '%raw%')->pluck('id')->toArray();

        if ($storage === self::STORAGE_2) {
            return $query->where(function ($q) use ($rawCatIds) {
                $q->where('location', 'like', '%STORAGE 2%')
                  ->orWhere('location', 'like', '%GUDANG 2%')
                  ->orWhereIn('category_id', $rawCatIds)
                  ->orWhere('code_item', 'like', 'RAW-%')
                  ->orWhere('name', 'like', '%raw material%');
            });
        } elseif ($storage === self::STORAGE_1) {
            return $query->where(function ($q) use ($rawCatIds) {
                $q->where(function ($sub) {
                    $sub->whereNull('location')
                        ->orWhere('location', 'not like', '%STORAGE 2%');
                })->where(function ($sub) use ($rawCatIds) {
                    $sub->whereNotIn('category_id', $rawCatIds)
                        ->orWhereNull('category_id');
                })->where(function ($sub) {
                    $sub->where('code_item', 'not like', 'RAW-%')
                        ->orWhereNull('code_item');
                });
            });
        }

        return $query;
    }

    /**
     * Check if item is in Consumable category.
     */
    public function isConsumable(): bool
    {
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }
        return $this->category && str_contains(strtolower($this->category->name), 'consumable');
    }

    /**
     * Check if material has requirement per ton configured.
     */
    public function hasRequirementPerTon(): bool
    {
        return $this->requirement_per_ton !== null;
    }

    /**
     * Calculate effective minimum stock.
     * Uses automatic calculation (Total Active Project Tonnage x Requirement per Ton) if requirement_per_ton is set.
     * Otherwise falls back to manual static min_stock.
     */
    public function getEffectiveMinStock(?float $totalActiveTonnage = null): float
    {
        if ($this->hasRequirementPerTon()) {
            if ($totalActiveTonnage === null) {
                $totalActiveTonnage = Project::getTotalActiveTonnage();
            }
            return (float) ($totalActiveTonnage * (float) $this->requirement_per_ton);
        }

        return (float) ($this->min_stock ?? 0);
    }

    /**
     * Accessor for effective minimum stock.
     */
    public function getEffectiveMinStockAttribute(): float
    {
        return $this->getEffectiveMinStock();
    }

    /**
     * Get item minimum stock breakdown per active project.
     */
    public function getMinStockProjectBreakdown($activeProjects = null): array
    {
        if ($activeProjects === null) {
            $activeProjects = Project::getActiveProjects();
        }

        $reqPerTon = $this->requirement_per_ton !== null ? (float) $this->requirement_per_ton : null;
        $isFallback = $reqPerTon === null;
        $totalTonnage = 0.0;
        $projectList = [];

        foreach ($activeProjects as $project) {
            $tonnage = (float) ($project->tonnage ?? 0);
            $totalTonnage += $tonnage;
            $requiredQty = $reqPerTon !== null ? ($tonnage * $reqPerTon) : 0;

            $projectList[] = [
                'id' => $project->id,
                'name' => $project->name,
                'code' => $project->code,
                'location' => $project->location,
                'status' => $project->status,
                'tonnage' => $tonnage,
                'requirement_qty' => $requiredQty,
            ];
        }

        $effectiveMinStock = $isFallback ? (float) ($this->min_stock ?? 0) : ($totalTonnage * $reqPerTon);

        return [
            'item_id' => $this->id,
            'item_name' => $this->name,
            'code_item' => $this->code_item,
            'unit' => $this->unit,
            'has_requirement' => !$isFallback,
            'requirement_per_ton' => $reqPerTon,
            'total_active_tonnage' => $totalTonnage,
            'total_min_stock' => $effectiveMinStock,
            'is_fallback' => $isFallback,
            'fallback_min_stock' => (float) ($this->min_stock ?? 0),
            'projects' => $projectList,
        ];
    }

    /**
     * Check if item stock is at or below minimum reorder point.
     */
    public function isLowStock(?float $totalActiveTonnage = null): bool
    {
        return $this->stock <= $this->getEffectiveMinStock($totalActiveTonnage);
    }

    /**
     * Get wholesale equivalent string (e.g. 5 Box @ 50 Pcs).
     */
    public function getWholesaleDisplayAttribute(): ?string
    {
        if (!empty($this->wholesale_unit) && $this->conversion_factor > 1) {
            $wholeCount = floor($this->stock / $this->conversion_factor);
            $remainder = fmod($this->stock, $this->conversion_factor);
            $remainderFormatted = format_quantity($remainder);
            return "{$wholeCount} {$this->wholesale_unit}" . ($remainder > 0 ? " + {$remainderFormatted} {$this->unit}" : "");
        }
        return null;
    }

    /**
     * Relationship to Category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship to Stock Logs.
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    /**
     * Relationship to Material Outs.
     */
    public function materialOuts()
    {
        return $this->hasMany(MaterialOut::class);
    }

    /**
     * Relationship to Daily Stock Opnames.
     */
    public function dailyStockOpnames()
    {
        return $this->hasMany(DailyStockOpname::class);
    }

    /**
     * Relationship to parent virgin raw material (if this item is an off-cut).
     */
    public function parentItem()
    {
        return $this->belongsTo(Item::class, 'parent_item_id');
    }

    /**
     * Relationship to child off-cut pieces created from this item.
     */
    public function childOffcuts()
    {
        return $this->hasMany(Item::class, 'parent_item_id');
    }

    /**
     * Relationship to Material Ins.
     */
    public function materialIns()
    {
        return $this->hasMany(MaterialIn::class);
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
        return $value ?? 'PCS';
    }
}

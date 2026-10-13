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
        'stock',
        'unit',
        'unit_price',
        'min_stock',
        'location',
        'manufacturing_date',
        'expiry_date',
        'wholesale_unit',
        'conversion_factor',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'stock' => 'float',
        'unit_price' => 'float',
        'min_stock' => 'float',
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
     * Check if item stock is at or below minimum reorder point.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
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

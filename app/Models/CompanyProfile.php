<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CompanyProfile extends Model
{
    use HasFactory;

    /**
     * In-memory memoized profile for single request lifecycle
     */
    protected static ?self $memoizedProfile = null;

    protected static function booted()
    {
        static::saved(function () {
            static::clearProfileCache();
        });
        static::deleted(function () {
            static::clearProfileCache();
        });
    }

    /**
     * Clear memoized and application cache for company profile
     */
    public static function clearProfileCache(): void
    {
        static::$memoizedProfile = null;
        try {
            Cache::forget('active_company_profile');
        } catch (\Throwable $e) {}
    }

    protected $fillable = [
        'name',
        'secondary_name',
        'tagline',
        'logo_path',
        'secondary_logo_path',
        'email',
        'phone',
        'address',
        'website',
        'theme_color',
        'btn_add_color',
        'btn_save_color',
        'btn_other_color',
        'cat_shelf_life_months',
        'cat_fefo_alert_days',
        'date_format',
        'time_format',
        'slow_moving_days',
        'dead_stock_days',
        'default_per_page',
        'signer_1_name',
        'signer_1_title',
        'signer_2_name',
        'signer_2_title',
        'signer_3_name',
        'signer_3_title',
        'sidebar_title',
        'sidebar_subtitle',
        'auto_backup_enabled',
        'auto_backup_frequency_value',
        'auto_backup_frequency_unit',
        'auto_backup_max_files',
        'last_backup_at',
    ];

    protected $casts = [
        'auto_backup_enabled' => 'boolean',
        'auto_backup_frequency_value' => 'integer',
        'auto_backup_max_files' => 'integer',
        'last_backup_at' => 'datetime',
    ];

    /**
     * Get active company profile instance with fallback
     */
    public static function getProfile(): self
    {
        if (static::$memoizedProfile !== null) {
            return static::$memoizedProfile;
        }

        try {
            $profile = Cache::rememberForever('active_company_profile', function () {
                return self::first();
            });
        } catch (\Throwable $e) {
            $profile = null;
        }

        if (!$profile) {
            $profile = new self([
                'name' => 'PT Mudalaya Energy Indonesia',
                'secondary_name' => 'PT Mutiara Global Industry',
                'tagline' => 'Energy, EPC & General Contractor',
                'logo_path' => 'uploads/company/logo_1789092534.png',
                'secondary_logo_path' => 'uploads/company/logo_2_1789373097.png',
                'email' => 'info@mudalayaenergy.co.id',
                'phone' => '+62 21 555-0199',
                'address' => 'Gedung Mudalaya Energy, Jakarta, Indonesia',
                'website' => 'www.mudalayaenergy.co.id',
                'theme_color' => '#275538',
                'btn_add_color' => '#275538',
                'btn_save_color' => '#275538',
                'btn_other_color' => '#275538',
                'cat_shelf_life_months' => 12,
                'cat_fefo_alert_days' => 60,
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'slow_moving_days' => 90,
                'dead_stock_days' => 180,
                'default_per_page' => 15,
                'signer_1_name' => '',
                'signer_1_title' => 'Staff Gudang / Inventory Control',
                'signer_2_name' => '',
                'signer_2_title' => 'Supervisor WMS / QC',
                'signer_3_name' => '',
                'signer_3_title' => 'Kepala Gudang / Project Manager',
                'sidebar_title' => 'WAREHOUSE',
                'sidebar_subtitle' => 'Management System',
                'auto_backup_enabled' => true,
                'auto_backup_frequency_value' => 7,
                'auto_backup_frequency_unit' => 'days',
                'auto_backup_max_files' => 10,
                'last_backup_at' => null,
            ]);
        }
        if (empty($profile->theme_color)) {
            $profile->theme_color = '#275538';
        }
        if (empty($profile->btn_add_color)) {
            $profile->btn_add_color = $profile->theme_color ?? '#275538';
        }
        if (empty($profile->btn_save_color)) {
            $profile->btn_save_color = $profile->theme_color ?? '#275538';
        }
        if (empty($profile->btn_other_color)) {
            $profile->btn_other_color = $profile->theme_color ?? '#275538';
        }
        if (empty($profile->cat_shelf_life_months)) {
            $profile->cat_shelf_life_months = 12;
        }
        if (empty($profile->cat_fefo_alert_days)) {
            $profile->cat_fefo_alert_days = 60;
        }
        if (empty($profile->date_format)) {
            $profile->date_format = 'd/m/Y';
        }
        if (empty($profile->time_format)) {
            $profile->time_format = 'H:i';
        }
        if (empty($profile->slow_moving_days)) {
            $profile->slow_moving_days = 90;
        }
        if (empty($profile->dead_stock_days)) {
            $profile->dead_stock_days = 180;
        }
        if (empty($profile->default_per_page)) {
            $profile->default_per_page = 15;
        }
        if (empty($profile->signer_1_title)) {
            $profile->signer_1_title = 'Staff Gudang / Inventory Control';
        }
        if (empty($profile->signer_2_title)) {
            $profile->signer_2_title = 'Supervisor WMS / QC';
        }
        if (empty($profile->signer_3_title)) {
            $profile->signer_3_title = 'Kepala Gudang / Project Manager';
        }
        if (empty($profile->sidebar_title)) {
            $profile->sidebar_title = 'WAREHOUSE';
        }
        if (empty($profile->sidebar_subtitle)) {
            $profile->sidebar_subtitle = 'Management System';
        }
        return static::$memoizedProfile = $profile;
    }

    /**
     * Get cat shelf life in months (fallback to 12 months)
     */
    public static function getCatShelfLifeMonths(): int
    {
        $profile = self::getProfile();
        $val = (int) ($profile->cat_shelf_life_months ?? 12);
        return $val > 0 ? $val : 12;
    }

    /**
     * Get cat FEFO yellow alert lead time in days (fallback to 60 days)
     */
    public static function getCatFefoAlertDays(): int
    {
        $profile = self::getProfile();
        $val = (int) ($profile->cat_fefo_alert_days ?? 60);
        return $val > 0 ? $val : 60;
    }

    /**
     * Get global system date format
     */
    public static function getSystemDateFormat(): string
    {
        $profile = self::getProfile();
        return $profile->date_format ?: 'd/m/Y';
    }

    /**
     * Get global time format
     */
    public static function getTimeFormat(): string
    {
        $profile = self::getProfile();
        return $profile->time_format ?: 'H:i';
    }

    /**
     * Format a date string or Carbon instance using system date format
     */
    public static function formatDate($date, string $fallback = '-'): string
    {
        if (empty($date)) return $fallback;
        try {
            $format = self::getSystemDateFormat();
            if ($date instanceof \Carbon\CarbonInterface) {
                return $date->translatedFormat($format);
            }
            return \Carbon\Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }

    /**
     * Format a datetime string or Carbon instance using system date + time format
     */
    public static function formatDateTime($date, string $fallback = '-'): string
    {
        if (empty($date)) return $fallback;
        try {
            $dateFormat = self::getSystemDateFormat();
            $timeFormat = self::getTimeFormat();
            $fullFormat = "{$dateFormat} {$timeFormat}";
            if ($date instanceof \Carbon\CarbonInterface) {
                return $date->translatedFormat($fullFormat);
            }
            return \Carbon\Carbon::parse($date)->translatedFormat($fullFormat);
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }

    /**
     * Get Slow Moving threshold in days (fallback to 90 days)
     */
    public static function getSlowMovingDays(): int
    {
        $profile = self::getProfile();
        $val = (int) ($profile->slow_moving_days ?? 90);
        return $val > 0 ? $val : 90;
    }

    /**
     * Get Dead Stock threshold in days (fallback to 180 days)
     */
    public static function getDeadStockDays(): int
    {
        $profile = self::getProfile();
        $val = (int) ($profile->dead_stock_days ?? 180);
        return $val > 0 ? $val : 180;
    }

    /**
     * Get default table rows per page (fallback to 15)
     */
    public static function getDefaultPerPage(): int
    {
        $profile = self::getProfile();
        $val = (int) ($profile->default_per_page ?? 15);
        return in_array($val, [10, 15, 25, 50, 100]) ? $val : 15;
    }

    /**
     * Get official PDF signers configuration
     */
    public static function getSigners(): array
    {
        $profile = self::getProfile();
        return [
            'signer_1' => [
                'name' => $profile->signer_1_name ?: '',
                'title' => $profile->signer_1_title ?: 'Staff Gudang / Inventory Control',
            ],
            'signer_2' => [
                'name' => $profile->signer_2_name ?: '',
                'title' => $profile->signer_2_title ?: 'Supervisor WMS / QC',
            ],
            'signer_3' => [
                'name' => $profile->signer_3_name ?: '',
                'title' => $profile->signer_3_title ?: 'Kepala Gudang / Project Manager',
            ],
        ];
    }

    /**
     * Get auto-cropped favicon URL (removing whitespace margin)
     */
    public function getFaviconUrl()
    {
        if (empty($this->logo_path)) {
            return null;
        }

        $fullLogoPath = public_path($this->logo_path);
        if (!file_exists($fullLogoPath)) {
            return null;
        }

        $pathInfo = pathinfo($this->logo_path);
        $favRelative = $pathInfo['dirname'] . '/fav_' . $pathInfo['filename'] . '.png';
        $favFullPath = public_path($favRelative);

        if (file_exists($favFullPath)) {
            return asset($favRelative) . '?v=' . filemtime($favFullPath);
        }

        // Generate cropped favicon on the fly if GD is available
        try {
            $info = @getimagesize($fullLogoPath);
            if (!$info) return asset($this->logo_path);

            $mime = $info['mime'];
            if (str_contains($mime, 'png')) {
                $img = @imagecreatefrompng($fullLogoPath);
            } elseif (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $img = @imagecreatefromjpeg($fullLogoPath);
            } else {
                return asset($this->logo_path);
            }

            if (!$img) return asset($this->logo_path);

            $width = imagesx($img);
            $height = imagesy($img);

            $minX = $width; $minY = $height; $maxX = 0; $maxY = 0;
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($img, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $alpha = ($rgb >> 24) & 0x7F;

                    $isWhite = ($r > 240 && $g > 240 && $b > 240);
                    if (!$isWhite && $alpha < 120) {
                        if ($x < $minX) $minX = $x;
                        if ($x > $maxX) $maxX = $x;
                        if ($y < $minY) $minY = $y;
                        if ($y > $maxY) $maxY = $y;
                    }
                }
            }

            if ($maxX > $minX && $maxY > $minY) {
                $margin = 2;
                $cropX = max(0, $minX - $margin);
                $cropY = max(0, $minY - $margin);
                $cropW = min($width - $cropX, ($maxX - $minX + 1) + ($margin * 2));
                $cropH = min($height - $cropY, ($maxY - $minY + 1) + ($margin * 2));

                $dst = imagecreatetruecolor($cropW, $cropH);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);

                $transparentColor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefill($dst, 0, 0, $transparentColor);

                imagecopy($dst, $img, 0, 0, $cropX, $cropY, $cropW, $cropH);

                for ($cx = 0; $cx < $cropW; $cx++) {
                    for ($cy = 0; $cy < $cropH; $cy++) {
                        $rgb = imagecolorat($dst, $cx, $cy);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        if ($r > 245 && $g > 245 && $b > 245) {
                            imagesetpixel($dst, $cx, $cy, $transparentColor);
                        }
                    }
                }

                imagepng($dst, $favFullPath);
                imagedestroy($dst);
                imagedestroy($img);
                return asset($favRelative) . '?v=' . filemtime($favFullPath);
            }
            imagedestroy($img);
        } catch (\Throwable $e) {}

        return asset($this->logo_path);
    }

    public function getName2Attribute()
    {
        return $this->secondary_name;
    }

    public function setName2Attribute($value)
    {
        $this->attributes['secondary_name'] = $value;
    }

    public function getLogoPath2Attribute()
    {
        return $this->secondary_logo_path;
    }

    public function setLogoPath2Attribute($value)
    {
        $this->attributes['secondary_logo_path'] = $value;
    }

    public function hasPrimaryLogo(): bool
    {
        return !empty($this->logo_path) && file_exists(public_path($this->logo_path));
    }

    public function hasSecondaryLogo(): bool
    {
        return !empty($this->secondary_logo_path) && file_exists(public_path($this->secondary_logo_path));
    }
}

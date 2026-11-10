<?php

use App\Models\CompanyProfile;

if (!function_exists('format_date')) {
    /**
     * Format a date string or Carbon instance using system date format
     */
    function format_date($date, string $fallback = '-'): string
    {
        return CompanyProfile::formatDate($date, $fallback);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format a datetime string or Carbon instance using system date + time format
     */
    function format_datetime($date, string $fallback = '-'): string
    {
        return CompanyProfile::formatDateTime($date, $fallback);
    }
}

if (!function_exists('system_date_format')) {
    /**
     * Get system date format string
     */
    function system_date_format(): string
    {
        return CompanyProfile::getSystemDateFormat();
    }
}

if (!function_exists('system_time_format')) {
    /**
     * Get system time format string
     */
    function system_time_format(): string
    {
        return CompanyProfile::getTimeFormat();
    }
}

if (!function_exists('is_decimal_unit')) {
    /**
     * Determine if a given unit represents a continuous measurement (weight, length, volume)
     * that naturally allows or requires decimal values (e.g., KG, GRAM, LITER, METER, CM).
     */
    function is_decimal_unit(?string $unit): bool
    {
        if (!$unit) {
            return false;
        }

        $normalized = strtoupper(trim($unit));
        $decimalUnits = [
            'KG', 'KILOGRAM',
            'GRAM', 'GR', 'G',
            'LITER', 'LTR', 'L',
            'ML', 'MILILITER',
            'METER', 'M',
            'CM', 'CENTIMETER',
            'MM', 'MILIMETER',
        ];

        return in_array($normalized, $decimalUnits, true);
    }
}

if (!function_exists('format_quantity')) {
    /**
     * Format inventory quantity cleanly:
     * - Whole numbers format without decimals (e.g. 10 -> "10", 1000 -> "1.000")
     * - Decimal values format up to 4 decimal places without trailing zeros (e.g. 1.5 -> "1,5", 1.75 -> "1,75")
     * - Optional unit appends after quantity (e.g. "1,5 KG")
     */
    function format_quantity($value, ?string $unit = null): string
    {
        if ($value === null || $value === '') {
            return '0' . ($unit ? ' ' . $unit : '');
        }

        $cleanVal = is_string($value) ? str_replace(',', '.', trim($value)) : $value;
        $floatVal = (float) $cleanVal;

        // If it's a whole number
        if (floor($floatVal) == $floatVal) {
            $formatted = number_format($floatVal, 0, ',', '.');
        } else {
            // Up to 4 decimal places, comma as decimal, dot as thousand, strip trailing zeros
            $formatted = rtrim(rtrim(number_format($floatVal, 4, ',', '.'), '0'), ',');
        }

        return $unit ? ($formatted . ' ' . $unit) : $formatted;
    }
}

if (!function_exists('format_unit_quantity')) {
    /**
     * Format a quantity respecting its unit characteristics:
     * - Discrete units (PCS, UNIT, PASANG, SET, TABUNG, etc.) display strictly as whole numbers if integer.
     * - Decimal units (KG, METER, LITER, etc.) display up to 4 decimal places with comma.
     * - $appendUnit: whether to append unit string (e.g. "1,5 KG" vs "1,5").
     */
    function format_unit_quantity($value, ?string $unit = null, bool $appendUnit = false): string
    {
        if ($value === null || $value === '') {
            return '0' . ($appendUnit && $unit ? ' ' . $unit : '');
        }

        $cleanVal = is_string($value) ? str_replace(',', '.', trim($value)) : $value;
        $floatVal = (float) $cleanVal;

        // If unit is discrete (not a decimal unit) and it's a whole number, format without decimals
        if (!is_decimal_unit($unit) && floor($floatVal) == $floatVal) {
            $formatted = number_format($floatVal, 0, ',', '.');
        } else {
            $formatted = format_quantity($floatVal);
        }

        return ($appendUnit && $unit) ? ($formatted . ' ' . $unit) : $formatted;
    }
}

if (!function_exists('format_unit_breakdown')) {
    /**
     * Format an associative array of [unit => quantity] into a clean combined string
     * e.g. ['PCS' => 200, 'KG' => 1.5] -> "200 PCS + 1,5 KG"
     */
    function format_unit_breakdown(array $unitTotals, string $separator = ' + '): string
    {
        $parts = [];
        // First discrete units
        foreach ($unitTotals as $unit => $qty) {
            if (!is_decimal_unit((string)$unit) && (float)$qty > 0) {
                $parts[] = format_unit_quantity($qty, (string)$unit, true);
            }
        }
        // Then decimal units
        foreach ($unitTotals as $unit => $qty) {
            if (is_decimal_unit((string)$unit) && (float)$qty > 0) {
                $parts[] = format_unit_quantity($qty, (string)$unit, true);
            }
        }

        return empty($parts) ? '0' : implode($separator, $parts);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format a monetary value in Indonesian Rupiah (IDR).
     * e.g. 150000 -> "Rp 150.000", null / 0 -> "Rp 0"
     */
    function format_currency($value, string $prefix = 'Rp '): string
    {
        if ($value === null || $value === '') {
            return $prefix . '0';
        }
        $floatVal = (float) $value;
        return $prefix . number_format($floatVal, 0, ',', '.');
    }
}

if (!function_exists('unit_code')) {
    function unit_code(?string $unit): string
    {
        if (empty($unit)) {
            return 'PCS';
        }
        $u = strtoupper(trim($unit));
        $map = [
            'PASANG' => 'PSG',
            'PAIR' => 'PSG',
            'TABUNG' => 'TBG',
            'LEMBAR' => 'LBR',
            'BATANG' => 'BTG',
            'LUSIN' => 'LSN',
            'DOZEN' => 'LSN',
            'BOTOL' => 'BTL',
            'LITER' => 'LTR',
            'METER' => 'MTR',
            'CENTIMETER' => 'CM',
            'MILIMETER' => 'MM',
            'MILILITER' => 'ML',
            'KILOGRAM' => 'KG',
            'GRAM' => 'GR',
            'PIECES' => 'PCS',
            'PIECE' => 'PCS',
            'ROLL' => 'ROL',
            'PACK' => 'PCK',
            'PAIL' => 'PAL',
            'DRUM' => 'DRM',
            'BOX' => 'BOX',
            'DUS' => 'BOX',
            'SET' => 'SET',
            'CAN' => 'CAN',
        ];
        return $map[$u] ?? (strlen($u) > 4 ? substr($u, 0, 3) : $u);
    }
}

if (!function_exists('format_number')) {
    /**
     * Format a generic numeric value cleanly using format_quantity formatting.
     */
    function format_number($value, int $decimals = 0): string
    {
        if ($value === null || $value === '') {
            return '0';
        }
        return format_quantity($value);
    }
}



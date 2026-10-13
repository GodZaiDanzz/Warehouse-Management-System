<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeService
{
    protected BarcodeGeneratorSVG $generator;

    public function __construct()
    {
        $this->generator = new BarcodeGeneratorSVG();
    }

    /**
     * Generate inline SVG string for Code 128 barcode (compact and clean).
     */
    public function getBarcodeSvg(string $code, int $widthFactor = 1, int $totalHeight = 32): string
    {
        $code = trim($code);
        if ($code === '') {
            $code = 'ITEM-CODE';
        }

        try {
            $rawSvg = $this->generator->getBarcode($code, BarcodeGeneratorSVG::TYPE_CODE_128, $widthFactor, $totalHeight);
            
            // Remove XML declaration and DOCTYPE so SVG embeds directly in HTML
            $cleanSvg = preg_replace('/<\\?xml.*\\?>/i', '', $rawSvg);
            $cleanSvg = preg_replace('/<!DOCTYPE.*>/i', '', $cleanSvg);
            
            return trim($cleanSvg);
        } catch (\Throwable $e) {
            return '<div class="text-rose-500 font-mono text-xs">ERR: ' . htmlspecialchars($code) . '</div>';
        }
    }
}

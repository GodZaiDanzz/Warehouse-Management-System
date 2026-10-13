<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WmsStandardizationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan Kategori Utama Terdaftar
        $catConsumable = Category::firstOrCreate(['name' => 'Consumable']);
        $catSecondary = Category::firstOrCreate(['name' => 'Secondary Part']);
        $catPainting = Category::firstOrCreate(['name' => 'Cat']);
        $catPrimer = Category::firstOrCreate(['name' => 'Cat - Primer']);
        $catSecond = Category::firstOrCreate(['name' => 'Cat - Second']);
        $catTopCoat = Category::firstOrCreate(['name' => 'Cat - Top Coat']);
        $catThinner = Category::firstOrCreate(['name' => 'Cat - Thinner']);

        // 2. Mapping Item ke Kategori & Konfigurasi Min Stock serta UOM
        $items = Item::all();
        foreach ($items as $item) {
            $nameUpper = strtoupper($item->name);
            $codeUpper = strtoupper($item->code_item ?? '');
            $catId = $catConsumable->id;
            $minStock = $item->min_stock > 0 ? $item->min_stock : 20;
            $wholesaleUnit = $item->wholesale_unit;
            $conversionFactor = $item->conversion_factor ?: 1;
            $mfgDate = $item->manufacturing_date;

            // Kategori Cat Spesifik
            if (str_contains($nameUpper, 'THINNER') || str_contains($codeUpper, 'CAT-THN')) {
                $catId = $catThinner->id;
                $minStock = 10;
                if (!$mfgDate) $mfgDate = '2026-08-01';
            } elseif (str_contains($nameUpper, 'PRIMER') || str_contains($codeUpper, 'CAT-PRM')) {
                $catId = $catPrimer->id;
                $minStock = 5;
                if (!$mfgDate) $mfgDate = '2026-08-31';
            } elseif (str_contains($nameUpper, 'SECOND') || str_contains($nameUpper, 'PF COATING') || str_contains($codeUpper, 'CAT-SEC')) {
                $catId = $catSecond->id;
                $minStock = 10;
                if (!$mfgDate) $mfgDate = '2025-12-15';
            } elseif (str_contains($nameUpper, 'TOP COAT') || str_contains($nameUpper, 'PU FINISH') || str_contains($codeUpper, 'CAT-TOP')) {
                $catId = $catTopCoat->id;
                $minStock = 5;
                if (!$mfgDate) $mfgDate = '2026-06-20';
            } elseif (
                str_contains($nameUpper, 'CAT ') ||
                str_contains($nameUpper, 'PAINT') ||
                str_contains($nameUpper, 'COATING') ||
                str_contains($nameUpper, 'COATING') ||
                str_contains($nameUpper, 'CLEAR COAT') ||
                str_contains($nameUpper, 'EPOXY')
            ) {
                $catId = $catPainting->id;
                $minStock = 20;
                if (!$mfgDate) {
                    $mfgDate = '2026-01-15';
                }
                if (!$wholesaleUnit && in_array(strtoupper($item->unit), ['CAN', 'KALENG', 'LITER'])) {
                    $wholesaleUnit = 'Pail';
                    $conversionFactor = 20; // 1 Pail = 20 Liter / Kaleng
                }
            }
            // Kategori Secondary Part
            elseif (
                str_contains($nameUpper, 'SPAREPART') ||
                str_contains($nameUpper, 'BEARING') ||
                str_contains($nameUpper, 'NOZZLE') ||
                str_contains($nameUpper, 'VALVE') ||
                str_contains($nameUpper, 'BOLT') ||
                str_contains($nameUpper, 'NUT') ||
                str_contains($nameUpper, 'SEAL') ||
                str_contains($nameUpper, 'FLANGE') ||
                str_contains($nameUpper, 'ROTOR') ||
                str_contains($nameUpper, 'STATOR') ||
                str_contains($nameUpper, 'FUSE') ||
                str_contains($nameUpper, 'SWITCH') ||
                str_contains($nameUpper, 'CONNECTOR')
            ) {
                $catId = $catSecondary->id;
                $minStock = 15;
            }
            // Kategori Consumable
            else {
                $catId = $catConsumable->id;
            }

            // ATURAN SPESIFIK BATAS MINIMAL STOK (REORDER POINT) SESUAI SPESIFIKASI:
            // 1. Batu Gerinda Poles 6 Inch: Minimal 150 pcs
            if (str_contains($nameUpper, 'GERINDA') && str_contains($nameUpper, 'POLES') && str_contains($nameUpper, '6')) {
                $minStock = 150;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 25;
            }
            // 2. Batu Gerinda (semua jenis lainnya): Minimal 200 pcs
            elseif (str_contains($nameUpper, 'GERINDA') || str_contains($nameUpper, 'GRINDING')) {
                $minStock = 200;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 50;
            }
            // 3. Masker: Minimal 5 box (isi 50 pcs/box) = 250 pcs
            elseif (str_contains($nameUpper, 'MASKER')) {
                $minStock = 250;
                $wholesaleUnit = 'Box';
                $conversionFactor = 50;
            }
            // 4. Sarung Tangan Katun: Minimal 6 lusin (72 pasang)
            elseif (str_contains($nameUpper, 'SARUNG TANGAN')) {
                $minStock = 72;
                $wholesaleUnit = 'Lusin';
                $conversionFactor = 12;
            }
            // 5. Kawat Las FCAW: Minimal 15 roll
            elseif (str_contains($nameUpper, 'KAWAT LAS') && str_contains($nameUpper, 'FCAW')) {
                $minStock = 15;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 1;
            }
            // 6. Kawat Las Solid: Minimal 10 roll
            elseif (str_contains($nameUpper, 'KAWAT LAS') && str_contains($nameUpper, 'SOLID')) {
                $minStock = 10;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 1;
            }
            // 7. Kawat Las 6013 2,6 mm: Minimal 25 kg
            elseif (str_contains($nameUpper, '6013') && (str_contains($nameUpper, '2.6') || str_contains($nameUpper, '2,6'))) {
                $minStock = 25;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 20; // 1 Dus = 20 Kg (4 pak @ 5kg)
            }
            // 8. Kawat Las 6013 3,2 mm: Minimal 30 kg
            elseif (str_contains($nameUpper, '6013') && (str_contains($nameUpper, '3.2') || str_contains($nameUpper, '3,2'))) {
                $minStock = 30;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 20;
            }
            // 9. Kawat Las 7018 3,2 mm: Minimal 30 kg
            elseif (str_contains($nameUpper, '7018') && (str_contains($nameUpper, '3.2') || str_contains($nameUpper, '3,2'))) {
                $minStock = 30;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 20;
            }
            // 10. Kawat Las 308 2,6 mm: Minimal 10 kg
            elseif (str_contains($nameUpper, '308') && (str_contains($nameUpper, '2.6') || str_contains($nameUpper, '2,6'))) {
                $minStock = 10;
                $wholesaleUnit = 'Dus';
                $conversionFactor = 5;
            }
            // Default item lainnya minimal 20 pcs
            elseif ($minStock < 15) {
                $minStock = 20;
            }

            $item->update([
                'category_id' => $catId,
                'min_stock' => $minStock,
                'wholesale_unit' => $wholesaleUnit,
                'conversion_factor' => $conversionFactor,
                'manufacturing_date' => $mfgDate,
            ]);
        }

        // 3. Pastikan Data Dummy Cat (Novalux Primer, Second, Top Coat, Thinner) Terdaftar Sesuai Spreadsheet
        $paintItems = [
            // Primer
            [
                'code_item' => 'CAT-PRM-5114',
                'name' => 'Novalux Epoxy Primer 511-4 Dark Grey 017 (Hardener 8920)',
                'stock' => 11,
                'unit' => 'Galon',
                'min_stock' => 5,
                'location' => 'Gudang Cat - Rak A-01 (Primer)',
                'manufacturing_date' => '2026-08-31',
                'category_id' => $catPrimer->id,
            ],
            // Second
            [
                'code_item' => 'CAT-SEC-554-01',
                'name' => 'Novalux Epoxy PF Coating 554 Light Grey 021 (Hardener 8920) - Batch 05/12/25',
                'stock' => 51,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak B-01 (Second)',
                'manufacturing_date' => '2025-12-05',
                'category_id' => $catSecond->id,
            ],
            [
                'code_item' => 'CAT-SEC-554-02',
                'name' => 'Novalux Epoxy PF Coating 554 Light Grey 021 (Hardener 8920) - Batch 08/12/25',
                'stock' => 11,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak B-01 (Second)',
                'manufacturing_date' => '2025-12-08',
                'category_id' => $catSecond->id,
            ],
            [
                'code_item' => 'CAT-SEC-554-03',
                'name' => 'Novalux Epoxy PF Coating 554 Light Grey 021 (Hardener 8920) - Batch 16/12/25',
                'stock' => 20,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak B-02 (Second)',
                'manufacturing_date' => '2025-12-16',
                'category_id' => $catSecond->id,
            ],
            [
                'code_item' => 'CAT-SEC-554-04',
                'name' => 'Novalux Epoxy PF Coating 554 Light Grey 021 (Hardener 8920) - Batch 17/12/25',
                'stock' => 35,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak B-02 (Second)',
                'manufacturing_date' => '2025-12-17',
                'category_id' => $catSecond->id,
            ],
            [
                'code_item' => 'CAT-SEC-554-05',
                'name' => 'Novalux Epoxy PF Coating 554 Light Grey 021 (Hardener 8920) - Batch 08/06/26',
                'stock' => 48,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak B-03 (Second)',
                'manufacturing_date' => '2026-06-08',
                'category_id' => $catSecond->id,
            ],
            [
                'code_item' => 'CAT-SEC-554-06',
                'name' => 'Novalux Epoxy PF Coating 554 Light Grey 021 (Hardener 8920) - Batch 14/07/26',
                'stock' => 28,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak B-03 (Second)',
                'manufacturing_date' => '2026-07-14',
                'category_id' => $catSecond->id,
            ],
            // Top Coat
            [
                'code_item' => 'CAT-TOP-7441-DG',
                'name' => 'Novalux PU Finish 7441 Dark Green RIAL 5730 PPLI (Hardener 8970)',
                'stock' => 9,
                'unit' => 'Galon',
                'min_stock' => 5,
                'location' => 'Gudang Cat - Rak C-01 (Top Coat)',
                'manufacturing_date' => '2026-06-22',
                'category_id' => $catTopCoat->id,
            ],
            [
                'code_item' => 'CAT-TOP-7441-LG',
                'name' => 'Novalux PU Finish 7441 Light Grey RIAL 7035 (Hardener 8970)',
                'stock' => 9,
                'unit' => 'Galon',
                'min_stock' => 5,
                'location' => 'Gudang Cat - Rak C-01 (Top Coat)',
                'manufacturing_date' => '2024-07-23',
                'category_id' => $catTopCoat->id,
            ],
            [
                'code_item' => 'CAT-TOP-7441-W01',
                'name' => 'Novalux PU Finish 7441 White 1000 (Hardener 8970) - Batch 20/02/26',
                'stock' => 3,
                'unit' => 'Galon',
                'min_stock' => 5,
                'location' => 'Gudang Cat - Rak C-02 (Top Coat)',
                'manufacturing_date' => '2026-02-20',
                'category_id' => $catTopCoat->id,
            ],
            [
                'code_item' => 'CAT-TOP-7441-W02',
                'name' => 'Novalux PU Finish 7441 White 1000 (Hardener 8970) - Batch 20/06/25',
                'stock' => 1,
                'unit' => 'Galon',
                'min_stock' => 5,
                'location' => 'Gudang Cat - Rak C-02 (Top Coat)',
                'manufacturing_date' => '2025-06-20',
                'category_id' => $catTopCoat->id,
            ],
            [
                'code_item' => 'CAT-TOP-7441-W03',
                'name' => 'Novalux PU Finish 7441 White 1000 (Hardener 8970) - Batch 07/05/26',
                'stock' => 8,
                'unit' => 'Galon',
                'min_stock' => 5,
                'location' => 'Gudang Cat - Rak C-02 (Top Coat)',
                'manufacturing_date' => '2026-05-07',
                'category_id' => $catTopCoat->id,
            ],
            [
                'code_item' => 'CAT-TOP-7441-JB',
                'name' => 'Novalux PU Finish 7441 Jet Black Ral Goar (Hardener 8970)',
                'stock' => 2,
                'unit' => 'Galon',
                'min_stock' => 5,
                'location' => 'Gudang Cat - Rak C-03 (Top Coat)',
                'manufacturing_date' => '2026-08-28',
                'category_id' => $catTopCoat->id,
            ],
            // Thinner
            [
                'code_item' => 'CAT-THN-339',
                'name' => 'Novalux Thinner 339',
                'stock' => 34,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak D-01 (Solvent)',
                'manufacturing_date' => '2026-08-01',
                'category_id' => $catThinner->id,
            ],
            [
                'code_item' => 'CAT-THN-664',
                'name' => 'Novalux Thinner 664',
                'stock' => 6,
                'unit' => 'Galon',
                'min_stock' => 10,
                'location' => 'Gudang Cat - Rak D-01 (Solvent)',
                'manufacturing_date' => '2026-08-01',
                'category_id' => $catThinner->id,
            ],
        ];

        foreach ($paintItems as $p) {
            Item::updateOrCreate(['code_item' => $p['code_item']], $p);
        }

        // 4. Pastikan Akun Demo Role Tersedia
        User::updateOrCreate(
            ['email' => 'warehouse@mudalaya.com'],
            [
                'name' => 'Tim Warehouse',
                'password' => Hash::make('password'),
                'role' => 'warehouse',
            ]
        );

        User::updateOrCreate(
            ['email' => 'ppic@mudalaya.com'],
            [
                'name' => 'PPIC Staff',
                'password' => Hash::make('password'),
                'role' => 'ppic',
            ]
        );

        User::updateOrCreate(
            ['email' => 'qc@mudalaya.com'],
            [
                'name' => 'QC Staff',
                'password' => Hash::make('password'),
                'role' => 'qc',
            ]
        );
    }
}

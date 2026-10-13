<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $mech = Category::where('name', 'Secondary Part')->first() ?? Category::first();
        $elec = Category::where('name', 'Secondary Part')->first() ?? Category::first();
        $safe = Category::where('name', 'Consumable')->first() ?? Category::first();
        $tool = Category::where('name', 'Consumable')->first() ?? Category::first();

        $items = [
            // Mechanical & Piping
            [
                'category_id' => $mech->id,
                'code_item' => 'MECH-001',
                'name' => 'Ball Valve 2 Inch ANSI 150 RF',
                'stock' => 45,
                'unit' => 'Pcs',
                'min_stock' => 10,
                'location' => 'Rak A-01 (Mekanikal)',
            ],
            [
                'category_id' => $mech->id,
                'code_item' => 'MECH-002',
                'name' => 'Flange 3 Inch Carbon Steel ANSI 150',
                'stock' => 80,
                'unit' => 'Pcs',
                'min_stock' => 15,
                'location' => 'Rak A-02 (Mekanikal)',
            ],
            [
                'category_id' => $mech->id,
                'code_item' => 'MECH-003',
                'name' => 'Globe Valve 4 Inch Class 300',
                'stock' => 12,
                'unit' => 'Pcs',
                'min_stock' => 5,
                'location' => 'Rak A-03 (Mekanikal)',
            ],
            [
                'category_id' => $mech->id,
                'code_item' => 'MECH-004',
                'name' => 'Pipe Seamless 4 Inch Sch 40 ASTM A106',
                'stock' => 150,
                'unit' => 'Meter',
                'min_stock' => 30,
                'location' => 'Area Gudang Terbuka B-1',
            ],
            [
                'category_id' => $mech->id,
                'code_item' => 'MECH-005',
                'name' => 'Gasket Spiral Wound 3 Inch 150#',
                'stock' => 200,
                'unit' => 'Pcs',
                'min_stock' => 50,
                'location' => 'Rak A-04 (Mekanikal)',
            ],

            // Electrical & Instrumentation
            [
                'category_id' => $elec->id,
                'code_item' => 'ELEC-001',
                'name' => 'Kabel Power XLPE 3x2.5mm',
                'stock' => 25,
                'unit' => 'Roll',
                'min_stock' => 5,
                'location' => 'Rak B-01 (Elektrikal)',
            ],
            [
                'category_id' => $elec->id,
                'code_item' => 'ELEC-002',
                'name' => 'Pressure Gauge 0-10 Bar Stainless Steel',
                'stock' => 30,
                'unit' => 'Pcs',
                'min_stock' => 8,
                'location' => 'Rak B-02 (Instrumen)',
            ],
            [
                'category_id' => $elec->id,
                'code_item' => 'ELEC-003',
                'name' => 'Transmitter Tekanan Yokogawa EJA110A',
                'stock' => 8,
                'unit' => 'Set',
                'min_stock' => 2,
                'location' => 'Rak B-03 (Instrumen)',
            ],
            [
                'category_id' => $elec->id,
                'code_item' => 'ELEC-004',
                'name' => 'Multi Meter Digital Fluke 87V',
                'stock' => 10,
                'unit' => 'Set',
                'min_stock' => 3,
                'location' => 'Rak B-04 (Tools Elek)',
            ],

            // Safety & APD
            [
                'category_id' => $safe->id,
                'code_item' => 'SAFE-001',
                'name' => 'Sepatu Safety Kings Size 42',
                'stock' => 60,
                'unit' => 'Pair',
                'min_stock' => 15,
                'location' => 'Rak APD-01 (Lantai 1)',
            ],
            [
                'category_id' => $safe->id,
                'code_item' => 'SAFE-002',
                'name' => 'Helm Safety V-Gard MSA Putih',
                'stock' => 100,
                'unit' => 'Pcs',
                'min_stock' => 20,
                'location' => 'Rak APD-02 (Lantai 1)',
            ],
            [
                'category_id' => $safe->id,
                'code_item' => 'SAFE-003',
                'name' => 'Sarung Tangan Las Kulit 16 Inch',
                'stock' => 120,
                'unit' => 'Pair',
                'min_stock' => 25,
                'location' => 'Rak APD-03 (Lantai 1)',
            ],
            [
                'category_id' => $safe->id,
                'code_item' => 'SAFE-004',
                'name' => 'APAR Gunnebo Powder 6 Kg',
                'stock' => 15,
                'unit' => 'Unit',
                'min_stock' => 4,
                'location' => 'Area K3 Safety A-1',
            ],

            // Tools & Consumables
            [
                'category_id' => $tool->id,
                'code_item' => 'TOOL-001',
                'name' => 'Kawat Las Kobelco LB-52U 3.2mm',
                'stock' => 75,
                'unit' => 'Box',
                'min_stock' => 20,
                'location' => 'Rak Consumable C-01',
            ],
            [
                'category_id' => $tool->id,
                'code_item' => 'TOOL-002',
                'name' => 'Batu Potong / Grinding Wheel 4 Inch Resibon',
                'stock' => 350,
                'unit' => 'Pcs',
                'min_stock' => 50,
                'location' => 'Rak Consumable C-02',
            ],
            [
                'category_id' => $tool->id,
                'code_item' => 'TOOL-003',
                'name' => 'Baut & Mur High Tensile A325 M16x60',
                'stock' => 500,
                'unit' => 'Set',
                'min_stock' => 100,
                'location' => 'Rak Fastener C-03',
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['code_item' => $item['code_item']], $item);
        }
    }
}

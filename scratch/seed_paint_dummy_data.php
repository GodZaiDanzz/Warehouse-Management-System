<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\StockLog;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== MEMULAI PEMBUATAN DATA DUMMY CAT & BAHAN KIMIA ===\n";

DB::transaction(function () {
    $admin = User::where('role', 'admin')->first() ?? User::first();
    $today = Carbon::today();

    // Pastikan kategori tersedia
    $catPrimer = Category::firstOrCreate(['name' => 'Cat - Primer']);
    $catTopCoat = Category::firstOrCreate(['name' => 'Cat - Top Coat']);
    $catThinner = Category::firstOrCreate(['name' => 'Cat - Thinner']);
    $catGeneral = Category::firstOrCreate(['name' => 'Cat']);

    // Pastikan proyek tersedia
    $projPainting = Project::where('name', 'like', '%PAINTING%')->first() 
        ?? Project::firstOrCreate(['name' => 'PPLI - SANDBLAS & PAINTING MUD TANK 3', 'status' => 'active']);
    $projAuger = Project::where('name', 'like', '%COVER SCREW%')->first() 
        ?? Project::firstOrCreate(['name' => 'PLI.036 - SANDBLASTING & PAINTING COVER SCREW AUGER', 'status' => 'active']);
    $projPallet = Project::where('name', 'like', '%PALLET THINNER%')->first() 
        ?? Project::firstOrCreate(['name' => 'PLI.015 - PALLET THINNER (RO)', 'status' => 'active']);

    // 1. Update Existing Items jika ada
    $existingItems = [
        'CAT UNICOAT' => [
            'stock' => 25,
            'unit' => 'Can (5L)',
            'category_id' => $catGeneral->id,
            'expiry_date' => $today->copy()->addMonths(8)->format('Y-m-d'), // Aman
        ],
        'CAT UNICOAT GALVANIZED' => [
            'stock' => 14,
            'unit' => 'Can (5L)',
            'category_id' => $catGeneral->id,
            'expiry_date' => $today->copy()->addDays(20)->format('Y-m-d'), // Near Expiry (20 Hari)
        ],
        'THINNER' => [
            'stock' => 45,
            'unit' => 'Liter',
            'category_id' => $catThinner->id,
            'expiry_date' => $today->copy()->addYears(2)->format('Y-m-d'), // Aman
        ],
    ];

    foreach ($existingItems as $name => $data) {
        $item = Item::where('name', $name)->first();
        if ($item) {
            $item->update($data);
            echo "Updated existing item: {$name} (Expiry: {$data['expiry_date']})\n";
        }
    }

    // 2. Definisi Item Cat Baru dengan Visual Status Kadaluarsa Beragam
    $paintList = [
        [
            'code_item' => 'CAT-PRI-001',
            'name' => 'Jotun Penguard Primer Red Oxide (20L)',
            'category_id' => $catPrimer->id,
            'stock' => 40,
            'unit' => 'Pail (20L)',
            'min_stock' => 10,
            'mfg_date' => $today->copy()->subMonths(3)->format('Y-m-d'),
            'expiry_date' => $today->copy()->addMonths(9)->format('Y-m-d'), // AMAN (> 30 hari)
            'supplier' => 'PT Jotun Indonesia',
            'in_qty' => 55,
            'out_qty' => 15,
            'out_project' => $projPainting,
            'last_out_days' => 5, // Bergerak aktif
        ],
        [
            'code_item' => 'CAT-TOP-002',
            'name' => 'Jotun Hardtop AX Polyurethane Grey 7035 (20L)',
            'category_id' => $catTopCoat->id,
            'stock' => 22,
            'unit' => 'Pail (20L)',
            'min_stock' => 8,
            'mfg_date' => $today->copy()->subMonths(11)->format('Y-m-d'),
            'expiry_date' => $today->copy()->addDays(18)->format('Y-m-d'), // MENDEKATI KADALUARSA (18 Hari)
            'supplier' => 'PT Jotun Indonesia',
            'in_qty' => 30,
            'out_qty' => 8,
            'out_project' => $projAuger,
            'last_out_days' => 12, // Bergerak aktif
        ],
        [
            'code_item' => 'CAT-PRI-003',
            'name' => 'Carboline Carbozinc 11 Inorganic Zinc Primer (5L)',
            'category_id' => $catPrimer->id,
            'stock' => 12,
            'unit' => 'Galon (5L)',
            'min_stock' => 5,
            'mfg_date' => $today->copy()->subMonths(13)->format('Y-m-d'),
            'expiry_date' => $today->copy()->subDays(14)->format('Y-m-d'), // SUDAH KADALUARSA (-14 Hari)
            'supplier' => 'PT Carboline Indonesia',
            'in_qty' => 12,
            'out_qty' => 0,
            'out_project' => null,
            'last_out_days' => 220, // DEAD STOCK (> 180 hari)
        ],
        [
            'code_item' => 'CAT-PRI-004',
            'name' => 'SigmaCover 280 Universal Epoxy Primer Green (20L)',
            'category_id' => $catPrimer->id,
            'stock' => 28,
            'unit' => 'Pail (20L)',
            'min_stock' => 6,
            'mfg_date' => $today->copy()->subMonths(11)->subDays(25)->format('Y-m-d'),
            'expiry_date' => $today->copy()->addDays(6)->format('Y-m-d'), // SANGAT KRITIS (6 Hari Lagi)
            'supplier' => 'PT PPG Coatings Indonesia',
            'in_qty' => 38,
            'out_qty' => 10,
            'out_project' => $projPainting,
            'last_out_days' => 100, // SLOW MOVING (100 hari)
        ],
        [
            'code_item' => 'CAT-TOP-005',
            'name' => 'International Interthane 990 Signal Red (20L)',
            'category_id' => $catTopCoat->id,
            'stock' => 35,
            'unit' => 'Pail (20L)',
            'min_stock' => 10,
            'mfg_date' => $today->copy()->subMonths(2)->format('Y-m-d'),
            'expiry_date' => $today->copy()->addMonths(14)->format('Y-m-d'), // AMAN (> 1 tahun)
            'supplier' => 'PT AkzoNobel International Paint',
            'in_qty' => 35,
            'out_qty' => 0,
            'out_project' => null,
            'last_out_days' => 15,
        ],
        [
            'code_item' => 'CAT-THI-006',
            'name' => 'Jotun Thinner No. 17 Epoxy Thinner (20L)',
            'category_id' => $catThinner->id,
            'stock' => 50,
            'unit' => 'Pail (20L)',
            'min_stock' => 15,
            'mfg_date' => $today->copy()->subMonths(4)->format('Y-m-d'),
            'expiry_date' => $today->copy()->addYears(2)->format('Y-m-d'), // AMAN
            'supplier' => 'PT Jotun Indonesia',
            'in_qty' => 70,
            'out_qty' => 20,
            'out_project' => $projPainting,
            'last_out_days' => 4,
        ],
        [
            'code_item' => 'CAT-THI-007',
            'name' => 'Carboline Thinner #25 Synthetic Thinner (5L)',
            'category_id' => $catThinner->id,
            'stock' => 18,
            'unit' => 'Can (5L)',
            'min_stock' => 5,
            'mfg_date' => $today->copy()->subMonths(8)->format('Y-m-d'),
            'expiry_date' => $today->copy()->addMonths(16)->format('Y-m-d'), // AMAN tapi DEAD STOCK
            'supplier' => 'PT Carboline Indonesia',
            'in_qty' => 20,
            'out_qty' => 2,
            'out_project' => $projPallet,
            'last_out_days' => 215, // DEAD STOCK (> 180 hari tidak keluar)
        ],
    ];

    foreach ($paintList as $idx => $p) {
        // Cari atau buat Item
        $item = Item::updateOrCreate(
            ['code_item' => $p['code_item']],
            [
                'category_id' => $p['category_id'],
                'name' => $p['name'],
                'stock' => $p['stock'],
                'unit' => $p['unit'],
                'min_stock' => $p['min_stock'],
                'expiry_date' => $p['expiry_date'],
            ]
        );

        echo "Created/Updated Item: {$item->name} | Expiry: {$item->expiry_date} ({$item->expiry_status})\n";

        // Material In (Penerimaan)
        $inDate = $today->copy()->subDays(max(15, $p['last_out_days'] + 10));
        $matIn = MaterialIn::create([
            'item_id' => $item->id,
            'user_id' => $admin->id,
            'supplier' => $p['supplier'],
            'surat_jalan_number' => 'SJ-PAINT-' . $today->format('Y') . '-' . sprintf('%03d', $idx + 1),
            'received_by' => 'Staff Warehouse MEI',
            'date' => $inDate->format('Y-m-d'),
            'quantity' => $p['in_qty'],
            'unit' => $p['unit'],
            'manufacturing_date' => $p['mfg_date'],
            'expiry_date' => $p['expiry_date'],
            'qc_status' => 'approved',
            'notes' => 'Batch penerimaan cat pabrikan resmi, lulus uji visual & sertifikat COA.',
        ]);

        StockLog::create([
            'reference_number' => $matIn->reference_number ?? ('IN-' . $inDate->format('Ymd') . '-' . $idx),
            'item_id' => $item->id,
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => $p['in_qty'],
            'recipient_or_provider' => $p['supplier'],
            'notes' => 'Penerimaan Material Inbound: ' . $p['name'],
            'created_at' => $inDate,
            'updated_at' => $inDate,
        ]);

        // Material Out (Pengeluaran Proyek jika ada)
        if ($p['out_qty'] > 0 && $p['out_project']) {
            $outDate = $today->copy()->subDays($p['last_out_days']);
            $matOut = MaterialOut::create([
                'item_id' => $item->id,
                'project_id' => $p['out_project']->id,
                'project' => $p['out_project']->name,
                'user_id' => $admin->id,
                'date' => $outDate->format('Y-m-d'),
                'quantity' => $p['out_qty'],
                'unit' => $p['unit'],
                'pic' => 'Budi (Supervisor Painting)',
                'notes' => 'Pengeluaran cat untuk blasting & coating proyek ' . $p['out_project']->name,
            ]);

            StockLog::create([
                'reference_number' => $matOut->reference_number ?? ('OUT-' . $outDate->format('Ymd') . '-' . $idx),
                'item_id' => $item->id,
                'user_id' => $admin->id,
                'type' => 'out',
                'quantity' => $p['out_qty'],
                'recipient_or_provider' => 'Team Painting - ' . $p['out_project']->name,
                'notes' => 'Pengeluaran Cat: ' . $p['name'],
                'created_at' => $outDate,
                'updated_at' => $outDate,
            ]);
        }
    }

    echo "=== SEEDING DUMMY CAT BERHASIL SELESAI ===\n";
});

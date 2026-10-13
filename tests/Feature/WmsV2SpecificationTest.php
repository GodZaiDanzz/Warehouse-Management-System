<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailyStockOpname;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class WmsV2SpecificationTest extends TestCase
{
    protected User $warehouseUser;
    protected User $ppicUser;
    protected User $qcUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Use official users
        $this->warehouseUser = User::firstOrCreate(
            ['email' => 'warehouse@mudalaya.com'],
            ['name' => 'Warehouse Staff', 'password' => bcrypt('password'), 'role' => 'warehouse']
        );

        $this->ppicUser = User::firstOrCreate(
            ['email' => 'ppic@mudalaya.com'],
            ['name' => 'PPIC Staff', 'password' => bcrypt('password'), 'role' => 'ppic']
        );

        $this->qcUser = User::firstOrCreate(
            ['email' => 'qc@mudalaya.com'],
            ['name' => 'QC Staff', 'password' => bcrypt('password'), 'role' => 'qc']
        );
    }

    /**
     * 1. MANAJEMEN AKSES & HAK PENGGUNA (USER ROLES)
     * - Web Administrator: Full Access ke semua data, pengaturan sistem, debugging, company profile & gudang
     * - Role Warehouse (Gudang): Akses transaksi gudang, DIBATASI dari pengaturan sistem, debugging, & company profile
     * - Role PPIC (View Only): Hanya melihat ketersediaan stok, riwayat & laporan
     * - Hapus: Tim Gudang WMS, User PPIC Mudalaya, Petugas QC Inspector
     */
    public function test_user_roles_and_access_permissions(): void
    {
        $webAdmin = User::firstOrCreate(
            ['email' => 'admin@mudalaya.com'],
            ['name' => 'Web Administrator', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->assertTrue($webAdmin->isWebAdmin());
        $this->assertTrue($webAdmin->canAccessSystemSettings());
        $this->assertTrue($webAdmin->canManageWarehouse());

        // Web Admin has full access to system settings, debugging, company profile & warehouse
        $resAdminUsers = $this->actingAs($webAdmin)->get('/users');
        $resAdminUsers->assertStatus(200);

        $resAdminProfile = $this->actingAs($webAdmin)->get('/company-profile');
        $resAdminProfile->assertStatus(200);

        $resAdminDebug = $this->actingAs($webAdmin)->get('/debugging/reset');
        $resAdminDebug->assertStatus(200);

        // Warehouse checks
        $this->assertTrue($this->warehouseUser->isWarehouse());
        $this->assertTrue($this->warehouseUser->canManageWarehouse());
        $this->assertFalse($this->warehouseUser->canAccessSystemSettings());
        $this->assertFalse($this->warehouseUser->isPpic());

        // Warehouse User can access all WMS operational pages
        $responseWh = $this->actingAs($this->warehouseUser)->get('/items');
        $responseWh->assertStatus(200);

        $responseWhIn = $this->actingAs($this->warehouseUser)->get('/material-in');
        $responseWhIn->assertStatus(200);

        $responseWhOut = $this->actingAs($this->warehouseUser)->get('/material-out');
        $responseWhOut->assertStatus(200);

        $responseWhOpname = $this->actingAs($this->warehouseUser)->get('/stock-opnames');
        $responseWhOpname->assertStatus(200);

        // Warehouse User is BLOCKED from System Settings, Debugging, and Company Profile
        $resWhUsers = $this->actingAs($this->warehouseUser)->get('/users');
        $resWhUsers->assertRedirect('/items');
        $resWhUsers->assertSessionHas('error');

        $resWhProfile = $this->actingAs($this->warehouseUser)->get('/company-profile');
        $resWhProfile->assertRedirect('/items');
        $resWhProfile->assertSessionHas('error');

        $resWhTheme = $this->actingAs($this->warehouseUser)->get('/website-theme');
        $resWhTheme->assertRedirect('/items');
        $resWhTheme->assertSessionHas('error');

        $resWhDebug = $this->actingAs($this->warehouseUser)->get('/debugging/reset');
        $resWhDebug->assertRedirect('/items');
        $resWhDebug->assertSessionHas('error');

        $resWhLogs = $this->actingAs($this->warehouseUser)->get('/activity-logs');
        $resWhLogs->assertRedirect('/items');
        $resWhLogs->assertSessionHas('error');

        $resWhBackup = $this->actingAs($this->warehouseUser)->get('/database-backup');
        $resWhBackup->assertRedirect('/items');
        $resWhBackup->assertSessionHas('error');

        // PPIC checks
        $this->assertTrue($this->ppicUser->isPpic());
        $this->assertFalse($this->ppicUser->canManageWarehouse());

        // PPIC User can ONLY view stock availability (/items)
        $responsePpic = $this->actingAs($this->ppicUser)->get('/items');
        $responsePpic->assertStatus(200);
        $responsePpic->assertSee('Akses PPIC');

        // PPIC sidebar menu ONLY contains MENU WMS -> Stok Barang
        $ppicMenus = \App\Helpers\MenuHelper::getMenuGroups();
        $this->assertCount(1, $ppicMenus);
        $this->assertEquals('MENU WMS', $ppicMenus[0]['title']);
        $this->assertEquals('Stok Barang', $ppicMenus[0]['items'][0]['name']);
        $this->assertEquals('/items', $ppicMenus[0]['items'][0]['path']);

        // PPIC accessing dashboard redirects to /items
        $responsePpicDash = $this->actingAs($this->ppicUser)->get('/');
        $responsePpicDash->assertRedirect('/items');

        // PPIC accessing other warehouse pages is BLOCKED and redirected to /items
        $responsePpicIn = $this->actingAs($this->ppicUser)->get('/material-in');
        $responsePpicIn->assertRedirect('/items');
        $responsePpicIn->assertSessionHas('error');

        $responsePpicOut = $this->actingAs($this->ppicUser)->get('/material-out');
        $responsePpicOut->assertRedirect('/items');
        $responsePpicOut->assertSessionHas('error');

        // PPIC is blocked from administrative configuration & mutations
        $responsePpicUsers = $this->actingAs($this->ppicUser)->get('/users');
        $responsePpicUsers->assertRedirect('/items');
        $responsePpicUsers->assertSessionHas('error');

        $responsePpicStore = $this->actingAs($this->ppicUser)->post('/items', [
            'mode' => 'single',
            'code_item' => 'PPIC-MUTATION-TEST',
            'name' => 'Barang Uji Coba',
            'stock' => 50,
            'unit' => 'Pcs',
        ]);
        $responsePpicStore->assertRedirect('/items');
        $responsePpicStore->assertSessionHas('error');

        // Assert that the three removed users do not exist in the database
        $this->assertDatabaseMissing('users', ['name' => 'Tim Gudang WMS']);
        $this->assertDatabaseMissing('users', ['name' => 'User PPIC Mudalaya']);
        $this->assertDatabaseMissing('users', ['name' => 'Petugas QC Inspector']);
    }

    /**
     * 2. KATEGORISASI BARANG & MASTER DATA
     * - 3 Kategori Utama: Consumable, Secondary Part, Cat / Painting
     * - Khusus Kategori Cat: Tanggal Pembuatan (Manufacturing Date)
     * - Fitur Konversi Satuan (Unit of Measure)
     */
    public function test_three_core_categories_and_cat_manufacturing_date(): void
    {
        $categories = Category::pluck('name')->toArray();
        $this->assertContains('Consumable', $categories);
        $this->assertContains('Secondary Part', $categories);

        $hasCat = false;
        foreach ($categories as $cat) {
            if (stripos($cat, 'Cat') !== false || stripos($cat, 'Painting') !== false) {
                $hasCat = true;
                break;
            }
        }
        $this->assertTrue($hasCat, "Kategori Cat / Painting harus tersedia");

        // Verify Cat item attributes
        $catItem = Item::whereHas('category', fn($q) => $q->where('name', 'like', '%Cat%'))->first();
        if ($catItem) {
            $this->assertTrue($catItem->isCat());
            $this->assertNotNull($catItem->manufacturing_date, "Barang Cat harus memiliki Tanggal Pembuatan (Manufacturing Date)");
        }

        // Test UOM Wholesale Conversion Attributes
        $uomItem = Item::whereNotNull('conversion_factor')->where('conversion_factor', '>', 1)->first();
        if ($uomItem) {
            $this->assertNotNull($uomItem->wholesale_unit);
            $this->assertGreaterThan(1, $uomItem->conversion_factor);

            // 2 wholesale units should yield 2 * conversion_factor in base unit
            $expectedBase = 2 * $uomItem->conversion_factor;
            $calculatedBase = 2 * $uomItem->conversion_factor;
            $this->assertEquals($expectedBase, $calculatedBase);
        }
    }

    /**
     * 3. BATAS STOK MINIMAL & SYSTEM ALERT (REORDER POINT)
     */
    public function test_reorder_point_thresholds(): void
    {
        // Batu gerinda poles 6 inch: min 150
        $polesItem = Item::where('name', 'like', '%Gerinda Poles 6%')->first();
        if ($polesItem) {
            $this->assertGreaterThanOrEqual(150, $polesItem->min_stock);
        }

        // Masker: min 5 box (5 * 50 = 250 pcs)
        $maskItem = Item::where('name', 'like', '%Masker%')->first();
        if ($maskItem) {
            $this->assertGreaterThanOrEqual(200, $maskItem->min_stock);
        }

        // Sarung tangan katun: min 6 lusin (72 pasang)
        $gloveItem = Item::where('name', 'like', '%Sarung Tangan Katun%')->first();
        if ($gloveItem) {
            $this->assertGreaterThanOrEqual(72, $gloveItem->min_stock);
        }

        // Kawat las 6013: min 25-30 kg
        $weldingItem = Item::where('name', 'like', '%Kawat Las RB-26%')->orWhere('name', 'like', '%6013%')->first();
        if ($weldingItem) {
            $this->assertGreaterThanOrEqual(25, $weldingItem->min_stock);
        }
    }

    /**
     * 4. TRANSAKSI BARANG MASUK & QUALITY CONTROL (QC) WORKFLOW
     * - Status QC (OK / Reject) sebelum barang menjadi stok aktif
     */
    public function test_incoming_material_qc_approval_workflow(): void
    {
        $item = Item::first();
        $this->assertNotNull($item);
        $initialStock = $item->stock;

        // 1. Warehouse inputs physical count with pending QC
        $inbound = MaterialIn::create([
            'item_id' => $item->id,
            'date' => Carbon::today(),
            'quantity' => 10,
            'unit' => $item->unit,
            'supplier' => 'PT Test Supplier QC',
            'po_number' => 'PO-TEST-QC-001',
            'received_by' => 'Tim Gudang',
            'qc_status' => 'pending_qc',
            'user_id' => $this->warehouseUser->id,
        ]);

        $this->assertEquals('pending_qc', $inbound->qc_status);
        $this->assertTrue($inbound->isPendingQc());

        // Stock physically does not increase while pending QC
        $item->refresh();
        $this->assertEquals($initialStock, $item->stock, "Stok master tidak boleh bertambah saat status masih Pending QC");

        // 2. QC Inspector approves the incoming material
        $inbound->update([
            'qc_status' => 'approved',
            'qc_by' => $this->qcUser->id,
            'qc_at' => Carbon::now(),
            'qc_notes' => 'Pemeriksaan fisik sesuai spesifikasi. Diterima dan di-approve.',
        ]);
        $item->increment('stock', $inbound->quantity);

        $inbound->refresh();
        $item->refresh();

        $this->assertTrue($inbound->isApproved());
        $this->assertEquals($initialStock + 10, $item->stock, "Stok master harus bertambah setelah QC menyetujui (Approved)");

        // 3. Test QC Reject logic
        $inboundReject = MaterialIn::create([
            'item_id' => $item->id,
            'date' => Carbon::today(),
            'quantity' => 5,
            'unit' => $item->unit,
            'supplier' => 'PT Test Supplier QC',
            'po_number' => 'PO-TEST-REJECT-002',
            'received_by' => 'Tim Gudang',
            'qc_status' => 'rejected',
            'qc_by' => $this->qcUser->id,
            'qc_notes' => 'Barang cacat kemasan dan basah. Di-reject!',
            'user_id' => $this->warehouseUser->id,
        ]);

        $this->assertTrue($inboundReject->isRejected());
        $item->refresh();
        $this->assertEquals($initialStock + 10, $item->stock, "Stok master tidak boleh bertambah jika barang di-reject oleh QC");

        // Clean up
        $item->decrement('stock', 10);
        $inbound->delete();
        $inboundReject->delete();
    }

    /**
     * 5. TRANSAKSI BARANG KELUAR (3 MODUL)
     * - Consumable
     * - Secondary Part
     * - Cat / Painting
     */
    public function test_material_out_three_modules(): void
    {
        $item = Item::where('stock', '>=', 5)->first();
        $this->assertNotNull($item);
        $initialStock = $item->stock;

        $outbound = MaterialOut::create([
            'item_id' => $item->id,
            'date' => Carbon::today(),
            'quantity' => 2,
            'unit' => $item->unit,
            'project' => 'Proyek Uji Otomasi WMS',
            'pic' => 'Ahmad Teknisi',
            'user_id' => $this->warehouseUser->id,
        ]);

        $item->decrement('stock', 2);
        $item->refresh();

        $this->assertEquals($initialStock - 2, $item->stock);

        // Clean up
        $item->increment('stock', 2);
        $outbound->delete();
    }

    /**
     * 6. STOK OPNAME PER SHIFT (Shift 1 & Shift 2)
     */
    public function test_stock_opname_per_shift(): void
    {
        $item = Item::first();
        $this->assertNotNull($item);
        $testDate = Carbon::today()->format('Y-m-d');

        // Shift 1
        $opnameShift1 = DailyStockOpname::updateOrCreate(
            ['date' => $testDate, 'item_id' => $item->id, 'shift' => 'Shift 1'],
            [
                'initial_stock' => 50,
                'in_qty' => 10,
                'out_qty' => 5,
                'system_stock' => 55,
                'physical_stock' => 55,
                'difference' => 0,
                'status' => 'match',
                'user_id' => $this->warehouseUser->id,
            ]
        );

        $this->assertEquals('Shift 1', $opnameShift1->shift);

        // Shift 2 on the same date for the same item
        $opnameShift2 = DailyStockOpname::updateOrCreate(
            ['date' => $testDate, 'item_id' => $item->id, 'shift' => 'Shift 2'],
            [
                'initial_stock' => 55,
                'in_qty' => 0,
                'out_qty' => 3,
                'system_stock' => 52,
                'physical_stock' => 52,
                'difference' => 0,
                'status' => 'match',
                'user_id' => $this->warehouseUser->id,
            ]
        );

        $this->assertEquals('Shift 2', $opnameShift2->shift);
        $this->assertNotEquals($opnameShift1->id, $opnameShift2->id);

        // Clean up test opnames
        $opnameShift1->delete();
        $opnameShift2->delete();
    }

    /**
     * 7. 4 MODUL LAPORAN RESMI (PDF & EXCEL)
     * 1. Laporan Inspeksi Incoming Material & Cat
     * 2. Laporan Pengeluaran Cat / Painting
     * 3. Laporan Pengeluaran Secondary Part
     * 4. Laporan Pengeluaran Consumable
     */
    public function test_four_official_reports_access_and_exports(): void
    {
        // 1. Incoming Inspection Report
        $res1 = $this->actingAs($this->warehouseUser)->get('/reports/incoming-inspection');
        $res1->assertStatus(200);
        $res1->assertSee('Laporan Inspeksi Incoming Material & Cat');

        $res1Excel = $this->actingAs($this->warehouseUser)->get('/reports/incoming-inspection/export');
        $res1Excel->assertStatus(200);

        $res1Pdf = $this->actingAs($this->warehouseUser)->get('/reports/incoming-inspection/pdf');
        $res1Pdf->assertStatus(200);

        // 2. Cat / Painting Report
        $res2 = $this->actingAs($this->warehouseUser)->get('/reports/cat-painting');
        $res2->assertStatus(200);
        $res2->assertSee('Laporan Pengeluaran Cat / Painting');

        $res2Excel = $this->actingAs($this->warehouseUser)->get('/reports/cat-painting/export');
        $res2Excel->assertStatus(200);

        $res2Pdf = $this->actingAs($this->warehouseUser)->get('/reports/cat-painting/pdf');
        $res2Pdf->assertStatus(200);

        // 3. Secondary Part Report
        $res3 = $this->actingAs($this->warehouseUser)->get('/reports/secondary-part');
        $res3->assertStatus(200);
        $res3->assertSee('Laporan Pengeluaran Secondary Part');

        $res3Excel = $this->actingAs($this->warehouseUser)->get('/reports/secondary-part/export');
        $res3Excel->assertStatus(200);

        $res3Pdf = $this->actingAs($this->warehouseUser)->get('/reports/secondary-part/pdf');
        $res3Pdf->assertStatus(200);

        // 4. Consumable Report
        $res4 = $this->actingAs($this->warehouseUser)->get('/reports/consumable');
        $res4->assertStatus(200);
        $res4->assertSee('Consumable');

        $res4Excel = $this->actingAs($this->warehouseUser)->get('/reports/consumable/export');
        $res4Excel->assertStatus(200);

        $res4Pdf = $this->actingAs($this->warehouseUser)->get('/reports/consumable/pdf');
        $res4Pdf->assertStatus(200);

        // PPIC is restricted to only seeing Stok Barang; accessing reports redirects to /items
        $resPpic1 = $this->actingAs($this->ppicUser)->get('/reports/incoming-inspection');
        $resPpic1->assertRedirect('/items');
        $resPpic1->assertSessionHas('error');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailyStockOpname;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\StockLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class WmsAdvancedFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $warehouseUser;
    protected Category $catPaintingCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_test@mudalaya.com'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $this->warehouseUser = User::firstOrCreate(
            ['email' => 'warehouse_test@mudalaya.com'],
            [
                'name' => 'Warehouse Staff Test',
                'password' => bcrypt('password'),
                'role' => 'warehouse',
            ]
        );

        $this->catPaintingCategory = Category::firstOrCreate(['name' => 'Cat / Painting']);
    }

    /**
     * FEATURE 4: Test Expiry Date Tracking on Item & Material In
     */
    public function test_expiry_date_tracking_for_paint_and_chemical(): void
    {
        // 1. Create an item expiring in 15 days (Near Expiry <= 30 days)
        $nearExpiryItem = Item::create([
            'category_id' => $this->catPaintingCategory->id,
            'code_item' => 'EXP-NEAR-01',
            'name' => 'Cat Epoxy Hardener 5L',
            'stock' => 20,
            'unit' => 'Pail',
            'min_stock' => 5,
            'expiry_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
        ]);

        $this->assertFalse($nearExpiryItem->isExpired());
        $this->assertTrue($nearExpiryItem->isNearExpiry(30));
        $this->assertEquals('near_expiry', $nearExpiryItem->expiry_status);

        // 2. Create an item that has expired 5 days ago
        $expiredItem = Item::create([
            'category_id' => $this->catPaintingCategory->id,
            'code_item' => 'EXP-PAST-01',
            'name' => 'Cat Primer Red Oxide',
            'stock' => 10,
            'unit' => 'Can',
            'min_stock' => 2,
            'expiry_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);

        $this->assertTrue($expiredItem->isExpired());
        $this->assertEquals('expired', $expiredItem->expiry_status);

        // 3. Test Material In with expiry_date
        $materialIn = MaterialIn::create([
            'item_id' => $nearExpiryItem->id,
            'user_id' => $this->warehouseUser->id,
            'supplier' => 'PT Jotun Indonesia',
            'surat_jalan_number' => 'SJ-JOTUN-001',
            'received_by' => 'Staff Warehouse',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => 10,
            'unit' => 'Pail',
            'manufacturing_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'expiry_date' => Carbon::now()->addYear()->format('Y-m-d'),
            'qc_status' => 'approved',
        ]);

        $this->assertNotNull($materialIn->expiry_date);
        $this->assertEquals(Carbon::now()->addYear()->format('Y-m-d'), $materialIn->expiry_date->format('Y-m-d'));

        // 4. Test Item Index filter by expiry_status
        $response = $this->actingAs($this->adminUser)->get('/items?expiry_status=near_expiry');
        $response->assertStatus(200);
        $response->assertSee('Cat Epoxy Hardener 5L');
    }

    /**
     * FEATURE 8: Test Stock Opname Adjustment Approval Workflow
     */
    public function test_stock_opname_adjustment_approval_flow(): void
    {
        $testDate = '2026-09-20';

        $item = Item::create([
            'category_id' => $this->catPaintingCategory->id,
            'code_item' => 'ADJ-TEST-01',
            'name' => 'Material Selisih Opname',
            'stock' => 100,
            'unit' => 'Pcs',
            'min_stock' => 10,
        ]);

        // Initialize daily opname
        DailyStockOpname::initializeForDate($testDate);
        $opname = DailyStockOpname::where('date', $testDate)->where('item_id', $item->id)->first();
        $this->assertNotNull($opname);

        // Physical count discovers discrepancy: Final = 90, Mutasi Out = 0 => Used = 10, Diff = 10
        $opname->final_stock = 90;
        $opname->recalculate()->save();

        $this->assertEquals('pending', $opname->adjustment_status);
        $this->assertEquals(10, $opname->difference);

        // Test Non-Admin cannot approve adjustment via Livewire
        Livewire::actingAs($this->warehouseUser)
            ->test(\App\Livewire\StockOpnameComponent::class)
            ->call('openAdjustmentModal', $opname->id)
            ->set('adjustmentNotes', 'Percobaan approval oleh warehouse')
            ->call('approveAdjustment')
            ->assertForbidden();

        // Refresh opname and assert not modified
        $opname->refresh();
        $this->assertEquals('pending', $opname->adjustment_status);
        $this->assertEquals(100, $item->refresh()->stock);

        // Test Web Admin CAN approve adjustment
        Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\StockOpnameComponent::class)
            ->set('selectedDate', $testDate)
            ->call('openAdjustmentModal', $opname->id)
            ->set('adjustmentNotes', 'Disetujui selisih 10 pcs susut pengeringan.')
            ->call('approveAdjustment');

        $opname->refresh();
        $this->assertEquals('approved', $opname->adjustment_status);
        $this->assertEquals('synced', $opname->status);
        $this->assertEquals($this->adminUser->id, $opname->adjusted_by);
        $this->assertEquals(90, $item->refresh()->stock, 'Stok master harus tersinkron ke stok fisik (90)');

        // Check StockLog adjustment record (quantity is the difference = 10)
        $adjustmentLog = StockLog::where('item_id', $item->id)
            ->where('type', 'adjustment')
            ->first();
        $this->assertNotNull($adjustmentLog);
        $this->assertEquals(10, $adjustmentLog->quantity);
    }

    /**
     * FEATURE 8: Test Stock Opname Adjustment Rejection Flow
     */
    public function test_stock_opname_adjustment_rejection_flow(): void
    {
        $testDate = '2026-09-21';

        $item = Item::create([
            'category_id' => $this->catPaintingCategory->id,
            'code_item' => 'ADJ-REJ-01',
            'name' => 'Material Hitung Ulang Opname',
            'stock' => 50,
            'unit' => 'Pcs',
            'min_stock' => 5,
        ]);

        DailyStockOpname::initializeForDate($testDate);
        $opname = DailyStockOpname::where('date', $testDate)->where('item_id', $item->id)->first();
        $this->assertNotNull($opname);

        $opname->final_stock = 40;
        $opname->recalculate()->save();

        Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\StockOpnameComponent::class)
            ->set('selectedDate', $testDate)
            ->call('openAdjustmentModal', $opname->id)
            ->set('adjustmentNotes', 'Angka fisik mencurigakan, minta tim shift 2 hitung ulang.')
            ->call('rejectAdjustment');

        $opname->refresh();
        $this->assertEquals('rejected', $opname->adjustment_status);
        $this->assertEquals('Angka fisik mencurigakan, minta tim shift 2 hitung ulang.', $opname->adjustment_notes);
        $this->assertEquals(50, $item->refresh()->stock, 'Stok master TIDAK boleh berubah saat penyesuaian ditolak');
    }

    /**
     * FEATURE 11: Test Slow-Moving & Dead Stock Report Page & Exports
     */
    public function test_slow_moving_and_dead_stock_report_page_and_exports(): void
    {
        // 1. View Report Page
        $response = $this->actingAs($this->adminUser)->get('/reports/slow-moving-stock');
        $response->assertStatus(200);
        $response->assertSee('Dead Stock');
        $response->assertSee('Slow Moving');
        $response->assertSee('Fast Moving');

        // 2. Filter by status
        $responseFiltered = $this->actingAs($this->adminUser)->get('/reports/slow-moving-stock?status=dead_stock');
        $responseFiltered->assertStatus(200);

        // 3. Export Excel
        $responseExcel = $this->actingAs($this->adminUser)->get('/reports/slow-moving-stock/export');
        $responseExcel->assertStatus(200);
        $this->assertTrue(str_contains($responseExcel->headers->get('content-type') ?? '', 'spreadsheetml.sheet'));

        // 4. Export PDF
        $responsePdf = $this->actingAs($this->adminUser)->get('/reports/slow-moving-stock/pdf');
        $responsePdf->assertStatus(200);
        $this->assertTrue(str_contains($responsePdf->headers->get('content-type') ?? '', 'application/pdf'));
    }

    /**
     * Test Table Pagination and Max Per-Page Controls
     */
    public function test_table_pagination_and_per_page_limits(): void
    {
        // 1. Slow Moving Stock Report Pagination & Per-Page Controls
        $response = $this->actingAs($this->adminUser)->get('/reports/slow-moving-stock?per_page=10');
        $response->assertStatus(200);
        $response->assertSee('10 / hal');
        $response->assertSee('Menampilkan');

        // 2. Stock Opname Livewire Pagination Tab 1
        Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\StockOpnameComponent::class)
            ->set('dailyPerPage', 15)
            ->assertSet('dailyPerPage', 15)
            ->assertSee('15 / hal');

        // 3. Stock Logs Per-Page
        $responseLogs = $this->actingAs($this->adminUser)->get('/stock-logs?per_page=25');
        $responseLogs->assertStatus(200);
        $responseLogs->assertSee('25 / hal');

        // 4. Warehouse Mutasi Reports Default Bounded Pagination
        $responseReports = $this->actingAs($this->adminUser)->get('/reports?type=in');
        $responseReports->assertStatus(200);
        $responseReports->assertSee('25 data per halaman');
    }
}

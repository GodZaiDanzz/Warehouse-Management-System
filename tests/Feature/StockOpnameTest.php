<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailyStockOpname;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    public function test_rolling_stock_workflow_and_formula(): void
    {
        // 1. Setup item dummy
        $category = Category::firstOrCreate(['name' => 'Consumable']);
        Item::where('code_item', 'TEST-001')->delete();
        $day1 = '2026-09-10';
        $day2 = '2026-09-11';
        DailyStockOpname::whereIn('date', [$day1, $day2])->delete();

        $item = Item::create([
            'category_id' => $category->id,
            'code_item' => 'TEST-001',
            'name' => 'Barang Uji Rolling Stock',
            'stock' => 100,
            'unit' => 'PCS',
            'min_stock' => 10,
        ]);

        // 2. Inisialisasi Hari 1
        DailyStockOpname::initializeForDate($day1);

        $opnameDay1 = DailyStockOpname::where('date', $day1)->where('item_id', $item->id)->first();
        $this->assertNotNull($opnameDay1);
        $this->assertEquals(100, $opnameDay1->initial_stock, 'Stok awal hari 1 harus sama dengan master stok');

        // 3. Input Stok Akhir Sore Hari 1 (Fisik = 85)
        $opnameDay1->final_stock = 85;
        $opnameDay1->recalculate()->save();

        // 4. Verifikasi Formula: Stok Awal - Stok Akhir = Total Terpakai
        // 100 - 85 = 15
        $this->assertEquals(15, $opnameDay1->used_qty, 'Total terpakai harus dihitung dari Stok Awal (100) - Stok Akhir (85) = 15');

        // 5. Inisialisasi Hari 2 (Rolling Stock)
        DailyStockOpname::initializeForDate($day2);

        $opnameDay2 = DailyStockOpname::where('date', $day2)->where('item_id', $item->id)->first();
        $this->assertNotNull($opnameDay2);

        // Stok awal hari 2 harus otomatis bergulir dari stok akhir hari 1 (85)
        $this->assertEquals(85, $opnameDay2->initial_stock, 'Stok awal hari 2 harus otomatis mengambil stok akhir hari 1 (85)');

        // 6. Test Roll Forward: Jika stok akhir hari 1 dikoreksi menjadi 80
        $opnameDay1->final_stock = 80;
        $opnameDay1->recalculate()->save();
        DailyStockOpname::rollForward($item->id, $day1, 80);

        $opnameDay2->refresh();
        $this->assertEquals(80, $opnameDay2->initial_stock, 'Stok awal hari 2 harus otomatis terupdate menjadi 80 setelah roll forward');

        // Cleanup
        $opnameDay1->delete();
        $opnameDay2->delete();
        $item->delete();
    }

    public function test_stock_opname_page_loads_successfully(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/stock-opnames');
        $response->assertStatus(200);
        $response->assertSee('Stock Opname Harian & Rolling Stock');
    }
}

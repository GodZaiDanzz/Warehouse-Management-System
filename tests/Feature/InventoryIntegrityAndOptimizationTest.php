<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\MaterialOut;
use App\Models\StockLog;
use App\Models\User;
use App\Livewire\MaterialOutComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryIntegrityAndOptimizationTest extends TestCase
{
    protected User $admin;
    protected Item $testItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?? User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);

        $category = \App\Models\Category::first() ?? \App\Models\Category::create(['name' => 'Consumable']);
        $this->testItem = Item::where('stock', '>', 0)->first() ?? Item::create([
            'name' => 'Test Valve 1 Inch',
            'code_item' => 'VALVE-01',
            'stock' => 10,
            'unit' => 'Pcs',
            'category_id' => $category->id,
        ]);
    }

    public function test_bulk_out_with_duplicate_item_exceeding_total_stock_is_rejected(): void
    {
        $initialStock = $this->testItem->stock;

        // If stock is 10, we request 6 and 6 (total 12 > 10). Both rows individually <= 10.
        $qty1 = (int) ceil($initialStock * 0.6);
        $qty2 = (int) ceil($initialStock * 0.6);

        $component = new MaterialOutComponent();
        $component->bulkRows = [
            ['item_id' => $this->testItem->id, 'quantity' => $qty1, 'unit' => 'Pcs', 'project' => 'Project Alpha', 'pic' => 'Budi', 'notes' => ''],
            ['item_id' => $this->testItem->id, 'quantity' => $qty2, 'unit' => 'Pcs', 'project' => 'Project Alpha', 'pic' => 'Budi', 'notes' => ''],
        ];
        $component->date = date('Y-m-d');
        $component->activeTab = 'bulk';
        $component->save();

        // Ensure stock didn't change and validation caught it
        $this->assertEquals($initialStock, $this->testItem->fresh()->stock);
        $this->assertTrue(session()->has('error'));
        $this->assertStringContainsString('melebihi stok yang tersedia', session('error'));
    }

    public function test_delete_material_out_creates_compensating_stock_log(): void
    {
        $refNo = 'OUT-TEST-' . uniqid();
        $outRecord = MaterialOut::create([
            'reference_number' => $refNo,
            'date' => date('Y-m-d'),
            'item_id' => $this->testItem->id,
            'quantity' => 2,
            'unit' => 'Pcs',
            'project' => 'Project Beta',
            'pic' => 'Joko',
            'user_id' => $this->admin->id,
        ]);
        $this->testItem->decrement('stock', 2);
        $stockBeforeDelete = $this->testItem->fresh()->stock;

        $component = new MaterialOutComponent();
        $component->delete($outRecord->id);

        $this->assertEquals($stockBeforeDelete + 2, $this->testItem->fresh()->stock);

        $compensatingLog = StockLog::where('reference_number', $refNo)
            ->where('type', 'in')
            ->first();

        $this->assertNotNull($compensatingLog);
        $this->assertEquals(2, $compensatingLog->quantity);
        $this->assertStringContainsString('Pengembalian stok', $compensatingLog->notes);
    }

    public function test_dashboard_optimizations_render_for_all_ranges(): void
    {
        $response1w = $this->get(route('dashboard', ['line_range' => '1w', 'bar_range' => '1w']));
        $response1w->assertStatus(200);

        $response1m = $this->get(route('dashboard', ['line_range' => '1m', 'bar_range' => '1m']));
        $response1m->assertStatus(200);

        $response1y = $this->get(route('dashboard', ['line_range' => '1y', 'bar_range' => '1y']));
        $response1y->assertStatus(200);
    }
}

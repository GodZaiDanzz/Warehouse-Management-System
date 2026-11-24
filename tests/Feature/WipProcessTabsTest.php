<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FinishGood;
use App\Models\Item;
use App\Models\MaterialOut;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WipProcessTabsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Project $project;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();

        Unit::firstOrCreate(['symbol' => 'PCS'], ['name' => 'Pieces', 'is_active' => true]);
        $category = Category::first() ?? Category::create(['name' => 'Raw Material', 'code' => 'RAW']);

        $this->project = Project::create([
            'name' => 'Storage Tank 5000L PT Pertamina ' . uniqid(),
            'po_number' => 'PO-' . rand(1000, 9999),
            'client' => 'PT Pertamina Hulu Energi',
            'status' => 'Ongoing',
            'start_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $this->item = Item::create([
            'name' => 'Plat Baja SS400 Tebal 10mm ' . uniqid(),
            'code_item' => 'PLT-' . rand(1000, 9999),
            'category_id' => $category->id,
            'unit' => 'PCS',
            'stock' => 100,
            'minimum_stock' => 5,
            'unit_price' => 1250000,
        ]);
    }

    public function test_can_navigate_finish_goods_and_wip_tabs(): void
    {
        // Create sample Finish Good
        FinishGood::create([
            'project_id' => $this->project->id,
            'item_name' => 'Pressure Vessel Body ' . uniqid(),
            'item_code' => 'PV-' . rand(100, 999),
            'quantity' => 2,
            'unit' => 'PCS',
            'qc_status' => 'Passed',
            'status' => 'In Storage',
            'user_id' => $this->user->id,
        ]);

        // Create sample Cutting WIP Material Out
        MaterialOut::create([
            'reference_number' => 'OUT-WIP-' . rand(1000, 9999),
            'date' => now()->format('Y-m-d'),
            'item_id' => $this->item->id,
            'quantity' => 10,
            'unit' => 'PCS',
            'project' => $this->project->name,
            'project_id' => $this->project->id,
            'wip_stage' => 'Cutting',
            'manpower_hours' => 8,
            'manpower_rate_per_hour' => 30000,
            'manpower_cost' => 240000,
            'pic' => 'Budi Santoso',
            'user_id' => $this->user->id,
        ]);

        // Default tab: finish_goods
        $response = $this->actingAs($this->user)->get(route('finish-goods.index'));
        $response->assertStatus(200);
        $response->assertSee('Barang Jadi & WIP (Work In Process)', false);
        $response->assertSee('Tahap 1: Cutting & Profiling', false);

        // Tab: cutting
        $response = $this->actingAs($this->user)->get(route('finish-goods.index', ['tab' => 'cutting']));
        $response->assertStatus(200);
        $response->assertSee('Tahap Cutting');
        $response->assertSee('+ Input WIP (Cutting)');
        $response->assertSee($this->item->name);
        $response->assertSee('240.000');

        // Tab: vendor
        $response = $this->actingAs($this->user)->get(route('finish-goods.index', ['tab' => 'vendor']));
        $response->assertStatus(200);
        $response->assertSee('Tahap Vendor Eksternal');

        // Tab: welding
        $response = $this->actingAs($this->user)->get(route('finish-goods.index', ['tab' => 'welding']));
        $response->assertStatus(200);
        $response->assertSee('Tahap Welding');

        // Tab: painting
        $response = $this->actingAs($this->user)->get(route('finish-goods.index', ['tab' => 'painting']));
        $response->assertStatus(200);
        $response->assertSee('Tahap Painting');
    }

    public function test_can_record_wip_from_process_tab_modal(): void
    {
        $payload = [
            'project_id' => $this->project->id,
            'wip_stage' => 'Cutting',
            'date' => now()->format('Y-m-d'),
            'item_id' => $this->item->id,
            'quantity' => 15,
            'unit' => 'PCS',
            'manpower_hours' => 6,
            'manpower_rate_per_hour' => 35000,
            'pic' => 'Ahmad Operator',
            'notes' => 'Cutting plat shell segmen 1',
        ];

        $response = $this->actingAs($this->user)->post(route('finish-goods.store-wip'), $payload);

        $response->assertRedirect(route('finish-goods.index', ['tab' => 'cutting', 'project_id' => $this->project->id]));
        $response->assertSessionHas('success');

        // Verify MaterialOut record
        $this->assertDatabaseHas('material_outs', [
            'project_id' => $this->project->id,
            'item_id' => $this->item->id,
            'quantity' => 15,
            'wip_stage' => 'Cutting',
            'manpower_hours' => 6,
            'manpower_rate' => 35000,
            'manpower_cost' => 210000,
            'pic' => 'Ahmad Operator',
        ]);

        // Stock decreased from 100 to 85
        $this->assertEquals(85, $this->item->fresh()->stock);

        // StockLog recorded
        $this->assertDatabaseHas('stock_logs', [
            'item_id' => $this->item->id,
            'type' => 'out',
            'quantity' => 15,
            'recipient_or_provider' => 'Ahmad Operator',
        ]);
    }

    public function test_can_record_wip_material_and_manpower_from_project_show(): void
    {
        $payload = [
            'date' => now()->format('Y-m-d'),
            'item_id' => $this->item->id,
            'quantity' => 8,
            'unit' => 'PCS',
            'wip_stage' => 'Welding',
            'manpower_hours' => 10,
            'manpower_rate_per_hour' => 30000,
            'pic' => 'Joko Welder',
            'notes' => 'Pengelasan seam luar',
        ];

        $response = $this->actingAs($this->user)->post(route('projects.add-material', $this->project->id), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('material_outs', [
            'project_id' => $this->project->id,
            'item_id' => $this->item->id,
            'quantity' => 8,
            'wip_stage' => 'Welding',
            'manpower_hours' => 10,
            'manpower_rate' => 30000,
            'manpower_cost' => 300000,
            'pic' => 'Joko Welder',
        ]);

        $this->assertEquals(92, $this->item->fresh()->stock);

        // Check project detail filter tab
        $showResponse = $this->actingAs($this->user)->get(route('projects.show', [
            'project' => $this->project->id,
            'wip_stage' => 'Welding',
        ]));

        $showResponse->assertStatus(200);
        $showResponse->assertSee('Welding');
        $showResponse->assertSee('10 Jam');
        $showResponse->assertSee('300.000');
    }
}

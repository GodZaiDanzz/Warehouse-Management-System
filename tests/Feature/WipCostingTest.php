<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FinishGood;
use App\Models\Item;
use App\Models\MaterialOut;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WipCostingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_material_out_saves_wip_stage_and_calculates_manpower_cost(): void
    {
        $user = User::first() ?? User::factory()->create();
        $user->role = 'admin';
        $user->save();

        $category = Category::first() ?? Category::create(['name' => 'Raw Material']);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Pelat Baja SS400 10mm',
            'code' => 'PLT-SS400-10',
            'unit' => 'SHEET',
            'unit_price' => 1500000,
            'stock' => 50,
        ]);

        $project = Project::create([
            'name' => 'Fabrikasi Silo Storage Project Test',
            'code' => 'PRJ-SILO-001',
            'status' => 'Ongoing',
            'tonnage' => 12.5,
        ]);

        $matOut = MaterialOut::create([
            'item_id' => $item->id,
            'project_id' => $project->id,
            'project' => $project->name,
            'user_id' => $user->id,
            'pic' => 'Budi Operator',
            'date' => now()->toDateString(),
            'quantity' => 2,
            'unit' => 'SHEET',
            'wip_stage' => MaterialOut::STAGE_CUTTING,
            'manpower_hours' => 3.5,
            'manpower_rate' => 50000,
            'manpower_cost' => 175000,
            'notes' => 'Cutting tahap 1 silinder bodi',
        ]);

        $this->assertDatabaseHas('material_outs', [
            'id' => $matOut->id,
            'wip_stage' => MaterialOut::STAGE_CUTTING,
            'manpower_hours' => 3.5,
            'manpower_cost' => 175000,
        ]);

        $this->assertEquals(3000000, $matOut->material_cost);
        $this->assertEquals(3175000, $matOut->total_wip_cost);
    }

    public function test_finish_goods_store_auto_calculates_wip_and_aggregates_hpp(): void
    {
        $user = User::first() ?? User::factory()->create();
        $user->role = 'admin';
        $user->save();

        $category = Category::first() ?? Category::create(['name' => 'Bahan Baku']);

        $itemA = Item::create([
            'category_id' => $category->id,
            'name' => 'Besi Pipa Seamless 4 inch',
            'code' => 'PIP-SEA-4',
            'unit' => 'BATANG',
            'unit_price' => 500000,
            'stock' => 100,
        ]);

        $itemB = Item::create([
            'category_id' => $category->id,
            'name' => 'Kawat Las LB-52 3.2mm',
            'code' => 'KW-LB52-32',
            'unit' => 'KG',
            'unit_price' => 50000,
            'stock' => 200,
        ]);

        $project = Project::create([
            'name' => 'Proyek Tangki Finish Goods HPP Test',
            'code' => 'PRJ-TGK-HPP-01',
            'status' => 'Ongoing',
            'tonnage' => 5.0,
        ]);

        // Out 1: Cutting (1 batang pipa = 500,000 + 2 jam manpower @ 50,000 = 100,000)
        $out1 = MaterialOut::create([
            'item_id' => $itemA->id,
            'project_id' => $project->id,
            'project' => $project->name,
            'user_id' => $user->id,
            'pic' => 'Budi Welder',
            'date' => now()->toDateString(),
            'quantity' => 1,
            'unit' => 'BATANG',
            'wip_stage' => MaterialOut::STAGE_CUTTING,
            'manpower_hours' => 2,
            'manpower_rate' => 50000,
            'manpower_cost' => 100000,
        ]);

        // Out 2: Welding (4 kg kawat las = 200,000 + 4 jam welder @ 50,000 = 200,000)
        $out2 = MaterialOut::create([
            'item_id' => $itemB->id,
            'project_id' => $project->id,
            'project' => $project->name,
            'user_id' => $user->id,
            'pic' => 'Budi Welder',
            'date' => now()->toDateString(),
            'quantity' => 4,
            'unit' => 'KG',
            'wip_stage' => MaterialOut::STAGE_WELDING,
            'manpower_hours' => 4,
            'manpower_rate' => 50000,
            'manpower_cost' => 200000,
        ]);

        // Total Expected: Material = 700,000; Manpower = 300,000; Total HPP = 1,000,000
        // Qty Finish Good = 2 unit -> HPP per unit = 500,000

        $response = $this->actingAs($user)->post(route('finish-goods.store'), [
            'project_id' => $project->id,
            'item_name' => 'Tangki Buffer 500L',
            'item_code' => 'TK-BUF-500-01',
            'quantity' => 2,
            'unit' => 'UNIT',
            'production_date' => now()->toDateString(),
            'qc_status' => 'Passed',
            'status' => 'In Storage',
            'auto_calculate_wip' => '1',
            'notes' => 'Pengetesan akumulasi HPP otomatis',
        ]);

        $response->assertRedirect();

        $fg = FinishGood::where('project_id', $project->id)->latest('id')->first();
        $this->assertNotNull($fg);
        $this->assertEquals(700000, (float) $fg->material_cost);
        $this->assertEquals(700000, (float) $fg->total_hpp);
        $this->assertEquals(350000, (float) $fg->hpp_per_unit);

        // Verify MaterialOuts are now linked to this FinishGood
        $this->assertEquals($fg->id, $out1->fresh()->finish_good_id);
        $this->assertEquals($fg->id, $out2->fresh()->finish_good_id);
    }

    public function test_project_show_displays_wip_summary_breakdown(): void
    {
        $user = User::first() ?? User::factory()->create();
        $user->role = 'admin';
        $user->save();

        $project = Project::create([
            'name' => 'Project View WIP Testing',
            'code' => 'PRJ-VIEW-WIP',
            'status' => 'Ongoing',
            'tonnage' => 1.0,
        ]);

        $response = $this->actingAs($user)->get(route('projects.show', $project->id));

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Biaya WIP');
        $response->assertSee('Cutting');
        $response->assertSee('Welding');
        $response->assertSee('Painting');
        $response->assertSee('Packing');
    }

    public function test_finish_goods_index_has_traceability_features(): void
    {
        $user = User::first() ?? User::factory()->create();
        $user->role = 'admin';
        $user->save();

        $response = $this->actingAs($user)->get(route('finish-goods.index'));

        $response->assertStatus(200);
        $response->assertSee('Traceability Alur Produksi');
        $response->assertSee('setTrace');
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\ItemTableComponent;
use App\Models\Category;
use App\Models\Item;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ItemTableMinStockLazyLoadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::first() ?? User::factory()->create();
        $this->actingAs($admin);
    }

    public function test_component_passes_critical_items_list_without_inline_blade_queries(): void
    {
        $cat = Category::firstOrCreate(['name' => 'Consumable']);
        $item = Item::create([
            'name' => 'Critical Test Item ' . uniqid(),
            'category_id' => $cat->id,
            'stock' => 0,
            'min_stock' => 10,
            'unit' => 'Pcs',
        ]);

        $test = Livewire::test(ItemTableComponent::class);
        $test->assertStatus(200);

        // Verify that criticalItemsList is passed from Component
        $criticalList = $test->viewData('criticalItemsList');
        $this->assertIsArray($criticalList);
        $ids = array_column($criticalList, 'id');
        $this->assertContains($item->id, $ids);
    }

    public function test_lazy_loads_min_stock_breakdown_on_demand_and_dispatches_event(): void
    {
        $cat = Category::firstOrCreate(['name' => 'Consumable']);
        $item = Item::create([
            'name' => 'Dynamic Tonnage Item ' . uniqid(),
            'category_id' => $cat->id,
            'stock' => 100,
            'unit' => 'Liter',
            'min_stock' => 5,
            'requirement_per_ton' => 0.5,
        ]);

        $project = Project::firstOrCreate(
            ['name' => 'Project Lazy Test'],
            [
                'code' => 'PRJ-LAZY-01',
                'status' => 'Ongoing',
                'tonnage' => 100,
            ]
        );

        $test = Livewire::test(ItemTableComponent::class)
            ->call('loadMinStockBreakdown', $item->id);

        $test->assertDispatched('show-min-stock-breakdown', function ($eventName, $params) use ($item) {
            $data = $params['data'] ?? $params;
            return isset($data['item_id']) 
                && $data['item_id'] === $item->id
                && (float)$data['requirement_per_ton'] === 0.5
                && isset($data['projects']);
        });
    }
}

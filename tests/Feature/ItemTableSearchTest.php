<?php

namespace Tests\Feature;

use App\Livewire\ItemTableComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ItemTableSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $cat = \App\Models\Category::firstOrCreate(['name' => 'Consumable']);
        \App\Models\Item::where('name', 'like', '%sarung%')
            ->whereNotIn('name', ['Sarung Tangan Las', 'Sarung Tangan Katun'])
            ->forceDelete();

        \App\Models\Item::firstOrCreate(
            ['name' => 'Sarung Tangan Las'],
            ['category_id' => $cat->id, 'code_item' => 'SAFE-003', 'stock' => 50, 'unit' => 'Pair', 'min_stock' => 10]
        );
        \App\Models\Item::firstOrCreate(
            ['name' => 'Sarung Tangan Katun'],
            ['category_id' => $cat->id, 'code_item' => 'SAFE-005', 'stock' => 100, 'unit' => 'Pair', 'min_stock' => 72]
        );
    }

    public function test_searching_sarung_returns_both_items(): void
    {
        $user = User::first() ?? User::factory()->create();

        $test = Livewire::actingAs($user)
            ->test(ItemTableComponent::class)
            ->set('search', 'sarung');

        $items = $test->viewData('items');
        $itemNames = $items->pluck('name')->toArray();

        $this->assertContains('Sarung Tangan Las', $itemNames);
        $this->assertContains('Sarung Tangan Katun', $itemNames);
        $this->assertCount(2, $itemNames);
    }

    public function test_searching_specific_sarung_tangan_katun_returns_item(): void
    {
        $user = User::first() ?? User::factory()->create();

        $test = Livewire::actingAs($user)
            ->test(ItemTableComponent::class)
            ->set('search', 'sarung tangan katun');

        $items = $test->viewData('items');
        $itemNames = $items->pluck('name')->toArray();

        $this->assertContains('Sarung Tangan Katun', $itemNames);
        $this->assertNotContains('Sarung Tangan Las', $itemNames);
        $this->assertCount(1, $itemNames);
    }

    public function test_searching_with_extra_spaces_returns_item(): void
    {
        $user = User::first() ?? User::factory()->create();

        $test = Livewire::actingAs($user)
            ->test(ItemTableComponent::class)
            ->set('search', 'sarung   tangan   katun');

        $items = $test->viewData('items');
        $itemNames = $items->pluck('name')->toArray();

        $this->assertContains('Sarung Tangan Katun', $itemNames);
        $this->assertNotContains('Sarung Tangan Las', $itemNames);
        $this->assertCount(1, $itemNames);
    }

    public function test_auto_generates_code_item_on_creation(): void
    {
        $category = \App\Models\Category::firstOrCreate(['name' => 'Consumable']);
        $item = \App\Models\Item::create([
            'name' => 'Test Auto Code Barang Unik ' . uniqid(),
            'category_id' => $category->id,
            'stock' => 10,
            'unit' => 'Pcs',
            'min_stock' => 2,
        ]);

        $this->assertNotEmpty($item->code_item);
        $this->assertStringStartsWith('CSM-', $item->code_item);
    }

    public function test_searching_by_material_code(): void
    {
        $user = User::first() ?? User::factory()->create();
        $category = \App\Models\Category::firstOrCreate(['name' => 'Consumable']);
        $uniqueCode = 'CSM-TEST' . rand(100, 999);
        $item = \App\Models\Item::create([
            'code_item' => $uniqueCode,
            'name' => 'Barang Khusus Kode Test',
            'category_id' => $category->id,
            'stock' => 5,
            'unit' => 'Pcs',
            'min_stock' => 2,
        ]);

        $test = Livewire::actingAs($user)
            ->test(ItemTableComponent::class)
            ->set('search', $uniqueCode);

        $items = $test->viewData('items');
        $itemNames = $items->pluck('name')->toArray();

        $this->assertContains('Barang Khusus Kode Test', $itemNames);
    }
}

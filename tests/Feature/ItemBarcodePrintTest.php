<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemBarcodePrintTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_single_item_barcode_print_renders_correctly(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $item = Item::first();

        if (!$item) {
            $cat = Category::first() ?? Category::create(['name' => 'General']);
            $item = Item::create([
                'name' => 'Barang Uji Barcode',
                'code_item' => 'CSM-999',
                'category_id' => $cat->id,
                'stock' => 50,
                'unit' => 'Pcs',
                'min_stock' => 10,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('items.barcode.print', [
            'item' => $item->id,
            'copies' => 3,
            'layout' => 'a4'
        ]));

        $response->assertStatus(200);
        $response->assertSee($item->name);
        $response->assertSee($item->code_item);
        $response->assertSee('<svg', false);
        $response->assertSee('Cetak Label Barcode Gudang');
        $response->assertSee('Standar A4 (24)');
        $response->assertDontSee('Stok:');
    }

    public function test_bulk_items_barcode_print_renders_all_selected_items(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $items = Item::take(2)->get();

        if ($items->count() < 2) {
            $cat = Category::first() ?? Category::create(['name' => 'General']);
            Item::create([
                'name' => 'Item Bulk 1',
                'code_item' => 'CSM-101',
                'category_id' => $cat->id,
                'stock' => 20,
                'unit' => 'Pcs',
            ]);
            Item::create([
                'name' => 'Item Bulk 2',
                'code_item' => 'CSM-102',
                'category_id' => $cat->id,
                'stock' => 30,
                'unit' => 'Pcs',
            ]);
            $items = Item::take(2)->get();
        }

        $ids = $items->pluck('id')->toArray();

        $response = $this->actingAs($admin)->get(route('items.barcode.bulk', [
            'ids' => implode(',', $ids),
            'copies' => 1,
            'layout' => 'thermal'
        ]));

        $response->assertStatus(200);
        foreach ($items as $item) {
            $response->assertSee($item->name);
            $response->assertSee($item->code_item);
        }
        $response->assertSee('Roll Thermal');
    }

    public function test_bulk_print_without_selection_redirects_with_warning(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('items.barcode.bulk'));

        $response->assertRedirect(route('items.index'));
        $response->assertSessionHas('warning');
    }
}

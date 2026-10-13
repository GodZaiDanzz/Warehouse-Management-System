<?php

namespace Tests\Feature;

use App\Livewire\MaterialOutComponent;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class MaterialOutItemComboboxTest extends TestCase
{
    use DatabaseTransactions;

    public function test_material_out_modal_renders_item_search_combobox(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MaterialOutComponent::class)
            ->assertStatus(200)
            ->call('openCreateModal')
            ->assertSet('isModalOpen', true)
            ->assertSet('activeTab', 'bulk')
            ->assertSeeHtml('itemSearchCombobox')
            ->assertSeeHtml('window.warehouseItemsList')
            ->assertSeeHtml('x-teleport="body"')
            ->assertSeeHtml('Pilih Barang')
            ->assertSeeHtml('Ketik nama, kode, atau kategori barang');
    }

    public function test_selecting_item_in_bulk_row_updates_unit_automatically(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $category = Category::firstOrCreate(['name' => 'Consumable']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST AMPLAS SPECIAL ' . uniqid(),
            'code_item' => 'TST-' . rand(100, 999),
            'stock' => 25,
            'unit' => 'ROLL',
        ]);

        Livewire::actingAs($admin)
            ->test(MaterialOutComponent::class)
            ->call('openCreateModal')
            ->set('bulkRows.0.item_id', $item->id)
            ->assertSet('bulkRows.0.unit', 'ROLL');
    }

    public function test_single_mode_item_combobox_loads_item_details(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $category = Category::firstOrCreate(['name' => 'Consumable']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST BATU GERINDA ' . uniqid(),
            'code_item' => 'TST-' . rand(100, 999),
            'stock' => 88,
            'unit' => 'PCS',
        ]);

        Livewire::actingAs($admin)
            ->test(MaterialOutComponent::class)
            ->call('openCreateModal')
            ->set('activeTab', 'single')
            ->set('item_id', $item->id)
            ->assertSet('selectedItemStock', 88)
            ->assertSet('selectedItemUnit', 'PCS')
            ->assertSet('unit', 'PCS');
    }
}

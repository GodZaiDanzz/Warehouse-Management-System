<?php

namespace Tests\Feature;

use App\Livewire\MaterialInComponent;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class MaterialInItemComboboxTest extends TestCase
{
    use DatabaseTransactions;

    public function test_material_in_modal_renders_item_search_combobox(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MaterialInComponent::class)
            ->assertStatus(200)
            ->call('openCreateModal')
            ->assertSet('isModalOpen', true)
            ->assertSeeHtml('itemSearchCombobox')
            ->assertSeeHtml('window.warehouseItemsList')
            ->assertSeeHtml('x-teleport="body"')
            ->assertSeeHtml('Pilih Barang')
            ->assertSeeHtml('Ketik nama, kode, atau kategori barang');
    }

    public function test_selecting_item_in_batch_row_updates_unit_and_stock_automatically(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $category = Category::firstOrCreate(['name' => 'Consumable']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST SARUNG TANGAN ' . uniqid(),
            'code_item' => 'STG-' . rand(100, 999),
            'stock' => 50,
            'unit' => 'BOX',
        ]);

        Livewire::actingAs($admin)
            ->test(MaterialInComponent::class)
            ->call('openCreateModal')
            ->set('inboundItems.0.item_id', (string)$item->id)
            ->assertSet('inboundItems.0.unit', 'BOX')
            ->assertSet('inboundItems.0.current_stock', 50);
    }

    public function test_single_mode_item_combobox_loads_details(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $category = Category::firstOrCreate(['name' => 'Consumable']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST CAT BESI ' . uniqid(),
            'code_item' => 'CB-' . rand(100, 999),
            'stock' => 15,
            'unit' => 'KALENG',
        ]);

        Livewire::actingAs($admin)
            ->test(MaterialInComponent::class)
            ->call('openCreateModal')
            ->set('item_id', (string)$item->id)
            ->assertSet('selectedItemStock', 15)
            ->assertSet('selectedItemUnit', 'KALENG')
            ->assertSet('unit', 'KALENG');
    }

    public function test_reject_modal_renders_item_search_combobox(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MaterialInComponent::class)
            ->call('openCreateRejectModal')
            ->assertSet('isRejectModalOpen', true)
            ->assertSeeHtml('itemSearchCombobox')
            ->assertSeeHtml('Pilih Master Barang');
    }
}

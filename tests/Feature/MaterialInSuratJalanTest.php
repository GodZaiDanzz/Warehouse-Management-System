<?php

namespace Tests\Feature;

use App\Livewire\MaterialInComponent;
use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class MaterialInSuratJalanTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $category = Category::firstOrCreate(['name' => 'Raw Material']);
        $this->item = Item::create([
            'category_id' => $category->id,
            'name' => 'PLAT BESI TEST ' . uniqid(),
            'code_item' => 'PLT-' . rand(1000, 9999),
            'stock' => 10,
            'unit' => 'LEMBAR',
        ]);
    }

    public function test_surat_jalan_is_displayed_in_material_in_table(): void
    {
        $sjNumber = 'SJ-TEST-' . rand(1000, 9999);
        $materialIn = MaterialIn::create([
            'reference_number' => 'IN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'date' => date('Y-m-d'),
            'item_id' => $this->item->id,
            'unit_id' => $this->item->unit_id,
            'quantity' => 5,
            'unit' => 'LEMBAR',
            'supplier' => 'PT Test Logistik',
            'received_by' => 'Staff Gudang',
            'delivery_order_number' => $sjNumber,
            'qc_status' => 'approved',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(MaterialInComponent::class)
            ->assertStatus(200)
            ->assertSee('NO. SURAT JALAN')
            ->assertSee($sjNumber);
    }

    public function test_search_by_surat_jalan_filters_material_in(): void
    {
        $uniqueTag = 'SJ-' . rand(10000, 99999);
        $targetSj = $uniqueTag . '-MATCH';
        $otherSj = $uniqueTag . '-OTHER';

        $inMatch = MaterialIn::create([
            'reference_number' => 'IN-MATCH-' . uniqid(),
            'date' => date('Y-m-d'),
            'item_id' => $this->item->id,
            'unit_id' => $this->item->unit_id,
            'quantity' => 2,
            'unit' => 'LEMBAR',
            'supplier' => 'PT Vendor A',
            'received_by' => 'Petugas A',
            'delivery_order_number' => $targetSj,
            'qc_status' => 'approved',
            'user_id' => $this->user->id,
        ]);

        $inOther = MaterialIn::create([
            'reference_number' => 'IN-OTHER-' . uniqid(),
            'date' => date('Y-m-d'),
            'item_id' => $this->item->id,
            'unit_id' => $this->item->unit_id,
            'quantity' => 3,
            'unit' => 'LEMBAR',
            'supplier' => 'PT Vendor B',
            'received_by' => 'Petugas B',
            'delivery_order_number' => $otherSj,
            'qc_status' => 'approved',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(MaterialInComponent::class)
            ->set('search', $targetSj)
            ->assertSee($targetSj)
            ->assertDontSee($otherSj);
    }

    public function test_create_material_in_saves_surat_jalan(): void
    {
        $sjNumber = 'SJ-NEW-' . rand(1000, 9999);

        Livewire::actingAs($this->user)
            ->test(MaterialInComponent::class)
            ->call('openCreateModal')
            ->assertSet('isModalOpen', true)
            ->assertSeeHtml('No. Surat Jalan / DO')
            ->set('date', date('Y-m-d'))
            ->set('delivery_order_number', $sjNumber)
            ->set('supplier', 'PT Mitra Perkasa')
            ->set('received_by', 'Zaidan')
            ->set('qc_status', 'approved')
            ->set('inboundItems.0.item_id', (string)$this->item->id)
            ->set('inboundItems.0.quantity', '5')
            ->set('inboundItems.0.unit', 'LEMBAR')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isModalOpen', false);

        $this->assertDatabaseHas('material_ins', [
            'po_number' => $sjNumber,
            'supplier' => 'PT Mitra Perkasa',
            'item_id' => $this->item->id,
        ]);
    }

    public function test_edit_material_in_updates_surat_jalan(): void
    {
        $oldSj = 'SJ-OLD-' . rand(1000, 9999);
        $newSj = 'SJ-UPDATED-' . rand(1000, 9999);

        $record = MaterialIn::create([
            'reference_number' => 'IN-EDIT-' . uniqid(),
            'date' => date('Y-m-d'),
            'item_id' => $this->item->id,
            'unit_id' => $this->item->unit_id,
            'quantity' => 8,
            'unit' => 'LEMBAR',
            'supplier' => 'PT Steelindo',
            'received_by' => 'Gudang 1',
            'delivery_order_number' => $oldSj,
            'qc_status' => 'approved',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(MaterialInComponent::class)
            ->call('openEditModal', $record->id)
            ->assertSet('isModalOpen', true)
            ->assertSet('isEditMode', true)
            ->assertSet('delivery_order_number', $oldSj)
            ->set('delivery_order_number', $newSj)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isModalOpen', false);

        $record->refresh();
        $this->assertEquals($newSj, $record->delivery_order_number);
    }
}

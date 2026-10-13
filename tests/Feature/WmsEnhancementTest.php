<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class WmsEnhancementTest extends TestCase
{
    private function getAdmin(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
    }

    private function getPpic(): User
    {
        return User::where('role', 'ppic')->first() ?? User::factory()->create(['role' => 'ppic']);
    }

    public function test_material_in_page_loads_for_admin(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/material-in');
        $response->assertStatus(200);
        $response->assertSee('Penerimaan Material (Material Masuk)');
        $response->assertSee('Catat Penerimaan Barang');
    }

    public function test_ppic_is_restricted_from_material_in(): void
    {
        $ppic = $this->getPpic();

        // PPIC is restricted to only seeing Stok Barang; accessing /material-in redirects to /items
        $response = $this->actingAs($ppic)->get('/material-in');
        $response->assertRedirect('/items');
        $response->assertSessionHas('error');

        // And PPIC cannot access user settings
        $responseBlocked = $this->actingAs($ppic)->get('/users');
        $responseBlocked->assertRedirect('/items');
        $responseBlocked->assertSessionHas('error');
    }

    public function test_material_in_component_saves_and_increments_stock(): void
    {
        $admin = $this->getAdmin();
        $cat = Category::firstOrCreate(['name' => 'Secondary Part']);
        $item = Item::create([
            'category_id' => $cat->id,
            'code_item' => 'TEST-IN-' . rand(100, 999),
            'name' => 'Kabel NYY 4x10mm Test ' . rand(100, 999),
            'stock' => 10,
            'unit' => 'Meter',
            'min_stock' => 5,
        ]);

        $initialStock = $item->stock;

        $this->actingAs($admin);

        // 1. Warehouse records incoming material (physical receipt)
        $component = Livewire::test(\App\Livewire\MaterialInComponent::class)
            ->set('date', date('Y-m-d'))
            ->set('item_id', $item->id)
            ->set('quantity', 25)
            ->set('unit', 'Meter')
            ->set('supplier', 'PT Schneider Electric Indonesia')
            ->set('received_by', 'Zaidan Gudang')
            ->set('notes', 'Kondisi barang prima')
            ->call('save')
            ->assertHasNoErrors();

        $inbound = MaterialIn::where('item_id', $item->id)->latest('id')->first();
        $this->assertNotNull($inbound);
        $this->assertEquals('pending_qc', $inbound->qc_status);

        // Stock does not increment while pending QC (Prompt Section 4)
        $item->refresh();
        $this->assertEquals($initialStock, $item->stock);

        // 2. QC Inspector approves the incoming material
        $component->call('openQcModal', $inbound->id)
            ->set('qcDecision', 'approved')
            ->set('qcNotes', 'Lolos uji inspeksi fisik')
            ->call('submitQc')
            ->assertHasNoErrors();

        $item->refresh();
        $this->assertEquals($initialStock + 25, $item->stock);

        $this->assertDatabaseHas('material_ins', [
            'item_id' => $item->id,
            'quantity' => 25,
            'qc_status' => 'approved',
            'supplier' => 'PT Schneider Electric Indonesia',
        ]);
    }

    public function test_consumable_incoming_material_directly_approved_without_qc_and_increments_stock(): void
    {
        $admin = $this->getAdmin();
        $cat = Category::firstOrCreate(['name' => 'Consumable']);
        $item = Item::create([
            'category_id' => $cat->id,
            'code_item' => 'TEST-CONS-' . rand(100, 999),
            'name' => 'Batu Gerinda Test ' . rand(100, 999),
            'stock' => 50,
            'unit' => 'PCS',
            'min_stock' => 10,
        ]);

        $initialStock = $item->stock;
        $this->actingAs($admin);

        // Warehouse records incoming consumable material
        $component = Livewire::test(\App\Livewire\MaterialInComponent::class)
            ->set('date', date('Y-m-d'))
            ->set('item_id', $item->id)
            ->set('quantity', 20)
            ->set('unit', 'PCS')
            ->set('supplier', 'CV Teknik Mandiri')
            ->set('received_by', 'Petugas Gudang')
            ->set('notes', 'Batu gerinda baru datang')
            ->call('save')
            ->assertHasNoErrors();

        // Must directly be approved without requiring QC
        $inbound = MaterialIn::where('item_id', $item->id)->latest('id')->first();
        $this->assertNotNull($inbound);
        $this->assertEquals('approved', $inbound->qc_status);

        // Stock must directly increment
        $item->refresh();
        $this->assertEquals($initialStock + 20, $item->stock);

        // Opening QC modal on consumable should be blocked
        $component->call('openQcModal', $inbound->id);
        $this->assertFalse($component->get('isQcModalOpen'));
    }

    public function test_database_backup_page_loads_for_admin(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/database-backup');
        $response->assertStatus(200);
        $response->assertSee('Backup Database');
        $response->assertSee('Download File Backup (.sql)');
    }

    public function test_database_backup_download_returns_sql_dump(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/database-backup/download');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/sql');
    }

    public function test_ppic_is_blocked_from_database_backup(): void
    {
        $ppic = $this->getPpic();

        $response = $this->actingAs($ppic)->get('/database-backup');
        $response->assertRedirect('/items');
        $response->assertSessionHas('error');
    }

    public function test_notification_dropdown_renders_properly(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Notifikasi');
        $response->assertSee('Lihat Seluruh Stok Barang');
        // Verify old template dummy names are gone
        $response->assertDontSee('Terry Franci');
        $response->assertDontSee('Alex Johnson');
        $response->assertDontSee('Project - Nganter App');
    }

    public function test_batch_multi_item_inbound_saves_multiple_records_and_updates_stocks(): void
    {
        $admin = $this->getAdmin();
        $cat = Category::firstOrCreate(['name' => 'General Equipment']);
        $catConsumable = Category::firstOrCreate(['name' => 'Consumable']);

        $itemA = Item::create([
            'category_id' => $cat->id,
            'code_item' => 'BATCH-A-' . rand(100, 999),
            'name' => 'Item Batch A ' . rand(100, 999),
            'stock' => 10,
            'unit' => 'PCS',
            'min_stock' => 5,
        ]);

        $itemB = Item::create([
            'category_id' => $catConsumable->id,
            'code_item' => 'BATCH-B-' . rand(100, 999),
            'name' => 'Item Batch B ' . rand(100, 999),
            'stock' => 20,
            'unit' => 'Pack',
            'min_stock' => 5,
        ]);

        $this->actingAs($admin);

        $component = \Livewire\Livewire::test(\App\Livewire\MaterialInComponent::class)
            ->call('openCreateModal')
            ->assertSee('Catat Penerimaan Fisik Barang Masuk (Batch / Banyak Barang)')
            ->assertSee('Daftar Rincian Barang yang Diterima')
            ->assertSee('Tambah Barang')
            ->set('date', date('Y-m-d'))
            ->set('supplier', 'PT Multi Delivery Vendor')
            ->set('received_by', 'Zaidan Staff Gudang')
            ->set('qc_status', 'approved')
            ->set('notes', 'Surat Jalan SJ-BATCH-999')
            // Row 0
            ->set('inboundItems.0.item_id', $itemA->id)
            ->set('inboundItems.0.quantity', 15)
            ->set('inboundItems.0.unit', 'PCS')
            // Add row 1
            ->call('addInboundItemRow')
            ->set('inboundItems.1.item_id', $itemB->id)
            ->set('inboundItems.1.quantity', 25)
            ->set('inboundItems.1.unit', 'Pack')
            ->call('save')
            ->assertHasNoErrors();

        // Check both records in material_ins
        $this->assertDatabaseHas('material_ins', [
            'item_id' => $itemA->id,
            'quantity' => 15,
            'supplier' => 'PT Multi Delivery Vendor',
            'qc_status' => 'approved',
        ]);

        $this->assertDatabaseHas('material_ins', [
            'item_id' => $itemB->id,
            'quantity' => 25,
            'supplier' => 'PT Multi Delivery Vendor',
            'qc_status' => 'approved',
        ]);

        // Check stocks incremented
        $itemA->refresh();
        $this->assertEquals(25, $itemA->stock); // 10 + 15

        $itemB->refresh();
        $this->assertEquals(45, $itemB->stock); // 20 + 25
    }
}

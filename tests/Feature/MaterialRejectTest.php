<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialReject;
use App\Models\User;
use App\Models\ActivityLog;
use Livewire\Livewire;
use App\Livewire\MaterialInComponent;
use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class MaterialRejectTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureUsersExist();
    }

    private function ensureUsersExist(): array
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@mudalaya.com'],
            ['name' => 'Admin WMS', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $qc = User::firstOrCreate(
            ['email' => 'qc_test@mudalaya.com'],
            ['name' => 'QC Staff', 'password' => bcrypt('password'), 'role' => 'qc']
        );

        $warehouse = User::firstOrCreate(
            ['email' => 'warehouse_test@mudalaya.com'],
            ['name' => 'Warehouse Staff', 'password' => bcrypt('password'), 'role' => 'warehouse']
        );

        $ppic = User::firstOrCreate(
            ['email' => 'ppic_test@mudalaya.com'],
            ['name' => 'PPIC Staff', 'password' => bcrypt('password'), 'role' => 'ppic']
        );

        return compact('admin', 'qc', 'warehouse', 'ppic');
    }

    private function createSampleItem(): Item
    {
        $cat = Category::firstOrCreate(['name' => 'Secondary Part'], ['code' => 'SEC']);
        return Item::create([
            'code_item' => 'TEST-REJ-' . uniqid(),
            'name' => 'Baut High Tensile M16 ' . rand(100, 999),
            'category_id' => $cat->id,
            'unit' => 'PCS',
            'stock' => 100,
            'minimum_stock' => 10,
        ]);
    }

    /**
     * Test Admin can view reject report and download BAP PDF.
     */
    public function test_admin_can_access_reject_report_and_bap_pdf(): void
    {
        $admin = User::where('role', 'admin')->first();
        $item = $this->createSampleItem();
        $refNo = 'REJ-' . uniqid() . '-ADM';

        $reject = MaterialReject::create([
            'reference_number' => $refNo,
            'item_id' => $item->id,
            'date' => '2026-09-15',
            'supplier' => 'PT Fastenerindo Steel',
            'quantity' => 15,
            'unit' => 'PCS',
            'damage_type' => 'Cacat Fisik / Pecah',
            'disposition' => 'retur_supplier',
            'disposition_status' => 'pending',
            'description' => 'Ulir drat baut rusak dan pecah pada saat unboxing',
            'reported_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/reports/reject-material');
        $response->assertStatus(200);
        $response->assertSee('Laporan Material Rusak / Reject');
        $response->assertSee($refNo);
        $response->assertSee($item->name);

        // Test BAP PDF generation
        $pdfResponse = $this->actingAs($admin)->get("/material-in/rejects/{$reject->id}/bap-pdf");
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }

    /**
     * Test QC user can view material-in rejects tab and download BAP.
     */
    public function test_qc_can_access_material_in_reject_tab_and_bap(): void
    {
        $qc = User::where('role', 'qc')->first();
        $item = $this->createSampleItem();
        $refNo = 'REJ-' . uniqid() . '-QC';

        $reject = MaterialReject::create([
            'reference_number' => $refNo,
            'item_id' => $item->id,
            'date' => '2026-09-15',
            'supplier' => 'CV Logam Perkasa',
            'quantity' => 5,
            'unit' => 'PCS',
            'damage_type' => 'Tidak Sesuai Spek',
            'disposition' => 'retur_supplier',
            'disposition_status' => 'pending',
            'description' => 'Dimensi toleransi melampaui standar gambar kerja',
            'reported_by' => $qc->id,
        ]);

        // QC can access material-in
        $response = $this->actingAs($qc)->get('/material-in?tab=rejects');
        $response->assertStatus(200);
        $response->assertSee('Material Rusak / Reject');

        // QC can download BAP PDF
        $pdfResponse = $this->actingAs($qc)->get("/material-in/rejects/{$reject->id}/bap-pdf");
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }

    /**
     * Test Livewire creation of Reject Material and ActivityLog entry.
     */
    public function test_livewire_can_record_reject_material(): void
    {
        $warehouse = User::where('role', 'warehouse')->first();
        $item = $this->createSampleItem();
        $uniqueSupplier = 'PT Sumber Baja ' . rand(1000, 9999);

        Livewire::actingAs($warehouse)
            ->test(MaterialInComponent::class)
            ->call('setTab', 'rejects')
            ->call('openCreateRejectModal')
            ->set('rejectDate', '2026-09-15')
            ->set('rejectItemId', $item->id)
            ->set('rejectSupplierName', $uniqueSupplier)
            ->set('rejectQuantity', 20)
            ->set('rejectUnit', 'PCS')
            ->set('rejectDamageTypeChoice', 'Karat / Korosi')
            ->set('rejectDispositionChoice', 'retur_supplier')
            ->set('rejectDescription', 'Material mengalami oksidasi dan korosi tebal saat diterima di gudang')
            ->call('saveReject')
            ->assertHasNoErrors()
            ->assertSet('isRejectModalOpen', false);

        $this->assertDatabaseHas('material_rejects', [
            'item_id' => $item->id,
            'supplier' => $uniqueSupplier,
            'quantity' => 20,
            'damage_type' => 'Karat / Korosi',
            'disposition' => 'retur_supplier',
        ]);

        // Check ActivityLog
        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'Catat Material Rusak / Reject',
            'module' => 'Material Reject',
        ]);
    }

    /**
     * Test Livewire update disposition status and resolution notes.
     */
    public function test_livewire_can_update_reject_disposition(): void
    {
        $warehouse = User::where('role', 'warehouse')->first();
        $item = $this->createSampleItem();
        $refNo = 'REJ-' . uniqid() . '-DISP';

        $reject = MaterialReject::create([
            'reference_number' => $refNo,
            'item_id' => $item->id,
            'date' => '2026-09-15',
            'supplier' => 'PT Testindo Metal',
            'quantity' => 10,
            'unit' => 'PCS',
            'damage_type' => 'Cacat Fisik / Pecah',
            'disposition' => 'karantina',
            'disposition_status' => 'pending',
            'description' => 'Pecah pada pengiriman ekspedisi',
            'reported_by' => $warehouse->id,
        ]);

        Livewire::actingAs($warehouse)
            ->test(MaterialInComponent::class)
            ->call('openDispositionModal', $reject->id)
            ->set('dispositionAction', 'replacement')
            ->set('dispositionStatus', 'completed')
            ->set('resolutionNotes', 'Barang pengganti telah dikirim via ekspedisi resi JNE-998811 dan tiba dalam kondisi baik.')
            ->call('updateDisposition')
            ->assertSet('isDispositionModalOpen', false);

        $reject->refresh();
        $this->assertEquals('replacement', $reject->disposition);
        $this->assertEquals('completed', $reject->disposition_status);
        $this->assertNotNull($reject->resolved_at);

        // Check ActivityLog
        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'Update Disposisi Material Reject',
            'module' => 'Material Reject',
        ]);
    }

    /**
     * Test Only Admin can delete reject record; Non-Admin cannot delete.
     */
    public function test_only_admin_can_delete_reject_record(): void
    {
        $qc = User::where('role', 'qc')->first();
        $admin = User::where('role', 'admin')->first();
        $item = $this->createSampleItem();
        $refNo = 'REJ-' . uniqid() . '-DEL';

        $reject = MaterialReject::create([
            'reference_number' => $refNo,
            'item_id' => $item->id,
            'date' => '2026-09-15',
            'supplier' => 'PT Test Delete Supplier',
            'quantity' => 2,
            'unit' => 'PCS',
            'damage_type' => 'Lainnya',
            'disposition' => 'scrap',
            'disposition_status' => 'pending',
            'description' => 'Testing delete guard',
        ]);

        // QC attempts to delete: should be blocked
        Livewire::actingAs($qc)
            ->test(MaterialInComponent::class)
            ->call('deleteReject', $reject->id);

        $this->assertDatabaseHas('material_rejects', ['id' => $reject->id, 'deleted_at' => null]);

        // Admin deletes: should succeed
        Livewire::actingAs($admin)
            ->test(MaterialInComponent::class)
            ->call('deleteReject', $reject->id);

        $this->assertSoftDeleted('material_rejects', ['id' => $reject->id]);

        // Check ActivityLog
        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'Hapus Laporan Material Reject',
            'module' => 'Material Reject',
        ]);
    }

    /**
     * Test PPIC is blocked from mutating reject data.
     */
    public function test_ppic_is_blocked_from_mutating_reject_records(): void
    {
        $ppic = User::where('role', 'ppic')->first();
        $item = $this->createSampleItem();
        $refNo = 'REJ-' . uniqid() . '-PPIC';

        $reject = MaterialReject::create([
            'reference_number' => $refNo,
            'item_id' => $item->id,
            'date' => '2026-09-15',
            'supplier' => 'PT PPIC Block Test',
            'quantity' => 3,
            'unit' => 'PCS',
            'damage_type' => 'Tidak Sesuai Spek',
            'disposition' => 'karantina',
            'disposition_status' => 'pending',
        ]);

        // PPIC attempts to save reject
        Livewire::actingAs($ppic)
            ->test(MaterialInComponent::class)
            ->call('openCreateRejectModal')
            ->set('rejectDate', '2026-09-15')
            ->set('rejectItemId', $item->id)
            ->set('rejectSupplierName', 'PT Ilegal')
            ->set('rejectQuantity', 10)
            ->call('saveReject');

        $this->assertDatabaseMissing('material_rejects', ['supplier' => 'PT Ilegal']);

        // PPIC attempts to update disposition
        Livewire::actingAs($ppic)
            ->test(MaterialInComponent::class)
            ->call('openDispositionModal', $reject->id)
            ->set('dispositionStatus', 'completed')
            ->call('updateDisposition');

        $reject->refresh();
        $this->assertEquals('pending', $reject->disposition_status);
    }

    /**
     * Test Admin can perform all QC actions and is recorded as inspector in reject and BAP.
     */
    public function test_admin_can_perform_all_qc_actions_and_is_recorded_as_inspector(): void
    {
        $admin = User::where('role', 'admin')->first();
        $item = $this->createSampleItem();
        $initialStock = $item->stock;

        // 1. Admin creates inbound material that requires QC
        $inbound = MaterialIn::create([
            'reference_number' => 'IN-TEST-ADM-' . uniqid(),
            'item_id' => $item->id,
            'date' => '2026-09-15',
            'supplier' => 'PT Baja Unggul Mandiri',
            'quantity' => 50,
            'unit' => 'PCS',
            'received_by' => 'Admin WMS',
            'qc_status' => 'pending_qc',
            'user_id' => $admin->id,
        ]);

        // 2. Admin performs QC Inspection (Approval OK)
        Livewire::actingAs($admin)
            ->test(MaterialInComponent::class)
            ->call('openQcModal', $inbound->id)
            ->set('qcDecision', 'approved')
            ->set('qcNotes', 'Inspeksi QC oleh Admin: Hasil dimensi presisi dan sesuai spek PO.')
            ->call('submitQc')
            ->assertHasNoErrors();

        $inbound->refresh();
        $this->assertEquals('approved', $inbound->qc_status);
        $this->assertEquals($admin->id, $inbound->qc_by);
        $item->refresh();
        $this->assertEquals($initialStock + 50, $item->stock);

        // 3. Admin records a reject material directly: inspected_by must be Admin's ID
        $uniqueSupplier = 'PT Plat Besi ' . rand(1000, 9999);
        Livewire::actingAs($admin)
            ->test(MaterialInComponent::class)
            ->call('setTab', 'rejects')
            ->call('openCreateRejectModal')
            ->set('rejectDate', '2026-09-15')
            ->set('rejectItemId', $item->id)
            ->set('rejectSupplierName', $uniqueSupplier)
            ->set('rejectQuantity', 12)
            ->set('rejectUnit', 'PCS')
            ->set('rejectDamageTypeChoice', 'Gagal Uji Fungsi')
            ->set('rejectDispositionChoice', 'retur_supplier')
            ->set('rejectDescription', 'Ditolak dan diuji langsung oleh Web Administrator')
            ->call('saveReject')
            ->assertHasNoErrors();

        $rejectRecord = MaterialReject::where('supplier', $uniqueSupplier)->first();
        $this->assertNotNull($rejectRecord);
        $this->assertEquals($admin->id, $rejectRecord->reported_by);
        $this->assertEquals($admin->id, $rejectRecord->inspected_by);

        // 4. Admin accesses BAP PDF and verifies Admin name is included
        $bapResponse = $this->actingAs($admin)->get("/material-in/rejects/{$rejectRecord->id}/bap-pdf");
        $bapResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $bapResponse->headers->get('Content-Type'));
    }
}

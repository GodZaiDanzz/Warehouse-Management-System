<?php

namespace Tests\Feature;

use App\Livewire\MaterialInComponent;
use App\Livewire\PurchaseOrderComponent;
use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockLog;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $procurementUser;
    protected User $warehouseUser;
    protected Item $testItem;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Get or create users
        $this->adminUser = User::where('role', 'admin')->first() ?? User::factory()->create([
            'name' => 'Web Administrator',
            'email' => 'admin@mudalaya.com',
            'role' => 'admin',
        ]);

        $this->procurementUser = User::where('role', 'procurement')->first() ?? User::create([
            'name' => 'Staff Pengadaan',
            'email' => 'procurement_test_' . uniqid() . '@mudalaya.com',
            'password' => bcrypt('password123'),
            'role' => 'procurement',
        ]);

        $this->warehouseUser = User::where('role', 'warehouse')->first() ?? User::create([
            'name' => 'Staff Gudang',
            'email' => 'warehouse_test_' . uniqid() . '@mudalaya.com',
            'password' => bcrypt('password123'),
            'role' => 'warehouse',
        ]);

        // 2. Get or create test item
        $category = Category::first() ?? Category::create(['name' => 'Raw Material']);
        $unit = Unit::first() ?? Unit::create(['name' => 'Pieces', 'symbol' => 'PCS', 'is_active' => true]);

        $this->testItem = Item::first() ?? Item::create([
            'code_item' => 'TEST-PO-01',
            'name' => 'Pelat Baja ASTM A36 Test',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'unit' => 'PCS',
            'stock' => 50,
            'location' => 'STORAGE 1',
            'unit_price' => 125000,
        ]);
    }

    /**
     * Test procurement role user can access PO index page.
     */
    public function test_procurement_user_can_access_purchase_orders_page(): void
    {
        $response = $this->actingAs($this->procurementUser)
            ->get(route('purchase-orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Order (PO)');
    }

    /**
     * Test warehouse user is restricted from accessing PO page and menu.
     */
    public function test_warehouse_user_is_restricted_from_purchase_orders(): void
    {
        // 1. HTTP route restriction
        $response = $this->actingAs($this->warehouseUser)
            ->get(route('purchase-orders.index'));

        $response->assertRedirect(route('items.index'));
        $response->assertSessionHas('error');

        // 2. Menu visibility check: Purchase Order (PO) must NOT exist in Warehouse menu
        auth()->login($this->warehouseUser);
        $groups = \App\Helpers\MenuHelper::getMenuGroups();
        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {
                $this->assertNotEquals('/purchase-orders', $item['path']);
                $this->assertNotEquals('Purchase Order (PO)', $item['name']);
            }
        }
    }

    /**
     * Test procurement role user has dedicated PO menu group.
     */
    public function test_procurement_user_has_dedicated_purchase_order_menu(): void
    {
        auth()->login($this->procurementUser);
        $groups = \App\Helpers\MenuHelper::getMenuGroups();

        $foundPo = false;
        foreach ($groups as $group) {
            if ($group['title'] === 'PENGADAAN (PROCUREMENT)') {
                foreach ($group['items'] as $item) {
                    if ($item['path'] === '/purchase-orders') {
                        $foundPo = true;
                    }
                }
            }
        }

        $this->assertTrue($foundPo, 'Procurement role must have Purchase Order (PO) in menu.');
    }

    /**
     * Test admin user has full access to all features including Purchase Order menu and page.
     */
    public function test_admin_user_can_access_purchase_orders_and_has_menu(): void
    {
        // 1. Admin can access PO index page
        $response = $this->actingAs($this->adminUser)
            ->get(route('purchase-orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Order (PO)');

        // 2. Admin has Purchase Order (PO) in dedicated PENGADAAN (PROCUREMENT) menu group
        auth()->login($this->adminUser);
        $groups = \App\Helpers\MenuHelper::getMenuGroups();

        $foundPo = false;
        foreach ($groups as $group) {
            if ($group['title'] === 'PENGADAAN (PROCUREMENT)') {
                foreach ($group['items'] as $item) {
                    if ($item['path'] === '/purchase-orders') {
                        $foundPo = true;
                    }
                }
            }
        }

        $this->assertTrue($foundPo, 'Web Administrator must have Purchase Order (PO) in menu.');
    }

    /**
     * Test procurement role user can create a new Purchase Order via Livewire.
     */
    public function test_procurement_user_can_create_purchase_order(): void
    {
        $poNumber = 'PO-TEST-' . strtoupper(uniqid());

        Livewire::actingAs($this->procurementUser)
            ->test(PurchaseOrderComponent::class)
            ->call('openCreateModal')
            ->set('po_number', $poNumber)
            ->set('po_date', date('Y-m-d'))
            ->set('supplier', 'PT Baja Perkasa Nusantara')
            ->set('notes', 'Pengiriman tahap 1 untuk fabrikasi')
            ->set('items.0.item_id', $this->testItem->id)
            ->set('items.0.quantity_ordered', 20)
            ->set('items.0.unit', 'PCS')
            ->set('items.0.unit_price', 150000)
            ->call('saveCreate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => $poNumber,
            'supplier' => 'PT Baja Perkasa Nusantara',
            'status' => 'open',
            'created_by' => $this->procurementUser->id,
        ]);

        $po = PurchaseOrder::where('po_number', $poNumber)->first();
        $this->assertNotNull($po);
        $this->assertCount(1, $po->items);
        $this->assertEquals(20, (float) $po->items->first()->quantity_ordered);
        $this->assertEquals(0, (float) $po->items->first()->quantity_received);
        $this->assertEquals(20, (float) $po->items->first()->quantity_remaining);
    }

    /**
     * Test warehouse can receive items from PO with immediate ACC and automatic stock increment (no QC step).
     */
    public function test_warehouse_can_receive_from_po_with_immediate_acc_and_stock_increment(): void
    {
        $initialStock = (float) $this->testItem->fresh()->stock;

        // 1. Create a PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-RCV-' . strtoupper(uniqid()),
            'po_date' => date('Y-m-d'),
            'supplier' => 'CV Mega Baja Mandiri',
            'status' => 'open',
            'created_by' => $this->procurementUser->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_id' => $this->testItem->id,
            'quantity_ordered' => 15,
            'quantity_received' => 0,
            'unit' => 'PCS',
            'unit_price' => 120000,
        ]);

        // 2. Warehouse receives entire quantity (15 units)
        $deliveryOrderNumber = 'SJ-TEST-' . rand(1000, 9999);

        Livewire::actingAs($this->warehouseUser)
            ->test(MaterialInComponent::class)
            ->call('openReceivePoModal')
            ->set('selectedPoId', $po->id)
            ->assertSet('poDeliveryOrderNumber', '')
            ->set('poDeliveryOrderNumber', $deliveryOrderNumber)
            ->set('poReceivedDate', date('Y-m-d'))
            ->set('poReceivedBy', 'Doni (Warehouse)')
            ->set('poItemsToReceive.0.quantity_to_receive', 15)
            ->set('poItemsToReceive.0.selected', true)
            ->call('submitReceivePo')
            ->assertHasNoErrors();

        // 3. Assert MaterialIn created with qc_status = approved
        $materialIn = MaterialIn::where('purchase_order_id', $po->id)->first();
        $this->assertNotNull($materialIn, 'Record MaterialIn harus tercipta dari penerimaan PO.');
        $this->assertEquals('approved', $materialIn->qc_status, 'Penerimaan PO harus langsung berstatus approved (bebas QC/ACC langsung).');
        $this->assertEquals($deliveryOrderNumber, $materialIn->delivery_order_number);
        $this->assertEquals(15, (float) $materialIn->quantity);

        // 4. Assert Master Item stock immediately incremented by 15
        $newStock = (float) $this->testItem->fresh()->stock;
        $this->assertEquals($initialStock + 15, $newStock, 'Stok master item harus otomatis bertambah sebesar 15 unit.');

        // 5. Assert StockLog created
        $this->assertDatabaseHas('stock_logs', [
            'item_id' => $this->testItem->id,
            'type' => 'in',
            'quantity' => 15,
            'recipient_or_provider' => $po->supplier,
        ]);

        // 6. Assert PO status is completed
        $poFresh = $po->fresh();
        $this->assertEquals('completed', $poFresh->status, 'Status PO harus berubah menjadi completed.');
        $this->assertEquals(15, (float) $poItem->fresh()->quantity_received);
        $this->assertEquals(0, (float) $poItem->fresh()->quantity_remaining);
    }

    /**
     * Test partial delivery updates PO status to partial, and second delivery completes it.
     */
    public function test_partial_delivery_lifecycle_transitions_correctly(): void
    {
        $initialStock = (float) $this->testItem->fresh()->stock;

        // Create PO for 30 units
        $po = PurchaseOrder::create([
            'po_number' => 'PO-PARTIAL-' . strtoupper(uniqid()),
            'po_date' => date('Y-m-d'),
            'supplier' => 'PT Mitra Logam Abadi',
            'status' => 'open',
            'created_by' => $this->procurementUser->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_id' => $this->testItem->id,
            'quantity_ordered' => 30,
            'quantity_received' => 0,
            'unit' => 'PCS',
        ]);

        // 1. First delivery: Warehouse receives only 10 units (Partial)
        Livewire::actingAs($this->warehouseUser)
            ->test(MaterialInComponent::class)
            ->call('openReceivePoModal')
            ->set('selectedPoId', $po->id)
            ->set('poDeliveryOrderNumber', 'SJ-PARTIAL-01')
            ->set('poReceivedDate', date('Y-m-d'))
            ->set('poReceivedBy', 'Budi Gudang')
            ->set('poItemsToReceive.0.quantity_to_receive', 10)
            ->set('poItemsToReceive.0.selected', true)
            ->call('submitReceivePo')
            ->assertHasNoErrors();

        // Verify partial state
        $this->assertEquals('partial', $po->fresh()->status, 'Status PO setelah pengiriman parsial harus partial.');
        $this->assertEquals(10, (float) $poItem->fresh()->quantity_received);
        $this->assertEquals(20, (float) $poItem->fresh()->quantity_remaining);
        $this->assertEquals($initialStock + 10, (float) $this->testItem->fresh()->stock);

        // 2. Second delivery: Warehouse receives remaining 20 units (Completion)
        Livewire::actingAs($this->warehouseUser)
            ->test(MaterialInComponent::class)
            ->call('openReceivePoModal')
            ->set('selectedPoId', $po->id)
            ->set('poDeliveryOrderNumber', 'SJ-PARTIAL-02')
            ->set('poReceivedDate', date('Y-m-d'))
            ->set('poReceivedBy', 'Budi Gudang')
            ->set('poItemsToReceive.0.quantity_to_receive', 20)
            ->set('poItemsToReceive.0.selected', true)
            ->call('submitReceivePo')
            ->assertHasNoErrors();

        // Verify completed state
        $this->assertEquals('completed', $po->fresh()->status, 'Status PO setelah sisa pengiriman tiba harus completed.');
        $this->assertEquals(30, (float) $poItem->fresh()->quantity_received);
        $this->assertEquals(0, (float) $poItem->fresh()->quantity_remaining);
        $this->assertEquals($initialStock + 30, (float) $this->testItem->fresh()->stock);
    }

    /**
     * Test printable PO view renders correctly.
     */
    public function test_po_printable_view_renders_successfully(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-PRINT-' . strtoupper(uniqid()),
            'po_date' => date('Y-m-d'),
            'supplier' => 'PT Krakatau Posco',
            'status' => 'open',
            'created_by' => $this->procurementUser->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_id' => $this->testItem->id,
            'quantity_ordered' => 50,
            'quantity_received' => 0,
            'unit' => 'PCS',
            'unit_price' => 200000,
        ]);

        $response = $this->actingAs($this->procurementUser)
            ->get(route('purchase-orders.print', $po->id));

        $response->assertStatus(200);
        $response->assertSee($po->po_number);
        $response->assertSee('PT Krakatau Posco');
        $response->assertSee('PURCHASE ORDER');
    }
}


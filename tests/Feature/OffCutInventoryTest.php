<?php

namespace Tests\Feature;

use App\Livewire\MaterialInComponent;
use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\StockLog;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class OffCutInventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::first();
        if ($admin) {
            $this->actingAs($admin);
        }
    }

    public function test_raw_material_offcut_category_exists_and_maps_to_storage_2(): void
    {
        $offcutCategory = Category::where('name', 'Raw Material - Off Cut')->first();
        $this->assertNotNull($offcutCategory, 'Kategori Raw Material - Off Cut wajib ada.');
        $this->assertTrue($offcutCategory->isOffCut(), 'Category harus dikenali sebagai isOffCut().');
        $this->assertTrue($offcutCategory->isRawMaterial(), 'Category off-cut harus diklasifikasikan sebagai Raw Material untuk STORAGE 2.');
    }

    public function test_item_model_supports_offcut_relationships_and_code_generation(): void
    {
        $cat = Category::where('name', 'Raw Material - Off Cut')->first();
        $this->assertNotNull($cat);

        $offCode = Item::generateUniqueCodeItem($cat->id);
        $this->assertStringStartsWith('OFF-', $offCode, 'Kode unik item off-cut harus berawalan OFF-.');

        $parent = Item::where('is_offcut', false)
            ->where(function ($q) {
                $q->where('code_item', 'like', 'RAW%')
                    ->orWhereHas('category', function ($qc) {
                        $qc->where('name', 'like', '%raw%');
                    });
            })->first();

        $this->assertNotNull($parent, 'Minimal 1 raw material induk harus ada di database.');

        $unit = Unit::first() ?? Unit::create(['name' => 'Pieces', 'symbol' => 'PCS', 'is_active' => true]);
        $cat = Category::where('name', 'Raw Material - Off Cut')->first();

        $offcutItem = Item::create([
            'code_item' => $offCode,
            'name' => "{$parent->name} (Sisa 1.5m)",
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'unit' => $unit->symbol,
            'stock' => 1,
            'min_stock' => 0,
            'storage' => 'STORAGE 2',
            'location' => 'STORAGE 2 (Rak Off-Cut)',
            'is_offcut' => true,
            'parent_item_id' => $parent->id,
            'dimension' => '1.5m',
            'origin_project' => 'Project Test Ref',
            'heat_number' => 'HT-TEST-001',
        ]);

        $this->assertTrue($offcutItem->isOffCut());
        $this->assertTrue($offcutItem->isRawMaterial());
        $this->assertEquals('STORAGE 2', $offcutItem->storage);
        $this->assertNotNull($offcutItem->parentItem);
        $this->assertEquals($parent->id, $offcutItem->parentItem->id);
    }

    public function test_material_in_component_submits_offcut_return_and_creates_records(): void
    {
        $parent = Item::where('is_offcut', false)
            ->where(function ($q) {
                $q->where('code_item', 'like', 'RAW%')
                    ->orWhereHas('category', function ($qc) {
                        $qc->where('name', 'like', '%raw%');
                    });
            })->first();

        $this->assertNotNull($parent);

        $initialParentStock = (float) $parent->stock;

        $component = Livewire::test(MaterialInComponent::class)
            ->call('openOffCutModal')
            ->set('offCutParentItemId', $parent->id)
            ->set('offCutDimension', 'P: 2.25m, OD: 4"')
            ->set('offCutQuantity', 2)
            ->set('offCutUnit', $parent->unit ?: 'PCS')
            ->set('offCutOriginProjectName', 'Proyek Tangki Balongan')
            ->set('offCutHeatNumber', 'HT-BALONGAN-889')
            ->set('offCutLocation', 'STORAGE 2 (Rak Sisa Potongan)')
            ->set('offCutNotes', 'Sisa cutting spool pipa line 02')
            ->call('submitOffCutReturn');

        $component->assertHasNoErrors();

        // 1. Check created off-cut item
        $offItem = Item::where('parent_item_id', $parent->id)
            ->where('dimension', 'P: 2.25m, OD: 4"')
            ->where('heat_number', 'HT-BALONGAN-889')
            ->first();

        $this->assertNotNull($offItem, 'Item off-cut baru harus berhasil terdaftar.');
        $this->assertTrue($offItem->is_offcut);
        $this->assertEquals(2.0, (float) $offItem->stock);
        $this->assertEquals('STORAGE 2', $offItem->storage);
        $this->assertStringStartsWith('OFF-', $offItem->code_item);

        // 2. Parent item stock must remain unchanged (virgin stock was already deducted upon release)
        $parent->refresh();
        $this->assertEquals($initialParentStock, (float) $parent->stock, 'Stok virgin material induk tidak boleh terganggu.');

        // 3. MaterialIn record created with approval status
        $matIn = MaterialIn::where('item_id', $offItem->id)->latest('id')->first();
        $this->assertNotNull($matIn);
        $this->assertEquals('approved', $matIn->qc_status);
        $this->assertStringContainsString('Retur Workshop', $matIn->supplier);
        $this->assertStringStartsWith('IN-OFF-', $matIn->reference_number);

        // 4. StockLog created
        $stockLog = StockLog::where('item_id', $offItem->id)->latest('id')->first();
        $this->assertNotNull($stockLog);
        $this->assertEquals(2.0, (float) $stockLog->quantity);
    }

    public function test_submitting_identical_offcut_increments_existing_stock_instead_of_duplicating(): void
    {
        $parent = Item::where('is_offcut', false)
            ->where(function ($q) {
                $q->where('code_item', 'like', 'RAW%')
                    ->orWhereHas('category', function ($qc) {
                        $qc->where('name', 'like', '%raw%');
                    });
            })->first();

        $dimension = 'P: 1.80m, Lebar: 300mm';
        $heat = 'HT-MERGE-999';

        // First return: 1 piece
        Livewire::test(MaterialInComponent::class)
            ->set('offCutParentItemId', $parent->id)
            ->set('offCutDimension', $dimension)
            ->set('offCutQuantity', 1)
            ->set('offCutHeatNumber', $heat)
            ->call('submitOffCutReturn');

        $offItem = Item::where('parent_item_id', $parent->id)
            ->where('dimension', $dimension)
            ->where('heat_number', $heat)
            ->first();

        $this->assertNotNull($offItem);
        $this->assertEquals(1.0, (float) $offItem->stock);
        $offItemId = $offItem->id;

        // Second return with exact same dimension & heat number: 3 pieces
        Livewire::test(MaterialInComponent::class)
            ->set('offCutParentItemId', $parent->id)
            ->set('offCutDimension', $dimension)
            ->set('offCutQuantity', 3)
            ->set('offCutHeatNumber', $heat)
            ->call('submitOffCutReturn');

        // Check that item is merged, not duplicated
        $matchingCount = Item::where('parent_item_id', $parent->id)
            ->where('dimension', $dimension)
            ->where('heat_number', $heat)
            ->count();

        $this->assertEquals(1, $matchingCount, 'Item off-cut dengan dimensi dan heat no sama harus di-merge, bukan duplikat.');

        $offItem->refresh();
        $this->assertEquals(4.0, (float) $offItem->stock, 'Stok item off-cut harus bertambah dari 1 menjadi 4.');
        $this->assertEquals($offItemId, $offItem->id);
    }
}

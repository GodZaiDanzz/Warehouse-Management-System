<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class DecimalQuantityInventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Item $kgItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test_decimal@example.com'],
            [
                'name' => 'Admin Decimal Tester',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $category = Category::firstOrCreate(['name' => 'Consumable']);

        $this->kgItem = Item::create([
            'category_id' => $category->id,
            'name' => 'Kawat Las Test Decimal',
            'stock' => 10.0,
            'unit' => 'KG',
            'min_stock' => 2.5,
        ]);
    }

    public function test_decimal_material_in_and_stock_increment()
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\MaterialInComponent::class)
            ->set('activeTab', 'inbound')
            ->set('item_id', $this->kgItem->id)
            ->set('quantity', 2.5)
            ->set('unit', 'KG')
            ->set('supplier', 'PT Las Mandiri')
            ->set('received_by', 'Warehouse Staff')
            ->set('date', date('Y-m-d'))
            ->call('save');

        $this->kgItem->refresh();
        $this->assertEquals(12.5, (float)$this->kgItem->stock);

        $matIn = MaterialIn::where('item_id', $this->kgItem->id)->latest('id')->first();
        $this->assertNotNull($matIn);
        $this->assertEquals(2.5, (float)$matIn->quantity);
    }

    public function test_decimal_material_out_and_stock_decrement()
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\MaterialOutComponent::class)
            ->set('activeTab', 'single')
            ->set('item_id', $this->kgItem->id)
            ->set('quantity', 1.75)
            ->set('unit', 'KG')
            ->set('project', 'Project Offshore Alpha')
            ->set('pic', 'Budi')
            ->set('date', date('Y-m-d'))
            ->call('save');

        $this->kgItem->refresh();
        $this->assertEquals(8.25, (float)$this->kgItem->stock);

        $matOut = MaterialOut::where('item_id', $this->kgItem->id)->latest('id')->first();
        $this->assertNotNull($matOut);
        $this->assertEquals(1.75, (float)$matOut->quantity);
    }

    public function test_bulk_decimal_material_out()
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\MaterialOutComponent::class)
            ->set('activeTab', 'bulk')
            ->set('date', date('Y-m-d'))
            ->set('bulkRows', [
                [
                    'item_id' => $this->kgItem->id,
                    'quantity' => 2.25,
                    'unit' => 'KG',
                    'project' => 'Project Yard 1',
                    'pic' => 'Andi',
                    'notes' => 'Test bulk decimal',
                ]
            ])
            ->call('save');

        $this->kgItem->refresh();
        $this->assertEquals(7.75, (float)$this->kgItem->stock);
    }

    public function test_comma_decimal_input_material_out_and_views_consistency()
    {
        // 1. Input 1,5 kg in Material Out
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\MaterialOutComponent::class)
            ->set('activeTab', 'single')
            ->set('item_id', $this->kgItem->id)
            ->set('quantity', '1,5')
            ->set('unit', 'KG')
            ->set('project', 'Project Decimal Review')
            ->set('pic', 'Mandor Zaidan')
            ->set('date', date('Y-m-d'))
            ->call('save');

        // Item stock must decrease by exactly 1.5
        $this->kgItem->refresh();
        $this->assertEquals(8.5, (float)$this->kgItem->stock);

        $matOut = MaterialOut::where('item_id', $this->kgItem->id)->latest('id')->first();
        $this->assertNotNull($matOut);
        $this->assertEquals(1.5, (float)$matOut->quantity);

        $stockLog = \App\Models\StockLog::where('item_id', $this->kgItem->id)->where('type', 'out')->latest('id')->first();
        $this->assertNotNull($stockLog);
        $this->assertEquals(1.5, (float)$stockLog->quantity);

        // 2. Verify Riwayat Transaksi Stok Terbaru (/stock-logs) displays -1,5 KG
        $response = $this->actingAs($this->admin)->get('/stock-logs');
        $response->assertStatus(200);
        $response->assertSee('-1,5 KG');

        // 3. Verify Laporan Pengeluaran Material (/reports/material-out) displays -1,5 KG
        $response = $this->actingAs($this->admin)->get('/reports/material-out');
        $response->assertStatus(200);
        $response->assertSee('-1,5 KG');

        // 4. Verify Rekap Project Detail (/projects/{id}) displays -1,5
        $project = \App\Models\Project::where('name', 'Project Decimal Review')->first();
        $this->assertNotNull($project);
        $response = $this->actingAs($this->admin)->get("/projects/{$project->id}");
        $response->assertStatus(200);
        $response->assertSee('-1,5');

        // 5. Verify Bukti Mutasi / Surat Jalan PDF renders -1,5 KG
        $html = view('pdf.surat-jalan', [
            'log' => $stockLog,
            'company' => \App\Models\CompanyProfile::getProfile(),
        ])->render();
        $this->assertStringContainsString('-1,5 KG', $html);

        $response = $this->actingAs($this->admin)->get("/stock-logs/{$stockLog->id}/pdf");
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_comma_decimal_input_material_in()
    {
        // Input 2,5 kg in Material In
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\MaterialInComponent::class)
            ->set('activeTab', 'inbound')
            ->set('item_id', $this->kgItem->id)
            ->set('quantity', '2,5')
            ->set('unit', 'KG')
            ->set('supplier', 'PT Vendor Decimal')
            ->set('received_by', 'Warehouse Dec Staff')
            ->set('date', date('Y-m-d'))
            ->call('save');

        $this->kgItem->refresh();
        $this->assertEquals(12.5, (float)$this->kgItem->stock);

        $matIn = MaterialIn::where('item_id', $this->kgItem->id)->latest('id')->first();
        $this->assertNotNull($matIn);
        $this->assertEquals(2.5, (float)$matIn->quantity);

        // Verify Riwayat Transaksi Stok Terbaru (/stock-logs) displays +2,5 KG
        $response = $this->actingAs($this->admin)->get('/stock-logs');
        $response->assertStatus(200);
        $response->assertSee('+2,5 KG');
    }

    public function test_format_quantity_helper()
    {
        $this->assertEquals('10 KG', format_quantity(10, 'KG'));
        $this->assertEquals('2,5 KG', format_quantity(2.5, 'KG'));
        $this->assertEquals('1,75 KG', format_quantity(1.75, 'KG'));
        $this->assertEquals('0,125 KG', format_quantity(0.125, 'KG'));
        $this->assertEquals('1.000,5 KG', format_quantity(1000.5, 'KG'));
        $this->assertEquals('50', format_quantity(50));
        $this->assertEquals('0 KG', format_quantity(0, 'KG'));
        $this->assertEquals('0', format_quantity(null));
    }
}

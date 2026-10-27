<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpicAccessTest extends TestCase
{
    /**
     * Test PPIC user can access items list and view stock availability.
     */
    public function test_ppic_user_can_access_items_page(): void
    {
        $ppic = User::where('role', 'ppic')->first();
        if (!$ppic) {
            $ppic = User::create([
                'name' => 'PPIC Staff',
                'email' => 'ppic@mudalaya.com',
                'password' => bcrypt('password'),
                'role' => 'ppic',
            ]);
        }

        $response = $this->actingAs($ppic)->get('/items');
        $response->assertStatus(200);
        $response->assertSee('Daftar Stok Barang Gudang');
        $response->assertSee('Akses PPIC');
    }

    /**
     * Test PPIC user is blocked from admin routes like user management and categories.
     */
    public function test_ppic_user_is_blocked_from_admin_routes(): void
    {
        $ppic = User::where('role', 'ppic')->first();
        $this->assertNotNull($ppic);

        // Accessing /users directly should redirect to /items
        $response = $this->actingAs($ppic)->get('/users');
        $response->assertRedirect('/items');
        $response->assertSessionHas('error');

        // Accessing /categories directly should redirect to /items
        $responseCat = $this->actingAs($ppic)->get('/categories');
        $responseCat->assertRedirect('/items');
        $responseCat->assertSessionHas('error');
    }

    /**
     * Test PPIC user cannot store items.
     */
    public function test_ppic_user_cannot_store_items(): void
    {
        $ppic = User::where('role', 'ppic')->first();

        $response = $this->actingAs($ppic)->post('/items', [
            'mode' => 'single',
            'code_item' => 'TEST-PPIC',
            'name' => 'Barang Uji PPIC',
            'stock' => 10,
            'unit' => 'Pcs',
        ]);

        $response->assertRedirect('/items');
        $response->assertSessionHas('error');
    }

    /**
     * Test Admin user has full access to user management.
     */
    public function test_admin_user_has_full_access(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Web Administrator');
    }

    /**
     * Test PPIC menu only contains MENU WMS -> Stok Barang.
     */
    public function test_ppic_menu_only_shows_stok_barang(): void
    {
        $ppic = User::where('role', 'ppic')->first();
        $this->actingAs($ppic);
        $groups = \App\Helpers\MenuHelper::getMenuGroups();

        $this->assertCount(1, $groups);
        $this->assertEquals('MENU WMS', $groups[0]['title']);
        $this->assertCount(1, $groups[0]['items']);
        $this->assertEquals('Stok Barang', $groups[0]['items'][0]['name']);
        $this->assertEquals('/items', $groups[0]['items'][0]['path']);
    }

    /**
     * Test Option A: PPIC items page renders Mini Dashboard KPI cards and Planning Banner.
     */
    public function test_ppic_items_page_renders_mini_dashboard_kpi_cards(): void
    {
        $ppic = User::where('role', 'ppic')->first();
        $response = $this->actingAs($ppic)->get('/items');
        $response->assertStatus(200);
        $response->assertSee('Warning Stok');
        $response->assertSee('Stok Habis');
        $response->assertSee('Total Kuantitas Fisik');
        $response->assertSee('Kategori Cat', false);
    }
}

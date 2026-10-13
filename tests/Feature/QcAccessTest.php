<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class QcAccessTest extends TestCase
{
    /**
     * Test QC menu only shows MENU WMS -> Material Masuk.
     */
    public function test_qc_menu_only_shows_material_masuk(): void
    {
        $qc = User::where('role', 'qc')->first();
        if (!$qc) {
            $qc = User::create([
                'name' => 'QC Staff',
                'email' => 'qc@mudalaya.com',
                'password' => bcrypt('password'),
                'role' => 'qc',
            ]);
        }

        $this->actingAs($qc);
        $groups = \App\Helpers\MenuHelper::getMenuGroups();

        // Must strictly only have 1 group: MENU WMS
        $this->assertCount(1, $groups);
        $this->assertEquals('MENU WMS', $groups[0]['title']);

        // Group MENU WMS must strictly only have 1 item: Material Masuk
        $this->assertCount(1, $groups[0]['items']);
        $this->assertEquals('Material Masuk', $groups[0]['items'][0]['name']);
        $this->assertEquals('/material-in', $groups[0]['items'][0]['path']);
    }

    /**
     * Test QC user can access allowed route (Material Masuk).
     */
    public function test_qc_user_can_access_material_in(): void
    {
        $qc = User::where('role', 'qc')->first();
        $this->assertNotNull($qc);

        // Material Masuk
        $resMatIn = $this->actingAs($qc)->get('/material-in');
        $resMatIn->assertStatus(200);
    }

    /**
     * Test QC user is restricted from all other routes (redirected to /material-in).
     */
    public function test_qc_user_is_restricted_from_all_other_routes(): void
    {
        $qc = User::where('role', 'qc')->first();
        $this->assertNotNull($qc);

        $restrictedRoutes = [
            '/',
            '/items',
            '/items/export',
            '/material-out',
            '/projects',
            '/categories',
            '/stock-logs',
            '/stock-opnames',
            '/reports',
            '/reports/incoming-inspection',
            '/reports/cat-painting',
            '/reports/secondary-part',
            '/reports/consumable',
            '/company-profile',
            '/website-theme',
            '/users',
            '/activity-logs',
            '/database-backup',
            '/debugging/reset',
        ];

        foreach ($restrictedRoutes as $route) {
            $response = $this->actingAs($qc)->get($route);
            $response->assertRedirect('/material-in');
        }
    }

    /**
     * Test Option A: QC material-in page renders Mini Dashboard KPI cards and Inspection Queue Alert Banner.
     */
    public function test_qc_material_in_page_renders_mini_dashboard_kpi_cards(): void
    {
        $qc = User::where('role', 'qc')->first();
        $response = $this->actingAs($qc)->get('/material-in');
        $response->assertStatus(200);
        $response->assertSee('Menunggu QC');
        $response->assertSee('Persetujuan QC (OK)');
        $response->assertSee('Ditolak QC (Reject)');
        $response->assertSee('Total Kedatangan');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockLog;
use Tests\TestCase;

class ReportTest extends TestCase
{
    private function getAdmin(): \App\Models\User
    {
        return \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::factory()->create(['role' => 'admin']);
    }

    public function test_reports_page_loads_with_default_pagination(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('LAPORAN MUTASI & PERSEDIAAN STOK GUDANG', false);
        $response->assertSee('25 data per halaman');
    }

    public function test_reports_page_with_filter_and_per_page(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/reports?type=in');

        $response->assertStatus(200);
        $response->assertSee('Hasil Filter');
    }

    public function test_reports_page_with_custom_per_page(): void
    {
        $response = $this->actingAs($this->getAdmin())->get('/reports?per_page=50');

        $response->assertStatus(200);
        $response->assertSee('50 data per halaman');
    }

    public function test_all_pages_have_d_m_y_date_picker(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::factory()->create(['role' => 'admin']);

        // 1. Reports Index
        $res = $this->actingAs($admin)->get('/reports');
        $res->assertStatus(200);
        $res->assertSee("altFormat: 'd/m/Y'", false);

        // 2. Consumable Reports
        $res = $this->actingAs($admin)->get('/reports/material-out?category=consumable');
        $res->assertStatus(200);
        $res->assertSee("altFormat: 'd/m/Y'", false);

        // 3. Activity Logs
        $res = $this->actingAs($admin)->get('/activity-logs');
        $res->assertStatus(200);
        $res->assertSee("altFormat: 'd/m/Y'", false);

        // 4. Mutation Logs
        $res = $this->actingAs($admin)->get('/stock-logs');
        $res->assertStatus(200);
        $res->assertSee("altFormat: 'd/m/Y'", false);

        // 5. Stock Opname
        $res = $this->actingAs($admin)->get('/stock-opnames');
        $res->assertStatus(200);
        $res->assertSee("altFormat: 'd/m/Y'", false);

        // 6. Material Out
        $res = $this->actingAs($admin)->get('/material-out');
        $res->assertStatus(200);
        $res->assertSee("altFormat: 'd/m/Y'", false);

        // 7. Project Show Modal
        $project = \App\Models\Project::first();
        if ($project) {
            $res = $this->actingAs($admin)->get("/projects/{$project->id}");
            $res->assertStatus(200);
            $res->assertSee("altFormat: 'd/m/Y'", false);
        }
    }
}

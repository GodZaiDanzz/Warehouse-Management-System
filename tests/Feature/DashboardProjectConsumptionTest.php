<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardProjectConsumptionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_renders_project_material_consumption_information(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Penggunaan Material per Project', false);
        $response->assertSee('Grafik Top Pemakaian Material Project', false);
        $response->assertSee('Daftar Penggunaan per Project', false);
        $response->assertSee('Project ini menggunakan', false);
        $response->assertSee('chartMaterialPerProject', false);
        $response->assertSee('Total Material Project', false);
    }

    public function test_dashboard_supports_project_range_filter(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $responseAll = $this->actingAs($admin)->get(route('dashboard', ['project_range' => 'all']));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('chartMaterialPerProject', false);

        $responseYear = $this->actingAs($admin)->get(route('dashboard', ['project_range' => '1y']));
        $responseYear->assertStatus(200);

        $responseMonth = $this->actingAs($admin)->get(route('dashboard', ['project_range' => '1m']));
        $responseMonth->assertStatus(200);

        $responseWeek = $this->actingAs($admin)->get(route('dashboard', ['project_range' => '1w']));
        $responseWeek->assertStatus(200);
    }
}

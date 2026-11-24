<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_projects_index_displays_project_name_and_code(): void
    {
        $user = User::first() ?? User::factory()->create();
        $project = Project::create([
            'name' => 'Proyek Uji Coba Display Kode',
            'code' => 'PRJ-TEST-CODE-001',
            'status' => 'Ongoing',
        ]);

        $response = $this->actingAs($user)->get(route('projects.index', ['search' => 'Display Kode']));

        $response->assertStatus(200);
        $response->assertSee('Proyek Uji Coba Display Kode');
        $response->assertSee('PRJ-TEST-CODE-001');
    }

    public function test_projects_index_search_by_code_works(): void
    {
        $user = User::first() ?? User::factory()->create();
        $project = Project::create([
            'name' => 'Proyek Khusus Pencarian Kode',
            'code' => 'PRJ-SPECIFIC-999',
            'status' => 'Ongoing',
        ]);

        $response = $this->actingAs($user)->get(route('projects.index', ['search' => 'SPECIFIC-999']));

        $response->assertStatus(200);
        $response->assertSee('Proyek Khusus Pencarian Kode');
        $response->assertSee('PRJ-SPECIFIC-999');
    }

    public function test_project_show_displays_code(): void
    {
        $user = User::first() ?? User::factory()->create();
        $project = Project::create([
            'name' => 'Proyek Detail Kode',
            'code' => 'PRJ-DETAIL-001',
            'status' => 'Ongoing',
        ]);

        $response = $this->actingAs($user)->get(route('projects.show', $project->id));

        $response->assertStatus(200);
        $response->assertSee('Proyek Detail Kode');
        $response->assertSee('PRJ-DETAIL-001');
    }
}

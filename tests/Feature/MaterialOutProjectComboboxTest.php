<?php

namespace Tests\Feature;

use App\Livewire\MaterialOutComponent;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class MaterialOutProjectComboboxTest extends TestCase
{
    use DatabaseTransactions;

    public function test_material_out_modal_renders_project_search_combobox(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MaterialOutComponent::class)
            ->assertStatus(200)
            ->call('openCreateModal')
            ->assertSet('isModalOpen', true)
            ->assertSet('activeTab', 'bulk')
            ->assertSeeHtml('projectSearchCombobox')
            ->assertSeeHtml('x-teleport="body"')
            ->assertSeeHtml('Pilih Project');
    }

    public function test_apply_first_project_to_all_rows_works(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MaterialOutComponent::class)
            ->call('openCreateModal')
            ->set('bulkRows.0.project', 'LCI.025 - EMBEDED PLATE')
            ->call('applyFirstProjectToAll')
            ->assertSet('bulkRows.1.project', 'LCI.025 - EMBEDED PLATE')
            ->assertSet('bulkRows.2.project', 'LCI.025 - EMBEDED PLATE');
    }
}

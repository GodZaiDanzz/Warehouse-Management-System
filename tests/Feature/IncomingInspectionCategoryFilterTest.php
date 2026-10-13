<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomingInspectionCategoryFilterTest extends TestCase
{
    public function test_incoming_inspection_can_filter_by_specific_category(): void
    {
        $user = User::first() ?? User::factory()->create();

        $catConsumable = Category::where('name', 'like', '%Consumable%')->first();
        $catGas = Category::where('name', 'like', '%Gas%')->first();

        if ($catConsumable && $catGas) {
            $itemConsumable = Item::where('category_id', $catConsumable->id)->first();
            $itemGas = Item::where('category_id', $catGas->id)->first();

            if ($itemConsumable && $itemGas) {
                // Filter consumable
                $responseConsumable = $this->actingAs($user)->get('/reports/incoming-inspection?category=' . $catConsumable->id);
                $responseConsumable->assertStatus(200);
                $responseConsumable->assertSee($catConsumable->name);

                // Filter gas
                $responseGas = $this->actingAs($user)->get('/reports/incoming-inspection?category=' . $catGas->id);
                $responseGas->assertStatus(200);
                $responseGas->assertSee($catGas->name);

                // Export Excel with category filter
                $resExcel = $this->actingAs($user)->get('/reports/incoming-inspection/export?category=' . $catConsumable->id);
                $resExcel->assertStatus(200);

                // Export PDF with category filter
                $resPdf = $this->actingAs($user)->get('/reports/incoming-inspection/pdf?category=' . $catGas->id);
                $resPdf->assertStatus(200);
            }
        }
    }
}

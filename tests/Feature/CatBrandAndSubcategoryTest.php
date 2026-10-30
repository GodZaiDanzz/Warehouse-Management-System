<?php

namespace Tests\Feature;

use App\Livewire\ItemTableComponent;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class CatBrandAndSubcategoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_standardized_four_cat_subcategories_exist(): void
    {
        $umbrella = Category::where('name', 'Cat')->first();
        $this->assertNotNull($umbrella);
        $this->assertTrue($umbrella->isCatUmbrella());

        $expectedSubcategories = [
            'Cat - Primer',
            'Cat - Second',
            'Cat - Top Coat',
            'Cat - Thinner',
        ];

        foreach ($expectedSubcategories as $subName) {
            $cat = Category::where('name', $subName)->first();
            $this->assertNotNull($cat, "Subcategory {$subName} must exist.");
            $this->assertTrue($cat->isCatSubcategory(), "{$subName} must be recognized as a cat subcategory.");
        }
    }

    public function test_paint_items_have_correct_brands_and_categories(): void
    {
        $catPrimer = Category::where('name', 'Cat - Primer')->first();
        $catTopCoat = Category::where('name', 'Cat - Top Coat')->first();
        $catThinner = Category::where('name', 'Cat - Thinner')->first();

        // Check Novalux items
        $novaluxPrimer = Item::where('name', 'like', '%Novalux Epoxy Primer%')->first();
        if ($novaluxPrimer) {
            $this->assertEquals('Novalux', $novaluxPrimer->brand);
            $this->assertEquals($catPrimer->id, $novaluxPrimer->category_id);
        }

        // Check International items
        $interzinc = Item::where('name', 'like', '%Interzinc 52%')->first();
        if ($interzinc) {
            $this->assertEquals('International', $interzinc->brand);
            $this->assertEquals($catPrimer->id, $interzinc->category_id);
        }

        // Check Jotun items
        $chemflake = Item::where('name', 'like', '%Chemflake%')->first();
        if ($chemflake) {
            $this->assertEquals('Jotun', $chemflake->brand);
            $this->assertEquals($catTopCoat->id, $chemflake->category_id);
        }
    }

    public function test_non_cat_categories_remain_untouched(): void
    {
        $consumable = Category::where('name', 'Consumable')->first();
        $this->assertNotNull($consumable);
        $this->assertFalse($consumable->isCatSubcategory());
        $this->assertFalse($consumable->isCatUmbrella());

        $secondary = Category::where('name', 'Secondary Part')->first();
        $this->assertNotNull($secondary);

        $gas = Category::where('name', 'Gas')->first();
        $this->assertNotNull($gas);

        $raw = Category::where('name', 'Raw Material')->first();
        $this->assertNotNull($raw);
    }

    public function test_searching_by_brand_in_item_table(): void
    {
        $user = User::first() ?? User::factory()->create();

        // Search for 'Novalux'
        $test = Livewire::actingAs($user)
            ->test(ItemTableComponent::class)
            ->set('search', 'Novalux');

        $items = $test->viewData('items');
        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertTrue(
                str_contains(strtolower($item->name), 'novalux') || strtolower($item->brand) === 'novalux',
                "Item {$item->name} should match Novalux"
            );
        }
    }
}


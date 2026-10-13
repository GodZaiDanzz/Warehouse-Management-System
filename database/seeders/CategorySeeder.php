<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Consumable',
                'description' => 'Material habis pakai untuk pekerjaan fabrikasi, pengelasan, dan operasional pergudangan.'
            ],
            [
                'name' => 'Secondary Part',
                'description' => 'Suku cadang dan komponen pendukung mekanikal, elektrikal, dan pemeliharaan mesin.'
            ],
            [
                'name' => 'Cat',
                'description' => 'Material cat, thinner, coating, dan solvent umum pergudangan.'
            ],
            [
                'name' => 'Cat - Primer',
                'description' => 'Cat lapisan dasar (Epoxy Primer / Primer Coat) dengan kontrol shelf life / tanggal pembuatan.'
            ],
            [
                'name' => 'Cat - Second',
                'description' => 'Cat lapisan kedua (Epoxy PF Coating / Intermediate Coat).'
            ],
            [
                'name' => 'Cat - Top Coat',
                'description' => 'Cat lapisan akhir / finish (PU Finish Top Coat).'
            ],
            [
                'name' => 'Cat - Thinner',
                'description' => 'Cairan pengencer cat, thinner, dan solvent (Novalux Thinner 339, 664, dsb).'
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // Clean up obsolete dummy categories
        $allowedNames = array_column($categories, 'name');
        $defaultCat = Category::where('name', 'Consumable')->first();
        $allowedIds = Category::whereIn('name', $allowedNames)->pluck('id')->toArray();

        Item::whereNotIn('category_id', $allowedIds)->update(['category_id' => $defaultCat->id]);
        Category::whereNotIn('id', $allowedIds)->delete();
    }
}

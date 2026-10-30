<?php

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom brand di tabel items jika belum ada
        if (!Schema::hasColumn('items', 'brand')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('brand', 100)->nullable()->after('name');
            });
        }

        // 2. Standardisasi Kategori Cat: Utama (Cat) dan 4 Subkategori
        // Pastikan Kategori Utama 'Cat' ada
        $catUmbrella = Category::firstOrCreate(
            ['name' => 'Cat'],
            ['description' => 'Material cat, thinner, coating, dan solvent pergudangan']
        );

        // Sub 1: Cat - Primer
        $targetPrimer = Category::where('name', 'Cat - Primer')->first()
            ?? Category::find(38)
            ?? Category::where('name', 'like', 'Cat - Primer%')->first()
            ?? Category::create(['name' => 'Cat - Primer', 'description' => 'Cat lapisan dasar / primer coat']);
        $targetPrimer->name = 'Cat - Primer';
        $targetPrimer->description = 'Cat lapisan dasar / primer coat';
        $targetPrimer->save();

        // Sub 2: Cat - Second
        $targetSecond = Category::where('name', 'Cat - Second')->first()
            ?? Category::find(39)
            ?? Category::where('name', 'like', 'Cat - Second%')->first()
            ?? Category::create(['name' => 'Cat - Second', 'description' => 'Cat lapisan kedua / intermediate coat']);
        $targetSecond->name = 'Cat - Second';
        $targetSecond->description = 'Cat lapisan kedua / intermediate coat';
        $targetSecond->save();

        // Sub 3: Cat - Top Coat
        $targetTopCoat = Category::where('name', 'Cat - Top Coat')->first()
            ?? Category::find(40)
            ?? Category::where('name', 'like', 'Cat - Top Coat%')->first()
            ?? Category::create(['name' => 'Cat - Top Coat', 'description' => 'Cat lapisan akhir / top coat finish']);
        $targetTopCoat->name = 'Cat - Top Coat';
        $targetTopCoat->description = 'Cat lapisan akhir / top coat finish';
        $targetTopCoat->save();

        // Sub 4: Cat - Thinner
        $targetThinner = Category::where('name', 'Cat - Thinner')->first()
            ?? Category::find(41)
            ?? Category::where('name', 'like', 'Cat - Thinner%')->first()
            ?? Category::create(['name' => 'Cat - Thinner', 'description' => 'Pengencer cat, thinner, dan solvent']);
        $targetThinner->name = 'Cat - Thinner';
        $targetThinner->description = 'Pengencer cat, thinner, dan solvent';
        $targetThinner->save();

        // 3. Migrasi Data Barang Cat: Isi Kolom Brand & Hubungkan ke 4 Subkategori
        // Mapping Kategori Lama ke Subkategori Baru & Brand
        $catMappings = [
            // Novalux
            'Cat - Primer (Novalux)' => ['cat_id' => $targetPrimer->id, 'brand' => 'Novalux'],
            'Cat - Second (Novalux)' => ['cat_id' => $targetSecond->id, 'brand' => 'Novalux'],
            'Cat - Top Coat (Novalux)' => ['cat_id' => $targetTopCoat->id, 'brand' => 'Novalux'],
            'Cat - Thinner (Novalux)' => ['cat_id' => $targetThinner->id, 'brand' => 'Novalux'],

            // International
            'Cat - Primer (International)' => ['cat_id' => $targetPrimer->id, 'brand' => 'International'],
            'Cat - Second (International)' => ['cat_id' => $targetSecond->id, 'brand' => 'International'],
            'Cat - Top Coat (International)' => ['cat_id' => $targetTopCoat->id, 'brand' => 'International'],
            'Cat - Thinner (International)' => ['cat_id' => $targetThinner->id, 'brand' => 'International'],
        ];

        foreach ($catMappings as $oldCatName => $map) {
            $oldCat = Category::where('name', $oldCatName)->first();
            if ($oldCat) {
                Item::where('category_id', $oldCat->id)->update([
                    'category_id' => $map['cat_id'],
                    'brand' => $map['brand'],
                ]);
            }
        }

        // Tangani Item yang sebelumnya langsung berada di Kategori 37 (Cat)
        // Chemflake Special White -> Jotun (Top Coat)
        Item::where('name', 'like', '%Chemflake%')->update([
            'category_id' => $targetTopCoat->id,
            'brand' => 'Jotun',
        ]);

        // Styrene -> Jotun (Thinner / Solvent)
        Item::where('name', 'like', '%Styrene%')->update([
            'category_id' => $targetThinner->id,
            'brand' => 'Jotun',
        ]);

        // Tankguard / Thankguard -> Jotun (Top Coat)
        Item::where('name', 'like', '%Thankguard%')
            ->orWhere('name', 'like', '%Tankguard%')
            ->update([
                'category_id' => $targetTopCoat->id,
                'brand' => 'Jotun',
            ]);

        // Update brand berbasis nama produk cat jika brand masih null
        Item::whereNull('brand')->where(function ($q) {
            $q->where('name', 'like', 'Novalux%')
              ->orWhere('name', 'like', 'Thinner 339%')
              ->orWhere('name', 'like', 'Thinner 664%');
        })->update(['brand' => 'Novalux']);
        Item::whereNull('brand')->where(function ($q) {
            $q->where('name', 'like', 'Interzinc%')
              ->orWhere('name', 'like', 'Intergard%')
              ->orWhere('name', 'like', 'Interthane%')
              ->orWhere('name', 'like', 'Interzone%')
              ->orWhere('name', 'like', 'Interline%')
              ->orWhere('name', 'like', 'GTA%');
        })->update(['brand' => 'International']);

        Item::whereNull('brand')->where('name', 'like', 'Jotun%')->update(['brand' => 'Jotun']);
        Item::whereNull('brand')->where('name', 'like', 'Carboline%')->update(['brand' => 'Carboline']);
        Item::whereNull('brand')->where('name', 'like', 'Sigma%')->update(['brand' => 'Sigma']);

        // 4. Bersihkan Kategori Lama yang sudah tidak memiliki item (Soft Delete)
        $standardCategoryIds = [
            $catUmbrella->id,
            $targetPrimer->id,
            $targetSecond->id,
            $targetTopCoat->id,
            $targetThinner->id,
        ];

        $obsoleteCategories = Category::where(function ($q) {
            $q->where('name', 'like', 'Cat - % (Novalux)')
              ->orWhere('name', 'like', 'Cat - % (International)');
        })->whereNotIn('id', $standardCategoryIds)->get();

        foreach ($obsoleteCategories as $obs) {
            if ($obs->items()->count() === 0) {
                $obs->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('items', 'brand')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('brand');
            });
        }
    }
};

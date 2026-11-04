<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure subcategory "Raw Material - Off Cut" exists
        Category::firstOrCreate(
            ['name' => 'Raw Material - Off Cut'],
            ['description' => 'Sisa potongan raw material layak pakai dari proses fabrikasi/cutting']
        );

        // 2. Add off-cut tracking columns to items table
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'is_offcut')) {
                $table->boolean('is_offcut')->default(false)->index()->after('brand');
            }
            if (!Schema::hasColumn('items', 'parent_item_id')) {
                $table->foreignId('parent_item_id')
                    ->nullable()
                    ->after('is_offcut')
                    ->constrained('items')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('items', 'dimension')) {
                $table->string('dimension')->nullable()->after('parent_item_id');
            }
            if (!Schema::hasColumn('items', 'origin_project')) {
                $table->string('origin_project')->nullable()->after('dimension');
            }
            if (!Schema::hasColumn('items', 'heat_number')) {
                $table->string('heat_number', 100)->nullable()->after('origin_project');
            }
        });

        // 3. Add off-cut tracking columns to material_ins table
        Schema::table('material_ins', function (Blueprint $table) {
            if (!Schema::hasColumn('material_ins', 'is_offcut')) {
                $table->boolean('is_offcut')->default(false)->after('unit');
            }
            if (!Schema::hasColumn('material_ins', 'parent_item_id')) {
                $table->foreignId('parent_item_id')
                    ->nullable()
                    ->after('is_offcut')
                    ->constrained('items')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('material_ins', 'dimension')) {
                $table->string('dimension')->nullable()->after('parent_item_id');
            }
            if (!Schema::hasColumn('material_ins', 'origin_project')) {
                $table->string('origin_project')->nullable()->after('dimension');
            }
            if (!Schema::hasColumn('material_ins', 'heat_number')) {
                $table->string('heat_number', 100)->nullable()->after('origin_project');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_ins', function (Blueprint $table) {
            if (Schema::hasColumn('material_ins', 'parent_item_id')) {
                $table->dropForeign(['parent_item_id']);
            }
            $table->dropColumn(['is_offcut', 'parent_item_id', 'dimension', 'origin_project', 'heat_number']);
        });

        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'parent_item_id')) {
                $table->dropForeign(['parent_item_id']);
            }
            $table->dropColumn(['is_offcut', 'parent_item_id', 'dimension', 'origin_project', 'heat_number']);
        });

        Category::where('name', 'Raw Material - Off Cut')->delete();
    }
};


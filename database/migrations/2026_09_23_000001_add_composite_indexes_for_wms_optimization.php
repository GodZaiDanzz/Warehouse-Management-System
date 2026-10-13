<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Composite index for category filtering on active non-deleted items
            $table->index(['category_id', 'deleted_at'], 'items_cat_deleted_index');
            // Composite index for fast min_stock alert and low-stock scans
            $table->index(['stock', 'min_stock'], 'items_stock_min_stock_index');
        });

        Schema::table('stock_logs', function (Blueprint $table) {
            // Composite index for fetching stock history of specific item chronologically
            $table->index(['item_id', 'created_at'], 'stock_logs_item_created_index');
        });

        Schema::table('material_outs', function (Blueprint $table) {
            // Composite index for project consumption reports by date
            $table->index(['date', 'project_id'], 'material_outs_date_proj_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_cat_deleted_index');
            $table->dropIndex('items_stock_min_stock_index');
        });

        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropIndex('stock_logs_item_created_index');
        });

        Schema::table('material_outs', function (Blueprint $table) {
            $table->dropIndex('material_outs_date_proj_index');
        });
    }
};

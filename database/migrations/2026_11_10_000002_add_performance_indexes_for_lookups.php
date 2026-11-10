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
        Schema::table('material_outs', function (Blueprint $table) {
            // Speed up distinct project lookups across 3600+ records
            $table->index('project', 'material_outs_project_index');
            // Speed up distinct PIC lookups across 3600+ records
            $table->index('pic', 'material_outs_pic_index');
            // Speed up fast moving item queries (item_id + date grouping/filter)
            $table->index(['item_id', 'date'], 'material_outs_item_date_index');
        });

        Schema::table('material_ins', function (Blueprint $table) {
            // Speed up distinct supplier lookups in material-in filter
            $table->index('supplier', 'material_ins_supplier_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_outs', function (Blueprint $table) {
            $table->dropIndex('material_outs_project_index');
            $table->dropIndex('material_outs_pic_index');
            $table->dropIndex('material_outs_item_date_index');
        });

        Schema::table('material_ins', function (Blueprint $table) {
            $table->dropIndex('material_ins_supplier_index');
        });
    }
};

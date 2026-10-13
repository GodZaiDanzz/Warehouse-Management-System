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
        Schema::table('daily_stock_opnames', function (Blueprint $table) {
            $table->string('shift', 20)->default('Shift 1')->after('item_id')->comment('Shift 1 atau Shift 2');
            $table->dropUnique('unique_date_item_opname');
            $table->unique(['date', 'item_id', 'shift'], 'unique_date_item_shift_opname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_stock_opnames', function (Blueprint $table) {
            $table->dropUnique('unique_date_item_shift_opname');
            $table->dropColumn('shift');
            $table->unique(['date', 'item_id'], 'unique_date_item_opname');
        });
    }
};

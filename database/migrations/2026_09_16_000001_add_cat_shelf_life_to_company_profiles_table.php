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
        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'cat_shelf_life_months')) {
                $table->unsignedInteger('cat_shelf_life_months')->default(12)->after('btn_other_color')->comment('Masa kadaluarsa cat dalam satuan bulan (default: 12 bulan / 1 tahun)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('company_profiles', 'cat_shelf_life_months')) {
                $table->dropColumn('cat_shelf_life_months');
            }
        });
    }
};

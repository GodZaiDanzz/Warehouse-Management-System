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
            $table->string('btn_add_color')->default('#275538')->nullable()->after('theme_color');
            $table->string('btn_save_color')->default('#275538')->nullable()->after('btn_add_color');
            $table->string('btn_other_color')->default('#275538')->nullable()->after('btn_save_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['btn_add_color', 'btn_save_color', 'btn_other_color']);
        });
    }
};

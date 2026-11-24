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
        if (Schema::hasColumn('material_outs', 'ppic')) {
            Schema::table('material_outs', function (Blueprint $table) {
                $table->renameColumn('ppic', 'pic');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_outs', function (Blueprint $table) {
            $table->renameColumn('pic', 'ppic');
        });
    }
};

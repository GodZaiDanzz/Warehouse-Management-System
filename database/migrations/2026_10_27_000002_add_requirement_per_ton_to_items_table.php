<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'requirement_per_ton')) {
                $table->decimal('requirement_per_ton', 12, 4)->nullable()->after('min_stock')->comment('Perkiraan kebutuhan material per 1 ton project');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'requirement_per_ton')) {
                $table->dropColumn('requirement_per_ton');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'tonnage')) {
                $table->decimal('tonnage', 12, 3)->default(0)->after('budget')->comment('Tonase Project dalam satuan Ton');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'tonnage')) {
                $table->dropColumn('tonnage');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unique index on code so projects without a code won't collide
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropUnique('projects_code_unique');
            });
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE projects DROP INDEX projects_code_unique');
            } catch (\Throwable $ex) {}
        }

        // Make code nullable with default null
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('code', 50)->nullable()->default(null)->change();
            });
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE projects MODIFY code VARCHAR(50) NULL DEFAULT NULL');
            } catch (\Throwable $ex) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('code', 50)->nullable(false)->change();
                $table->unique('code');
            });
        } catch (\Throwable $e) {}
    }
};

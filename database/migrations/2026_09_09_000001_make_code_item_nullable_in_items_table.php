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
        // Drop unique index on code_item so multiple nulls or duplicates won't error
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->dropUnique('items_code_item_unique');
            });
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE items DROP INDEX items_code_item_unique');
            } catch (\Throwable $ex) {}
        }

        // Make code_item nullable
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->string('code_item')->nullable()->change();
            });
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE items MODIFY code_item VARCHAR(255) NULL');
            } catch (\Throwable $ex) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->string('code_item')->nullable(false)->change();
                $table->unique('code_item');
            });
        } catch (\Throwable $e) {}
    }
};

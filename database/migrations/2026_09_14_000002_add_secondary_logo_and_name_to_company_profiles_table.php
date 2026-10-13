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
        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'secondary_name')) {
                $table->string('secondary_name')->nullable()->after('name')->comment('Nama Perusahaan Ke-2 / Partner');
            }
            if (!Schema::hasColumn('company_profiles', 'secondary_logo_path')) {
                $table->string('secondary_logo_path')->nullable()->after('logo_path')->comment('Path Logo Perusahaan Ke-2');
            }
        });

        // If tagline previously contained the second company name (e.g., PT. MUTIARA GLOBAL INDUSTRY),
        // migrate it to secondary_name automatically so data is preserved.
        try {
            $profile = DB::table('company_profiles')->first();
            if ($profile && empty($profile->secondary_name) && !empty($profile->tagline)) {
                if (str_contains(strtoupper($profile->tagline), 'PT')) {
                    DB::table('company_profiles')->where('id', $profile->id)->update([
                        'secondary_name' => $profile->tagline,
                    ]);
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('company_profiles', 'secondary_logo_path')) {
                $table->dropColumn('secondary_logo_path');
            }
            if (Schema::hasColumn('company_profiles', 'secondary_name')) {
                $table->dropColumn('secondary_name');
            }
        });
    }
};

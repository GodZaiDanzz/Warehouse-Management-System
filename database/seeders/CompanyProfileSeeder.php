<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'PT Mudalaya Energy Indonesia',
                'tagline' => 'Energy, EPC & General Contractor',
                'logo_path' => null,
                'email' => 'info@mudalayaenergy.co.id',
                'phone' => '+62 21 555-0199',
                'address' => 'Gedung Mudalaya Energy, Jl. Jend. Sudirman Kaveling 45-46, Jakarta Selatan',
                'website' => 'www.mudalayaenergy.co.id',
            ]
        );
    }
}

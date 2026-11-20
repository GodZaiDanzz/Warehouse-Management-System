<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder
 *
 * Seed database with initial system users, standard categories, master items, and profiles.
 *
 * Default Seeded Accounts:
 * - Web Administrator : admin@mudalaya.com       | password
 * - Warehouse Staff    : warehouse@mudalaya.com   | password
 * - PPIC Staff         : ppic@mudalaya.com        | password
 * - QC Inspector       : qc@mudalaya.com          | password
 * - Procurement Staff  : procurement@mudalaya.com | password
 *
 * Command to execute:
 *   php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default Web Administrator
        User::updateOrCreate(
            ['email' => 'admin@mudalaya.com'],
            [
                'name' => 'Web Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create default Warehouse User
        User::updateOrCreate(
            ['email' => 'warehouse@mudalaya.com'],
            [
                'name' => 'Tim Warehouse',
                'password' => Hash::make('password'),
                'role' => 'warehouse',
            ]
        );

        // Create default PPIC User (Hanya Melihat Stok Ketersediaan Barang)
        User::updateOrCreate(
            ['email' => 'ppic@mudalaya.com'],
            [
                'name' => 'PPIC Staff',
                'password' => Hash::make('password'),
                'role' => 'ppic',
            ]
        );

        // Create default QC Inspector
        User::updateOrCreate(
            ['email' => 'qc@mudalaya.com'],
            [
                'name' => 'QC Staff',
                'password' => Hash::make('password'),
                'role' => 'qc',
            ]
        );

        // Create default Procurement Staff (Kelola Purchase Order & Pengadaan)
        User::updateOrCreate(
            ['email' => 'procurement@mudalaya.com'],
            [
                'name' => 'Procurement Staff',
                'password' => Hash::make('password'),
                'role' => 'procurement',
            ]
        );

        $this->call([
            CompanyProfileSeeder::class,
            CategorySeeder::class,
            ItemSeeder::class,
            StockLogSeeder::class,
            WmsStandardizationSeeder::class,
        ]);
    }
}

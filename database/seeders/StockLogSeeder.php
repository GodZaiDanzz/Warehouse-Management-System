<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StockLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $items = Item::all();

        if ($items->isEmpty()) {
            return;
        }

        $providers = [
            'PT Kawan Lama Sejahtera',
            'PT Seamless Pipe Indonesia',
            'PT Kito Indonesia',
            'PT Mitratama Energi',
            'CV Teknik Bersama',
            'PT Schneider Electric Indonesia',
        ];

        $recipients = [
            'Tim Proyek Piping Block B',
            'Seksi Maintenance Boiler 2',
            'Tim EPC Substation Cilegon',
            'Divisi K3 & Rigging Site',
            'Subkon Konstruksi Fabrikasi',
        ];

        // Seed logs spanning the last 30 days
        foreach ($items as $index => $item) {
            // Incoming Log
            StockLog::create([
                'item_id' => $item->id,
                'user_id' => $admin?->id,
                'type' => 'in',
                'quantity' => rand(20, 100),
                'recipient_or_provider' => $providers[array_rand($providers)],
                'notes' => 'Penerimaan stok awal proyek PT Mudalaya Energy Indonesia.',
                'created_at' => Carbon::now()->subDays(rand(10, 30)),
            ]);

            // Outgoing Log
            StockLog::create([
                'item_id' => $item->id,
                'user_id' => $admin?->id,
                'type' => 'out',
                'quantity' => rand(5, 15),
                'recipient_or_provider' => $recipients[array_rand($recipients)],
                'notes' => 'Pengeluaran barang material untuk aktivitas pekerjaan di site.',
                'created_at' => Carbon::now()->subDays(rand(1, 9)),
            ]);
        }
    }
}

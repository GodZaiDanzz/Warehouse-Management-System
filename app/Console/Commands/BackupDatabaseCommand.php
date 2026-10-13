<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wms:backup-database {--force : Jalankan pencadangan tanpa memeriksa apakah jadwal sudah tiba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan pencadangan database sistem secara otomatis sesuai interval (hari/bulan) yang dikonfigurasi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        if (!$force && !DatabaseBackupService::isBackupDue()) {
            $nextDate = DatabaseBackupService::getNextBackupDate();
            $this->info("Auto backup belum jatuh tempo. Jadwal berikutnya: " . ($nextDate ? $nextDate->format('d/m/Y H:i:s') : 'Nonaktif'));
            return Command::SUCCESS;
        }

        $this->info("Memulai proses auto backup database...");

        try {
            $result = DatabaseBackupService::generateBackup(true);
            $this->info("✓ Berhasil membuat backup database: {$result['filename']} ({$result['formatted_size']})");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("✗ Gagal membuat backup database: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

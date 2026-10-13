<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    /**
     * Display Database Backup status, auto-backup settings, and saved backup files.
     */
    public function index()
    {
        // Web request fallback: run auto backup if due
        if (DatabaseBackupService::isBackupDue()) {
            try {
                DatabaseBackupService::generateBackup(true);
            } catch (\Throwable $e) {
                // Silently log or continue to ensure UI renders
            }
        }

        $dbName = DB::connection()->getDatabaseName();

        // Get table details and data size from information_schema
        $tablesInfo = DB::select("
            SELECT 
                TABLE_NAME as name, 
                ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = ? 
            ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
        ", [$dbName]);

        // Calculate exact real row counts (InnoDB TABLE_ROWS in information_schema is only an inaccurate estimate)
        foreach ($tablesInfo as $table) {
            try {
                $table->rows_count = DB::table($table->name)->count();
            } catch (\Throwable $e) {
                $table->rows_count = 0;
            }
        }

        $totalTables = count($tablesInfo);
        $totalRows = array_sum(array_column($tablesInfo, 'rows_count'));
        $totalSizeMb = array_sum(array_column($tablesInfo, 'size_mb'));

        $profile = CompanyProfile::getProfile();
        $backupFiles = DatabaseBackupService::getBackupFiles();
        $nextBackupDate = DatabaseBackupService::getNextBackupDate();

        return view('pages.backup.index', [
            'dbName' => $dbName,
            'tablesInfo' => $tablesInfo,
            'totalTables' => $totalTables,
            'totalRows' => $totalRows,
            'totalSizeMb' => round($totalSizeMb, 2),
            'serverTime' => now()->translatedFormat('d F Y - H:i:s'),
            'profile' => $profile,
            'backupFiles' => $backupFiles,
            'nextBackupDate' => $nextBackupDate,
        ]);
    }

    /**
     * Update auto backup database settings (frequency, unit, retention limit, enabled toggle).
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'auto_backup_enabled' => 'nullable|boolean',
            'auto_backup_frequency_value' => 'required|integer|min:1|max:365',
            'auto_backup_frequency_unit' => 'required|string|in:days,months',
            'auto_backup_max_files' => 'required|integer|min:1|max:50',
        ], [
            'auto_backup_frequency_value.required' => 'Nilai interval backup wajib diisi.',
            'auto_backup_frequency_value.min' => 'Interval backup minimal 1.',
            'auto_backup_frequency_unit.in' => 'Satuan interval harus Hari atau Bulan.',
            'auto_backup_max_files.required' => 'Batas maksimal file backup wajib diisi.',
            'auto_backup_max_files.min' => 'Batas maksimal file backup minimal 1 file.',
        ]);

        $validated['auto_backup_enabled'] = $request->boolean('auto_backup_enabled');

        $profile = CompanyProfile::getProfile();
        $profile->update($validated);

        $unitLabel = $validated['auto_backup_frequency_unit'] === 'months' ? 'Bulan' : 'Hari';
        $statusLabel = $validated['auto_backup_enabled'] ? 'Aktif' : 'Nonaktif';

        ActivityLog::record(
            'Ubah Pengaturan Auto Backup',
            "Memperbarui pengaturan Auto Backup Database: {$statusLabel}, setiap {$validated['auto_backup_frequency_value']} {$unitLabel}, retensi {$validated['auto_backup_max_files']} file.",
            'Pengaturan System'
        );

        return redirect()->route('backup.index')->with('success', 'Pengaturan Auto Backup Database berhasil disimpan!');
    }

    /**
     * Create an instant manual backup directly to server storage.
     */
    public function createManual()
    {
        try {
            $result = DatabaseBackupService::generateBackup(false);
            return redirect()->route('backup.index')->with('success', "Cadangan database berhasil dibuat: {$result['filename']} ({$result['formatted_size']})!");
        } catch (\Throwable $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal membuat cadangan database: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific stored backup file from server.
     */
    public function downloadFile(string $filename)
    {
        $cleanName = basename($filename);
        $filepath = DatabaseBackupService::getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanName;

        if (!File::exists($filepath)) {
            return redirect()->route('backup.index')->with('error', "Berkas cadangan {$cleanName} tidak ditemukan di server.");
        }

        ActivityLog::record(
            'Download File Backup',
            "Mengunduh berkas cadangan database ({$cleanName}) dari server.",
            'Pengaturan System'
        );

        return response()->download($filepath, $cleanName, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Restore database from a stored backup file on the server.
     */
    public function restoreFile(string $filename)
    {
        $cleanName = basename($filename);
        $filepath = DatabaseBackupService::getBackupDirectory() . DIRECTORY_SEPARATOR . $cleanName;

        try {
            DatabaseBackupService::restoreFromFile($filepath);
            return redirect()->route('backup.index')->with('success', "Database berhasil dipulihkan dari berkas cadangan '{$cleanName}'!");
        } catch (\Throwable $e) {
            return redirect()->route('backup.index')->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific stored backup file from the server.
     */
    public function deleteFile(string $filename)
    {
        $cleanName = basename($filename);

        if (DatabaseBackupService::deleteBackupFile($cleanName)) {
            return redirect()->route('backup.index')->with('success', "Berkas cadangan '{$cleanName}' berhasil dihapus dari server!");
        }

        return redirect()->route('backup.index')->with('error', "Berkas cadangan '{$cleanName}' tidak ditemukan.");
    }

    /**
     * Generate and stream full .sql database dump directly to browser (Instant download).
     */
    public function download(): StreamedResponse
    {
        $dbName = DB::connection()->getDatabaseName();
        $filename = 'backup_wms_mudalaya_' . date('Y-m-d_His') . '.sql';

        // Record Activity Log
        ActivityLog::record(
            'Backup Database',
            "Mengunduh file cadangan database sistem ({$filename})",
            'Pengaturan System'
        );

        $headers = [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($dbName) {
            $handle = fopen('php://output', 'w');

            // Header comments
            fwrite($handle, "-- ======================================================\n");
            fwrite($handle, "-- PT MUDALAYA ENERGY INDONESIA - WAREHOUSE MANAGEMENT SYSTEM\n");
            fwrite($handle, "-- Database Backup Dump (.sql)\n");
            fwrite($handle, "-- Database: `{$dbName}`\n");
            fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Generated by: " . (auth()->user()->name ?? 'Administrator') . "\n");
            fwrite($handle, "-- ======================================================\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $dbName;
            $pdo = DB::connection()->getPdo();

            foreach ($tables as $tblObj) {
                $tableName = $tblObj->$tableKey ?? current((array) $tblObj);

                // Table structure
                fwrite($handle, "-- ------------------------------------------------------\n");
                fwrite($handle, "-- Table structure for `{$tableName}`\n");
                fwrite($handle, "-- ------------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTableResult)) {
                    $createRow = (array) $createTableResult[0];
                    $createSql = $createRow['Create Table'] ?? '';
                    fwrite($handle, $createSql . ";\n\n");
                }

                // Table data
                fwrite($handle, "-- Dumping data for table `{$tableName}`\n");
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $columnNames = array_keys((array) $rows->first());
                    $columnsList = '`' . implode('`, `', $columnNames) . '`';

                    foreach ($rows->chunk(100) as $chunk) {
                        $valuesList = [];
                        foreach ($chunk as $row) {
                            $rowValues = [];
                            foreach ((array) $row as $val) {
                                if (is_null($val)) {
                                    $rowValues[] = 'NULL';
                                } elseif (is_numeric($val)) {
                                    $rowValues[] = $val;
                                } else {
                                    $rowValues[] = $pdo->quote($val);
                                }
                            }
                            $valuesList[] = '(' . implode(', ', $rowValues) . ')';
                        }
                        fwrite($handle, "INSERT INTO `{$tableName}` ({$columnsList}) VALUES\n" . implode(",\n", $valuesList) . ";\n");
                    }
                }
                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($handle, "-- Backup completed successfully on " . date('Y-m-d H:i:s') . "\n");

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Restore database from uploaded .sql backup file.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt,plain|max:51200', // max 50MB
        ], [
            'sql_file.required' => 'Pilih file backup database (.sql) terlebih dahulu.',
            'sql_file.mimes' => 'File harus berformat .sql atau file teks basis data SQL.',
            'sql_file.max' => 'Ukuran file cadangan maksimal adalah 50MB.',
        ]);

        try {
            $file = $request->file('sql_file');
            $sqlContent = file_get_contents($file->getRealPath());

            if (empty($sqlContent)) {
                return redirect()->back()->with('error', 'File database yang diunggah kosong.');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::unprepared($sqlContent);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            ActivityLog::record(
                'Restore Database System',
                "Memulihkan seluruh struktur & data database dari file: {$file->getClientOriginalName()}",
                'Pengaturan System'
            );

            return redirect()->back()->with('success', 'Database berhasil dipulihkan dari titik pemulihan (Restore Point)!');
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->back()->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }
}

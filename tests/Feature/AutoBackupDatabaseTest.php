<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AutoBackupDatabaseTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?? User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_access_backup_page_and_see_auto_backup_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('backup.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Auto Backup');
        $response->assertSee('Berkas Cadangan di Server');
        $response->assertSee('Batas Retensi File Cadangan');
        $response->assertSee('Backup Sekarang');
    }

    public function test_admin_can_update_auto_backup_settings_with_days(): void
    {
        $response = $this->actingAs($this->admin)->post(route('backup.settings'), [
            'auto_backup_enabled' => 1,
            'auto_backup_frequency_value' => 14,
            'auto_backup_frequency_unit' => 'days',
            'auto_backup_max_files' => 5,
        ]);

        $response->assertRedirect(route('backup.index'));
        $response->assertSessionHas('success');

        $profile = CompanyProfile::getProfile();
        $this->assertTrue($profile->auto_backup_enabled);
        $this->assertEquals(14, $profile->auto_backup_frequency_value);
        $this->assertEquals('days', $profile->auto_backup_frequency_unit);
        $this->assertEquals(5, $profile->auto_backup_max_files);
    }

    public function test_admin_can_update_auto_backup_settings_with_months(): void
    {
        $response = $this->actingAs($this->admin)->post(route('backup.settings'), [
            'auto_backup_enabled' => 1,
            'auto_backup_frequency_value' => 2,
            'auto_backup_frequency_unit' => 'months',
            'auto_backup_max_files' => 8,
        ]);

        $response->assertRedirect(route('backup.index'));

        $profile = CompanyProfile::getProfile();
        $this->assertEquals(2, $profile->auto_backup_frequency_value);
        $this->assertEquals('months', $profile->auto_backup_frequency_unit);
    }

    public function test_database_backup_service_generates_file(): void
    {
        $result = DatabaseBackupService::generateBackup(false);

        $this->assertFileExists($result['filepath']);
        $this->assertGreaterThan(0, $result['size']);
        $this->assertStringStartsWith('backup_manual_', $result['filename']);

        // Clean up created test file
        if (File::exists($result['filepath'])) {
            File::delete($result['filepath']);
        }
    }

    public function test_backup_due_calculation_for_days_and_months(): void
    {
        $profile = CompanyProfile::getProfile();

        // Case 1: Disabled
        $profile->update([
            'auto_backup_enabled' => false,
        ]);
        $this->assertFalse(DatabaseBackupService::isBackupDue());

        // Case 2: Days - Past due (8 days ago with 7 days interval)
        $profile->update([
            'auto_backup_enabled' => true,
            'auto_backup_frequency_value' => 7,
            'auto_backup_frequency_unit' => 'days',
            'last_backup_at' => Carbon::now()->subDays(8),
        ]);
        $this->assertTrue(DatabaseBackupService::isBackupDue());

        // Case 3: Days - Not due yet (2 days ago with 7 days interval)
        $profile->update([
            'last_backup_at' => Carbon::now()->subDays(2),
        ]);
        $this->assertFalse(DatabaseBackupService::isBackupDue());

        // Case 4: Months - Past due (40 days ago with 1 month interval)
        $profile->update([
            'auto_backup_frequency_value' => 1,
            'auto_backup_frequency_unit' => 'months',
            'last_backup_at' => Carbon::now()->subDays(40),
        ]);
        $this->assertTrue(DatabaseBackupService::isBackupDue());
    }

    public function test_admin_can_download_and_delete_backup_file(): void
    {
        $result = DatabaseBackupService::generateBackup(false);
        $filename = $result['filename'];

        // Download
        $downloadRes = $this->actingAs($this->admin)->get(route('backup.file.download', $filename));
        $downloadRes->assertStatus(200);

        // Delete
        $deleteRes = $this->actingAs($this->admin)->delete(route('backup.file.delete', $filename));
        $deleteRes->assertRedirect(route('backup.index'));
        $this->assertFileDoesNotExist($result['filepath']);
    }

    public function test_artisan_command_creates_backup_when_forced(): void
    {
        $this->artisan('wms:backup-database', ['--force' => true])
            ->assertExitCode(0);

        $files = DatabaseBackupService::getBackupFiles();
        $this->assertNotEmpty($files);

        // Clean up test file
        if (!empty($files[0]['path']) && File::exists($files[0]['path'])) {
            File::delete($files[0]['path']);
        }
    }
}

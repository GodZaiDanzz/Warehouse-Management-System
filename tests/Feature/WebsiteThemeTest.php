<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebsiteThemeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
    }

    public function test_website_theme_page_loads_successfully(): void
    {
        $response = $this->get('/website-theme');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Warna Tema Website & Tombol', false);
        $response->assertSee('Warna Header Tabel');
        $response->assertSee('Warna Tombol Tambah');
        $response->assertSee('Warna Tombol Simpan');
        $response->assertSee('Warna Tombol Aksi Lainnya');
        $response->assertSee('Masa Kadaluarsa Cat');
        $response->assertSee('cat_shelf_life_months');
        $response->assertSee('cat_fefo_alert_days');
        $response->assertSee('date_format');
        $response->assertSee('time_format');
        $response->assertSee('slow_moving_days');
        $response->assertSee('dead_stock_days');
        $response->assertSee('default_per_page');
        $response->assertSee('signer_1_name');
        $response->assertSee('signer_1_title');
        $response->assertSee('signer_2_name');
        $response->assertSee('signer_2_title');
        $response->assertSee('signer_3_name');
        $response->assertSee('signer_3_title');
    }

    public function test_website_theme_update_saves_all_button_colors(): void
    {
        $payload = [
            'theme_color' => '#1e40af',
            'btn_add_color' => '#059669',
            'btn_save_color' => '#275538',
            'btn_other_color' => '#f59e0b',
            'cat_shelf_life_months' => 18,
            'cat_fefo_alert_days' => 45,
            'date_format' => 'd M Y',
            'time_format' => 'H:i:s',
            'slow_moving_days' => 60,
            'dead_stock_days' => 150,
            'default_per_page' => 25,
            'signer_1_name' => 'Ahmad Fauzi',
            'signer_1_title' => 'Staff Inventory',
            'signer_2_name' => 'Budi Santoso, S.T.',
            'signer_2_title' => 'Supervisor QC',
            'signer_3_name' => 'Ir. Hendra Wijaya',
            'signer_3_title' => 'Kepala Gudang',
        ];

        $response = $this->put('/website-theme', $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $profile = CompanyProfile::getProfile();
        $this->assertEquals('#1e40af', $profile->theme_color);
        $this->assertEquals('#059669', $profile->btn_add_color);
        $this->assertEquals('#275538', $profile->btn_save_color);
        $this->assertEquals('#f59e0b', $profile->btn_other_color);
        $this->assertEquals(18, CompanyProfile::getCatShelfLifeMonths());
        $this->assertEquals(45, CompanyProfile::getCatFefoAlertDays());
        $this->assertEquals('d M Y', CompanyProfile::getSystemDateFormat());
        $this->assertEquals('H:i:s', CompanyProfile::getTimeFormat());
        $this->assertEquals(60, CompanyProfile::getSlowMovingDays());
        $this->assertEquals(150, CompanyProfile::getDeadStockDays());
        $this->assertEquals(25, CompanyProfile::getDefaultPerPage());

        $signers = CompanyProfile::getSigners();
        $this->assertEquals('Ahmad Fauzi', $signers['signer_1']['name']);
        $this->assertEquals('Staff Inventory', $signers['signer_1']['title']);
        $this->assertEquals('Budi Santoso, S.T.', $signers['signer_2']['name']);
        $this->assertEquals('Supervisor QC', $signers['signer_2']['title']);
        $this->assertEquals('Ir. Hendra Wijaya', $signers['signer_3']['name']);
        $this->assertEquals('Kepala Gudang', $signers['signer_3']['title']);
    }

    public function test_website_theme_update_auto_adjusts_dead_stock_if_less_than_slow_moving(): void
    {
        $payload = [
            'theme_color' => '#275538',
            'slow_moving_days' => 90,
            'dead_stock_days' => 50, // lower than slow moving
        ];

        $response = $this->put('/website-theme', $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $profile = CompanyProfile::getProfile();
        // Should automatically adjust dead_stock_days to slow_moving_days + 90 = 180
        $this->assertGreaterThan(CompanyProfile::getSlowMovingDays(), CompanyProfile::getDeadStockDays());
        $this->assertEquals(180, CompanyProfile::getDeadStockDays());
    }

    public function test_website_theme_update_validates_hex_colors(): void
    {
        $response = $this->put('/website-theme', [
            'theme_color' => 'invalid-color',
            'btn_add_color' => 'not-a-hex',
            'cat_shelf_life_months' => 0,
            'cat_fefo_alert_days' => 2, // min 7
            'default_per_page' => 999, // not in enum
        ]);

        $response->assertSessionHasErrors(['theme_color', 'btn_add_color', 'cat_shelf_life_months', 'cat_fefo_alert_days', 'default_per_page']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;

class WebsiteThemeController extends Controller
{
    public function index()
    {
        $profile = CompanyProfile::getProfile();
        return view('pages.website_theme.index', compact('profile'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme_color' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'btn_add_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'btn_save_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'btn_other_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'cat_shelf_life_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'cat_fefo_alert_days' => ['nullable', 'integer', 'min:7', 'max:365'],
            'date_format' => ['nullable', 'string', 'in:d/m/Y,d M Y,d F Y,Y-m-d'],
            'time_format' => ['nullable', 'string', 'in:H:i,H:i:s,h:i A'],
            'slow_moving_days' => ['nullable', 'integer', 'min:15', 'max:365'],
            'dead_stock_days' => ['nullable', 'integer', 'min:30', 'max:730'],
            'default_per_page' => ['nullable', 'integer', 'in:10,15,25,50,100'],
            'signer_1_name' => ['nullable', 'string', 'max:100'],
            'signer_1_title' => ['nullable', 'string', 'max:100'],
            'signer_2_name' => ['nullable', 'string', 'max:100'],
            'signer_2_title' => ['nullable', 'string', 'max:100'],
            'signer_3_name' => ['nullable', 'string', 'max:100'],
            'signer_3_title' => ['nullable', 'string', 'max:100'],
        ], [
            'theme_color.required' => 'Warna tema website harus dipilih.',
            'theme_color.regex' => 'Format kode warna HEX header tabel tidak valid (contoh: #275538).',
            'btn_add_color.regex' => 'Format kode warna HEX tombol tambah tidak valid (contoh: #275538).',
            'btn_save_color.regex' => 'Format kode warna HEX tombol simpan tidak valid (contoh: #275538).',
            'btn_other_color.regex' => 'Format kode warna HEX tombol lainnya tidak valid (contoh: #275538).',
            'cat_shelf_life_months.integer' => 'Masa kadaluarsa cat harus berupa angka bulan.',
            'cat_shelf_life_months.min' => 'Masa kadaluarsa cat minimal 1 bulan.',
            'cat_shelf_life_months.max' => 'Masa kadaluarsa cat maksimal 120 bulan (10 tahun).',
            'cat_fefo_alert_days.min' => 'Ambang peringatan FEFO minimal 7 hari.',
            'cat_fefo_alert_days.max' => 'Ambang peringatan FEFO maksimal 365 hari.',
            'slow_moving_days.min' => 'Batas Slow Moving minimal 15 hari.',
            'dead_stock_days.min' => 'Batas Dead Stock minimal 30 hari.',
            'default_per_page.in' => 'Pilihan baris tabel tidak valid (pilih 10, 15, 25, 50, atau 100).',
        ]);

        if (empty($validated['btn_add_color'])) {
            $validated['btn_add_color'] = $validated['theme_color'];
        }
        if (empty($validated['btn_save_color'])) {
            $validated['btn_save_color'] = $validated['theme_color'];
        }
        if (empty($validated['btn_other_color'])) {
            $validated['btn_other_color'] = $validated['theme_color'];
        }
        if (empty($validated['cat_shelf_life_months'])) {
            $validated['cat_shelf_life_months'] = 12;
        }
        if (empty($validated['cat_fefo_alert_days'])) {
            $validated['cat_fefo_alert_days'] = 60;
        }
        if (empty($validated['date_format'])) {
            $validated['date_format'] = 'd/m/Y';
        }
        if (empty($validated['time_format'])) {
            $validated['time_format'] = 'H:i';
        }
        if (empty($validated['slow_moving_days'])) {
            $validated['slow_moving_days'] = 90;
        }
        if (empty($validated['dead_stock_days'])) {
            $validated['dead_stock_days'] = 180;
        }
        // Ensure dead_stock_days is strictly greater than slow_moving_days
        if ($validated['dead_stock_days'] <= $validated['slow_moving_days']) {
            $validated['dead_stock_days'] = $validated['slow_moving_days'] + 90;
        }
        if (empty($validated['default_per_page'])) {
            $validated['default_per_page'] = 15;
        }
        if (empty($validated['signer_1_title'])) {
            $validated['signer_1_title'] = 'Staff Gudang / Inventory Control';
        }
        if (empty($validated['signer_2_title'])) {
            $validated['signer_2_title'] = 'Supervisor WMS / QC';
        }
        if (empty($validated['signer_3_title'])) {
            $validated['signer_3_title'] = 'Kepala Gudang / Project Manager';
        }

        $profile = CompanyProfile::first();
        if (!$profile) {
            $profile = CompanyProfile::create(array_merge([
                'name' => 'PT Mudalaya Energy Indonesia',
            ], $validated));
        } else {
            $profile->update($validated);
        }

        ActivityLog::record(
            activity: 'Pengaturan Tema & Sistem Lanjutan',
            description: "Memperbarui preferensi sistem: Tema ({$validated['theme_color']}), Shelf Life ({$validated['cat_shelf_life_months']} Bln), FEFO Alert ({$validated['cat_fefo_alert_days']} Hari), Dead Stock ({$validated['dead_stock_days']} Hari), Pagination ({$validated['default_per_page']}), Format ({$validated['date_format']})",
            module: 'Company Profile'
        );

        return redirect()->back()->with('success', 'Pengaturan tema, parameter sistem, dan penandatangan PDF berhasil disimpan!');
    }
}

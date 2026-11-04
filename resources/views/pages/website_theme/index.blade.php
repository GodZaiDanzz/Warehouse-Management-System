@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Warna Tema Website & Tombol</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Atur warna aksen tabel, tombol aksi, ambang FEFO cat, batas slow moving/dead stock, default baris tabel, dan penandatangan PDF</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>
                Konfigurasi Global Aktif
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" 
        x-data="{ 
            activeTab: 'theme',
            previewTab: 'theme',
            tableColor: '{{ old('theme_color', $profile->theme_color ?? '#275538') }}',
            btnAddColor: '{{ old('btn_add_color', $profile->btn_add_color ?? ($profile->theme_color ?? '#275538')) }}',
            btnSaveColor: '{{ old('btn_save_color', $profile->btn_save_color ?? ($profile->theme_color ?? '#275538')) }}',
            btnOtherColor: '{{ old('btn_other_color', $profile->btn_other_color ?? ($profile->theme_color ?? '#275538')) }}',
            defaultPerPage: {{ old('default_per_page', $profile->default_per_page ?? 15) }},
            dateFormat: '{{ old('date_format', $profile->date_format ?? 'd/m/Y') }}',
            timeFormat: '{{ old('time_format', $profile->time_format ?? 'H:i') }}',
            shelfLifeMonths: {{ old('cat_shelf_life_months', $profile->cat_shelf_life_months ?? 12) }},
            fefoAlertDays: {{ old('cat_fefo_alert_days', $profile->cat_fefo_alert_days ?? 60) }},
            slowMovingDays: {{ old('slow_moving_days', $profile->slow_moving_days ?? 90) }},
            deadStockDays: {{ old('dead_stock_days', $profile->dead_stock_days ?? 180) }},
            signer1Name: '{{ old('signer_1_name', $profile->signer_1_name ?? '') }}',
            signer1Title: '{{ old('signer_1_title', $profile->signer_1_title ?? 'Staff Gudang / Inventory Control') }}',
            signer2Name: '{{ old('signer_2_name', $profile->signer_2_name ?? '') }}',
            signer2Title: '{{ old('signer_2_title', $profile->signer_2_title ?? 'Supervisor WMS / QC') }}',
            signer3Name: '{{ old('signer_3_name', $profile->signer_3_name ?? '') }}',
            signer3Title: '{{ old('signer_3_title', $profile->signer_3_title ?? 'Kepala Gudang / Project Manager') }}',
            syncWithTableColor() {
                this.btnAddColor = this.tableColor;
                this.btnSaveColor = this.tableColor;
                this.btnOtherColor = this.tableColor;
            },
            getFormattedDatePreview() {
                if (this.dateFormat === 'd M Y') return '16 Sep 2026';
                if (this.dateFormat === 'd F Y') return '16 September 2026';
                if (this.dateFormat === 'Y-m-d') return '2026-09-16';
                return '16/09/2026';
            },
            getFormattedTimePreview() {
                if (this.timeFormat === 'H:i:s') return '14:30:00';
                if (this.timeFormat === 'h:i A') return '02:30 PM';
                return '14:30';
            },
            switchTab(tab) {
                this.activeTab = tab;
                this.previewTab = tab;
            },
            init() {
                this.$watch('tableColor', (val) => {
                    if (val) {
                        document.documentElement.style.setProperty('--theme-table-header-bg', val);
                    }
                });
            }
        }">
        
        <!-- Left: Configuration Form Card -->
        <div class="lg:col-span-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-6">
            <!-- Card Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Kustomisasi Website & Parameter</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih tab di bawah untuk menyesuaikan preferensi sistem</p>
                    </div>
                </div>

                <!-- One Click Sync Button (Only visible on theme tab) -->
                <button type="button" x-show="activeTab === 'theme'" @click="syncWithTableColor()" class="inline-flex items-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-100 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300 transition cursor-pointer" title="Samakan warna semua tombol dengan warna header tabel">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Samakan Warna</span>
                </button>
            </div>

            <!-- Form Navigation Tabs (Clean Pill Styling) -->
            <div class="grid grid-cols-3 gap-1.5 p-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80">
                <button type="button" @click="switchTab('theme')" :class="activeTab === 'theme' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-2xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium'" class="py-2 px-2 text-xs rounded-lg transition cursor-pointer text-center flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.6 0 1.16.13 1.67.36l6.63-6.63A2 2 0 0116.71 6a2 2 0 011.41.59l.88.88a2 2 0 010 2.83l-6.63 6.63c.23.51.36 1.07.36 1.67a4 4 0 01-4 4z"/></svg>
                    <span>Tampilan & Tabel</span>
                </button>
                <button type="button" @click="switchTab('inventory')" :class="activeTab === 'inventory' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-2xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium'" class="py-2 px-2 text-xs rounded-lg transition cursor-pointer text-center flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Parameter Inventaris</span>
                </button>
                <button type="button" @click="switchTab('pdf')" :class="activeTab === 'pdf' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-2xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium'" class="py-2 px-2 text-xs rounded-lg transition cursor-pointer text-center flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Penandatangan PDF</span>
                </button>
            </div>

            <form action="{{ route('website-theme.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- TAB 1: TAMPILAN & TABEL -->
                <div x-show="activeTab === 'theme'" class="space-y-4">
                    <!-- 1. COLOR HEADER TABEL -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                1. Warna Header Tabel
                            </label>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400" x-text="tableColor"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="tableColor" class="h-10 w-12 rounded-xl border border-gray-200 cursor-pointer dark:border-gray-700 bg-transparent p-1">
                            <div class="relative flex-1">
                                <input type="text" name="theme_color" x-model="tableColor" placeholder="#275538" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 uppercase outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                        <!-- Quick Presets -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach(['#275538' => 'Forest Green (Brand 30%)', '#1e432c' => 'Dark Forest Green', '#347b52' => 'Medium Green', '#f59e0b' => 'Energy Amber (Accent 10%)', '#d97706' => 'Warm Amber', '#111827' => 'Black Slate (Neutral 60%)', '#475467' => 'Gray Slate'] as $c => $lbl)
                                <button type="button" @click="tableColor = '{{ $c }}'" class="h-6 w-6 rounded-lg transition shadow-2xs border border-black/10 flex items-center justify-center cursor-pointer" style="background-color: {{ $c }}" title="{{ $lbl }}">
                                    <svg x-show="tableColor.toLowerCase() === '{{ strtolower($c) }}'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- 2. COLOR TOMBOL TAMBAH -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    2. Warna Tombol Tambah
                                </label>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Tombol "Tambah Barang", "Tambah Project", "Tambah Kategori", dll</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400" x-text="btnAddColor"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="btnAddColor" class="h-10 w-12 rounded-xl border border-gray-200 cursor-pointer dark:border-gray-700 bg-transparent p-1">
                            <div class="relative flex-1">
                                <input type="text" name="btn_add_color" x-model="btnAddColor" placeholder="#275538" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 uppercase outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach(['#275538' => 'Forest Green (Brand 30%)', '#1e432c' => 'Dark Forest Green', '#347b52' => 'Medium Green', '#f59e0b' => 'Energy Amber (Accent 10%)', '#d97706' => 'Warm Amber', '#111827' => 'Black Slate (Neutral 60%)', '#475467' => 'Gray Slate'] as $c => $lbl)
                                <button type="button" @click="btnAddColor = '{{ $c }}'" class="h-6 w-6 rounded-lg transition shadow-2xs border border-black/10 flex items-center justify-center cursor-pointer" style="background-color: {{ $c }}" title="{{ $lbl }}">
                                    <svg x-show="btnAddColor.toLowerCase() === '{{ strtolower($c) }}'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. COLOR TOMBOL SIMPAN -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    3. Warna Tombol Simpan
                                </label>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Tombol "Simpan Barang", "Simpan Perubahan", "Simpan Project", dll</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400" x-text="btnSaveColor"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="btnSaveColor" class="h-10 w-12 rounded-xl border border-gray-200 cursor-pointer dark:border-gray-700 bg-transparent p-1">
                            <div class="relative flex-1">
                                <input type="text" name="btn_save_color" x-model="btnSaveColor" placeholder="#275538" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 uppercase outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach(['#275538' => 'Forest Green (Brand 30%)', '#1e432c' => 'Dark Forest Green', '#347b52' => 'Medium Green', '#f59e0b' => 'Energy Amber (Accent 10%)', '#d97706' => 'Warm Amber', '#111827' => 'Black Slate (Neutral 60%)', '#475467' => 'Gray Slate'] as $c => $lbl)
                                <button type="button" @click="btnSaveColor = '{{ $c }}'" class="h-6 w-6 rounded-lg transition shadow-2xs border border-black/10 flex items-center justify-center cursor-pointer" style="background-color: {{ $c }}" title="{{ $lbl }}">
                                    <svg x-show="btnSaveColor.toLowerCase() === '{{ strtolower($c) }}'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- 4. COLOR TOMBOL LAINNYA -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    4. Warna Tombol Aksi Lainnya (Cari & Filter)
                                </label>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Tombol "Cari Project", "Filter Mutasi", "Bulk Input", dll</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400" x-text="btnOtherColor"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="btnOtherColor" class="h-10 w-12 rounded-xl border border-gray-200 cursor-pointer dark:border-gray-700 bg-transparent p-1">
                            <div class="relative flex-1">
                                <input type="text" name="btn_other_color" x-model="btnOtherColor" placeholder="#275538" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 uppercase outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach(['#275538' => 'Forest Green (Brand 30%)', '#1e432c' => 'Dark Forest Green', '#347b52' => 'Medium Green', '#f59e0b' => 'Energy Amber (Accent 10%)', '#d97706' => 'Warm Amber', '#111827' => 'Black Slate (Neutral 60%)', '#475467' => 'Gray Slate'] as $c => $lbl)
                                <button type="button" @click="btnOtherColor = '{{ $c }}'" class="h-6 w-6 rounded-lg transition shadow-2xs border border-black/10 flex items-center justify-center cursor-pointer" style="background-color: {{ $c }}" title="{{ $lbl }}">
                                    <svg x-show="btnOtherColor.toLowerCase() === '{{ strtolower($c) }}'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- 5. DEFAULT JUMLAH BARIS TABEL (PAGINATION) -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    5. Default Jumlah Baris Tabel
                                </label>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Jumlah baris data otomatis per halaman saat membuka tabel inventaris & mutasi</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400">
                                <span x-text="defaultPerPage"></span> Baris
                            </span>
                        </div>

                        <input type="hidden" name="default_per_page" :value="defaultPerPage">
                        <div class="grid grid-cols-5 gap-2">
                            @foreach([10, 15, 25, 50, 100] as $opt)
                                <button type="button" @click="defaultPerPage = {{ $opt }}" :class="defaultPerPage == {{ $opt }} ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border py-2 text-xs text-center transition cursor-pointer">
                                    {{ $opt }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- 6. FORMAT PENOMORAN TANGGAL & WAKTU GLOBAL -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    6. Format Tanggal & Waktu Global
                                </label>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Standarisasi penulisan tanggal dan jam pada tabel, log aktivitas, dan laporan cetak</span>
                            </div>
                        </div>

                        <!-- Date Format Selection -->
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 block">Pola Tanggal:</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" @click="dateFormat = 'd/m/Y'" :class="dateFormat === 'd/m/Y' ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border p-2 text-left transition cursor-pointer">
                                    <span class="block text-[11px] font-mono font-bold">d/m/Y</span>
                                    <span class="text-[10px] opacity-75">16/09/2026</span>
                                </button>
                                <button type="button" @click="dateFormat = 'd M Y'" :class="dateFormat === 'd M Y' ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border p-2 text-left transition cursor-pointer">
                                    <span class="block text-[11px] font-mono font-bold">d M Y</span>
                                    <span class="text-[10px] opacity-75">16 Sep 2026</span>
                                </button>
                                <button type="button" @click="dateFormat = 'd F Y'" :class="dateFormat === 'd F Y' ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border p-2 text-left transition cursor-pointer">
                                    <span class="block text-[11px] font-mono font-bold">d F Y</span>
                                    <span class="text-[10px] opacity-75">16 Sept 2026</span>
                                </button>
                                <button type="button" @click="dateFormat = 'Y-m-d'" :class="dateFormat === 'Y-m-d' ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border p-2 text-left transition cursor-pointer">
                                    <span class="block text-[11px] font-mono font-bold">Y-m-d</span>
                                    <span class="text-[10px] opacity-75">2026-09-16</span>
                                </button>
                            </div>
                            <input type="hidden" name="date_format" :value="dateFormat">
                        </div>

                        <!-- Time Format Selection -->
                        <div class="space-y-1.5 pt-2">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 block">Pola Waktu / Jam:</span>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="timeFormat = 'H:i'" :class="timeFormat === 'H:i' ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border p-2 text-left transition cursor-pointer">
                                    <span class="block text-[11px] font-mono font-bold">H:i</span>
                                    <span class="text-[10px] opacity-75">14:30 (24 Jam)</span>
                                </button>
                                <button type="button" @click="timeFormat = 'H:i:s'" :class="timeFormat === 'H:i:s' ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border p-2 text-left transition cursor-pointer">
                                    <span class="block text-[11px] font-mono font-bold">H:i:s</span>
                                    <span class="text-[10px] opacity-75">14:30:45 (Detik)</span>
                                </button>
                                <button type="button" @click="timeFormat = 'h:i A'" :class="timeFormat === 'h:i A' ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border p-2 text-left transition cursor-pointer">
                                    <span class="block text-[11px] font-mono font-bold">h:i A</span>
                                    <span class="text-[10px] opacity-75">02:30 PM (12 Jam)</span>
                                </button>
                            </div>
                            <input type="hidden" name="time_format" :value="timeFormat">
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PARAMETER INVENTARIS & FEFO -->
                <div x-show="activeTab === 'inventory'" class="space-y-4">
                    <!-- 1. MASA KADALUARSA CAT -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    Masa Kadaluarsa Cat (Cat Shelf Life)
                                </label>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Masa berlaku cat dari tanggal pembuatan saat registrasi batch barang baru</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400">
                                <span x-text="shelfLifeMonths"></span> Bulan
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <input type="number" name="cat_shelf_life_months" x-model.number="shelfLifeMonths" min="1" max="120" placeholder="12" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Bulan</span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 block">Preset Standar:</span>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" @click="shelfLifeMonths = 6" :class="shelfLifeMonths == 6 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border px-2 py-1.5 text-xs text-center transition cursor-pointer">
                                    6 Bln
                                </button>
                                <button type="button" @click="shelfLifeMonths = 12" :class="shelfLifeMonths == 12 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border px-2 py-1.5 text-xs text-center transition cursor-pointer">
                                    12 Bln
                                </button>
                                <button type="button" @click="shelfLifeMonths = 18" :class="shelfLifeMonths == 18 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border px-2 py-1.5 text-xs text-center transition cursor-pointer">
                                    18 Bln
                                </button>
                                <button type="button" @click="shelfLifeMonths = 24" :class="shelfLifeMonths == 24 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border px-2 py-1.5 text-xs text-center transition cursor-pointer">
                                    24 Bln
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. AMBANG PERINGATAN FEFO ALERT (HARI) -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    Ambang Peringatan Menjelang Expired Cat (FEFO Alert)
                                </label>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Sisa hari menjelang kadaluarsa untuk memicu banner & badge kuning "Segera Gunakan"</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400">
                                ≤ <span x-text="fefoAlertDays"></span> Hari
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <input type="number" name="cat_fefo_alert_days" x-model.number="fefoAlertDays" min="7" max="365" placeholder="60" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Hari Menjelang ED</span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 block">Preset Peringatan FEFO:</span>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="fefoAlertDays = 30" :class="fefoAlertDays == 30 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border px-2 py-1.5 text-xs text-center transition cursor-pointer">
                                    30 Hari (1 Bulan)
                                </button>
                                <button type="button" @click="fefoAlertDays = 60" :class="fefoAlertDays == 60 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border px-2 py-1.5 text-xs text-center transition cursor-pointer">
                                    60 Hari (2 Bulan)
                                </button>
                                <button type="button" @click="fefoAlertDays = 90" :class="fefoAlertDays == 90 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border px-2 py-1.5 text-xs text-center transition cursor-pointer">
                                    90 Hari (3 Bulan)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. BATAS HARI DEAD STOCK / SLOW MOVING -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Batas Hari Dead Stock / Slow Moving
                            </label>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Sesuaikan analisis barang stagnan di Laporan Dead Stock sesuai kebutuhan project</span>
                        </div>

                        <!-- Slow Moving Days -->
                        <div class="space-y-2 pt-1 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-gray-700 dark:text-gray-300">Ambang Slow Moving (Hari Tanpa Pengeluaran):</span>
                                <span class="font-mono font-bold text-amber-600 dark:text-amber-400">≥ <span x-text="slowMovingDays"></span> Hari</span>
                            </div>
                            <div class="relative">
                                <input type="number" name="slow_moving_days" x-model.number="slowMovingDays" min="15" max="365" placeholder="90" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Hari</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="slowMovingDays = 60" :class="slowMovingDays == 60 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border py-1.5 text-xs text-center transition cursor-pointer">
                                    60 Hari
                                </button>
                                <button type="button" @click="slowMovingDays = 90" :class="slowMovingDays == 90 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border py-1.5 text-xs text-center transition cursor-pointer">
                                    90 Hari
                                </button>
                                <button type="button" @click="slowMovingDays = 120" :class="slowMovingDays == 120 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border py-1.5 text-xs text-center transition cursor-pointer">
                                    120 Hari
                                </button>
                            </div>
                        </div>

                        <!-- Dead Stock Days -->
                        <div class="space-y-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-gray-700 dark:text-gray-300">Ambang Dead Stock (Kritis Tanpa Pengeluaran):</span>
                                <span class="font-mono font-bold text-rose-600 dark:text-rose-400">≥ <span x-text="deadStockDays"></span> Hari</span>
                            </div>
                            <div class="relative">
                                <input type="number" name="dead_stock_days" x-model.number="deadStockDays" min="30" max="730" placeholder="180" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Hari</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="deadStockDays = 120" :class="deadStockDays == 120 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border py-1.5 text-xs text-center transition cursor-pointer">
                                    120 Hari
                                </button>
                                <button type="button" @click="deadStockDays = 180" :class="deadStockDays == 180 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border py-1.5 text-xs text-center transition cursor-pointer">
                                    180 Hari
                                </button>
                                <button type="button" @click="deadStockDays = 365" :class="deadStockDays == 365 ? 'border-gray-800 bg-gray-800 text-white font-bold dark:border-white dark:bg-white dark:text-gray-900 shadow-2xs' : 'border-gray-200 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-gray-300'" class="rounded-lg border py-1.5 text-xs text-center transition cursor-pointer">
                                    365 Hari (1 Thn)
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400">
                                Catatan: Batas Dead Stock harus selalu lebih lama daripada batas Slow Moving.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: PENANDATANGAN LAPORAN PDF -->
                <div x-show="activeTab === 'pdf'" class="space-y-4">
                    <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-300">
                        <p class="font-bold text-gray-800 dark:text-gray-100 mb-1">Otomatisasi Penandatangan Dokumen PDF</p>
                        <p class="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                            Nama dan jabatan di bawah ini otomatis dicetak pada lembar pengesahan <strong>Laporan Pengeluaran Material</strong>, <strong>Laporan Inspeksi QC Masuk</strong>, <strong>Laporan Cat / Painting</strong>, dan <strong>Laporan Slow Moving Stock</strong>.
                        </p>
                    </div>

                    <!-- Signer 1: Pembuat / Staff Gudang -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                1. Penandatangan Kolom 1 (Dibuat Oleh)
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Staff / Inventory Control</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Lengkap Pejabat:</label>
                                <input type="text" name="signer_1_name" x-model="signer1Name" placeholder="Contoh: Ahmad Fauzi" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Jabatan Resmi:</label>
                                <input type="text" name="signer_1_title" x-model="signer1Title" placeholder="Staff Gudang / Inventory Control" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Signer 2: Pemeriksa / QC / Supervisor -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                2. Penandatangan Kolom 2 (Diperiksa Oleh)
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Supervisor / QC Officer</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Lengkap Pejabat:</label>
                                <input type="text" name="signer_2_name" x-model="signer2Name" placeholder="Contoh: Budi Santoso, S.T." class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Jabatan Resmi:</label>
                                <input type="text" name="signer_2_title" x-model="signer2Title" placeholder="Supervisor WMS / QC" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Signer 3: Mengetahui / Project Manager -->
                    <div class="space-y-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                3. Penandatangan Kolom 3 (Mengetahui / Disetujui)
                            </span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Kepala Gudang / Project Manager</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Lengkap Pejabat:</label>
                                <input type="text" name="signer_3_name" x-model="signer3Name" placeholder="Contoh: Ir. Hendra Wijaya" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Jabatan Resmi:</label>
                                <input type="text" name="signer_3_title" x-model="signer3Title" placeholder="Kepala Gudang / Project Manager" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="tableColor = '#275538'; btnAddColor = '#275538'; btnSaveColor = '#275538'; btnOtherColor = '#275538'; defaultPerPage = 15; dateFormat = 'd/m/Y'; timeFormat = 'H:i'; shelfLifeMonths = 12; fefoAlertDays = 60; slowMovingDays = 90; deadStockDays = 180" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer text-center">
                        Reset Default Sistem
                    </button>
                    <button type="submit" class="btn-save-theme inline-flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-xs font-bold text-white shadow-md focus:outline-none transition cursor-pointer" :style="'background-color: ' + btnSaveColor">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Pengaturan Tema & Parameter
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Live Preview Card -->
        <div class="lg:col-span-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Live Preview Interaktif</h3>
                    <span class="text-[10px] px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">Real-time</span>
                </div>
                
                <!-- Preview Switcher -->
                <div class="flex items-center gap-1">
                    <button type="button" @click="previewTab = 'theme'" :class="previewTab === 'theme' ? 'bg-gray-200 dark:bg-gray-700 font-bold text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-2 py-1 rounded text-[11px] transition cursor-pointer">
                        Tabel & Tombol
                    </button>
                    <button type="button" @click="previewTab = 'inventory'" :class="previewTab === 'inventory' ? 'bg-gray-200 dark:bg-gray-700 font-bold text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-2 py-1 rounded text-[11px] transition cursor-pointer">
                        FEFO & Stok
                    </button>
                    <button type="button" @click="previewTab = 'pdf'" :class="previewTab === 'pdf' ? 'bg-gray-200 dark:bg-gray-700 font-bold text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-2 py-1 rounded text-[11px] transition cursor-pointer">
                        PDF Signer
                    </button>
                </div>
            </div>

            <!-- PREVIEW 1: TAMPILAN & TABEL -->
            <div x-show="previewTab === 'theme'" class="space-y-4">
                <!-- Buttons Preview -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Preview Tombol Aksi Sistem:</span>
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 space-y-3">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-sm transition" :style="'background-color: ' + btnAddColor">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Barang</span>
                            </button>

                            <button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-sm transition" :style="'background-color: ' + btnSaveColor">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Barang</span>
                            </button>

                            <button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-sm transition" :style="'background-color: ' + btnOtherColor">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Cari & Filter</span>
                            </button>
                        </div>

                        <!-- Simulated Modal Action -->
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Simulasi Dialog / Modal:</span>
                            <div class="flex gap-2">
                                <button type="button" class="rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Batal
                                </button>
                                <button type="button" class="rounded-xl px-4 py-1.5 text-xs font-bold text-white shadow-sm transition" :style="'background-color: ' + btnSaveColor">
                                    Simpan Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Preview with Date Format & Pagination indicator -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Preview Header Tabel & Data:</span>
                        <span class="text-[10.5px] font-mono text-gray-500 dark:text-gray-400">
                            Waktu: <span x-text="getFormattedDatePreview()"></span> <span x-text="getFormattedTimePreview()"></span>
                        </span>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800" :style="'--theme-table-header-bg: ' + tableColor">
                        <table class="w-full text-left text-xs" :style="'--theme-table-header-bg: ' + tableColor">
                            <thead>
                                <tr class="font-bold text-white transition-colors duration-200" :style="'background-color: ' + tableColor + ' !important; --theme-table-header-bg: ' + tableColor">
                                    <th class="px-3 py-3" :style="'background-color: ' + tableColor + ' !important; color: #ffffff !important;'">NO</th>
                                    <th class="px-3 py-3" :style="'background-color: ' + tableColor + ' !important; color: #ffffff !important;'">KODE MATERIAL</th>
                                    <th class="px-3 py-3" :style="'background-color: ' + tableColor + ' !important; color: #ffffff !important;'">NAMA BARANG</th>
                                    <th class="px-3 py-3" :style="'background-color: ' + tableColor + ' !important; color: #ffffff !important;'">KATEGORI</th>
                                    <th class="px-3 py-3" :style="'background-color: ' + tableColor + ' !important; color: #ffffff !important;'">TGL MASUK</th>
                                    <th class="px-3 py-3 text-center" :style="'background-color: ' + tableColor + ' !important; color: #ffffff !important;'">STOK</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900 font-medium text-[11px]">
                                <tr>
                                    <td class="px-3 py-2.5 text-gray-500">1</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap"><span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">CSM-001</span></td>
                                    <td class="px-3 py-2.5 font-bold text-gray-900 dark:text-white">BATU GERINDA CUTTING 14 INCH</td>
                                    <td class="px-3 py-2.5 text-gray-500">CONSUMABLE</td>
                                    <td class="px-3 py-2.5 font-mono text-gray-600 dark:text-gray-300" x-text="getFormattedDatePreview()"></td>
                                    <td class="px-3 py-2.5 text-center font-bold text-emerald-600">150 Pcs</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2.5 text-gray-500">2</td>
                                    <td class="px-3 py-2.5 whitespace-nowrap"><span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">CAT-001</span></td>
                                    <td class="px-3 py-2.5 font-bold text-gray-900 dark:text-white">CAT EPOXY PRIMER GREY 20L</td>
                                    <td class="px-3 py-2.5 text-gray-500">PAINTING</td>
                                    <td class="px-3 py-2.5 font-mono text-gray-600 dark:text-gray-300" x-text="getFormattedDatePreview()"></td>
                                    <td class="px-3 py-2.5 text-center font-bold text-emerald-600">35 Pail</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer Simulation -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                        <span>Menampilkan 1 - <strong class="text-gray-800 dark:text-gray-200" x-text="defaultPerPage"></strong> dari 150 barang</span>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Default: <span x-text="defaultPerPage"></span> baris / hal</span>
                    </div>
                </div>
            </div>

            <!-- PREVIEW 2: PARAMETER INVENTARIS & FEFO -->
            <div x-show="previewTab === 'inventory'" class="space-y-4">
                <!-- FEFO Simulator Widget -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Simulasi Klasifikasi Status FEFO Cat:</span>
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 space-y-3">
                        <div class="flex items-center justify-between text-xs pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">Masa Simpan Pabrik:</span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white"><span x-text="shelfLifeMonths"></span> Bulan</span>
                        </div>
                        <div class="flex items-center justify-between text-xs pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">Ambang Pemicu Warning FEFO:</span>
                            <span class="font-mono font-bold text-amber-600 dark:text-amber-400">≤ <span x-text="fefoAlertDays"></span> Hari Menjelang ED</span>
                        </div>

                        <!-- 3 Badges Simulation -->
                        <div class="space-y-2 pt-1">
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-white dark:bg-gray-900 border border-emerald-200 dark:border-emerald-800/40 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">Aman / Segar</span>
                                </div>
                                <span class="font-mono text-gray-500 dark:text-gray-400">&gt; <span x-text="fefoAlertDays"></span> hari lagi</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-white dark:bg-gray-900 border border-amber-200 dark:border-amber-800/40 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    <span class="font-bold text-amber-700 dark:text-amber-400">Segera Gunakan (FEFO Alert)</span>
                                </div>
                                <span class="font-mono text-amber-600 dark:text-amber-400">≤ <span x-text="fefoAlertDays"></span> hari lagi</span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-white dark:bg-gray-900 border border-rose-200 dark:border-rose-800/40 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <span class="font-bold text-rose-700 dark:text-rose-400">Kadaluarsa (Expired)</span>
                                </div>
                                <span class="font-mono text-rose-600 dark:text-rose-400">Lewat masa simpan</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dead Stock Simulator Widget -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Simulasi Analisis Dead Stock di Menu Laporan:</span>
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Fast Moving (Aktif):</span>
                            <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">&lt; <span x-text="slowMovingDays"></span> Hari Diam</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-gray-900 border border-amber-200 dark:border-amber-800/50">
                            <span class="font-semibold text-amber-700 dark:text-amber-400">Slow Moving (Peringatan):</span>
                            <span class="font-mono text-amber-600 dark:text-amber-400 font-bold">≥ <span x-text="slowMovingDays"></span> Hari Diam</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-gray-900 border border-rose-200 dark:border-rose-800/50">
                            <span class="font-semibold text-rose-700 dark:text-rose-400">Dead Stock (Kritis):</span>
                            <span class="font-mono text-rose-600 dark:text-rose-400 font-bold">≥ <span x-text="deadStockDays"></span> Hari Diam</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PREVIEW 3: PENANDATANGAN LAPORAN PDF -->
            <div x-show="previewTab === 'pdf'" class="space-y-3">
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Preview Format Pengesahan / Lembar Tanda Tangan PDF:</span>
                <div class="p-4 rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <div class="text-center border-b border-gray-200 dark:border-gray-800 pb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">LEMBAR PENGESAHAN DOKUMEN GUDANG & QC</span>
                    </div>

                    <!-- 3 Signature Columns -->
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <!-- Signer 1 -->
                        <div class="space-y-6">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 block">Dibuat Oleh:</span>
                            <div class="h-10"></div>
                            <div>
                                <span class="block text-xs font-bold text-gray-900 dark:text-white underline decoration-gray-400 decoration-1 underline-offset-4" x-text="signer1Name ? signer1Name : '(Nama Pejabat)'"></span>
                                <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-1" x-text="signer1Title"></span>
                            </div>
                        </div>

                        <!-- Signer 2 -->
                        <div class="space-y-6">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 block">Diperiksa Oleh:</span>
                            <div class="h-10"></div>
                            <div>
                                <span class="block text-xs font-bold text-gray-900 dark:text-white underline decoration-gray-400 decoration-1 underline-offset-4" x-text="signer2Name ? signer2Name : '(Nama Pejabat)'"></span>
                                <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-1" x-text="signer2Title"></span>
                            </div>
                        </div>

                        <!-- Signer 3 -->
                        <div class="space-y-6">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 block">Mengetahui:</span>
                            <div class="h-10"></div>
                            <div>
                                <span class="block text-xs font-bold text-gray-900 dark:text-white underline decoration-gray-400 decoration-1 underline-offset-4" x-text="signer3Name ? signer3Name : '(Nama Pejabat)'"></span>
                                <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-1" x-text="signer3Title"></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 text-[10px] text-gray-400 text-center">
                        Otomatis terintegrasi pada cetak PDF Laporan Material Keluar, QC Masuk, Cat, dan Dead Stock.
                    </div>
                </div>
            </div>

            <!-- Design Rules Information Footer -->
            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800 text-[11px] text-gray-500 dark:text-gray-400 space-y-1">
                <p class="font-bold text-gray-700 dark:text-gray-300">Pedoman Desain & Harmonisasi (Standar 60 - 30 - 10):</p>
                <p>• <strong>Warna 1 (60%):</strong> Netral Grayscale untuk latar belakang, kartu, dan tipografi teks.</p>
                <p>• <strong>Warna 2 (30%):</strong> Corporate Forest Green (<code>#275538</code>) sebagai identitas primer tabel dan tombol aksi utama.</p>
                <p>• <strong>Warna 3 (10%):</strong> Energy Amber (<code>#F59E0B</code>) untuk aksen tombol sekunder, filter, dan sorotan.</p>
                <p>• Gunakan tab di sebelah kiri untuk mengatur tampilan atau parameter fungsional sistem.</p>
            </div>
        </div>
    </div>
</div>
@endsection

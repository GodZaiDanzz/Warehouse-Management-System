<div>
    <!-- Flash Messages (SweetAlert2 Trigger) -->
    @if (session()->has('success'))
        @php
            $successMsg = session('success');
            $isDelete = preg_match('/hapus|delete|deleted|dihapus/i', $successMsg);
            $swalTitle = $isDelete ? 'Deleted!' : 'Berhasil!';
        @endphp
        <div x-init="if (typeof window.showSwalSuccess === 'function') window.showSwalSuccess(@json($swalTitle), @json($successMsg))" class="hidden"></div>
    @endif

    @if (session()->has('error') && !$isModalOpen)
        <div x-init="if (typeof window.showSwalError === 'function') window.showSwalError('Gagal!', @json(session('error')))" class="hidden"></div>
    @endif

    @php
        $isPpic = auth()->user() && auth()->user()->isPpic();
    @endphp

    @if($isPpic)
        <!-- PPIC View Only Notification Banner -->
        <div class="mb-6 flex items-center justify-between rounded-2xl border border-brand-200 bg-brand-50/80 p-4 text-xs text-brand-900 dark:border-brand-900/40 dark:bg-brand-950/30 dark:text-brand-300">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white font-bold">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </span>
                <div>
                    <span class="font-extrabold text-brand-900 dark:text-brand-200 block">Mode Akses PPIC (View-Only)</span>
                    <span>Anda memiliki akses membaca ketersediaan riwayat pengeluaran material untuk keperluan perencanaan produksi. Fitur pencatatan dan modifikasi data dinonaktifkan.</span>
                </div>
            </div>
            <span class="rounded-lg bg-brand-200/80 px-2.5 py-1 text-[11px] font-bold text-brand-900 dark:bg-brand-900/60 dark:text-brand-200">
                PPIC Active
            </span>
        </div>
    @endif

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Transaksi Keluar</span>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Jumlah Keluar (Bulan Ini)</span>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ number_format($monthDiscreteQty ?? 0, 0, ',', '.') }}</h3>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Pcs/Unit</span>
                    </div>
                    @if(!empty($monthDecimalString))
                        <div class="mt-1">
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 font-mono">
                                + {{ $monthDecimalString }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Project Aktif</span>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalProjects) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Module Tabs (Section 5: Consumable, Secondary Part, Cat/Painting) -->
    <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-2 shadow-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="setActiveModule('all')" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition cursor-pointer {{ $activeModule === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <span>Semua Modul</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeModule === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ $totalTransactions }}</span>
            </button>

            <button type="button" wire:click="setActiveModule('Consumable')" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition cursor-pointer {{ $activeModule === 'Consumable' ? 'bg-brand-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <span>1. Pengeluaran Consumable</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeModule === 'Consumable' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ $consumableCount }}</span>
            </button>

            <button type="button" wire:click="setActiveModule('Secondary Part')" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition cursor-pointer {{ $activeModule === 'Secondary Part' ? 'bg-brand-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <span>2. Pengeluaran Secondary Part</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeModule === 'Secondary Part' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ $secondaryCount }}</span>
            </button>

            <button type="button" wire:click="setActiveModule('Cat / Painting')" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition cursor-pointer {{ $activeModule === 'Cat / Painting' ? 'bg-brand-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <span>3. Pengeluaran Cat / Painting</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeModule === 'Cat / Painting' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ $catCount }}</span>
            </button>

            <button type="button" wire:click="setActiveModule('Raw Material')" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition cursor-pointer {{ $activeModule === 'Raw Material' ? 'bg-brand-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <span>4. Pengeluaran Raw Material</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeModule === 'Raw Material' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ $rawMaterialCount }}</span>
            </button>

            <button type="button" wire:click="setActiveModule('Gas')" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition cursor-pointer {{ $activeModule === 'Gas' ? 'bg-brand-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <span>5. Pengeluaran Gas</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ $activeModule === 'Gas' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ $gasCount }}</span>
            </button>
        </div>
    </div>

    @if ($activeModule === 'Cat / Painting')
        <!-- Special Cat Alert Info -->
        <div class="mb-6 flex items-center justify-between rounded-2xl border border-brand-200 bg-brand-50 p-4 text-xs text-brand-900 dark:border-brand-900/50 dark:bg-brand-950/30 dark:text-brand-300 shadow-xs">
            <div class="flex items-center gap-3">
                <div>
                    <span class="font-extrabold text-brand-950 dark:text-brand-200 block">Modul Spesifik: Pengeluaran Cat / Painting</span>
                    <span>Dilengkapi fitur pelacakan <strong>Tanggal Pembuatan (Manufacturing Date)</strong> secara real-time saat pengeluaran material berlangsung untuk mencegah kadaluarsa.</span>
                </div>
            </div>
            <a href="{{ route('reports.cat-painting') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Laporan Cat</span>
            </a>
        </div>
    @endif

    @if ($activeModule === 'Raw Material')
        <!-- Special Raw Material Alert Info -->
        <div class="mb-6 flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <span class="font-extrabold text-amber-950 dark:text-amber-200 block">Modul Spesifik: Pengeluaran Raw Material (STORAGE 2)</span>
                    <span>Monitoring pengeluaran bahan baku pabrikasi (Plat Besi, Pipa, Siku, Hollow, Round Bar) yang tersimpan di <strong>STORAGE 2</strong>.</span>
                </div>
            </div>
            <a href="{{ route('items.index', ['storage_filter' => 'STORAGE 2']) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Stok Storage 2</span>
            </a>
        </div>
    @endif

    @if ($activeModule === 'Gas')
        <!-- Special Gas Alert Info -->
        <div class="mb-6 flex items-center justify-between rounded-2xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-300 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-500 text-white shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <div>
                    <span class="font-extrabold text-sky-950 dark:text-sky-200 block">Modul Spesifik: Pengeluaran Tabung Gas Industri</span>
                    <span>Monitoring distribusi tabung gas operasional pabrikasi & pengelasan (Argon, CO2, Oxygen, Acetylene, LPG, Mixed Gas) dari <strong>STORAGE 1</strong>.</span>
                </div>
            </div>
            <a href="{{ route('items.index', ['category_id' => \App\Models\Category::where('name', 'Gas')->value('id') ?? '']) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-sky-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-sky-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Stok Gas</span>
            </a>
        </div>
    @endif

    @if(!$isPpic)
        <!-- Import Excel Card Section (Warehouse/Admin Only) -->
        <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900/60 mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Import Data Material Keluar Excel</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Unggah file Excel (.xlsx, .xls, .csv) pengeluaran material dengan kolom: Tanggal, PIC, Nama Barang, Jumlah, Satuan, Project.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="downloadTemplate" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs cursor-pointer">
                        <svg class="h-3.5 w-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template
                    </button>
                </div>
            </div>

            <form wire:submit.prevent="startImport" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex-1">
                    <input type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="block w-full rounded-xl border border-gray-200 bg-white text-xs text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-xs file:font-bold file:text-gray-800 hover:file:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-200 cursor-pointer">
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="file, startImport" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none shadow-sm transition cursor-pointer min-w-[150px] min-h-[38px]">
                    <span wire:loading.remove wire:target="file, startImport">Upload & Jalankan Import</span>
                    <span wire:loading wire:target="file, startImport" class="inline-flex items-center justify-center">
                        <svg class="h-5 w-5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                </button>
            </form>

            @if ($importErrorMessage)
                <div class="mt-3 rounded-xl bg-rose-50 p-3 text-xs font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    {{ $importErrorMessage }}
                </div>
            @endif
        </div>
    @endif

    <!-- Actions & Comprehensive Filter Card -->
    <div class="rounded-2xl border border-gray-100 bg-white p-4 sm:p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 mb-6 space-y-4">
        <!-- Top Action Bar -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <!-- Search Bar -->
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Nama Barang, PIC, Project, No Referensi..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-500">
                <svg class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Main Actions Buttons -->
            <div class="flex items-center gap-2.5">
                <!-- Kalkulator Stick / Gram ke Kg -->
                <button type="button" 
                        @click="$dispatch('open-stick-calculator', { mode: 'general' })" 
                        class="inline-flex items-center gap-2 rounded-xl border border-amber-300 dark:border-amber-700/80 bg-amber-50 hover:bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 px-3.5 py-2.5 text-xs font-bold transition shadow-2xs cursor-pointer"
                        title="Buka Kalkulator Konversi Stick / Batang ke Kilogram (Kg)">
                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Kalkulator Stick (Kg)</span>
                </button>

                <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs cursor-pointer transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span wire:loading.remove wire:target="exportExcel, exportCsv">Export Excel (.xlsx)</span>
                    <span wire:loading wire:target="exportExcel, exportCsv" class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Menyiapkan...
                    </span>
                </button>

                @if(!$isPpic)
                    <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 transition cursor-pointer btn-add-theme">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Catat Material Keluar
                    </button>
                @endif
            </div>
        </div>

        <!-- Filter Form Row (Rentang Tanggal, Filter Item, Filter Project, Filter Satuan & Presets) -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 items-end">
            <!-- Filter Tanggal Mulai s/d Sampai -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Tanggal</label>
                <div class="flex items-center gap-2">
                    <div wire:ignore class="relative w-full" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                this.fp = flatpickr(this.$refs.picker, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer transition',
                                    defaultDate: @js($dateFrom),
                                    onChange: (dates, dateStr) => {
                                        if (dateStr !== $wire.dateFrom) {
                                            $wire.set('dateFrom', dateStr);
                                        }
                                    }
                                });
                            };
                            this.$nextTick(() => initPicker());
                            this.$watch('$wire.dateFrom', val => {
                                if (this.fp && val !== this.fp.input.value) {
                                    this.fp.setDate(val, false);
                                }
                            });
                        },
                        destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                    }">
                        <input x-ref="picker" type="text" class="hidden" value="{{ $dateFrom }}">
                    </div>

                    <span class="text-xs font-bold text-gray-400">s/d</span>

                    <div wire:ignore class="relative w-full" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                this.fp = flatpickr(this.$refs.picker, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer transition',
                                    defaultDate: @js($dateTo),
                                    onChange: (dates, dateStr) => {
                                        if (dateStr !== $wire.dateTo) {
                                            $wire.set('dateTo', dateStr);
                                        }
                                    }
                                });
                            };
                            this.$nextTick(() => initPicker());
                            this.$watch('$wire.dateTo', val => {
                                if (this.fp && val !== this.fp.input.value) {
                                    this.fp.setDate(val, false);
                                }
                            });
                        },
                        destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                    }">
                        <input x-ref="picker" type="text" class="hidden" value="{{ $dateTo }}">
                    </div>
                </div>
            </div>

            <!-- Filter Nama Item (Multi-Select) -->
            <div x-data="materialOutItemFilter" @click.outside="close()" class="relative">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                    <span>Filter Nama Item</span>
                    <template x-if="$wire.selectedItemIds.length > 0">
                        <span class="text-[10px] font-extrabold text-brand-600 dark:text-brand-400 font-mono" x-text="$wire.selectedItemIds.length + ' Item'"></span>
                    </template>
                </label>
                
                <button type="button" @click.stop="toggle()" class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer text-left shadow-xs">
                    <span class="truncate pr-2" x-text="$wire.selectedItemIds.length === 0 ? '-- Semua Item / Barang --' : $wire.selectedItemIds.length + ' Item Terpilih'"></span>
                    <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-cloak x-transition class="absolute z-50 mt-1.5 w-72 rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-800 space-y-2.5 right-0 sm:right-auto">
                    <input type="text" x-model="search" placeholder="Cari nama item..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-1.5 text-[11px] px-1">
                        <button type="button" @click="selectAll()" class="text-brand-600 dark:text-brand-400 font-extrabold hover:underline">Pilih Semua</button>
                        <button type="button" @click="clearAll()" class="text-rose-500 font-extrabold hover:underline">Hapus Pilihan</button>
                    </div>

                    <div class="max-h-52 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                        @foreach($allItems as $itm)
                            <label x-show="search === '' || {{ json_encode(strtolower($itm->name)) }}.includes(search.toLowerCase())" class="flex items-center gap-2.5 rounded-xl px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/60 cursor-pointer text-xs transition">
                                <input type="checkbox" value="{{ $itm->id }}" wire:model.live.debounce.150ms="selectedItemIds" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:bg-gray-900 dark:border-gray-700">
                                <span class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $itm->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <script>
            window.warehouseProjectsList = @json($projectsJsonList ?? []);
            window.warehouseItemsList = @json($itemsJsonList ?? []);
            window.warehousePicList = @json($picSuggestionsList ?? []);

            window.formatUnitCode = function(unit) {
                if (!unit) return 'PCS';
                const u = String(unit).trim().toUpperCase();
                const map = {
                    'PASANG': 'PSG',
                    'PAIR': 'PSG',
                    'TABUNG': 'TBG',
                    'LEMBAR': 'LBR',
                    'BATANG': 'BTG',
                    'LUSIN': 'LSN',
                    'DOZEN': 'LSN',
                    'BOTOL': 'BTL',
                    'LITER': 'LTR',
                    'METER': 'MTR',
                    'CENTIMETER': 'CM',
                    'MILIMETER': 'MM',
                    'MILILITER': 'ML',
                    'KILOGRAM': 'KG',
                    'GRAM': 'GR',
                    'PIECES': 'PCS',
                    'PIECE': 'PCS',
                    'ROLL': 'ROL',
                    'PACK': 'PCK',
                    'PAIL': 'PAL',
                    'DRUM': 'DRM',
                    'BOX': 'BOX',
                    'DUS': 'BOX',
                    'SET': 'SET',
                    'CAN': 'CAN',
                };
                return map[u] || (u.length > 4 ? u.substring(0, 3) : u);
            };

            const registerProjectCombobox = () => {
                if (typeof Alpine !== 'undefined' && !Alpine.data('projectSearchCombobox')) {
                    Alpine.data('projectSearchCombobox', (initialVal = '', livewireKey = null) => ({
                        open: false,
                        search: '',
                        selected: initialVal || '',
                        livewireKey: livewireKey,
                        projects: window.warehouseProjectsList || [],
                        fixedTop: 0,
                        fixedLeft: 0,
                        fixedWidth: 320,

                        init() {
                            this.projects = window.warehouseProjectsList || [];

                            this.$watch('selected', (val) => {
                                const input = this.getInputEl();
                                if (input && input.value !== val) {
                                    input.value = val;
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                if (this.livewireKey === 'bulkRows.0.project') {
                                    window.dispatchEvent(new CustomEvent('first-project-updated', { detail: val || '' }));
                                }
                            });

                            if (this.livewireKey === 'bulkRows.0.project' && this.selected) {
                                window.dispatchEvent(new CustomEvent('first-project-updated', { detail: this.selected }));
                            }

                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$watch('$wire.' + this.livewireKey, (val) => {
                                    this.selected = val || '';
                                });
                            }

                            const onScroll = () => {
                                if (this.open) {
                                    this.updatePosition();
                                }
                            };
                            window.addEventListener('scroll', onScroll, { capture: true, passive: true });
                            window.addEventListener('resize', onScroll, { passive: true });
                        },

                        getTriggerEl() {
                            if (this.$refs.triggerWrap && this.$refs.triggerWrap.isConnected) {
                                return this.$refs.triggerWrap;
                            }
                            return this.$el.querySelector('[x-ref="triggerWrap"]') || this.$el;
                        },

                        getInputEl() {
                            if (this.$refs.actualInput && this.$refs.actualInput.isConnected) {
                                return this.$refs.actualInput;
                            }
                            return this.$el.querySelector('input[type="text"]') || this.$refs.actualInput;
                        },

                        get filteredProjects() {
                            const list = (this.projects && this.projects.length) ? this.projects : (window.warehouseProjectsList || []);
                            const q = (this.search !== '' ? this.search : (this.selected || '')).toLowerCase().trim();
                            if (!q) {
                                return list;
                            }
                            return list.filter(p => 
                                p.name.toLowerCase().includes(q) || 
                                (p.code && p.code.toLowerCase().includes(q)) ||
                                (p.client && p.client.toLowerCase().includes(q))
                            );
                        },

                        get hasExactMatch() {
                            const q = (this.selected || this.search || '').toLowerCase().trim();
                            if (!q) return true;
                            const list = (this.projects && this.projects.length) ? this.projects : (window.warehouseProjectsList || []);
                            return list.some(p => p.name.toLowerCase() === q);
                        },

                        onInput(e) {
                            this.selected = e.target.value;
                            this.search = e.target.value;
                            this.projects = window.warehouseProjectsList || [];
                            this.open = true;
                            this.updatePosition();
                            if (this.livewireKey === 'bulkRows.0.project') {
                                window.dispatchEvent(new CustomEvent('first-project-updated', { detail: this.selected }));
                            }
                        },

                        onFocus() {
                            this.search = this.selected || '';
                            this.projects = window.warehouseProjectsList || [];
                            this.open = true;
                            this.updatePosition();
                        },

                        toggle() {
                            if (this.open) {
                                this.close();
                            } else {
                                this.openDropdown();
                            }
                        },

                        openDropdown() {
                            this.open = true;
                            this.search = this.selected || '';
                            this.projects = window.warehouseProjectsList || [];
                            this.updatePosition();
                        },

                        updatePosition() {
                            const el = this.getTriggerEl();
                            if (!el || !el.isConnected) return;
                            const rect = el.getBoundingClientRect();
                            if (rect.width === 0 && rect.height === 0) return;
                            
                            this.fixedWidth = Math.max(rect.width, 320);
                            
                            let left = rect.left;
                            if (left + this.fixedWidth > window.innerWidth - 16) {
                                left = window.innerWidth - this.fixedWidth - 16;
                            }
                            this.fixedLeft = Math.max(16, left);

                            const dropdownHeight = 280;
                            const spaceBelow = window.innerHeight - rect.bottom;
                            if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                                this.fixedTop = Math.max(16, rect.top - dropdownHeight - 6);
                            } else {
                                this.fixedTop = Math.min(window.innerHeight - dropdownHeight - 16, rect.bottom + 4);
                            }
                        },

                        selectProject(name) {
                            this.selected = name;
                            this.search = name;
                            const input = this.getInputEl();
                            if (input) {
                                input.value = name;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$wire.set(this.livewireKey, name);
                            }
                            if (this.livewireKey === 'bulkRows.0.project') {
                                window.dispatchEvent(new CustomEvent('first-project-updated', { detail: name }));
                            }
                            this.close();
                        },

                        clearSelection() {
                            this.selected = '';
                            this.search = '';
                            const input = this.getInputEl();
                            if (input) {
                                input.value = '';
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$wire.set(this.livewireKey, '');
                            }
                            if (this.livewireKey === 'bulkRows.0.project') {
                                window.dispatchEvent(new CustomEvent('first-project-updated', { detail: '' }));
                            }
                            this.close();
                        },

                        close() {
                            this.open = false;
                        }
                    }));
                }
            };

            if (window.Alpine) {
                registerProjectCombobox();
            } else {
                document.addEventListener('alpine:init', registerProjectCombobox);
            }

            const registerPicAutocomplete = () => {
                if (typeof Alpine !== 'undefined' && !Alpine.data('picAutocomplete')) {
                    Alpine.data('picAutocomplete', (initialVal = '', livewireKey = null, isFirstRow = false) => ({
                        open: false,
                        value: initialVal || '',
                        livewireKey: livewireKey,
                        isFirstRow: isFirstRow,
                        focusedIndex: -1,
                        fixedTop: 0,
                        fixedLeft: 0,
                        fixedWidth: 220,

                        init() {
                            this.$watch('value', (val) => {
                                const input = this.getInputEl();
                                if (input && input.value !== val) {
                                    input.value = val;
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                if (this.isFirstRow) {
                                    window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: val || '' }));
                                }
                            });

                            if (this.isFirstRow && this.value) {
                                window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: this.value }));
                            }

                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$watch('$wire.' + this.livewireKey, (val) => {
                                    this.value = val || '';
                                });
                            }

                            const onScroll = () => {
                                if (this.open) {
                                    this.updatePosition();
                                }
                            };
                            window.addEventListener('scroll', onScroll, { capture: true, passive: true });
                            window.addEventListener('resize', onScroll, { passive: true });
                        },

                        getTriggerEl() {
                            if (this.$refs.triggerWrap && this.$refs.triggerWrap.isConnected) {
                                return this.$refs.triggerWrap;
                            }
                            return this.$el.querySelector('[x-ref="triggerWrap"]') || this.$el;
                        },

                        getInputEl() {
                            if (this.$refs.picInput && this.$refs.picInput.isConnected) {
                                return this.$refs.picInput;
                            }
                            return this.$el.querySelector('input') || this.$refs.picInput;
                        },

                        get suggestions() {
                            return window.warehousePicList || [];
                        },

                        get filteredSuggestions() {
                            const q = (this.value || '').toLowerCase().trim();
                            if (!q) return [];
                            const list = this.suggestions;
                            const startsWith = [];
                            const contains = [];
                            for (let i = 0; i < list.length; i++) {
                                const name = list[i];
                                const lower = name.toLowerCase();
                                if (lower === q) continue;
                                if (lower.startsWith(q)) {
                                    startsWith.push(name);
                                } else if (lower.includes(q)) {
                                    contains.push(name);
                                }
                            }
                            return [...startsWith, ...contains].slice(0, 8);
                        },

                        updatePosition() {
                            const el = this.getTriggerEl();
                            if (!el || !el.isConnected) return;
                            const rect = el.getBoundingClientRect();
                            if (rect.width === 0 && rect.height === 0) return;
                            
                            this.fixedWidth = Math.max(rect.width, 220);
                            
                            let left = rect.left;
                            if (left + this.fixedWidth > window.innerWidth - 16) {
                                left = window.innerWidth - this.fixedWidth - 16;
                            }
                            this.fixedLeft = Math.max(16, left);

                            const dropdownHeight = 220;
                            const spaceBelow = window.innerHeight - rect.bottom;
                            if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                                this.fixedTop = Math.max(16, rect.top - dropdownHeight - 4);
                            } else {
                                this.fixedTop = Math.min(window.innerHeight - dropdownHeight - 16, rect.bottom + 4);
                            }
                        },

                        onInput(e) {
                            this.value = e.target.value;
                            this.focusedIndex = -1;
                            if (this.filteredSuggestions.length > 0) {
                                this.open = true;
                                this.updatePosition();
                            } else {
                                this.open = false;
                            }
                            if (this.isFirstRow) {
                                window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: this.value }));
                            }
                        },

                        onFocus() {
                            if (this.filteredSuggestions.length > 0) {
                                this.open = true;
                                this.updatePosition();
                            }
                        },

                        selectPic(name) {
                            this.value = name;
                            const input = this.getInputEl();
                            if (input) {
                                input.value = name;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$wire.set(this.livewireKey, name);
                            }
                            this.close();
                            if (this.isFirstRow) {
                                window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: name }));
                            }
                        },

                        clearPic() {
                            this.value = '';
                            const input = this.getInputEl();
                            if (input) {
                                input.value = '';
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$wire.set(this.livewireKey, '');
                            }
                            this.close();
                            if (this.isFirstRow) {
                                window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: '' }));
                            }
                        },

                        close() {
                            this.open = false;
                            this.focusedIndex = -1;
                        },

                        onKeyDown(e) {
                            const list = this.filteredSuggestions;
                            if (!this.open && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                                if (list.length > 0) {
                                    this.open = true;
                                    this.updatePosition();
                                    e.preventDefault();
                                    return;
                                }
                            }
                            if (!this.open) return;

                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                this.focusedIndex = (this.focusedIndex + 1) % list.length;
                            } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                this.focusedIndex = (this.focusedIndex - 1 + list.length) % list.length;
                            } else if (e.key === 'Enter') {
                                if (this.focusedIndex >= 0 && this.focusedIndex < list.length) {
                                    e.preventDefault();
                                    this.selectPic(list[this.focusedIndex]);
                                }
                            } else if (e.key === 'Tab') {
                                if (this.focusedIndex >= 0 && this.focusedIndex < list.length) {
                                    e.preventDefault();
                                    this.selectPic(list[this.focusedIndex]);
                                } else if (list.length > 0) {
                                    e.preventDefault();
                                    this.selectPic(list[0]);
                                }
                            } else if (e.key === 'Escape') {
                                this.close();
                            }
                        }
                    }));
                }
            };

            if (window.Alpine) {
                registerPicAutocomplete();
            } else {
                document.addEventListener('alpine:init', registerPicAutocomplete);
            }

            const registerItemCombobox = () => {
                if (typeof Alpine !== 'undefined' && !Alpine.data('itemSearchCombobox')) {
                    Alpine.data('itemSearchCombobox', (initialId = '', livewireKey = null, rowIndex = null) => ({
                        open: false,
                        search: '',
                        selectedId: initialId ? String(initialId) : '',
                        livewireKey: livewireKey,
                        rowIndex: rowIndex,
                        items: window.warehouseItemsList || [],
                        fixedTop: 0,
                        fixedLeft: 0,
                        fixedWidth: 380,

                        init() {
                            this.items = window.warehouseItemsList || [];

                            this.$watch('selectedId', (val) => {
                                const input = this.getHiddenInputEl();
                                if (input && input.value !== val) {
                                    input.value = val;
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            });

                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$watch('$wire.' + this.livewireKey, (val) => {
                                    this.selectedId = val ? String(val) : '';
                                });
                            }

                            const onScroll = () => {
                                if (this.open) {
                                    this.updatePosition();
                                }
                            };
                            window.addEventListener('scroll', onScroll, { capture: true, passive: true });
                            window.addEventListener('resize', onScroll, { passive: true });
                        },

                        getTriggerEl() {
                            if (this.$refs.triggerWrap && this.$refs.triggerWrap.isConnected) {
                                return this.$refs.triggerWrap;
                            }
                            return this.$el.querySelector('[x-ref="triggerWrap"]') || this.$el;
                        },

                        getHiddenInputEl() {
                            if (this.$refs.hiddenInput && this.$refs.hiddenInput.isConnected) {
                                return this.$refs.hiddenInput;
                            }
                            return this.$el.querySelector('input[type="hidden"]') || this.$refs.hiddenInput;
                        },

                        get selectedItem() {
                            if (!this.selectedId) return null;
                            const items = (this.items && this.items.length) ? this.items : (window.warehouseItemsList || []);
                            return items.find(i => String(i.id) === String(this.selectedId)) || null;
                        },

                        get filteredItems() {
                            const items = (this.items && this.items.length) ? this.items : (window.warehouseItemsList || []);
                            if (!this.search || !this.search.trim()) {
                                return items;
                            }
                            const q = this.search.toLowerCase().trim();
                            return items.filter(i => 
                                (i.name && i.name.toLowerCase().includes(q)) || 
                                (i.code && i.code.toLowerCase().includes(q)) ||
                                (i.category && i.category.toLowerCase().includes(q)) ||
                                (i.dimension && i.dimension.toLowerCase().includes(q)) ||
                                (i.parent_name && i.parent_name.toLowerCase().includes(q)) ||
                                (i.heat_number && i.heat_number.toLowerCase().includes(q))
                            );
                        },

                        toggle() {
                            if (this.open) {
                                this.close();
                            } else {
                                this.openDropdown();
                            }
                        },

                        openDropdown() {
                            this.open = true;
                            this.search = '';
                            this.items = window.warehouseItemsList || [];
                            this.updatePosition();
                            this.$nextTick(() => {
                                if (this.$refs.searchField) {
                                    this.$refs.searchField.focus();
                                }
                            });
                        },

                        updatePosition() {
                            const el = this.getTriggerEl();
                            if (!el || !el.isConnected) return;
                            const rect = el.getBoundingClientRect();
                            if (rect.width === 0 && rect.height === 0) return;
                            
                            this.fixedWidth = Math.max(rect.width, 380);
                            
                            let left = rect.left;
                            if (left + this.fixedWidth > window.innerWidth - 16) {
                                left = window.innerWidth - this.fixedWidth - 16;
                            }
                            this.fixedLeft = Math.max(16, left);

                            const dropdownHeight = 320;
                            const spaceBelow = window.innerHeight - rect.bottom;
                            if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                                this.fixedTop = Math.max(16, rect.top - dropdownHeight - 6);
                            } else {
                                this.fixedTop = Math.min(window.innerHeight - dropdownHeight - 16, rect.bottom + 4);
                            }
                        },

                        selectItem(item) {
                            this.selectedId = String(item.id);
                            const input = this.getHiddenInputEl();
                            if (input) {
                                input.value = this.selectedId;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.rowIndex !== null) {
                                window.dispatchEvent(new CustomEvent('item-unit-updated-' + this.rowIndex, { detail: { unit: item.unit || 'PCS' } }));
                            }
                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$wire.set(this.livewireKey, item.id);
                            }
                            this.close();
                        },

                        clearSelection() {
                            this.selectedId = '';
                            this.search = '';
                            const input = this.getHiddenInputEl();
                            if (input) {
                                input.value = '';
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.rowIndex !== null) {
                                window.dispatchEvent(new CustomEvent('item-unit-updated-' + this.rowIndex, { detail: { unit: 'PCS' } }));
                            }
                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$wire.set(this.livewireKey, '');
                            }
                            this.close();
                        },

                        close() {
                            this.open = false;
                        }
                    }));
                }
            };

            if (window.Alpine) {
                registerItemCombobox();
            } else {
                document.addEventListener('alpine:init', registerItemCombobox);
            }

            const registerStickKgCalculator = () => {
                if (typeof Alpine !== 'undefined' && !Alpine.data('stickKgCalculator')) {
                    Alpine.data('stickKgCalculator', () => ({
                        isOpen: false,
                        targetMode: 'general',
                        targetRowIndex: 0,
                        calcMethod: 'gram',
                        stickCount: '',
                        gramPerStick: 25,
                        stickPerKg: 40,
                        copied: false,
                        targetItemName: '',

                        init() {
                            window.addEventListener('open-stick-calculator', (e) => {
                                const detail = e.detail || {};
                                const hasBulkRows = document.querySelectorAll('[wire\\:key^="bulk-row-"]').length > 0;
                                let mode = detail.mode || 'general';
                                if (mode === 'bulk' && !hasBulkRows) {
                                    mode = 'general';
                                }
                                this.targetMode = mode;
                                this.targetRowIndex = (detail.rowIndex !== undefined && detail.rowIndex !== null) ? Number(detail.rowIndex) : 0;
                                this.isOpen = true;
                                this.copied = false;
                                this.updateTargetItemName();
                                this.$nextTick(() => {
                                    if (this.$refs.stickCountInput) {
                                        this.$refs.stickCountInput.focus();
                                        this.$refs.stickCountInput.select();
                                    }
                                });
                            });
                        },

                        updateTargetItemName() {
                            if (this.targetMode === 'single') {
                                this.targetItemName = 'Input 1 Barang';
                                return;
                            }
                            if (this.targetMode === 'general' || this.targetMode === 'standalone') {
                                this.targetItemName = 'Kalkulator Bebas';
                                return;
                            }
                            const items = window.warehouseItemsList || [];
                            const rowWrap = document.querySelector(`[wire\\:key="bulk-row-${this.targetRowIndex}"]`);
                            if (rowWrap) {
                                const hiddenInput = rowWrap.querySelector('input[type="hidden"]');
                                if (hiddenInput && hiddenInput.value) {
                                    const found = items.find(i => String(i.id) === String(hiddenInput.value));
                                    if (found) {
                                        this.targetItemName = found.name;
                                        return;
                                    }
                                }
                            }
                            this.targetItemName = `Baris #${this.targetRowIndex + 1}`;
                        },

                        setTargetRow(idx) {
                            this.targetRowIndex = Number(idx);
                            this.targetMode = 'bulk';
                            this.updateTargetItemName();
                        },

                        addStick(n) {
                            const cur = parseFloat(this.stickCount) || 0;
                            this.stickCount = Math.max(0, cur + Number(n));
                        },

                        resetStick() {
                            this.stickCount = '';
                        },

                        setPreset(gram) {
                            this.calcMethod = 'gram';
                            this.gramPerStick = Number(gram);
                            if (this.gramPerStick > 0) {
                                this.stickPerKg = parseFloat((1000 / this.gramPerStick).toFixed(1));
                            }
                        },

                        setStickPerKgPreset(spk) {
                            this.calcMethod = 'per_kg';
                            this.stickPerKg = Number(spk);
                            if (this.stickPerKg > 0) {
                                this.gramPerStick = parseFloat((1000 / this.stickPerKg).toFixed(1));
                            }
                        },

                        onGramInput(val) {
                            const num = parseFloat(String(val).replace(',', '.'));
                            if (!isNaN(num) && num > 0) {
                                this.stickPerKg = parseFloat((1000 / num).toFixed(1));
                            }
                        },

                        onStickPerKgInput(val) {
                            const num = parseFloat(String(val).replace(',', '.'));
                            if (!isNaN(num) && num > 0) {
                                this.gramPerStick = parseFloat((1000 / num).toFixed(1));
                            }
                        },

                        get totalGrams() {
                            const count = parseFloat(String(this.stickCount).replace(',', '.')) || 0;
                            if (count <= 0) return 0;
                            if (this.calcMethod === 'per_kg') {
                                const spk = parseFloat(String(this.stickPerKg).replace(',', '.')) || 0;
                                if (spk <= 0) return 0;
                                return (count / spk) * 1000;
                            } else {
                                const g = parseFloat(String(this.gramPerStick).replace(',', '.')) || 0;
                                return count * g;
                            }
                        },

                        get calculatedKg() {
                            const count = parseFloat(String(this.stickCount).replace(',', '.')) || 0;
                            if (count <= 0) return 0;
                            if (this.calcMethod === 'per_kg') {
                                const spk = parseFloat(String(this.stickPerKg).replace(',', '.')) || 0;
                                if (spk <= 0) return 0;
                                return count / spk;
                            } else {
                                const g = parseFloat(String(this.gramPerStick).replace(',', '.')) || 0;
                                return (count * g) / 1000;
                            }
                        },

                        get formattedKg() {
                            const kg = this.calculatedKg;
                            if (kg <= 0) return '0';
                            return parseFloat(kg.toFixed(4)).toString();
                        },

                        get formulaText() {
                            const count = parseFloat(String(this.stickCount).replace(',', '.')) || 0;
                            if (count <= 0) return 'Masukkan jumlah stick untuk melihat kalkulasi';
                            if (this.calcMethod === 'per_kg') {
                                const spk = parseFloat(String(this.stickPerKg).replace(',', '.')) || 0;
                                return `${count} stick ÷ ${spk} stick/kg = ${this.formattedKg} KG`;
                            } else {
                                const g = parseFloat(String(this.gramPerStick).replace(',', '.')) || 0;
                                const totG = (count * g);
                                return `${count} stick × ${g} g = ${totG} g ÷ 1.000 = ${this.formattedKg} KG`;
                            }
                        },

                        applyResult() {
                            const val = this.formattedKg;
                            if (parseFloat(val) <= 0) return;

                            if (this.targetMode === 'general' || this.targetMode === 'standalone') {
                                this.copyResult();
                                return;
                            }

                            if (this.targetMode === 'single') {
                                if (this.$wire) {
                                    this.$wire.applyCalculatedQuantity(val, null, 'KG');
                                }
                                const singleInput = document.querySelector('input[wire\\:model="quantity"]');
                                if (singleInput) {
                                    singleInput.value = val;
                                    singleInput.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                this.isOpen = false;
                            } else {
                                const idx = this.targetRowIndex;
                                if (this.$wire) {
                                    this.$wire.applyCalculatedQuantity(val, idx, 'KG');
                                }
                                const bulkInput = document.querySelector(`input[wire\\:model="bulkRows.${idx}.quantity"]`);
                                if (bulkInput) {
                                    bulkInput.value = val;
                                    bulkInput.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                window.dispatchEvent(new CustomEvent('item-unit-updated-' + idx, { detail: { unit: 'KG' } }));
                                this.isOpen = false;
                            }
                        },

                        copyResult() {
                            const val = this.formattedKg;
                            if (navigator.clipboard) {
                                navigator.clipboard.writeText(val);
                                this.copied = true;
                                setTimeout(() => { this.copied = false; }, 2000);
                            }
                        },

                        close() {
                            this.isOpen = false;
                        }
                    }));
                }
            };

            if (window.Alpine) {
                registerStickKgCalculator();
            } else {
                document.addEventListener('alpine:init', registerStickKgCalculator);
            }

            document.addEventListener('alpine:init', () => {
                Alpine.data('materialOutItemFilter', () => ({
                    open: false,
                    search: '',
                    items: @json($allItems->map(fn($i) => (string)$i->id)->values()),

                    toggle() {
                        this.open = !this.open;
                    },
                    close() {
                        this.open = false;
                    },
                    selectAll() {
                        this.$wire.selectedItemIds = this.items;
                    },
                    clearAll() {
                        this.$wire.selectedItemIds = [];
                    }
                }));
            });
            </script>

            <!-- Filter Nama Project -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Nama Project</label>
                <select wire:model.live.debounce.150ms="selectedProject" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                    <option value="">-- Semua Project --</option>
                    @foreach($projectsList as $prjName)
                        <option value="{{ $prjName }}">{{ $prjName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Satuan Barang -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Satuan</label>
                <select wire:model.live.debounce.150ms="selectedUnit" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                    <option value="">-- Semua Satuan --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->symbol }} ({{ $u->name }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Quick Filter Preset Buttons -->
            <div class="flex items-center gap-2">
                <button type="button" wire:click="setFilterLastMonth" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-gray-100 px-2.5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer" title="Set Rentang Tanggal 1 Bulan Terakhir">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>1 Bulan Terakhir</span>
                </button>
                <button type="button" wire:click="resetFilters" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-gray-100 px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer" title="Reset Semua Filter">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Active Filter Summary Banner & Metrics -->
    @if($dateFrom || $dateTo || !empty($selectedItemIds) || $selectedItemId || $selectedProject || $selectedUnit || $search || $activeModule !== 'all')
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl bg-brand-50 p-4 text-xs text-brand-900 dark:bg-brand-950/30 dark:text-brand-200 border border-brand-200 dark:border-brand-800/60 shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white font-bold">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
                <div>
                    <span class="font-bold text-gray-900 dark:text-white block">Hasil Filter Data Material Keluar:</span>
                    <span class="text-gray-600 dark:text-gray-300">
                        Modul: <strong>{{ $activeModule === 'all' ? 'Semua Kategori' : $activeModule }}</strong>
                        • Periode: <strong>{{ $dateFrom ? format_date($dateFrom) : 'Awal' }}</strong> s/d <strong>{{ $dateTo ? format_date($dateTo) : 'Terbaru' }}</strong>
                        @if(!empty($selectedItemIds))
                            • Item Terpilih: <strong>{{ count($selectedItemIds) }} Barang</strong>
                        @elseif($selectedItemId)
                            • Item: <strong>{{ \App\Models\Item::find($selectedItemId)?->name }}</strong>
                        @endif
                        @if($selectedProject)
                            • Project: <strong>{{ $selectedProject }}</strong>
                        @endif
                        @if($selectedUnit)
                            @php
                                $uObj = $units->firstWhere('id', $selectedUnit);
                            @endphp
                            • Satuan: <strong>{{ $uObj ? $uObj->symbol : $selectedUnit }}</strong>
                        @endif
                        @if($search)
                            • Kata Kunci: <strong>"{{ $search }}"</strong>
                        @endif
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-4 border-t border-brand-200 dark:border-brand-800/40 pt-2 sm:pt-0 sm:border-t-0 font-extrabold text-xs">
                <div class="text-right">
                    <span class="text-gray-500 dark:text-gray-400 block text-[10px] uppercase">Transaksi Terfilter</span>
                    <span class="text-sm font-black text-gray-900 dark:text-white">{{ number_format($filteredTotalRecords) }} Record</span>
                </div>
                <div class="h-7 w-px bg-brand-200 dark:bg-brand-800/60"></div>
                <div class="text-right">
                    <span class="text-rose-600 dark:text-rose-400 block text-[10px] uppercase">Total Jumlah Barang</span>
                    <span class="text-sm font-black text-rose-600 dark:text-rose-400 font-mono">-{{ format_quantity($filteredTotalQuantity) }} Unit</span>
                </div>
                <button type="button" wire:click="resetFilters" class="ml-2 rounded-xl bg-gray-200/80 px-2.5 py-1 text-[11px] font-bold text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Table Container matching user specification -->
    <div class="relative rounded-2xl border border-gray-100 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Livewire Loading Overlay -->
        <div wire:loading.flex wire:target="search, dateFrom, dateTo, selectedItemId, selectedItemIds, selectedProject, selectedUnit, activeModule, resetFilters, setFilterLastMonth, setFilterLast7Days, gotoPage, nextPage, previousPage" class="absolute inset-0 z-20 items-center justify-center bg-white/70 backdrop-blur-[1px] dark:bg-gray-900/70">
            <div class="inline-flex items-center gap-2.5 rounded-xl border border-gray-200 bg-white px-4 py-2.5 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <svg class="h-4 w-4 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Memuat data transaksi...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="theme-table-header text-white font-extrabold uppercase border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center w-12">NO</th>
                        <th scope="col" class="px-4 py-3.5">TANGGAL</th>
                        <th scope="col" class="px-4 py-3.5">KATEGORI</th>
                        <th scope="col" class="px-4 py-3.5">KODE MATERIAL</th>
                        <th scope="col" class="px-4 py-3.5">NAMA BARANG</th>
                        <th scope="col" class="px-4 py-3.5 text-center">JUMLAH KELUAR</th>
                        <th scope="col" class="px-4 py-3.5">PROJECT</th>
                        <th scope="col" class="px-4 py-3.5">PIC</th>
                        @if(!$isPpic)
                            <th scope="col" class="px-4 py-3.5 text-center">AKSI</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($materialOuts as $index => $row)
                        <tr class="hover:bg-gray-50/80 transition dark:hover:bg-gray-800/50">
                            <!-- NO -->
                            <td class="px-4 py-3.5 text-center font-semibold text-gray-500 dark:text-gray-400">
                                {{ $materialOuts->firstItem() + $index }}
                            </td>
                            <!-- TANGGAL -->
                            <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                {{ format_date($row->date) }}
                            </td>
                            <!-- KATEGORI -->
                            <td class="text-center px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $catName = $row->item->category->name ?? '-';
                                    $isCat = $row->item && $row->item->isCat();
                                    $isRaw = ($row->item && $row->item->isRawMaterial()) || str_contains($catName, 'Raw Material');
                                    $isGas = str_contains($catName, 'Gas');
                                @endphp
                                @if($isCat)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2.5 py-0.5 text-[10px] font-extrabold text-brand-800 dark:bg-brand-950/60 dark:text-brand-300">
                                        {{ $catName }}
                                    </span>
                                @elseif($isRaw)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-extrabold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                        {{ $catName }}
                                    </span>
                                @elseif($isGas)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-[10px] font-extrabold text-sky-800 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60">
                                        {{ $catName }}
                                    </span>
                                @elseif(str_contains($catName, 'Secondary'))
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">
                                        {{ $catName }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] font-extrabold text-gray-800 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                        {{ $catName }}
                                    </span>
                                @endif
                            </td>
                            <!-- KODE MATERIAL -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $row->item->code_item ?? '-' }}
                                </span>
                            </td>
                            <!-- NAMA BARANG -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col">
                                    @if($row->item && $row->item->is_offcut)
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $row->item->name }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-700">OFF-CUT</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 flex-wrap">
                                            @if($row->item->dimension)
                                                <span class="font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-1 py-0.2 rounded border border-amber-200 dark:border-amber-800/50">
                                                    {{ $row->item->dimension }}
                                                </span>
                                            @endif
                                            @if($row->item->parentItem)
                                                <span>Induk: {{ $row->item->parentItem->name }}</span>
                                            @endif
                                            @if($row->item->heat_number)
                                                <span class="font-mono">Heat: {{ $row->item->heat_number }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $row->item->name ?? '-' }}</span>
                                        @if($row->item && $row->item->isCat())
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                Tgl Pembuatan: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ format_date($row->item->manufacturing_date, 'Belum diisi') }}</span>
                                            </span>
                                        @elseif($isRaw)
                                            <span class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 font-semibold">
                                                Lokasi: STORAGE 2
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <!-- JUMLAH KELUAR -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="font-extrabold text-rose-600 dark:text-rose-400 text-sm font-mono">
                                    -{{ format_unit_quantity($row->quantity, $row->unit) }} {{ $row->unit }}
                                </span>
                                @if($row->wholesale_qty && $row->wholesale_unit)
                                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-extrabold">
                                        ({{ format_unit_quantity($row->wholesale_qty, $row->wholesale_unit) }} {{ $row->wholesale_unit }})
                                    </span>
                                @endif
                            </td>
                            <!-- PROJECT -->
                            <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                                {{ $row->project }}
                            </td>
                            <!-- PIC -->
                            <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $row->pic }}
                                </span>
                            </td>
                            <!-- AKSI -->
                            @if(!$isPpic)
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" wire:click="openEditModal({{ $row->id }})" class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-brand-400 transition cursor-pointer" title="Edit Catatan">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" @click="confirmDelete('Apakah Anda yakin ingin menghapus data pengeluaran material ini? Stok akan dikembalikan.', () => $wire.delete({{ $row->id }}))" class="rounded-lg p-1.5 text-gray-500 hover:bg-rose-50 hover:text-rose-600 dark:text-gray-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 transition cursor-pointer" title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        @php
                            $hasActiveFilters = !empty($search) || !empty($dateFrom) || !empty($dateTo) || !empty($selectedItemId) || !empty($selectedItemIds) || !empty($selectedProject) || !empty($selectedUnit);
                        @endphp
                        <tr>
                            <td colspan="{{ $isPpic ? 7 : 8 }}" class="py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center justify-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3.5">
                                        @if($hasActiveFilters)
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @else
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        @endif
                                    </div>
                                    @if($hasActiveFilters)
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada transaksi yang cocok</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tidak ada data material keluar yang memenuhi kriteria pencarian atau filter yang dipilih.</p>
                                        <button type="button" wire:click="resetFilters" class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                                            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Reset Filter
                                        </button>
                                    @else
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Belum ada catatan material keluar</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Belum ada transaksi pengeluaran material yang tercatat dalam sistem pada modul ini.</p>
                                        @if(!$isPpic)
                                            <button type="button" wire:click="openCreateModal" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-700 cursor-pointer transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Catat Material Keluar
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($materialOuts->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $materialOuts->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Catat / Edit Material Keluar -->
    @if ($isModalOpen && !$isPpic)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn"
             x-init="window.warehouseItemsList = @js($itemsJsonList ?? []); window.warehouseProjectsList = @js($projectsJsonList ?? []);">
            <div class="w-full {{ ($activeTab === 'bulk' && !$isEditMode) ? 'max-w-4xl' : 'max-w-xl' }} rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $isEditMode ? 'Edit Data Pengeluaran Material' : 'Catat Pengeluaran Material' }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pencatatan langsung & otomatis memotong stok barang.</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if(!$isEditMode)
                    <!-- Segmented Tab Switcher -->
                    <div class="mt-4 mb-2 flex items-center p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
                        <button type="button" wire:click="$set('activeTab', 'bulk')" class="px-4 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer {{ $activeTab === 'bulk' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                            Input Banyak
                        </button>
                        <button type="button" wire:click="$set('activeTab', 'single')" class="px-4 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer {{ $activeTab === 'single' ? 'bg-white text-gray-900 shadow-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                            Input 1 Barang
                        </button>
                    </div>
                @endif

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <!-- Shared Context Fields -->
                    <div class="grid grid-cols-1 {{ ($activeTab === 'single' || $isEditMode) ? 'sm:grid-cols-3' : 'sm:grid-cols-1 max-w-xs' }} gap-3">
                        <!-- Tanggal Keluar -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Keluar <span class="text-rose-500">*</span></label>
                            <div wire:ignore class="relative" x-data="{
                                fp: null,
                                init() {
                                    const initPicker = () => {
                                        if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                        this.fp = flatpickr(this.$refs.picker, {
                                            dateFormat: 'Y-m-d',
                                            altInput: true,
                                            altFormat: 'd/m/Y',
                                            altInputClass: 'w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition',
                                            defaultDate: @js($date),
                                            onChange: (dates, dateStr) => {
                                                $wire.set('date', dateStr, false);
                                                const hidden = this.$refs.hiddenDate;
                                                if (hidden) {
                                                    hidden.value = dateStr;
                                                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                                                }
                                            }
                                        });
                                    };
                                    this.$nextTick(() => initPicker());
                                    this.$watch('$wire.date', val => {
                                        if (this.fp && val !== this.fp.input.value) {
                                            this.fp.setDate(val, false);
                                        }
                                    });
                                },
                                destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                            }">
                                <input x-ref="picker" type="text" class="hidden" value="{{ $date }}">
                                <input x-ref="hiddenDate" type="hidden" wire:model="date" value="{{ $date }}">
                            </div>
                            @error('date') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($activeTab === 'single' || $isEditMode)
                            <!-- Project / Keperluan -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Project / Keperluan <span class="text-rose-500">*</span></label>
                                <div class="relative" 
                                     x-data="projectSearchCombobox(@js($project ?? ''), 'project')"
                                     @scroll.window="if(open) updatePosition()"
                                     @resize.window="if(open) updatePosition()">
                                    
                                    <div x-ref="triggerWrap" class="relative flex items-center">
                                        <input type="text" 
                                               x-ref="actualInput"
                                               wire:model="project" 
                                               x-model="selected"
                                               @input="onInput($event)"
                                               @focus="onFocus()"
                                               placeholder="Ketik nama atau pilih project..." 
                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-3 pr-14 py-2 text-xs font-semibold text-gray-800 dark:text-white outline-none focus:border-brand-500 shadow-2xs">
                                        
                                        <div class="absolute right-2 flex items-center gap-1">
                                            <button type="button" 
                                                    x-show="selected && selected.length > 0" 
                                                    @click.stop="clearSelection()" 
                                                    class="p-0.5 text-gray-400 hover:text-rose-500 rounded text-xs transition cursor-pointer"
                                                    title="Hapus pilihan">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            <button type="button" 
                                                    @click.stop="toggle()" 
                                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition cursor-pointer"
                                                    title="Buka daftar project">
                                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Teleported Floating Dropdown Menu -->
                                    <div wire:ignore>
                                        <template x-teleport="body">
                                            <div x-show="open" 
                                                 x-cloak 
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                 :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                 class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                                
                                                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                    <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                        <span>Daftar / Rekomendasi Project</span>
                                                    </span>
                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredProjects.length + ' Project'"></span>
                                                </div>

                                                <div x-show="selected && selected.trim().length > 0 && !hasExactMatch">
                                                    <div class="px-2.5 py-1.5 text-[11px] text-brand-900 dark:text-brand-200 bg-brand-50 dark:bg-brand-950/50 rounded-xl border border-brand-200 dark:border-brand-800/60 flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-1.5 min-w-0">
                                                            <span class="text-brand-600 font-bold text-xs">✓</span>
                                                            <span class="truncate">Gunakan: <strong class="underline" x-text="selected.trim()"></strong></span>
                                                        </div>
                                                        <span class="text-[9px] font-bold uppercase bg-brand-600 text-white px-1.5 py-0.5 rounded shrink-0">Custom</span>
                                                    </div>
                                                </div>

                                                <div class="max-h-56 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                    <template x-for="p in filteredProjects" :key="p.name">
                                                        <div @click="selectProject(p.name)"
                                                             class="p-2 rounded-xl hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-brand-200 dark:hover:border-brand-800"
                                                             :class="selected === p.name ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                            <div class="min-w-0 flex-1">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="p.name"></span>
                                                                </div>
                                                                <div class="flex items-center gap-2 mt-0.5 text-[10px] text-gray-400">
                                                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.2 rounded" x-show="p.code && p.code !== '-'" x-text="p.code"></span>
                                                                    <span x-show="p.client" class="truncate" x-text="'• ' + p.client"></span>
                                                                    <span x-show="p.status && p.status !== 'Historical'" class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase"
                                                                          :class="p.status === 'Completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'"
                                                                          x-text="p.status"></span>
                                                                </div>
                                                            </div>
                                                            <div x-show="selected === p.name" class="text-brand-600 dark:text-brand-400 font-extrabold text-sm shrink-0">
                                                                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <div x-show="filteredProjects.length === 0" class="py-3 px-2 text-center text-xs text-gray-400">
                                                        <span x-show="selected && selected.trim().length > 0">Tidak ada project yang cocok di master. Teks Anda akan disimpan sebagai keperluan / project baru.</span>
                                                        <span x-show="!selected || selected.trim().length === 0">Belum ada daftar project terdaftar. Silakan ketik nama project / keperluan langsung di kolom.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                @error('project') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- PIC Penerima -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">PIC Penerima <span class="text-rose-500">*</span></label>
                                <div class="relative" 
                                     x-data="picAutocomplete(@js($pic ?? ''), 'pic', false)"
                                     @scroll.window="if(open) updatePosition()"
                                     @resize.window="if(open) updatePosition()">
                                    
                                    <div x-ref="triggerWrap" class="relative flex items-center">
                                        <input type="text" 
                                               x-ref="picInput"
                                               wire:model="pic" 
                                               x-model="value"
                                               @input="onInput($event)"
                                               @focus="onFocus()"
                                               @keydown="onKeyDown($event)"
                                               placeholder="Nama penerima..." 
                                               class="w-full rounded-xl border border-gray-200 bg-white pl-3 pr-8 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-medium">
                                        
                                        <div class="absolute right-2 flex items-center gap-0.5">
                                            <button type="button" 
                                                    x-show="value && value.length > 0" 
                                                    @click.stop="clearPic()" 
                                                    class="p-1 text-gray-400 hover:text-rose-500 rounded transition cursor-pointer"
                                                    title="Hapus nama PIC">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Floating Recommendations Dropdown -->
                                    <div wire:ignore>
                                        <template x-teleport="body">
                                            <div x-show="open" 
                                                 x-cloak 
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-100"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                 :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                 class="rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-900 space-y-1.5">
                                                
                                                <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                    <span class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 text-[11px]">
                                                        <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                        Saran PIC
                                                    </span>
                                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredSuggestions.length + ' Pilihan'"></span>
                                                </div>

                                                <div class="max-h-48 overflow-y-auto space-y-0.5 pr-0.5 custom-scrollbar">
                                                    <template x-for="(name, sIndex) in filteredSuggestions" :key="name">
                                                        <div @click="selectPic(name)"
                                                             class="px-2 py-1.5 rounded-lg hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent"
                                                             :class="focusedIndex === sIndex ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                <div class="w-5 h-5 rounded-full bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold text-[10px] shrink-0"
                                                                     x-text="name.charAt(0).toUpperCase()"></div>
                                                                <span class="font-medium text-xs text-gray-800 dark:text-white truncate" x-text="name"></span>
                                                            </div>
                                                            <span class="text-[9px] text-gray-400 shrink-0 font-mono">Pilih</span>
                                                        </div>
                                                    </template>
                                                </div>

                                                <div class="pt-1 border-t border-gray-100 dark:border-gray-800 text-[9px] text-gray-400 text-center">
                                                    Tekan <kbd class="px-1 py-0.2 bg-gray-100 dark:bg-gray-800 rounded font-mono">Tab</kbd> atau <kbd class="px-1 py-0.2 bg-gray-100 dark:bg-gray-800 rounded font-mono">Enter</kbd> untuk memilih
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                @error('pic') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    @if(!$isEditMode && $activeTab === 'bulk')
                        <!-- Bulk Multi-Row Table Grid -->
                        <div class="pt-2"
                             x-data="{ 
                                 firstProject: @js($bulkRows[0]['project'] ?? ''), 
                                 firstPic: @js($bulkRows[0]['pic'] ?? '') 
                             }"
                             @first-project-updated.window="firstProject = $event.detail"
                             @first-pic-updated.window="firstPic = $event.detail"
                             @bulk-projects-applied.window="firstProject = $event.detail.project"
                             @bulk-pics-applied.window="firstPic = $event.detail.pic">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>Daftar Barang yang Dikeluarkan</span>
                                    <span class="text-[10px] text-gray-400 font-normal">({{ count($bulkRows) }} baris)</span>
                                </h4>
                                <div class="flex flex-wrap items-center gap-2">
                                    <!-- Kalkulator Stick / Gram ke Kg -->
                                    <button type="button" 
                                            @click="$dispatch('open-stick-calculator', { mode: 'bulk', rowIndex: 0 })" 
                                            class="text-[11px] font-bold text-amber-800 dark:text-amber-300 hover:text-amber-900 inline-flex items-center gap-1.5 cursor-pointer bg-amber-50 dark:bg-amber-950/50 px-2.5 py-1 rounded-lg border border-amber-300 dark:border-amber-700/80 transition shadow-2xs hover:bg-amber-100 dark:hover:bg-amber-900/50"
                                            title="Buka Kalkulator Konversi Stick / Batang ke Kilogram (Kg)">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Kalkulator Stick (Kg)</span>
                                    </button>

                                    @if(count($bulkRows) > 1)
                                        <!-- Terapkan Project Baris #1 ke Semua Baris -->
                                        <button type="button" 
                                                x-show="firstProject && firstProject.trim().length > 0"
                                                x-cloak
                                                wire:click="applyFirstProjectToAll(firstProject)" 
                                                class="text-[11px] font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1.5 cursor-pointer bg-brand-50/70 dark:bg-brand-950/40 px-2.5 py-1 rounded-lg border border-brand-200/70 dark:border-brand-800/50 transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Terapkan Project Baris #1 ke Semua Baris</span>
                                        </button>

                                        <!-- Terapkan PIC Baris #1 ke Semua Baris -->
                                        <button type="button" 
                                                x-show="firstPic && firstPic.trim().length > 0"
                                                x-cloak
                                                wire:click="applyFirstPicToAll(firstPic)" 
                                                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:underline inline-flex items-center gap-1.5 cursor-pointer bg-emerald-50/70 dark:bg-emerald-950/40 px-2.5 py-1 rounded-lg border border-emerald-200/70 dark:border-emerald-800/50 transition shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Terapkan PIC Baris #1 ke Semua Baris</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full min-w-[760px] text-left text-xs text-gray-700 dark:text-gray-300">
                                    <thead class="theme-table-header text-[11px] font-bold text-white uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th class="py-2.5 px-3 w-10 text-center">#</th>
                                            <th class="py-2.5 px-3 w-[280px] min-w-[240px]">Pilih Barang <span class="text-rose-500">*</span></th>
                                            <th class="py-2.5 px-3 w-36 min-w-[125px] text-center">Jumlah <span class="text-rose-500">*</span></th>
                                            <th class="py-2.5 px-3 min-w-[220px]">Nama Project / Keperluan</th>
                                            <th class="py-2.5 px-3 w-32 min-w-[110px]">PIC Penerima</th>
                                            <th class="py-2.5 px-3 w-14 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                        @foreach($bulkRows as $index => $row)
                                            <tr wire:key="bulk-row-{{ $index }}">
                                                <td class="py-2 px-3 text-center font-semibold text-gray-400">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="py-2 px-3 w-[280px] min-w-[240px]">
                                                     <div class="relative" 
                                                          x-data="itemSearchCombobox(@js($row['item_id'] ?? ''), 'bulkRows.{{ $index }}.item_id', {{ $index }})"
                                                          @scroll.window="if(open) updatePosition()"
                                                          @resize.window="if(open) updatePosition()">
                                                         
                                                         <input type="hidden" 
                                                                x-ref="hiddenInput"
                                                                wire:model="bulkRows.{{ $index }}.item_id" 
                                                                :value="selectedId">

                                                         <div x-ref="triggerWrap" 
                                                              @click="toggle()"
                                                              style="min-height: 36px;"
                                                              class="relative flex items-center justify-between w-full min-h-[36px] px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:border-brand-400 transition shadow-2xs">
                                                             
                                                             <div class="flex-1 min-w-0 pr-2">
                                                                 <template x-if="selectedItem">
                                                                     <div class="flex items-center gap-1 truncate">
                                                                         <span x-show="selectedItem.is_offcut" class="px-1 py-0.2 rounded text-[8px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 shrink-0">OFF-CUT</span>
                                                                         <span class="font-bold text-gray-900 dark:text-white truncate block text-xs" :title="selectedItem.name" x-text="selectedItem.name + (selectedItem.dimension ? ' (' + selectedItem.dimension + ')' : '')"></span>
                                                                     </div>
                                                                 </template>
                                                                 <template x-if="!selectedItem">
                                                                     <span class="text-gray-400 dark:text-gray-500 truncate block text-xs">Ketik / Pilih Barang...</span>
                                                                 </template>
                                                             </div>

                                                             <div class="flex items-center gap-0.5 shrink-0">
                                                                 <button type="button" 
                                                                         x-show="selectedId" 
                                                                         @click.stop="clearSelection()" 
                                                                         class="p-0.5 text-gray-400 hover:text-rose-500 rounded text-[10px] transition cursor-pointer"
                                                                         title="Hapus pilihan">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                 </button>
                                                                 <button type="button" 
                                                                         @click.stop="toggle()" 
                                                                         class="p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition cursor-pointer"
                                                                         title="Buka daftar barang">
                                                                     <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                     </svg>
                                                                 </button>
                                                             </div>
                                                         </div>

                                                         <!-- Teleported Floating Dropdown Menu -->
                                                         <div wire:ignore>
                                                         <template x-teleport="body">
                                                             <div x-show="open" 
                                                                  x-cloak 
                                                                  x-transition:enter="transition ease-out duration-150"
                                                                  x-transition:enter-start="opacity-0 scale-95"
                                                                  x-transition:enter-end="opacity-100 scale-100"
                                                                  x-transition:leave="transition ease-in duration-100"
                                                                  x-transition:leave-start="opacity-100 scale-100"
                                                                  x-transition:leave-end="opacity-0 scale-95"
                                                                  @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                                  :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                                  class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                                                 
                                                                 <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                                     <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                                         <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Pilih Barang
                                                                     </span>
                                                                     <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredItems.length + ' Barang'"></span>
                                                                 </div>

                                                                 <div class="relative">
                                                                     <input type="text" 
                                                                            x-ref="searchField" 
                                                                            x-model="search" 
                                                                            @keydown.enter.prevent="if (filteredItems.length > 0) { selectItem(filteredItems[0]); }"
                                                                            @keydown.escape="close()"
                                                                            placeholder="Ketik nama, kode, atau kategori barang..." 
                                                                            class="w-full pl-3 pr-7 py-1.5 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-500 focus:bg-white dark:focus:bg-gray-800 shadow-xs">
                                                                     <button x-show="search.length > 0" @click="search = ''" type="button" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                                                                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                  </button>
                                                                 </div>

                                                                 <div class="max-h-60 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                                     <template x-for="item in filteredItems" :key="item.id">
                                                                         <div @click="selectItem(item)"
                                                                              class="p-2 rounded-xl hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-brand-200 dark:hover:border-brand-800"
                                                                              :class="String(selectedId) === String(item.id) ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                                             <div class="min-w-0 flex-1">
                                                                                 <div class="flex items-center gap-1.5 flex-wrap">
                                                                                     <template x-if="item.is_offcut">
                                                                                         <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0 bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-700">OFF-CUT</span>
                                                                                     </template>
                                                                                     <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0"
                                                                                           :class="item.category && item.category.toLowerCase().includes('cat') ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : (item.category && item.category.toLowerCase().includes('secondary') ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')"
                                                                                           x-text="item.category"></span>
                                                                                     <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="item.name"></span>
                                                                                 </div>
                                                                                 <div class="flex items-center gap-2 mt-0.5 text-[10px] flex-wrap">
                                                                                     <span x-show="item.code" class="font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 px-1 py-0.2 rounded" x-text="item.code"></span>
                                                                                     <template x-if="item.dimension">
                                                                                         <span class="font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-1 py-0.2 rounded border border-amber-200 dark:border-amber-800/50" x-text="item.dimension"></span>
                                                                                     </template>
                                                                                     <span class="font-semibold"
                                                                                           :class="item.stock <= 0 ? 'text-rose-500 dark:text-rose-400' : (item.stock < 10 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')">
                                                                                         Stok: <strong x-text="item.stock"></strong> <span x-text="item.unit"></span>
                                                                                     </span>
                                                                                 </div>
                                                                             </div>
                                                                             <div x-show="String(selectedId) === String(item.id)" class="text-brand-600 dark:text-brand-400 shrink-0">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                             </div>
                                                                         </div>
                                                                     </template>

                                                                     <div x-show="filteredItems.length === 0" class="py-4 text-center text-xs text-gray-400">
                                                                         Tidak ada barang yang cocok dengan pencarian.
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </template>
                                                         </div>
                                                     </div>
                                                     @error('bulkRows.' . $index . '.item_id') 
                                                         <span class="text-[10px] text-rose-500 mt-0.5 block">{{ $message }}</span> 
                                                     @enderror
                                                 </td>
                                                <td class="py-2 px-3 w-36 min-w-[125px]">
                                                    <div class="flex items-center gap-1.5"
                                                         x-data="{ currentUnit: '{{ $row['unit'] ?? 'PCS' }}' }"
                                                         @item-unit-updated-{{ $index }}.window="currentUnit = $event.detail.unit">
                                                        <input type="number" min="0.001" step="any" wire:model.live.debounce.300ms="bulkRows.{{ $index }}.quantity" class="w-full rounded-lg border @error('bulkRows.' . $index . '.quantity') border-rose-500 bg-rose-50/40 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 @else border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white @enderror px-2 py-1.5 text-xs text-center font-bold outline-none focus:border-brand-500">
                                                        <span class="text-[10px] font-extrabold text-gray-500 dark:text-gray-400 uppercase shrink-0 bg-gray-100 dark:bg-gray-800 px-1.5 py-1 rounded border border-gray-200 dark:border-gray-700 font-mono min-w-[34px] text-center"
                                                              :title="currentUnit"
                                                              x-text="typeof formatUnitCode === 'function' ? formatUnitCode(currentUnit) : currentUnit">
                                                            {{ unit_code($row['unit'] ?? 'PCS') }}
                                                        </span>
                                                    </div>
                                                    @error('bulkRows.' . $index . '.quantity')
                                                        <span class="text-[10px] text-rose-500 font-semibold mt-1 block leading-tight">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                                <td class="py-2 px-3">
                                                    <div class="relative" 
                                                         x-data="projectSearchCombobox(@js($row['project'] ?? ''), 'bulkRows.{{ $index }}.project')"
                                                         @scroll.window="if(open) updatePosition()"
                                                         @resize.window="if(open) updatePosition()"
                                                         @bulk-projects-applied.window="selected = $event.detail.project; search = $event.detail.project; if (getInputEl()) getInputEl().value = $event.detail.project;">
                                                        
                                                        <div x-ref="triggerWrap" class="relative flex items-center">
                                                            <input type="text" 
                                                                   x-ref="actualInput"
                                                                   wire:model="bulkRows.{{ $index }}.project" 
                                                                   x-model="selected"
                                                                   @input="onInput($event)"
                                                                   @focus="onFocus()"
                                                                   placeholder="{{ !empty($project) ? 'Ikut: ' . $project : 'Ketik / Pilih Project...' }}" 
                                                                   class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-2.5 pr-14 py-1.5 text-xs text-gray-800 dark:text-white outline-none focus:border-brand-500 font-medium truncate shadow-2xs">
                                                            
                                                            <div class="absolute right-1.5 flex items-center gap-0.5">
                                                                <button type="button" 
                                                                        x-show="selected && selected.length > 0" 
                                                                        @click.stop="clearSelection()" 
                                                                        class="p-1 text-gray-400 hover:text-rose-500 rounded transition cursor-pointer"
                                                                        title="Hapus pilihan project">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                                <button type="button" 
                                                                        @click.stop="toggle()" 
                                                                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition cursor-pointer"
                                                                        title="Buka daftar project">
                                                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Teleported Floating Dropdown Menu -->
                                                        <div wire:ignore>
                                                        <template x-teleport="body">
                                                            <div x-show="open" 
                                                                 x-cloak 
                                                                 x-transition:enter="transition ease-out duration-150"
                                                                 x-transition:enter-start="opacity-0 scale-95"
                                                                 x-transition:enter-end="opacity-100 scale-100"
                                                                 x-transition:leave="transition ease-in duration-100"
                                                                 x-transition:leave-start="opacity-100 scale-100"
                                                                 x-transition:leave-end="opacity-0 scale-95"
                                                                 @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                                 :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                                 class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                                                
                                                                <!-- Dropdown Header -->
                                                                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                                    <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                                        Pilih Project
                                                                    </span>
                                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredProjects.length + ' Project'"></span>
                                                                </div>

                                                                <!-- Custom typed indicator -->
                                                                <div x-show="selected && selected.trim().length > 0 && !hasExactMatch">
                                                                    <div class="px-2.5 py-1.5 text-[11px] text-brand-900 dark:text-brand-200 bg-brand-50 dark:bg-brand-950/50 rounded-xl border border-brand-200 dark:border-brand-800/60 flex items-center justify-between gap-2">
                                                                        <div class="flex items-center gap-1.5 min-w-0">
                                                                            <span class="text-brand-600 font-bold text-xs">✓</span>
                                                                            <span class="truncate">Gunakan: <strong class="underline" x-text="selected.trim()"></strong></span>
                                                                        </div>
                                                                        <span class="text-[9px] font-bold uppercase bg-brand-600 text-white px-1.5 py-0.5 rounded shrink-0">Custom</span>
                                                                    </div>
                                                                </div>

                                                                <!-- Scrollable Projects List -->
                                                                <div class="max-h-56 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                                    <template x-for="p in filteredProjects" :key="p.name">
                                                                        <div @click="selectProject(p.name)"
                                                                             class="p-2 rounded-xl hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-brand-200 dark:hover:border-brand-800"
                                                                             :class="selected === p.name ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                                            <div class="min-w-0 flex-1">
                                                                                <div class="flex items-center gap-2">
                                                                                    <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="p.name"></span>
                                                                                </div>
                                                                                <div class="flex items-center gap-2 mt-0.5 text-[10px] text-gray-400">
                                                                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.2 rounded" x-show="p.code && p.code !== '-'" x-text="p.code"></span>
                                                                                    <span x-show="p.client" class="truncate" x-text="'• ' + p.client"></span>
                                                                                    <span x-show="p.status && p.status !== 'Historical'" class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase"
                                                                                          :class="p.status === 'Completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'"
                                                                                          x-text="p.status"></span>
                                                                                </div>
                                                                            </div>
                                                                            <div x-show="selected === p.name" class="text-brand-600 dark:text-brand-400 font-extrabold text-sm shrink-0">
                                                                                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                            </div>
                                                                        </div>
                                                                    </template>

                                                                    <div x-show="filteredProjects.length === 0" class="py-3 px-2 text-center text-xs text-gray-400">
                                                                        <span x-show="selected && selected.trim().length > 0">Tidak ada project yang cocok di master. Teks Anda akan disimpan sebagai keperluan / project baru.</span>
                                                                        <span x-show="!selected || selected.trim().length === 0">Belum ada daftar project terdaftar. Silakan ketik nama project / keperluan langsung di kolom.</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-2 px-3 w-32 min-w-[110px]">
                                                    <div class="relative" 
                                                         x-data="picAutocomplete(@js($row['pic'] ?? ''), 'bulkRows.{{ $index }}.pic', {{ $index === 0 ? 'true' : 'false' }})"
                                                         @scroll.window="if(open) updatePosition()"
                                                         @resize.window="if(open) updatePosition()"
                                                         @bulk-pics-applied.window="value = $event.detail.pic; if (getInputEl()) getInputEl().value = $event.detail.pic;">
                                                        
                                                        <div x-ref="triggerWrap" class="relative flex items-center">
                                                            <input type="text" 
                                                                   x-ref="picInput"
                                                                   wire:model="bulkRows.{{ $index }}.pic" 
                                                                   x-model="value"
                                                                   @input="onInput($event)"
                                                                   @focus="onFocus()"
                                                                   @keydown="onKeyDown($event)"
                                                                   placeholder="{{ !empty($pic) ? 'Ikut: ' . $pic : 'Nama PIC...' }}" 
                                                                   class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-2.5 pr-7 py-1.5 text-xs text-gray-800 dark:text-white outline-none focus:border-brand-500 font-medium truncate shadow-2xs">
                                                            
                                                            <div class="absolute right-1.5 flex items-center gap-0.5">
                                                                <button type="button" 
                                                                        x-show="value && value.length > 0" 
                                                                        @click.stop="clearPic()" 
                                                                        class="p-1 text-gray-400 hover:text-rose-500 rounded transition cursor-pointer"
                                                                        title="Hapus nama PIC">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Floating Recommendations Dropdown -->
                                                        <div wire:ignore>
                                                            <template x-teleport="body">
                                                                <div x-show="open" 
                                                                     x-cloak 
                                                                     x-transition:enter="transition ease-out duration-150"
                                                                     x-transition:enter-start="opacity-0 scale-95"
                                                                     x-transition:enter-end="opacity-100 scale-100"
                                                                     x-transition:leave="transition ease-in duration-100"
                                                                     x-transition:leave-start="opacity-100 scale-100"
                                                                     x-transition:leave-end="opacity-0 scale-95"
                                                                     @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                                     :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                                     class="rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-900 space-y-1.5">
                                                                    
                                                                    <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                                        <span class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 text-[11px]">
                                                                            <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                                            Saran PIC
                                                                        </span>
                                                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredSuggestions.length + ' Pilihan'"></span>
                                                                    </div>

                                                                    <div class="max-h-48 overflow-y-auto space-y-0.5 pr-0.5 custom-scrollbar">
                                                                        <template x-for="(name, sIndex) in filteredSuggestions" :key="name">
                                                                            <div @click="selectPic(name)"
                                                                                 class="px-2 py-1.5 rounded-lg hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent"
                                                                                 :class="focusedIndex === sIndex ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                                                <div class="flex items-center gap-2 min-w-0">
                                                                                    <div class="w-5 h-5 rounded-full bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center font-bold text-[10px] shrink-0"
                                                                                         x-text="name.charAt(0).toUpperCase()"></div>
                                                                                    <span class="font-medium text-xs text-gray-800 dark:text-white truncate" x-text="name"></span>
                                                                                </div>
                                                                                <span class="text-[9px] text-gray-400 shrink-0 font-mono">Pilih</span>
                                                                            </div>
                                                                        </template>
                                                                    </div>

                                                                    <div class="pt-1 border-t border-gray-100 dark:border-gray-800 text-[9px] text-gray-400 text-center">
                                                                        Tekan <kbd class="px-1 py-0.2 bg-gray-100 dark:bg-gray-800 rounded font-mono">Tab</kbd> atau <kbd class="px-1 py-0.2 bg-gray-100 dark:bg-gray-800 rounded font-mono">Enter</kbd> untuk memilih
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-2 px-3 text-center">
                                                    @if(count($bulkRows) > 1)
                                                        <button type="button" wire:click="removeBulkRow({{ $index }})" class="text-xs font-semibold text-rose-600 hover:text-rose-800 dark:text-rose-400 transition cursor-pointer">
                                                            Hapus
                                                        </button>
                                                    @else
                                                        <span class="text-gray-300 dark:text-gray-600">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="addBulkRow" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 px-3 py-1.5 rounded-lg border border-brand-200 dark:border-brand-800/60 bg-brand-50/50 hover:bg-brand-50 dark:bg-brand-950/30 transition cursor-pointer">
                                        + Tambah Baris
                                    </button>
                                    <button type="button" wire:click="addBulkRows(5)" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 px-3 py-1.5 rounded-lg border border-brand-200 dark:border-brand-800/60 bg-brand-50/50 hover:bg-brand-50 dark:bg-brand-950/30 transition cursor-pointer" title="Tambah 5 Baris">
                                        +5 Baris
                                    </button>
                                    <button type="button" wire:click="addBulkRows(10)" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 px-3 py-1.5 rounded-lg border border-brand-200 dark:border-brand-800/60 bg-brand-50/50 hover:bg-brand-50 dark:bg-brand-950/30 transition cursor-pointer" title="Tambah 10 Baris">
                                        +10 Baris
                                    </button>
                                </div>
                                <span class="text-xs text-gray-400 font-medium">{{ count($bulkRows) }} Baris</span>
                            </div>
                        </div>
                    @else
                        <!-- Single Mode Entry (or Edit Mode) -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pilih Barang <span class="text-rose-500">*</span></label>
                                <div class="relative" 
                                     x-data="itemSearchCombobox(@js($item_id ?? ''), 'item_id')"
                                     @scroll.window="if(open) updatePosition()"
                                     @resize.window="if(open) updatePosition()">
                                    
                                    <input type="hidden" 
                                           x-ref="hiddenInput"
                                           wire:model.live="item_id" 
                                           :value="selectedId">

                                    <div x-ref="triggerWrap" 
                                         @click="toggle()"
                                         style="min-height: 40px;"
                                         class="relative flex items-center justify-between w-full min-h-[40px] px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:border-brand-400 transition shadow-2xs">
                                        
                                        <div class="flex-1 min-w-0 pr-2">
                                            <template x-if="selectedItem">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="selectedItem.is_offcut" class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 shrink-0">OFF-CUT</span>
                                                    <span class="font-bold text-gray-900 dark:text-white truncate block text-xs" :title="selectedItem.name" x-text="selectedItem.name + (selectedItem.dimension ? ' (' + selectedItem.dimension + ')' : '')"></span>
                                                </div>
                                            </template>
                                            <template x-if="!selectedItem">
                                                <span class="text-gray-400 dark:text-gray-500 truncate block text-xs">Cari atau pilih barang...</span>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <button type="button" 
                                                    x-show="selectedId" 
                                                    @click.stop="clearSelection()" 
                                                    class="p-1 text-gray-400 hover:text-rose-500 rounded text-xs transition cursor-pointer"
                                                    title="Hapus pilihan">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            <button type="button" 
                                                    @click.stop="toggle()" 
                                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition cursor-pointer"
                                                    title="Buka daftar barang">
                                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Teleported Floating Dropdown Menu -->
                                    <div wire:ignore>
                                    <template x-teleport="body">
                                        <div x-show="open" 
                                             x-cloak 
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                             :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                             class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                            
                                            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                    Pilih Barang
                                                </span>
                                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredItems.length + ' Barang'"></span>
                                            </div>

                                            <div class="relative">
                                                <input type="text" 
                                                       x-ref="searchField"
                                                       x-model="search" 
                                                       @keydown.enter.prevent="if (filteredItems.length > 0) { selectItem(filteredItems[0]); }"
                                                       @keydown.escape="close()"
                                                       placeholder="Ketik nama, kode, atau kategori barang..." 
                                                       class="w-full pl-3 pr-7 py-1.5 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-500 focus:bg-white dark:focus:bg-gray-800 shadow-xs">
                                                <button x-show="search.length > 0" @click="search = ''" type="button" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            <div class="max-h-60 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                <template x-for="item in filteredItems" :key="item.id">
                                                    <div @click="selectItem(item)"
                                                         class="p-2 rounded-xl hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-brand-200 dark:hover:border-brand-800"
                                                         :class="String(selectedId) === String(item.id) ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                                <template x-if="item.is_offcut">
                                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0 bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-700">OFF-CUT</span>
                                                                </template>
                                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0"
                                                                      :class="item.category && item.category.toLowerCase().includes('cat') ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : (item.category && item.category.toLowerCase().includes('secondary') ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')"
                                                                      x-text="item.category"></span>
                                                                <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="item.name"></span>
                                                            </div>
                                                            <div class="flex items-center gap-2 mt-0.5 text-[10px] flex-wrap">
                                                                <span x-show="item.code" class="font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 px-1 py-0.2 rounded" x-text="item.code"></span>
                                                                <template x-if="item.dimension">
                                                                    <span class="font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-1 py-0.2 rounded border border-amber-200 dark:border-amber-800/50" x-text="item.dimension"></span>
                                                                </template>
                                                                <span class="font-semibold"
                                                                      :class="item.stock <= 0 ? 'text-rose-500 dark:text-rose-400' : (item.stock < 10 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')">
                                                                    Stok: <strong x-text="item.stock"></strong> <span x-text="item.unit"></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div x-show="String(selectedId) === String(item.id)" class="text-brand-600 dark:text-brand-400 font-extrabold text-sm shrink-0">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        </div>
                                                    </div>
                                                </template>

                                                <div x-show="filteredItems.length === 0" class="py-4 text-center text-xs text-gray-400">
                                                    Tidak ada barang yang cocok dengan pencarian.
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    </div>
                                </div>
                                @error('item_id') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            @if ($selectedItemStock !== null)
                                <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 space-y-1">
                                    <div class="flex items-center justify-between font-bold">
                                        <span>Stok Fisik Tersedia di Gudang:</span>
                                        <span class="text-sm font-black font-mono {{ $selectedItemStock <= 15 ? 'text-rose-600 dark:text-rose-400' : 'text-brand-600 dark:text-brand-400' }}">
                                            {{ format_quantity($selectedItemStock) }} {{ $selectedItemUnit }}
                                        </span>
                                    </div>

                                    @if($isCatItem)
                                        <div class="pt-1 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-[11px]">
                                            <span class="text-brand-700 dark:text-brand-300 font-bold">
                                                Kategori Cat / Painting:
                                            </span>
                                            <span class="font-bold">
                                                Tgl Pembuatan: {{ $catManufacturingDate ?? 'Belum ada data' }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($isOffCutItem)
                                        <div class="pt-2 border-t border-amber-200 dark:border-amber-800/60 flex flex-col gap-1 text-[11px] bg-amber-50/70 dark:bg-amber-950/30 p-2.5 rounded-xl border border-amber-200 dark:border-amber-900/40">
                                            <div class="flex items-center justify-between">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-300 uppercase tracking-wider">OFF-CUT REMNANT</span>
                                                <span class="font-bold text-amber-800 dark:text-amber-300">Dimensi: {{ $offCutDimension ?: '-' }}</span>
                                            </div>
                                            @if($offCutParentName)
                                                <div class="text-[10px] text-gray-600 dark:text-gray-300">
                                                    Material Induk: <strong>{{ $offCutParentName }}</strong>
                                                </div>
                                            @endif
                                            @if($offCutHeatNumber)
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                                    Heat No: {{ $offCutHeatNumber }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($wholesale_unit && $conversion_factor > 1)
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-gray-800 dark:text-gray-200">Mode Satuan Pengeluaran (UOM):</span>
                                        <div class="flex items-center gap-1 bg-white dark:bg-gray-900 p-0.5 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <button type="button" wire:click="setInputMode('base')" class="px-2 py-1 rounded-md text-[11px] font-bold transition {{ $input_mode === 'base' ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">
                                                {{ $selectedItemUnit ?: 'Pcs' }} (Satuan Kecil)
                                            </button>
                                            <button type="button" wire:click="setInputMode('wholesale')" class="px-2 py-1 rounded-md text-[11px] font-bold transition {{ $input_mode === 'wholesale' ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">
                                                {{ $wholesale_unit }} (Satuan Grosir)
                                            </button>
                                        </div>
                                    </div>

                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Rasio Konversi: <strong>1 {{ $wholesale_unit }} = {{ $conversion_factor }} {{ $selectedItemUnit }}</strong>.
                                    </p>

                                    @if($input_mode === 'wholesale')
                                        <div class="mt-2.5 pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-3 items-center">
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                                    Jumlah {{ $wholesale_unit }} <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="number" min="0.001" step="any" wire:model.live.debounce.150ms="wholesale_qty" placeholder="Misal: 1 {{ $wholesale_unit }}" class="w-full rounded-xl border @error('quantity') border-rose-500 bg-rose-50/40 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 @else border-gray-200 bg-white text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white @enderror px-3 py-2 text-xs font-bold outline-none focus:border-brand-500">
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block uppercase">Stok Terpotong (Pcs)</span>
                                                <span class="text-base font-black text-rose-600 dark:text-rose-400 font-mono">
                                                    -{{ format_quantity($quantity) }} {{ $selectedItemUnit }}
                                                </span>
                                            </div>
                                        </div>
                                        @error('quantity')
                                            <div class="flex items-center gap-1.5 text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1.5">
                                                <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                <span>{{ $message }}</span>
                                            </div>
                                        @enderror
                                    @endif
                                </div>
                            @endif

                            @if($input_mode === 'base' || empty($wholesale_unit) || $conversion_factor <= 1)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Jumlah Keluar <span class="text-rose-500">*</span></label>
                                            <button type="button" 
                                                    @click="$dispatch('open-stick-calculator', { mode: 'single' })" 
                                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 dark:text-amber-400 hover:text-amber-800 hover:underline cursor-pointer"
                                                    title="Hitung Stick / Batang ke Kilogram (Kg)">
                                                <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Kalkulator Stick (Kg)</span>
                                            </button>
                                        </div>
                                        <input type="number" min="0.001" step="any" wire:model.live.debounce.250ms="quantity" class="w-full rounded-xl border @error('quantity') border-rose-500 bg-rose-50/40 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 @else border-gray-200 bg-white text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white @enderror px-3.5 py-2 text-xs font-bold outline-none focus:border-brand-500">
                                        @error('quantity')
                                            <div class="flex items-center gap-1.5 text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1.5">
                                                <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                <span>{{ $message }}</span>
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan <span class="text-rose-500">*</span></label>
                                        <select wire:model="unit" class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            <option value="">-- Pilih Satuan --</option>
                                            <option value="PCS">PCS</option>
                                            <option value="LEMBAR">LEMBAR</option>
                                            <option value="BATANG">BATANG</option>
                                            <option value="ROLL">ROLL</option>
                                            <option value="TABUNG">TABUNG</option>
                                            <option value="KG">KG</option>
                                            <option value="LITER">LITER</option>
                                            <option value="ONS">ONS</option>
                                            <option value="CM">CM</option>
                                            <option value="M">M</option>
                                            <option value="MM">MM</option>
                                            <option value="BOX">BOX</option>
                                            <option value="ML">ML</option>
                                            <option value="PASANG">PASANG</option>
                                            <option value="LUSIN">LUSIN</option>
                                        </select>
                                        @error('unit') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan (Opsional)</label>
                                <textarea wire:model="notes" rows="2" placeholder="Keterangan pengeluaran material..." class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                            </div>
                        </div>
                    @endif

                    <!-- Action buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800 mt-4">
                        <button type="button" wire:click="closeModal" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="btn-save-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white hover:bg-brand-700 focus:outline-none shadow-xs transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="save">{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Pengeluaran Material' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Kalkulator Stick -> Kg -->
    <div x-data="stickKgCalculator()" x-cloak>
        <template x-teleport="body">
            <div x-show="isOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[99999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto"
                 @keydown.escape.window="close()">
                
                <div x-show="isOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                     @click.outside="close()"
                     class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl overflow-hidden my-auto">
                    
                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-amber-50/60 dark:bg-amber-950/30">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-extrabold text-gray-900 dark:text-white leading-tight">
                                    Kalkulator Stick ke Kilogram (Kg)
                                </h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Konversi otomatis barang satuan KG yang diambil per batang / stick (misal: kawat las).
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="close()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer" title="Tutup Kalkulator">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body Form -->
                    <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                        <!-- Target Penerapan (Bulk Row Selector, Single Indicator, or General Mode) -->
                        @if(count($bulkRows) > 0)
                            <div x-show="targetMode === 'bulk'" class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Target Penerapan Baris:
                                    </label>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 font-mono"
                                          x-text="targetItemName"></span>
                                </div>
                                <select x-model="targetRowIndex" @change="setTargetRow($event.target.value)" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-800 dark:text-white outline-none focus:border-amber-500 cursor-pointer shadow-2xs">
                                    @foreach($bulkRows as $bIdx => $bRow)
                                        @php
                                            $itemObj = !empty($bRow['item_id']) ? \App\Models\Item::find($bRow['item_id']) : null;
                                            $rowLabel = "Baris #" . ($bIdx + 1) . ($itemObj ? " - " . $itemObj->name . " (" . $itemObj->unit . ")" : " (Belum pilih barang)");
                                        @endphp
                                        <option value="{{ $bIdx }}">{{ $rowLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div x-show="targetMode === 'single'" class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">Target Form:</span>
                                <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Input 1 Barang (Jumlah Keluar & Satuan KG)</span>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 font-mono">Single Mode</span>
                        </div>

                        <div x-show="targetMode === 'general' || targetMode === 'standalone'" class="p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">Mode Kalkulator Umum:</span>
                                <span class="text-[11px] text-amber-700 dark:text-amber-400 font-medium">Hitung bebas konversi stick / batang ke kilogram (KG)</span>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-200/80 dark:bg-amber-900/80 text-amber-800 dark:text-amber-200 font-mono">
                                Kalkulator Bebas
                            </span>
                        </div>

                        <!-- Calculation Mode Selector -->
                        <div class="flex items-center p-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                            <button type="button" 
                                    @click="calcMethod = 'gram'" 
                                    class="flex-1 py-1.5 px-3 text-xs font-bold rounded-lg transition cursor-pointer text-center"
                                    :class="calcMethod === 'gram' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'">
                                Opsi A: Berat per Stick (Gram)
                            </button>
                            <button type="button" 
                                    @click="calcMethod = 'per_kg'" 
                                    class="flex-1 py-1.5 px-3 text-xs font-bold rounded-lg transition cursor-pointer text-center"
                                    :class="calcMethod === 'per_kg' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'">
                                Opsi B: Stick per 1 Kg
                            </button>
                        </div>

                        <!-- Input 1: Jumlah Stick Diambil -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Jumlah Stick / Batang yang Diambil <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-gray-400 font-mono">Contoh: 10, 15, 25 stick</span>
                            </div>
                            <div class="relative">
                                <input type="number" 
                                       x-ref="stickCountInput"
                                       x-model="stickCount" 
                                       min="1" 
                                       step="any" 
                                       placeholder="Ketik jumlah stick yang diambil..." 
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm font-black font-mono text-gray-900 dark:text-white outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-2xs">
                                <span class="absolute right-3.5 top-2.5 text-xs font-extrabold text-gray-400 uppercase">Stick</span>
                            </div>
                            <!-- Quick Increments -->
                            <div class="flex items-center gap-1.5 pt-1">
                                <span class="text-[10px] font-bold text-gray-400 mr-1">Tambah Cepat:</span>
                                <button type="button" @click="addStick(5)" class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition cursor-pointer">+5</button>
                                <button type="button" @click="addStick(10)" class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition cursor-pointer">+10</button>
                                <button type="button" @click="addStick(20)" class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition cursor-pointer">+20</button>
                                <button type="button" @click="addStick(50)" class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition cursor-pointer">+50</button>
                                <button type="button" @click="resetStick()" class="ml-auto text-[10px] font-bold text-rose-500 hover:underline cursor-pointer">Reset</button>
                            </div>
                        </div>

                        <!-- Input 2: Berat per stick (Mode Gram) -->
                        <div x-show="calcMethod === 'gram'" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Berat 1 Stick (Gram) <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-gray-400 font-mono" x-show="stickPerKg > 0" x-text="'≈ ' + stickPerKg + ' stick per kg'"></span>
                            </div>
                            <div class="relative">
                                <input type="number" 
                                       x-model="gramPerStick" 
                                       @input="onGramInput($event.target.value)"
                                       min="0.1" 
                                       step="any" 
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-bold font-mono text-gray-900 dark:text-white outline-none focus:border-amber-500 shadow-2xs">
                                <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">Gram / Stick</span>
                            </div>
                            <!-- Preset Buttons -->
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 block mb-1">Preset Standar Kawat Las:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" @click="setPreset(20)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(gramPerStick) === 20 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        20g (Ø 2.0-2.6mm)
                                    </button>
                                    <button type="button" @click="setPreset(25)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(gramPerStick) === 25 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        25g (Ø 2.6mm Std)
                                    </button>
                                    <button type="button" @click="setPreset(35)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(gramPerStick) === 35 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        35g (Ø 3.2mm Std)
                                    </button>
                                    <button type="button" @click="setPreset(40)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(gramPerStick) === 40 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        40g (Ø 3.2mm Berat)
                                    </button>
                                    <button type="button" @click="setPreset(50)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(gramPerStick) === 50 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        50g (Ø 4.0mm)
                                    </button>
                                    <button type="button" @click="setPreset(65)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(gramPerStick) === 65 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        65g (Ø 5.0mm)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Input 2: Stick per kg (Mode Stick per 1 Kg) -->
                        <div x-show="calcMethod === 'per_kg'" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Isi Stick per 1 Kilogram (Kg) <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-gray-400 font-mono" x-show="gramPerStick > 0" x-text="'≈ ' + gramPerStick + ' gram per stick'"></span>
                            </div>
                            <div class="relative">
                                <input type="number" 
                                       x-model="stickPerKg" 
                                       @input="onStickPerKgInput($event.target.value)"
                                       min="1" 
                                       step="any" 
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-bold font-mono text-gray-900 dark:text-white outline-none focus:border-amber-500 shadow-2xs">
                                <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">Stick / Kg</span>
                            </div>
                            <!-- Presets -->
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 block mb-1">Preset Kemasan (Stick / Kg):</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" @click="setStickPerKgPreset(50)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(stickPerKg) === 50 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        50 Stick/Kg (20g)
                                    </button>
                                    <button type="button" @click="setStickPerKgPreset(40)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(stickPerKg) === 40 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        40 Stick/Kg (25g)
                                    </button>
                                    <button type="button" @click="setStickPerKgPreset(28)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(stickPerKg) === 28 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        28 Stick/Kg (35g)
                                    </button>
                                    <button type="button" @click="setStickPerKgPreset(20)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                            :class="Number(stickPerKg) === 20 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                        20 Stick/Kg (50g)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Live Calculation Result Card -->
                        <div class="rounded-xl border border-amber-200 dark:border-amber-800/80 bg-amber-50/70 dark:bg-amber-950/40 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 block">
                                        Hasil Konversi ke Satuan Sistem:
                                    </span>
                                    <div class="flex items-baseline gap-2 mt-1">
                                        <span class="text-2xl sm:text-3xl font-black font-mono text-gray-900 dark:text-white" x-text="formattedKg"></span>
                                        <span class="text-xs sm:text-sm font-black text-amber-700 dark:text-amber-400 font-mono uppercase">KILOGRAM (KG)</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Total Berat:</span>
                                    <span class="text-sm font-bold font-mono text-gray-700 dark:text-gray-300" x-text="Math.round(totalGrams).toLocaleString('id-ID') + ' g'"></span>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-2.5 border-t border-amber-200/80 dark:border-amber-800/60 flex items-center justify-between text-[11px] text-amber-900 dark:text-amber-200 font-mono">
                                <span class="flex items-center gap-1.5 truncate">
                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="truncate" x-text="formulaText"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/60">
                        <button type="button" 
                                @click="copyResult()" 
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span x-text="copied ? '✓ Nilai Tersalin!' : 'Salin Nilai'"></span>
                        </button>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="close()" 
                                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="button" 
                                    @click="applyResult()" 
                                    :disabled="calculatedKg <= 0"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed px-4 py-2 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="targetMode === 'bulk' ? 'Terapkan ke ' + targetItemName : (targetMode === 'single' ? 'Terapkan ke Jumlah Keluar' : (copied ? '✓ Nilai Tersalin!' : 'Salin Nilai (KG)'))"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

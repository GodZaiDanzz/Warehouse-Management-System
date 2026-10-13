<div class="space-y-6" x-data="{ showHelpGuide: false, showStickModal: false, targetOpnameId: null, targetItemName: '' }">
    <!-- Flash Messages (SweetAlert2 Trigger) -->
    @if (session()->has('success'))
        @php
            $successMsg = session('success');
            $isDelete = preg_match('/hapus|delete|deleted|dihapus/i', $successMsg);
            $swalTitle = $isDelete ? 'Deleted!' : 'Berhasil!';
        @endphp
        <div x-init="if (typeof window.showSwalSuccess === 'function') window.showSwalSuccess(@json($swalTitle), @json($successMsg))" class="hidden"></div>
    @endif

    @if (session()->has('error'))
        <div x-init="if (typeof window.showSwalError === 'function') window.showSwalError('Gagal!', @json(session('error')))" class="hidden"></div>
    @endif

    <!-- TAB NAVIGATION BAR & MINIMALIST GUIDE TOGGLE -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-3">
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="$set('activeTab', 'daily')"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition cursor-pointer {{ $activeTab === 'daily' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}">
                Opname Harian & Input Sore
            </button>

            <button wire:click="$set('activeTab', 'recap')"
                class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition cursor-pointer {{ $activeTab === 'recap' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}">
                Tabel Rekap Harian Per Item
            </button>
        </div>

        <div class="flex items-center gap-3">
            <!-- Tombol Bantuan Cara Pakai -->
            <button type="button" @click="showHelpGuide = !showHelpGuide"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold transition cursor-pointer border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 shadow-2xs">
                <span x-text="showHelpGuide ? 'Sembunyikan Petunjuk' : 'Petunjuk Cara Pakai'"></span>
            </button>
        </div>
    </div>

    <!-- PANDUAN RINGKAS -->
    @if ($activeTab === 'daily')
        <div x-show="showHelpGuide" x-transition class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-900/50 space-y-3 shadow-xs">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-2">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Panduan Alur Stock Opname</h3>
                <button type="button" @click="showHelpGuide = false" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    Tutup
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-gray-600 dark:text-gray-300">
                <div class="p-3.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <span class="font-bold text-gray-900 dark:text-white block mb-1">1. Input Stok Fisik</span>
                    Masukkan jumlah fisik riil barang pada kolom <strong>Stok Akhir Fisik</strong> di tabel, atau klik <strong>Input Cepat Fisik</strong>.
                </div>
                <div class="p-3.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <span class="font-bold text-gray-900 dark:text-white block mb-1">2. Simpan Draf</span>
                    Klik tombol <strong>Simpan Data</strong> untuk menyimpan hitungan sementara.
                </div>
                <div class="p-3.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <span class="font-bold text-gray-900 dark:text-white block mb-1">3. Tutup Buku & Sync</span>
                    Jika pencatatan sudah selesai dan diverifikasi, klik <strong>Tutup Buku & Sync Master</strong> untuk mengupdate master stok WMS.
                </div>
            </div>

            <div class="text-[11px] text-gray-500 pt-1 border-t border-gray-200 dark:border-gray-800">
                <strong>Istilah:</strong> Stok Awal (Rolling kemarin), Stok Akhir Fisik (Hasil hitung gudang), Total Terpakai (Stok Awal - Fisik), Mutasi Sistem (Material Out), Selisih (Pemakaian Fisik vs Dokumen).
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- TAB 1: OPNAME HARIAN                                                      -->
    <!-- ========================================================================= -->
    @if ($activeTab === 'daily')
        <!-- Summary KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1: Total Barang -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Item Barang</span>
                <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ number_format($totalItemsCount) }}</h3>
                <p class="mt-2 text-[11px] text-gray-400">Terdaftar di WMS</p>
            </div>

            <!-- Card 2: Progres Input -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progres Input Fisik</span>
                <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white font-mono">
                    {{ $completedCount }} <span class="text-xs font-normal text-gray-400">/ {{ $totalItemsCount }}</span>
                </h3>
                <p class="mt-2 text-[11px] text-gray-400">{{ $pendingCount }} item belum diisi ({{ $totalItemsCount > 0 ? round(($completedCount / $totalItemsCount) * 100) : 0 }}%)</p>
            </div>

            <!-- Card 3: Total Terpakai -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Terpakai Hari Ini</span>
                <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ format_quantity($totalUsedToday) }}</h3>
                <p class="mt-2 text-[11px] text-gray-400">Stok Awal - Stok Akhir</p>
            </div>

            <!-- Card 4: Status Master WMS -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status Master WMS</span>
                <h3 class="mt-2 text-base font-bold text-gray-900 dark:text-white">
                    @if ($isAllSynced)
                        Tersinkron Penuh
                    @elseif ($completedCount > 0)
                        Siap Tutup Buku
                    @else
                        Menunggu Input
                    @endif
                </h3>
                <p class="mt-2 text-[11px] text-gray-400">
                    {{ $totalDifferenceCount > 0 ? "{$totalDifferenceCount} item ada selisih" : "Data konsisten" }}
                </p>
            </div>
        </div>

        <!-- CONTROL & FILTER BAR -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs space-y-4">
            <!-- Row 1: Tanggal & Action Buttons -->
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                <!-- Date Selector -->
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300">Tanggal:</label>
                    <div wire:ignore class="relative inline-flex items-center" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') {
                                    setTimeout(initPicker, 50);
                                    return;
                                }
                                this.fp = flatpickr(this.$refs.dateInput, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-28 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer text-center',
                                    defaultDate: @js($selectedDate),
                                    onChange: (selectedDates, dateStr) => {
                                        if (dateStr && dateStr !== $wire.selectedDate) {
                                            $wire.set('selectedDate', dateStr);
                                        }
                                    }
                                });
                            };
                            this.$nextTick(() => initPicker());

                            this.$watch('$wire.selectedDate', (val) => {
                                if (this.fp && val && val !== this.fp.input.value) {
                                    this.fp.setDate(val, false);
                                }
                            });
                        },
                        destroy() {
                            if (this.fp) {
                                this.fp.destroy();
                                this.fp = null;
                            }
                        }
                    }">
                        <input x-ref="dateInput" type="text" class="hidden" value="{{ $selectedDate }}">
                    </div>

                    <!-- Date Presets -->
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="setDatePreset('yesterday')"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $selectedDate === \Carbon\Carbon::yesterday()->format('Y-m-d') ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                            Kemarin
                        </button>
                        <button type="button" wire:click="setDatePreset('today')"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $selectedDate === date('Y-m-d') ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                            Hari Ini
                        </button>
                        <button type="button" wire:click="setDatePreset('tomorrow')"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $selectedDate === \Carbon\Carbon::tomorrow()->format('Y-m-d') ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                            Besok
                        </button>
                    </div>

                    <div wire:loading wire:target="selectedDate,setDatePreset,setShift" class="text-xs text-gray-500 animate-pulse">
                        Memuat data...
                    </div>
                </div>

                @php
                    $isPpic = auth()->user() && auth()->user()->isPpic();
                @endphp

                <!-- CLEAN & MINIMALIST BUTTON GROUP (NO EMOJIS, NO BRIGHT COLORS) -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Kalkulator Stick / Gram ke Kg -->
                    <button type="button" @click="targetOpnameId = null; targetItemName = ''; showStickModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 hover:bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 text-xs font-bold transition shadow-2xs cursor-pointer"
                        title="Buka Kalkulator Konversi Stick / Batang ke Kilogram (Kg)">
                        <svg class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>Kalkulator Stick (Kg)</span>
                    </button>

                    @if(!$isPpic)
                        <!-- Input Cepat -->
                        <button type="button" wire:click="openBulkModal"
                            class="px-3 py-1.5 rounded-lg bg-gray-800 text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-semibold transition cursor-pointer">
                            Input Cepat Fisik
                        </button>

                        <!-- Estimasi Sistem -->
                        <button type="button" @click="confirmAction('Isi otomatis semua item yang belum diinput dengan estimasi sistem?', () => $wire.autoFillExpectedStock(), 'KONFIRMASI', 'Ya, Isi Otomatis')"
                            class="px-3.5 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-xs font-semibold transition cursor-pointer shadow-2xs">
                            Estimasi Sistem
                        </button>

                        <!-- Simpan Batch -->
                        <button type="button" wire:click="saveAllRows"
                            class="btn-save-theme px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">
                            Simpan Data
                        </button>

                        <!-- Tutup Buku & Sync Master -->
                        <button type="button" @click="confirmAction('Apakah Anda yakin ingin menutup buku dan menyinkronkan stok fisik ke Master Stok Barang WMS?', () => $wire.closeBookAndSync(), 'KONFIRMASI', 'Ya, Tutup Buku & Sync')"
                            class="btn-add-theme px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">
                            Tutup Buku & Sync Master
                        </button>
                    @endif

                    <!-- Export Excel & Print -->
                    <a href="{{ route('stock-opnames.export', ['date' => $selectedDate, 'shift' => $selectedShift]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </a>
                    <a href="{{ route('stock-opnames.print', ['date' => $selectedDate, 'shift' => $selectedShift]) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-xs text-xs font-semibold transition">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </a>
                </div>
            </div>

            <!-- Row 2: Search & Filters -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode atau nama barang..."
                    class="w-full sm:w-72 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                <select wire:model.live="categoryId"
                    class="w-full sm:w-48 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="unitId"
                    class="w-full sm:w-44 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="">Semua Satuan</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->symbol }} ({{ $u->name }})</option>
                    @endforeach
                </select>

                <select wire:model.live="statusFilter"
                    class="w-full sm:w-48 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="">Semua Status Opname</option>
                    <option value="pending">Menunggu Input Fisik</option>
                    <option value="completed">Sudah Input (Sesuai)</option>
                    <option value="discrepancy">Ada Selisih</option>
                    <option value="synced">Sudah Tutup Buku (Synced)</option>
                </select>

                <select wire:model.live="dailyPerPage"
                    class="w-full sm:w-36 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="15">15 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                </select>
            </div>
        </div>

        <!-- TABEL OPNAME HARIAN -->
        <div class="relative rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-xs overflow-hidden">
            <!-- Loading Indicator Overlay -->
            <div wire:loading.flex class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-gray-900/60 backdrop-blur-xs">
                <div class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 text-gray-800 font-medium text-xs">
                    Memperbarui data...
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                    <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3">Barang & Kategori</th>
                            <th class="px-4 py-3 text-center w-28">Stok Awal</th>
                            <th class="px-4 py-3 text-center w-36">Stok Akhir Fisik</th>
                            <th class="px-4 py-3 text-center w-28">Terpakai</th>
                            <th class="px-4 py-3 text-center w-28">Mutasi Sistem</th>
                            <th class="px-4 py-3 text-center w-28">Selisih</th>
                            <th class="px-4 py-3 text-center w-32">Status</th>
                            <th class="px-4 py-3">Catatan</th>
                            <th class="px-4 py-3 text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($dailyOpnames as $index => $opname)
                            @php
                                $item = $opname->item;
                                $currFinal = $finalStocks[$opname->id] ?? null;
                                $calculatedUsed = ($currFinal !== null && $currFinal !== '')
                                    ? ((float)($initialStocks[$opname->id] ?? $opname->initial_stock) - (float)str_replace(',', '.', (string)$currFinal))
                                    : null;
                                $calculatedDiff = ($calculatedUsed !== null)
                                    ? ((float)$calculatedUsed - (float)$opname->system_out_qty)
                                    : null;
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                                <!-- No -->
                                <td class="px-4 py-3 text-center font-mono text-gray-500">
                                    {{ (method_exists($dailyOpnames, 'firstItem') ? ($dailyOpnames->firstItem() ?? 1) : 1) + $index }}
                                </td>

                                <!-- Nama Barang & Info -->
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 dark:text-white text-xs">
                                        {{ $item->name ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $item->category->name ?? 'General' }} • {{ $item->unit ?? 'PCS' }}
                                    </div>
                                </td>

                                <!-- Stok Awal Shift -->
                                <td class="px-4 py-3 text-center font-mono font-semibold text-gray-800 dark:text-gray-200">
                                    {{ format_quantity($initialStocks[$opname->id] ?? $opname->initial_stock) }}
                                </td>

                                <!-- Input Stok Akhir Fisik -->
                                <td class="px-4 py-3 text-center">
                                    @if($isPpic)
                                        <span class="font-mono font-bold text-xs text-gray-800 dark:text-gray-200">
                                            {{ $currFinal !== null ? format_quantity($currFinal) : '-' }}
                                        </span>
                                    @else
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            <input type="number" min="0" step="any"
                                                wire:model.live.debounce.400ms="finalStocks.{{ $opname->id }}"
                                                wire:keydown.enter="saveRowFinalStock({{ $opname->id }})"
                                                placeholder="Fisik..."
                                                class="w-24 text-center rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs font-mono font-semibold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            @if(str_contains(strtolower($opname->item->unit ?? ''), 'kg'))
                                                <button type="button" @click="targetOpnameId = {{ $opname->id }}; targetItemName = '{{ addslashes($opname->item->name ?? '') }}'; showStickModal = true;" class="p-1 rounded text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition cursor-pointer" title="Hitung konversi stick ke Kg untuk {{ $opname->item->name ?? '' }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <!-- Total Terpakai -->
                                <td class="px-4 py-3 text-center font-mono font-semibold">
                                    @if ($calculatedUsed !== null)
                                        {{ $calculatedUsed > 0 ? '-' : '' }}{{ format_quantity(abs($calculatedUsed)) }}
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">-</span>
                                    @endif
                                </td>

                                <!-- Mutasi Keluar Sistem -->
                                <td class="px-4 py-3 text-center font-mono text-gray-700 dark:text-gray-300">
                                    {{ format_quantity($opname->system_out_qty) }}
                                </td>

                                <!-- Selisih Fisik vs Dokumen Sistem -->
                                <td class="px-4 py-3 text-center">
                                    @if ($calculatedDiff !== null)
                                        @if (abs($calculatedDiff) < 0.0001)
                                            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                                                0 (Sesuai)
                                            </span>
                                        @elseif ($calculatedDiff > 0)
                                            <span class="text-xs font-semibold text-rose-700 dark:text-rose-400">
                                                +{{ format_quantity($calculatedDiff) }}
                                            </span>
                                        @else
                                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                                                {{ format_quantity($calculatedDiff) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3 text-center">
                                    @if ($opname->adjustment_status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800" title="Penyesuaian disetujui oleh {{ $opname->adjustedByUser->name ?? 'Admin' }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            Disesuaikan
                                        </span>
                                    @elseif ($opname->adjustment_status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800" title="{{ $opname->adjustment_notes }}">
                                            Ditolak (Hitung Ulang)
                                        </span>
                                    @elseif ($opname->status === 'synced')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                            Tersinkron
                                        </span>
                                    @elseif ($currFinal !== null)
                                        @if ($calculatedDiff != 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                                Ada Selisih
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700">
                                                Sesuai (Draft)
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <!-- Catatan -->
                                <td class="px-4 py-3">
                                    @if($isPpic)
                                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $opname->notes ?: '-' }}</span>
                                    @else
                                        <input type="text"
                                            wire:model.live.debounce.500ms="notes.{{ $opname->id }}"
                                            wire:keydown.enter="saveRowFinalStock({{ $opname->id }})"
                                            placeholder="Catatan..."
                                            class="w-full rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @endif
                                </td>

                                <!-- Tombol Aksi Simpan Baris & Penyesuaian -->
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        @if(!$isPpic)
                                            <button type="button"
                                                wire:click="saveRowFinalStock({{ $opname->id }})"
                                                class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/40 transition cursor-pointer"
                                                title="Simpan Baris Ini">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        @endif

                                        @if(auth()->user() && auth()->user()->isWebAdmin() && $currFinal !== null && $calculatedDiff != 0 && $opname->adjustment_status !== 'approved')
                                            <button type="button"
                                                wire:click="openAdjustmentModal({{ $opname->id }})"
                                                title="Review dan lakukan penyesuaian selisih stok opname"
                                                class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/40 transition cursor-pointer">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            </button>
                                        @endif
                                    </div>

                                    @if (session()->has('row_saved_' . $opname->id))
                                        <span class="block text-[9px] font-semibold text-emerald-600 mt-0.5">Tersimpan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            @php
                                $hasActiveDailyFilters = !empty($search) || !empty($categoryId) || !empty($unitId) || !empty($statusFilter);
                            @endphp
                            <tr>
                                <td colspan="10" class="px-6 py-14 text-center">
                                    <div class="max-w-md mx-auto flex flex-col items-center justify-center text-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3">
                                            @if($hasActiveDailyFilters)
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            @else
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            @endif
                                        </div>
                                        @if($hasActiveDailyFilters)
                                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak ada item opname yang cocok dengan filter</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sesuaikan kata kunci pencarian, kategori, satuan, atau status selisih.</p>
                                            <div class="mt-4">
                                                <button type="button" wire:click="resetDailyFilters" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    <span>Reset Filter</span>
                                                </button>
                                            </div>
                                        @else
                                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Lembar Opname untuk Tanggal Ini</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Belum ada data barang tercatat pada tanggal {{ format_date($selectedDate) }} ({{ $selectedShift }}).</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links Tab 1 -->
            @if(method_exists($dailyOpnames, 'hasPages') && $dailyOpnames->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    {{ $dailyOpnames->links() }}
                </div>
            @endif
        </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: REKAP HARIAN                                                       -->
    <!-- ========================================================================= -->
    @elseif ($activeTab === 'recap')
        <!-- Recap Header KPI (Minimalist) -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Akumulasi Terpakai</span>
                <h3 class="mt-1 text-xl font-bold text-gray-900 dark:text-white font-mono">-{{ format_quantity($recapTotalUsed) }}</h3>
                <p class="mt-2 text-[11px] text-gray-500">Dalam periode filter terpilih</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Record Opname</span>
                <h3 class="mt-1 text-xl font-bold text-gray-900 dark:text-white font-mono">{{ number_format($recapTotalRecords) }}</h3>
                <p class="mt-2 text-[11px] text-gray-500">Baris entri riwayat</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tersinkronisasi Master</span>
                <h3 class="mt-1 text-xl font-bold text-gray-900 dark:text-white font-mono">{{ number_format($recapSyncedCount) }}</h3>
                <p class="mt-2 text-[11px] text-gray-500">Tutup buku telah dilakukan</p>
            </div>
        </div>

        <!-- Filter Bar Rekap -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs space-y-4">
            <!-- Row 1: Date Range -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300">Rentang Tanggal:</label>
                    <div wire:ignore class="relative inline-flex items-center" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                this.fp = flatpickr(this.$refs.dateInput, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-28 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer text-center',
                                    defaultDate: @js($recapStartDate),
                                    onChange: (dates, dateStr) => {
                                        if (dateStr && dateStr !== $wire.recapStartDate) {
                                            $wire.set('recapStartDate', dateStr);
                                        }
                                    }
                                });
                            };
                            this.$nextTick(() => initPicker());
                            this.$watch('$wire.recapStartDate', val => {
                                if (this.fp && val && val !== this.fp.input.value) {
                                    this.fp.setDate(val, false);
                                }
                            });
                        },
                        destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                    }">
                        <input x-ref="dateInput" type="text" class="hidden" value="{{ $recapStartDate }}">
                    </div>

                    <span class="text-xs text-gray-400">s/d</span>

                    <div wire:ignore class="relative inline-flex items-center" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                this.fp = flatpickr(this.$refs.dateInput, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-28 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer text-center',
                                    defaultDate: @js($recapEndDate),
                                    onChange: (dates, dateStr) => {
                                        if (dateStr && dateStr !== $wire.recapEndDate) {
                                            $wire.set('recapEndDate', dateStr);
                                        }
                                    }
                                });
                            };
                            this.$nextTick(() => initPicker());
                            this.$watch('$wire.recapEndDate', val => {
                                if (this.fp && val && val !== this.fp.input.value) {
                                    this.fp.setDate(val, false);
                                }
                            });
                        },
                        destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                    }">
                        <input x-ref="dateInput" type="text" class="hidden" value="{{ $recapEndDate }}">
                    </div>

                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="setRecapPreset('today')" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">Hari Ini</button>
                        <button type="button" wire:click="setRecapPreset('7days')" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">7 Hari</button>
                        <button type="button" wire:click="setRecapPreset('30days')" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-brand-600 text-white">30 Hari</button>
                        <button type="button" wire:click="setRecapPreset('this_month')" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">Bulan Ini</button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="resetRecapFilters" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Reset Filter
                    </button>
                    <a href="{{ route('stock-opnames.export', ['mode' => 'recap', 'start_date' => $recapStartDate, 'end_date' => $recapEndDate]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Excel Rekap
                    </a>
                </div>
            </div>

            <!-- Row 2: Search & Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
                <input type="text" wire:model.live.debounce.300ms="recapSearch" placeholder="Cari nama barang..."
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                <select wire:model.live="recapItemId" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua Barang</option>
                    @foreach ($allItems as $itm)
                        <option value="{{ $itm->id }}">{{ $itm->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="recapCategoryId" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="recapUnitId" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua Satuan</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->symbol }} ({{ $u->name }})</option>
                    @endforeach
                </select>

                <select wire:model.live="recapShift" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua Shift</option>
                    <option value="Shift 1">Shift 1</option>
                    <option value="Shift 2">Shift 2</option>
                </select>

                <select wire:model.live="recapStatus" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua Status Sinkron</option>
                    <option value="synced">Tersinkronisasi (Master)</option>
                    <option value="completed">Selesai (Sesuai)</option>
                    <option value="discrepancy">Ada Selisih</option>
                    <option value="pending">Pending Fisik</option>
                </select>

                <select wire:model.live="recapPerPage" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="15">15 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                </select>
            </div>
        </div>

        <!-- Tabel Rekap Riwayat Pemakaian Per Item -->
        <div class="relative rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                    <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-center w-28">Tanggal</th>
                            <th class="px-4 py-3 text-center w-24">Shift</th>
                            <th class="px-4 py-3">Kode & Nama Barang</th>
                            <th class="px-4 py-3 text-center w-24">Satuan</th>
                            <th class="px-4 py-3 text-right w-28">Stok Awal</th>
                            <th class="px-4 py-3 text-right w-28">Stok Akhir</th>
                            <th class="px-4 py-3 text-right w-28">Terpakai</th>
                            <th class="px-4 py-3 text-right w-28">Mutasi Keluar</th>
                            <th class="px-4 py-3 text-center w-24">Selisih</th>
                            <th class="px-4 py-3 text-center w-32">Status</th>
                            <th class="px-4 py-3">Petugas</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($recapData as $row)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                                <td class="px-4 py-3 text-center font-mono font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    {{ format_date($row->date) }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    {{ $row->shift ?? 'Shift 1' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ $row->item->name ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 font-mono">
                                        {{ $row->item->category->name ?? 'General' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $row->item->unit ?? 'PCS' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ format_quantity($row->initial_stock) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">
                                    {{ $row->final_stock !== null ? format_quantity($row->final_stock) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-gray-100">
                                    {{ $row->final_stock !== null ? '-' . format_quantity($row->used_qty) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono">
                                    {{ format_quantity($row->system_out_qty) }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono">
                                    @if ($row->final_stock !== null)
                                        @if (abs($row->difference) < 0.0001)
                                            <span class="text-gray-600 font-medium">0</span>
                                        @else
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ ($row->difference > 0 ? '+' : '') . format_quantity($row->difference) }}
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($row->status === 'synced')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                            Tersinkron
                                        </span>
                                    @elseif ($row->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700">
                                            Selesai
                                        </span>
                                    @elseif ($row->status === 'discrepancy')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                                            Ada Selisih
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[11px]">
                                    {{ $row->countedByUser->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-[11px] text-gray-500">
                                    {{ $row->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            @php
                                $hasActiveRecapFilters = !empty($recapSearch) || !empty($recapCategoryId) || !empty($recapUnitId) || !empty($recapStatus) || !empty($recapShift) || !empty($recapItemId);
                            @endphp
                            <tr>
                                <td colspan="12" class="px-6 py-14 text-center">
                                    <div class="max-w-md mx-auto flex flex-col items-center justify-center text-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak ada data rekap yang sesuai dengan filter</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba sesuaikan kata kunci, filter kategori/satuan, atau ubah rentang tanggal rekap.</p>
                                        <div class="mt-4">
                                            <button type="button" wire:click="resetRecapFilters" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reset Filter Rekap</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                {{ $recapData->links() }}
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL INPUT CEPAT SORE (MINIMALIST BULK OPNAME)                           -->
    <!-- ========================================================================= -->
    @if ($showBulkModal)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn"
            x-data
            @keydown.escape.window="$wire.closeBulkModal()"
            wire:click.self="closeBulkModal">
            <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden flex flex-col max-h-[82vh] my-auto">
                <!-- Modal Header -->
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50 dark:bg-gray-800/80">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                            Input Cepat Stok Fisik
                        </h3>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Tanggal: {{ format_date($selectedDate) }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeBulkModal"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto flex-1 max-h-[60vh] relative">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="sticky top-0 z-20 theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="pl-6 pr-3 py-2.5 w-12 text-center">No</th>
                                <th class="px-3 py-2.5">Nama Barang</th>
                                <th class="px-3 py-2.5 text-right w-24">Stok Awal</th>
                                <th class="px-3 py-2.5 text-right w-24">Mutasi Sistem</th>
                                <th class="pl-3 pr-6 py-2.5 text-center w-32">Input Fisik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($dailyOpnames as $idx => $op)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <td class="pl-6 pr-3 py-2 text-center font-mono text-gray-400">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $op->item->name }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $op->item->category->name ?? 'General' }} • {{ $op->item->unit }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono font-semibold text-gray-700 dark:text-gray-300">
                                        {{ format_quantity($initialStocks[$op->id] ?? $op->initial_stock) }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-rose-600 dark:text-rose-400">
                                        {{ $op->system_out_qty > 0 ? '-' . format_quantity($op->system_out_qty) : '0' }}
                                    </td>
                                    <td class="pl-3 pr-6 py-2 text-center">
                                        <input type="number" step="any" min="0"
                                            wire:model.defer="bulkInputs.{{ $op->id }}"
                                            placeholder="{{ format_quantity($initialStocks[$op->id] ?? $op->initial_stock) }}"
                                            class="w-24 text-center rounded-xl border border-gray-300 px-2 py-1 text-xs font-bold text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-gray-400">
                                        Tidak ada item yang dapat diinput.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50 dark:bg-gray-800/80">
                    <span class="text-[11px] text-gray-500 hidden sm:inline">
                        Tekan Tab untuk berpindah antar kolom input.
                    </span>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" wire:click="closeBulkModal"
                            class="px-4 py-2 rounded-xl border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button" wire:click="saveBulkModal"
                            class="btn-save-theme px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer shadow-xs">
                            Simpan Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL APPROVAL PENYESUAIAN STOK (ADMIN ONLY)                              -->
    <!-- ========================================================================= -->
    @if ($showAdjustmentModal && $selectedOpnameRecord)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn"
            x-data
            @keydown.escape.window="$wire.closeAdjustmentModal()"
            wire:click.self="closeAdjustmentModal">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl overflow-hidden flex flex-col my-auto">
                <!-- Header -->
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-amber-50/60 dark:bg-amber-950/20">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400 flex items-center justify-center font-bold text-sm shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                Approval Penyesuaian Stok Opname
                            </h3>
                            <p class="text-[11px] text-gray-500">
                                Otoritas Khusus Administrator Sistem
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeAdjustmentModal"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 space-y-4 text-xs">
                    <!-- Item Detail Box -->
                    <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ $selectedOpnameRecord->item->name ?? '-' }}</h4>
                                <span class="text-[11px] text-gray-500 font-mono">{{ $selectedOpnameRecord->item->category->name ?? 'Material' }} • Satuan: {{ $selectedOpnameRecord->item->unit ?? 'pcs' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                {{ format_date($selectedOpnameRecord->date) }} (Shift {{ $selectedOpnameRecord->shift }})
                            </span>
                        </div>

                        <div class="grid grid-cols-4 gap-2 pt-2 border-t border-gray-200 dark:border-gray-700 text-center">
                            <div class="p-1.5 rounded bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                <span class="text-[10px] text-gray-500 block">Stok Awal</span>
                                <span class="font-bold font-mono text-gray-900 dark:text-white">{{ format_quantity($selectedOpnameRecord->initial_stock) }}</span>
                            </div>
                            <div class="p-1.5 rounded bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                <span class="text-[10px] text-gray-500 block">Mutasi Keluar</span>
                                <span class="font-bold font-mono text-gray-900 dark:text-white">{{ format_quantity($selectedOpnameRecord->system_out_qty) }}</span>
                            </div>
                            <div class="p-1.5 rounded bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                <span class="text-[10px] text-gray-500 block">Fisik Terhitung</span>
                                <span class="font-bold font-mono text-brand-600 dark:text-brand-400">{{ format_quantity($selectedOpnameRecord->final_stock ?? 0) }}</span>
                            </div>
                            <div class="p-1.5 rounded bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800">
                                <span class="text-[10px] text-amber-700 dark:text-amber-400 block font-semibold">Selisih Fisik</span>
                                <span class="font-bold font-mono {{ $selectedOpnameRecord->difference < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $selectedOpnameRecord->difference > 0 ? '+' : '' }}{{ format_quantity($selectedOpnameRecord->difference) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Info -->
                    <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 text-blue-800 dark:text-blue-300 text-[11px] leading-relaxed">
                        <strong>Dampak Penyesuaian:</strong> Menyetujui penyesuaian akan memperbarui stok di Master Barang secara resmi ke angka fisik (<strong>{{ format_quantity($selectedOpnameRecord->final_stock ?? 0) }} {{ $selectedOpnameRecord->item->unit ?? '' }}</strong>) serta mencatat riwayat penyesuaian (Adjustment) di kartu stok WMS.
                    </div>

                    <!-- Input Notes -->
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Alasan / Catatan Penyesuaian <span class="text-rose-500">*</span>
                        </label>
                        <textarea wire:model="adjustmentNotes" rows="3"
                            placeholder="Contoh: Selisih disebabkan tumpah saat proses painting / selisih hitung fisik warehouse..."
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        @error('adjustmentNotes')
                            <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50 dark:bg-gray-800/80">
                    <button type="button" wire:click="rejectAdjustment"
                        class="px-3 py-1.5 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-400 dark:hover:bg-rose-950/30 text-xs font-semibold transition cursor-pointer">
                        Tolak & Minta Hitung Ulang
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeAdjustmentModal"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button" wire:click="approveAdjustment"
                            class="px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-xs font-bold text-white transition shadow-sm cursor-pointer">
                            Setujui & Terapkan Penyesuaian
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: KALKULATOR STICK KE KG UNTUK STOCK OPNAME -->
    <template x-teleport="body">
        <div x-show="showStickModal" x-cloak 
            class="fixed inset-0 z-[9999999] flex items-center justify-center p-3 sm:p-5 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn" 
            style="z-index: 9999999;"
            @click.self="showStickModal = false" 
            @keydown.escape.window="showStickModal = false" 
            role="dialog" 
            aria-modal="true"
            x-data="{
                stickCount: '',
                gramPerStick: 25,
                stickPerKg: 40,
                calcMode: 'gram',
                copied: false,
                get totalGrams() {
                    const s = parseFloat(this.stickCount) || 0;
                    if (this.calcMode === 'gram') {
                        const g = parseFloat(this.gramPerStick) || 0;
                        return s * g;
                    } else {
                        const spk = parseFloat(this.stickPerKg) || 0;
                        return spk > 0 ? (s / spk) * 1000 : 0;
                    }
                },
                get calculatedKg() {
                    return this.totalGrams > 0 ? (this.totalGrams / 1000).toFixed(3) : '0.000';
                },
                get formulaText() {
                    const s = parseFloat(this.stickCount) || 0;
                    if (this.calcMode === 'gram') {
                        const g = parseFloat(this.gramPerStick) || 0;
                        return s + ' stick × ' + g + ' g = ' + Math.round(this.totalGrams).toLocaleString('id-ID') + ' g (' + this.calculatedKg + ' kg)';
                    } else {
                        const spk = parseFloat(this.stickPerKg) || 0;
                        return s + ' stick ÷ ' + spk + ' stick/kg = ' + this.calculatedKg + ' kg';
                    }
                },
                addStick(n) {
                    this.stickCount = (parseFloat(this.stickCount) || 0) + n;
                },
                resetStick() {
                    this.stickCount = '';
                },
                setGramPreset(val) {
                    this.gramPerStick = val;
                    this.calcMode = 'gram';
                    this.stickPerKg = Math.round(1000 / val);
                },
                setStickPerKgPreset(val) {
                    this.stickPerKg = val;
                    this.calcMode = 'per_kg';
                    this.gramPerStick = Math.round((1000 / val) * 10) / 10;
                },
                copyResult() {
                    if (parseFloat(this.calculatedKg) <= 0) return;
                    navigator.clipboard.writeText(this.calculatedKg);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                },
                applyToTarget() {
                    const kgVal = parseFloat(this.calculatedKg);
                    if (kgVal > 0 && targetOpnameId) {
                        $wire.set('finalStocks.' + targetOpnameId, kgVal);
                        showStickModal = false;
                        targetOpnameId = null;
                    }
                }
            }">

            <div x-show="showStickModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden my-auto"
                @click.stop>
                
                <!-- Header -->
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-amber-50/60 dark:bg-amber-950/30">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white leading-tight">
                                Kalkulator Stick ke Kilogram (Kg)
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Konversi fisik kawat las / rod yang dihitung per stick menjadi kilogram (KG)
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showStickModal = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer" title="Tutup Kalkulator">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Target Item Info (if opened from table row) -->
                    <template x-if="targetItemName">
                        <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 flex items-center justify-between">
                            <div class="text-xs">
                                <span class="font-bold text-gray-600 dark:text-gray-400 block text-[10px] uppercase">Target Barang:</span>
                                <span class="font-extrabold text-amber-900 dark:text-amber-300" x-text="targetItemName"></span>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200">
                                Baris Aktif
                            </span>
                        </div>
                    </template>

                    <!-- Method Tabs -->
                    <div class="flex items-center p-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                        <button type="button" 
                                @click="calcMode = 'gram'" 
                                class="flex-1 py-1.5 px-3 text-xs font-bold rounded-lg transition cursor-pointer text-center"
                                :class="calcMode === 'gram' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'">
                            Opsi A: Berat per Stick (Gram)
                        </button>
                        <button type="button" 
                                @click="calcMode = 'per_kg'" 
                                class="flex-1 py-1.5 px-3 text-xs font-bold rounded-lg transition cursor-pointer text-center"
                                :class="calcMode === 'per_kg' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'">
                            Opsi B: Stick per 1 Kg
                        </button>
                    </div>

                    <!-- Input 1: Jumlah Stick Diambil / Dihitung -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Jumlah Stick / Batang Hasil Hitung Fisik <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-mono">Misal: 15, 30, 75 stick</span>
                        </div>
                        <div class="relative">
                            <input type="number" 
                                   x-model="stickCount" 
                                   min="1" 
                                   step="any" 
                                   placeholder="Ketik jumlah stick..." 
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
                    <div x-show="calcMode === 'gram'" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Berat 1 Stick (Gram) <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-mono" x-show="stickPerKg > 0" x-text="'≈ ' + stickPerKg + ' stick per kg'"></span>
                        </div>
                        <div class="relative">
                            <input type="number" 
                                   x-model="gramPerStick" 
                                   @input="stickPerKg = $event.target.value > 0 ? Math.round(1000 / $event.target.value) : ''"
                                   min="0.1" 
                                   step="any" 
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-bold font-mono text-gray-900 dark:text-white outline-none focus:border-amber-500 shadow-2xs">
                            <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">Gram / Stick</span>
                        </div>
                        <!-- Preset Buttons -->
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 block mb-1">Preset Standar Kawat Las:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="setGramPreset(20)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                        :class="Number(gramPerStick) === 20 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                    20g (Ø 2.0-2.6mm)
                                </button>
                                <button type="button" @click="setGramPreset(25)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                        :class="Number(gramPerStick) === 25 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                    25g (Ø 2.6mm Std)
                                </button>
                                <button type="button" @click="setGramPreset(35)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                        :class="Number(gramPerStick) === 35 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                    35g (Ø 3.2mm Std)
                                </button>
                                <button type="button" @click="setGramPreset(40)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                        :class="Number(gramPerStick) === 40 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                    40g (Ø 3.2mm Berat)
                                </button>
                                <button type="button" @click="setGramPreset(50)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                        :class="Number(gramPerStick) === 50 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                    50g (Ø 4.0mm)
                                </button>
                                <button type="button" @click="setGramPreset(65)" class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer"
                                        :class="Number(gramPerStick) === 65 ? 'bg-amber-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'">
                                    65g (Ø 5.0mm)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Input 2: Stick per kg (Mode Stick per 1 Kg) -->
                    <div x-show="calcMode === 'per_kg'" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Isi Stick per 1 Kilogram (Kg) <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-mono" x-show="gramPerStick > 0" x-text="'≈ ' + gramPerStick + ' gram per stick'"></span>
                        </div>
                        <div class="relative">
                            <input type="number" 
                                   x-model="stickPerKg" 
                                   @input="gramPerStick = $event.target.value > 0 ? (1000 / $event.target.value).toFixed(1) : ''"
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

                    <!-- Result Card -->
                    <div class="rounded-xl border border-amber-200 dark:border-amber-800/80 bg-amber-50/70 dark:bg-amber-950/40 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 block">
                                    Hasil Konversi Satuan:
                                </span>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl sm:text-3xl font-black font-mono text-gray-900 dark:text-white" x-text="calculatedKg"></span>
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
                        <span x-text="copied ? '✓ Tersalin!' : 'Salin Nilai'"></span>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="showStickModal = false" 
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 transition cursor-pointer">
                            Tutup
                        </button>
                        <template x-if="targetOpnameId">
                            <button type="button" 
                                    @click="applyToTarget()" 
                                    :disabled="parseFloat(calculatedKg) <= 0"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed px-4 py-2 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Terapkan ke Stok Fisik</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

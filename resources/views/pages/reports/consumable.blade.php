@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- Page Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard & Laporan Pemakaian Consumable</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Monitoring pengeluaran barang consumable, dipisah per blok material, grafik 31 hari, serta rekapitulasi kesimpulan otomatis.</p>
        </div>
    </div>

    <!-- Print Only Header -->
    <div class="hidden print:block mb-6 border-b border-gray-300 pb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">PT MUDALAYA ENERGY INDONESIA</h1>
                <h2 class="text-lg font-bold text-gray-700">LAPORAN PEMAKAIAN MATERIAL CONSUMABLE</h2>
                <p class="text-xs text-gray-500">Periode: {{ format_date($startDate) }} s/d {{ format_date($endDate) }} ({{ $daysSpan }} Hari)</p>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>Tanggal Cetak: {{ format_datetime(now()) }}</p>
                <p>Dicetak Oleh: {{ auth()->user()->name ?? 'Administrator WMS' }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Action Card -->
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 print:hidden space-y-4">
        <form method="GET" action="{{ route('reports.consumable') }}" id="consumableFilterForm" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <!-- Tanggal Mulai s/d Sampai -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Periode Filter Tanggal (31 Hari)</label>
                    <div class="flex items-center gap-2">
                        <div class="relative w-full" x-data="{
                            fp: null,
                            init() {
                                const initPicker = () => {
                                    if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                    this.fp = flatpickr(this.$refs.picker, {
                                        dateFormat: 'Y-m-d',
                                        altInput: true,
                                        altFormat: 'd/m/Y',
                                        altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition',
                                        defaultDate: '{{ $startDate }}'
                                    });
                                };
                                this.$nextTick(() => initPicker());
                            },
                            destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                        }">
                            <input x-ref="picker" type="text" name="start_date" class="hidden" value="{{ $startDate }}">
                        </div>

                        <span class="text-xs font-bold text-gray-400">s/d</span>

                        <div class="relative w-full" x-data="{
                            fp: null,
                            init() {
                                const initPicker = () => {
                                    if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                    this.fp = flatpickr(this.$refs.picker, {
                                        dateFormat: 'Y-m-d',
                                        altInput: true,
                                        altFormat: 'd/m/Y',
                                        altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition',
                                        defaultDate: '{{ $endDate }}'
                                    });
                                };
                                this.$nextTick(() => initPicker());
                            },
                            destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                        }">
                            <input x-ref="picker" type="text" name="end_date" class="hidden" value="{{ $endDate }}">
                        </div>
                    </div>
                </div>

                <!-- Multi-Select Filter Item -->
                <div x-data="{ 
                    open: false, 
                    search: '', 
                    count: 0,
                    init() {
                        this.updateCount();
                    },
                    updateCount() {
                        this.count = document.querySelectorAll('input[name=\'item_ids[]\']:checked').length;
                    },
                    toggle() {
                        this.open = !this.open;
                    },
                    close() {
                        this.open = false;
                    },
                    selectAll() {
                        document.querySelectorAll('input[name=\'item_ids[]\']').forEach(cb => {
                            cb.checked = true;
                            cb.defaultChecked = true;
                            cb.setAttribute('checked', 'checked');
                        });
                        this.updateCount();
                    },
                    clearAll() {
                        document.querySelectorAll('input[name=\'item_ids[]\']').forEach(cb => {
                            cb.checked = false;
                            cb.defaultChecked = false;
                            cb.removeAttribute('checked');
                        });
                        this.updateCount();
                    }
                }" @click.outside="close()" class="relative">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 flex items-center justify-between">
                        <span>Pilih Barang (Bisa >1)</span>
                        <template x-if="count > 0">
                            <span class="text-[10px] font-extrabold text-brand-600 dark:text-brand-400 font-mono" x-text="count + ' Item'"></span>
                        </template>
                    </label>
                    
                    <button type="button" @click.stop="toggle()" class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer text-left shadow-xs">
                        <span class="truncate pr-2" x-text="count === 0 ? '-- Semua Barang --' : count + ' Item Terpilih'"></span>
                        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" x-cloak x-transition class="absolute z-50 mt-1.5 w-72 rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-800 space-y-2.5 right-0 sm:right-auto">
                        <input type="text" x-model="search" placeholder="Cari nama barang..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-1.5 text-[11px] px-1">
                            <button type="button" @click="selectAll()" class="text-brand-600 dark:text-brand-400 font-extrabold hover:underline">Pilih Semua</button>
                            <button type="button" @click="clearAll()" class="text-rose-500 font-extrabold hover:underline">Hapus Pilihan</button>
                        </div>

                        <div class="max-h-52 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                            @foreach($itemsList as $itm)
                                <label x-show="search === '' || {{ json_encode(strtolower($itm->name)) }}.includes(search.toLowerCase())" class="flex items-center gap-2.5 rounded-xl px-2.5 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700/60 cursor-pointer text-xs transition">
                                    <input type="checkbox" name="item_ids[]" value="{{ $itm->id }}" @change="updateCount()" {{ in_array((string)$itm->id, array_map('strval', $selectedItemIds ?? [])) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:bg-gray-900 dark:border-gray-700">
                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $itm->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Filter Project -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Nama Project</label>
                    <select name="project" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua Project --</option>
                        @foreach($projectsList as $prj)
                            <option value="{{ $prj }}" {{ request('project') == $prj ? 'selected' : '' }}>{{ $prj }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter PIC -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Nama PIC</label>
                    <select name="pic" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua PIC --</option>
                        @foreach($picsList as $p)
                            <option value="{{ $p }}" {{ request('pic') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.consumable', ['start_date' => \Carbon\Carbon::parse($latestDateStr)->subDays(30)->format('Y-m-d'), 'end_date' => $latestDateStr]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-extrabold text-brand-700 hover:bg-brand-100 dark:border-brand-900/60 dark:bg-brand-950/40 dark:text-brand-300 transition">
                        <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>31 Hari Terakhir</span>
                    </a>
                    <a href="{{ route('reports.consumable', ['start_date' => \Carbon\Carbon::parse($latestDateStr)->subDays(7)->format('Y-m-d'), 'end_date' => $latestDateStr]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>7 Hari Terakhir</span>
                    </a>
                    <a href="{{ route('reports.consumable') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 transition">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset Filter</span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gray-800 px-4 py-2 text-xs font-bold text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 shadow-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cari
                    </button>
                    <!-- EXPORT EXCEL BUTTON -->
                    <a href="{{ route('reports.consumable.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Excel (.xlsx)
                    </a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
        <!-- Metric 1: Total Kuantitas -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kuantitas Terpakai</span>
                    <h3 class="mt-2 text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">-{{ format_quantity($totalQuantity) }}</h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Dalam periode {{ $daysSpan }} hari terfilter</p>
        </div>

        <!-- Metric 2: Total Transaksi -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Transaksi Keluar</span>
                    <h3 class="mt-2 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalTransactions) }}</h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Catatan mutasi material keluar</p>
        </div>

        <!-- Metric 3: Total Project -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Project Terkait</span>
                    <h3 class="mt-2 text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($totalProjectsCount) }}</h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Project aktif penerima material</p>
        </div>

        <!-- Metric 4: Total PIC -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PIC Person In Charge</span>
                    <h3 class="mt-2 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalPicsCount) }}</h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Orang yang bertanggung jawab</p>
        </div>
    </div>

    <!-- TABEL UTAMA: PENGELUARAN DENGAN BLOK/TABEL TERPISAH PER JENIS MATERIAL (MATCHING USER EXCEL DESIGN) -->
    <div class="space-y-6 pt-2">
        @forelse ($groupedLogs as $itemId => $itemLogs)
            @php
                $itemObj = $itemLogs->first()->item;
                $itemName = $itemObj ? $itemObj->name : ('Item #' . $itemId);
                $unit = $itemLogs->first()->unit ?? ($itemObj ? $itemObj->unit : 'PCS');
                $subtotalQty = $itemLogs->sum('quantity');
            @endphp

            <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden print:border print:border-gray-300 print:shadow-none">
                <!-- Blok Header Material Name -->
                <div class="bg-gray-50/90 dark:bg-gray-800/90 px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-2.5 w-2.5 rounded-full bg-brand-600 ring-4 ring-brand-600/20"></span>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wide">
                            {{ $itemName }}
                        </h3>
                    </div>
                    <span class="text-xs font-black text-rose-600 dark:text-rose-400 font-mono bg-white dark:bg-gray-900 px-3 py-1 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xs">
                        Subtotal: {{ format_quantity($subtotalQty) }} {{ strtoupper($unit) }} ({{ $itemLogs->count() }} Transaksi)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="theme-table-header">
                            <tr class="border-b border-gray-200 text-white font-extrabold uppercase print:bg-gray-200 print:text-black">
                                <th scope="col" class="px-4 py-2.5 text-center w-12 border-r border-white/10">NO</th>
                                <th scope="col" class="px-4 py-2.5 min-w-[100px] border-r border-white/10">TANGGAL</th>
                                <th scope="col" class="px-4 py-2.5 min-w-[120px] border-r border-white/10">KODE MATERIAL</th>
                                <th scope="col" class="px-4 py-2.5 min-w-[200px] border-r border-white/10">NAMA BARANG</th>
                                <th scope="col" class="px-4 py-2.5 text-center w-24 border-r border-white/10">JUMLAH</th>
                                <th scope="col" class="px-4 py-2.5 text-center w-24 border-r border-white/10">SATUAN</th>
                                <th scope="col" class="px-4 py-2.5 min-w-[240px] border-r border-white/10">PROJECT</th>
                                <th scope="col" class="px-4 py-2.5 min-w-[120px]">PIC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($itemLogs as $idx => $row)
                                @php
                                    $prjId = $row->project_id ?? \App\Models\Project::where('name', $row->project)->value('id');
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition dark:hover:bg-gray-800/50">
                                    <!-- NO -->
                                    <td class="px-4 py-2 text-center font-semibold text-gray-600 dark:text-gray-400 border-r border-gray-100 dark:border-gray-800">
                                        {{ $idx + 1 }}
                                    </td>
                                    <!-- TANGGAL -->
                                    <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap border-r border-gray-100 dark:border-gray-800">
                                        {{ format_date($row->date) }}
                                    </td>
                                    <!-- KODE MATERIAL -->
                                    <td class="px-4 py-2 whitespace-nowrap border-r border-gray-100 dark:border-gray-800 font-mono text-xs">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 font-bold text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                            {{ $row->item->code_item ?? '-' }}
                                        </span>
                                    </td>
                                    <!-- NAMA BARANG -->
                                    <td class="px-4 py-2 font-bold text-gray-900 dark:text-white uppercase border-r border-gray-100 dark:border-gray-800">
                                        {{ $row->item->name ?? '-' }}
                                    </td>
                                    <!-- JUMLAH -->
                                    <td class="px-4 py-2 text-center font-extrabold text-gray-900 dark:text-white text-xs border-r border-gray-100 dark:border-gray-800">
                                        {{ format_quantity($row->quantity) }}
                                    </td>
                                    <!-- SATUAN -->
                                    <td class="px-4 py-2 text-center font-bold uppercase text-gray-700 dark:text-gray-300 border-r border-gray-100 dark:border-gray-800">
                                        {{ strtoupper($row->unit) }}
                                    </td>
                                    <!-- PROJECT -->
                                    <td class="px-4 py-2 font-semibold text-gray-900 dark:text-white border-r border-gray-100 dark:border-gray-800">
                                        @if($prjId)
                                            <a href="{{ route('projects.show', $prjId) }}" class="font-bold text-gray-900 hover:underline dark:text-white">
                                                {{ $row->project }}
                                            </a>
                                        @else
                                            {{ $row->project }}
                                        @endif
                                    </td>
                                    <!-- PIC -->
                                    <td class="px-4 py-2 font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $row->pic }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-gray-400 dark:border-gray-800 dark:bg-gray-900">
                Tidak ada data pengeluaran material consumable pada periode terfilter.
            </div>
        @endforelse
    </div>

    <!-- GRAFIK PEMAKAIAN CONSUMABLE BULANAN (31 HARI) -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-xs space-y-4 min-w-0 overflow-hidden print:break-inside-avoid pt-4">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Penggunaan Consumable Las dalam 31 Hari</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Grafik batang akumulasi total pengeluaran per material consumable</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-extrabold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/50 px-2.5 py-1 rounded-lg border border-brand-200 dark:border-brand-800">
                    Akumulasi 31 Hari
                </span>
            </div>
        </div>

        <div class="w-full min-w-0 overflow-hidden">
            <div id="chartConsumableMonthly" class="min-h-[300px] w-full max-w-full"></div>
        </div>
    </div>

    <!-- KESIMPULAN / RATA PENGGUNAAN CONSUMABLE (EXACT MATCHING USER LAYOUT) -->
    <div class="rounded-2xl border-2 border-gray-900 bg-white p-6 dark:border-gray-100 dark:bg-gray-900 shadow-sm space-y-4 print:break-inside-avoid">
        <div class="border-b border-gray-300 dark:border-gray-700 pb-2">
            <h3 class="text-base font-black uppercase text-gray-900 dark:text-white tracking-wider">KESIMPULAN</h3>
            <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Rata penggunaan / pemakaian consumable</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-gray-900 dark:border-gray-100">
                <thead class="theme-table-header">
                    <tr class="border-b border-gray-900 font-black text-white uppercase">
                        <th scope="col" class="px-4 py-2.5 border-r border-gray-900 dark:border-gray-100 min-w-[200px]">ITEM</th>
                        <th scope="col" class="px-4 py-2.5 border-r border-gray-900 dark:border-gray-100 text-center min-w-[120px]">QTY</th>
                        <th scope="col" class="px-4 py-2.5 border-r border-gray-900 dark:border-gray-100 text-center min-w-[100px]">WAKTU</th>
                        <th scope="col" class="px-4 py-2.5 min-w-[300px]">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-900 dark:divide-gray-100">
                    @forelse ($summaryTableData as $sumRow)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <!-- ITEM -->
                            <td class="px-4 py-2.5 font-bold text-gray-900 dark:text-white border-r border-gray-900 dark:border-gray-100">
                                {{ $sumRow['item_name'] }}
                            </td>
                            <!-- QTY -->
                            <td class="px-4 py-2.5 text-center font-extrabold text-gray-900 dark:text-white border-r border-gray-900 dark:border-gray-100">
                                {{ format_quantity($sumRow['total_qty']) }} {{ strtoupper($sumRow['unit']) }}
                            </td>
                            <!-- WAKTU -->
                            <td class="px-4 py-2.5 text-center font-bold text-gray-900 dark:text-white border-r border-gray-900 dark:border-gray-100 uppercase">
                                {{ $sumRow['time_span'] }}
                            </td>
                            <!-- KETERANGAN -->
                            <td class="px-4 py-2.5 text-xs text-gray-800 dark:text-gray-200">
                                {{ $sumRow['keterangan'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                Belum ada data kesimpulan ringkasan untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- APEXCHARTS JS SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function() {
    function initConsumableChart() {
        const isDarkMode = document.documentElement.classList.contains("dark");
        const textColor = isDarkMode ? "#9CA3AF" : "#4B5563";
        const gridColor = isDarkMode ? "#374151" : "#E5E7EB";

        const chartColors = [
            "#4F46E5", "#6366F1", "#3B82F6", "#2563EB", "#1D4ED8", "#4338CA", "#3730A3", "#1E40AF"
        ];

        const consumableChartOptions = {
            series: [{
                name: "Total Consumable Terpakai",
                data: @json($chartTotals)
            }],
            chart: {
                type: "bar",
                height: 320,
                fontFamily: "Outfit, sans-serif",
                redrawOnParentResize: true,
                toolbar: {
                    show: true,
                    offsetX: -15,
                    offsetY: 0,
                    tools: {
                        download: true,
                        selection: false,
                        zoom: false,
                        zoomin: false,
                        zoomout: false,
                        pan: false,
                        reset: false
                    }
                }
            },
            colors: chartColors,
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: "40%",
                    distributed: true,
                    dataLabels: { position: 'center' }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return val > 0 ? numberFormat(val) : ''; },
                style: {
                    fontSize: '11px',
                    fontFamily: 'Outfit, sans-serif',
                    fontWeight: 'bold',
                    colors: ['#FFFFFF']
                },
                dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000000', opacity: 0.7 }
            },
            xaxis: {
                categories: @json($chartShortNames),
                axisBorder: { show: true, color: gridColor },
                axisTicks: { show: true, color: gridColor },
                labels: {
                    style: { colors: textColor, fontSize: "10px", fontWeight: 700 },
                    rotate: 0,
                    rotateAlways: false,
                    hideOverlappingLabels: true
                }
            },
            yaxis: {
                min: 0,
                labels: { style: { colors: textColor, fontSize: "11px" } }
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 3,
                padding: { left: 10, right: 15 }
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '10px',
                fontFamily: 'Outfit, sans-serif',
                fontWeight: 500,
                labels: { colors: textColor },
                markers: { width: 10, height: 10, radius: 3 },
                itemMargin: { horizontal: 6, vertical: 2 }
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light',
                x: {
                    formatter: function(val, opts) {
                        const fullNames = @json($chartNames);
                        const idx = opts.dataPointIndex;
                        return fullNames[idx] || val;
                    }
                },
                y: { formatter: (val) => numberFormat(val) + " Unit" }
            }
        };

        function numberFormat(num) {
            return new Intl.NumberFormat().format(num);
        }

        const chartEl = document.querySelector("#chartConsumableMonthly");
        if (chartEl && typeof ApexCharts !== 'undefined') {
            chartEl.innerHTML = '';
            new ApexCharts(chartEl, consumableChartOptions).render();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConsumableChart);
    } else {
        initConsumableChart();
    }
})();
</script>
@endsection

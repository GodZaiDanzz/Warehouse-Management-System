@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Inspeksi Incoming Material & Cat</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pemeriksaan fisik barang masuk gudang, approval QC (OK / Reject), dan pelacakan tanggal pembuatan cat.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reports.incoming-inspection.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>
            <a href="{{ route('reports.incoming-inspection.pdf', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-rose-700 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Download PDF
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 print:hidden">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kedatangan</span>
            <h3 class="mt-2 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalCount) }}</h3>
            <p class="mt-1 text-xs text-gray-400">Transaksi kedatangan</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">QC Passed</span>
            <h3 class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($totalApproved) }}</h3>
            <p class="mt-1 text-xs text-gray-400">Masuk stok aktif</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Ditolak (Reject)</span>
            <h3 class="mt-2 text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($totalRejected) }}</h3>
            <p class="mt-1 text-xs text-gray-400">Retur / Tidak layak</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Menunggu QC</span>
            <h3 class="mt-2 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalPending) }}</h3>
            <p class="mt-1 text-xs text-gray-400">Belum di-approve</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Total Kuantitas</span>
            <h3 class="mt-2 text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">+{{ format_quantity($totalQty) }}</h3>
            <p class="mt-1 text-xs text-gray-400">Fisik diterima</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 print:hidden space-y-4">
        <form method="GET" action="{{ route('reports.incoming-inspection') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <!-- Periode Tanggal -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Periode Tanggal Terima</label>
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

                <!-- Status QC Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status Quality Control</label>
                    <select name="qc_status" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="all" {{ $selectedQc == 'all' ? 'selected' : '' }}>-- Semua Status QC --</option>
                        <option value="approved" {{ $selectedQc == 'approved' ? 'selected' : '' }}>QC Passed</option>
                        <option value="rejected" {{ $selectedQc == 'rejected' ? 'selected' : '' }}>Reject / Ditolak</option>
                        <option value="pending_qc" {{ $selectedQc == 'pending_qc' ? 'selected' : '' }}>Menunggu QC</option>
                    </select>
                </div>

                <!-- Filter Kategori -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Kategori</label>
                    <select name="category" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="all" {{ $selectedCategory == 'all' ? 'selected' : '' }}>-- Semua Kategori --</option>
                        <option value="cat" {{ $selectedCategory == 'cat' ? 'selected' : '' }}>Khusus Semua Cat / Painting</option>
                        @foreach($categoriesList ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ ((string)$selectedCategory === (string)$cat->id || $selectedCategory === $cat->name) ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Supplier Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Supplier / Vendor</label>
                    <select name="supplier" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua Supplier --</option>
                        @foreach($suppliersList as $sup)
                            <option value="{{ $sup }}" {{ $selectedSupplier == $sup ? 'selected' : '' }}>{{ $sup }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Search & Actions -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                <div class="flex-1 min-w-[240px] max-w-md">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, kode, no PO, supplier..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-save-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer shadow-xs">
                        Cari & Terapkan
                    </button>
                    <a href="{{ route('reports.incoming-inspection') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden print:border print:border-gray-300 print:shadow-none">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 rounded-full bg-brand-500 ring-4 ring-brand-500/20"></span>
                <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wide">
                    Log Inspeksi Fisik Kedatangan Material & Status QC
                </h3>
            </div>
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-mono">
                Menampilkan {{ $logs->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="theme-table-header">
                    <tr class="border-b border-gray-200 text-white font-extrabold uppercase print:bg-gray-200 print:text-black">
                        <th scope="col" class="px-4 py-3 text-center w-12 border-r border-white/10">NO</th>
                        <th scope="col" class="px-4 py-3 min-w-[100px] border-r border-white/10">TANGGAL</th>
                        <!-- <th scope="col" class="px-4 py-3 min-w-[120px] border-r border-white/10">NO PO</th> -->
                        <th scope="col" class="px-4 py-3 min-w-[150px] border-r border-white/10">SUPPLIER</th>
                        <th scope="col" class="px-4 py-3 min-w-[200px] border-r border-white/10">BARANG / MATERIAL</th>
                        <th scope="col" class="px-4 py-3 text-center w-28 border-r border-white/10">QTY FISIK</th>
                        <th scope="col" class="px-4 py-3 text-center min-w-[120px] border-r border-white/10">TGL BUAT (CAT)</th>
                        <th scope="col" class="px-4 py-3 text-center min-w-[120px] border-r border-white/10">STATUS QC</th>
                        <th scope="col" class="px-4 py-3 min-w-[140px] border-r border-white/10">QC INSPECTOR</th>
                        <th scope="col" class="px-4 py-3 min-w-[160px]">CATATAN INSPEKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $index => $row)
                        @php
                            $mfgDate = ($row->manufacturing_date) 
                                ? format_date($row->manufacturing_date) 
                                : ($row->item && $row->item->isCat() && $row->item->manufacturing_date ? format_date($row->item->manufacturing_date) : '-');
                            $isCat = $row->item && $row->item->isCat();
                            $isConsumable = $row->item && $row->item->isConsumable();
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition dark:hover:bg-gray-800/50">
                            <td class="px-4 py-2.5 text-center font-semibold text-gray-600 dark:text-gray-400 border-r border-gray-100 dark:border-gray-800">
                                {{ $logs->firstItem() ? $logs->firstItem() + $index : $index + 1 }}
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap border-r border-gray-100 dark:border-gray-800">
                                {{ format_date($row->date) }}
                            </td>
                            <!-- <td class="px-4 py-2.5 font-mono text-gray-700 dark:text-gray-300 border-r border-gray-100 dark:border-gray-800">
                                {{ $row->po_number ?? '-' }}
                            </td> -->
                            <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white border-r border-gray-100 dark:border-gray-800">
                                {{ $row->supplier }}
                            </td>
                            <td class="px-4 py-2.5 border-r border-gray-100 dark:border-gray-800">
                                <div class="font-bold text-gray-900 dark:text-white uppercase">{{ $row->item->name ?? '-' }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold {{ $isCat ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $row->item->category->name ?? 'Consumable' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-center border-r border-gray-100 dark:border-gray-800">
                                <span class="font-black text-gray-900 dark:text-white text-xs">+{{ format_quantity($row->quantity) }} {{ strtoupper($row->unit) }}</span>
                                @if($row->wholesale_qty && $row->wholesale_unit)
                                    <div class="text-[10px] font-bold text-brand-600 dark:text-brand-400">
                                        ({{ format_quantity($row->wholesale_qty) }} {{ $row->wholesale_unit }})
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center border-r border-gray-100 dark:border-gray-800 font-mono">
                                @if($mfgDate !== '-')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-brand-50 text-brand-700 border border-brand-200 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800">
                                        {{ $mfgDate }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center border-r border-gray-100 dark:border-gray-800">
                                @if($isConsumable)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800" title="Bebas QC: Otomatis Langsung Masuk Stok">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Bebas QC
                                    </span>
                                @elseif($row->qc_status === 'approved')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        QC Passed
                                    </span>
                                @elseif($row->qc_status === 'rejected')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-extrabold text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Reject
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-extrabold text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                        <svg class="h-3 w-3 animate-spin text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Pending QC
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-700 dark:text-gray-300 border-r border-gray-100 dark:border-gray-800">
                                <div class="font-bold">{{ $isConsumable ? 'Bebas QC (Sistem)' : ($row->qcUser->name ?? ($row->qc_by ? 'QC' : '-')) }}</div>
                                @if($row->qc_at && !$isConsumable)
                                    <div class="text-[10px] text-gray-400">{{ format_datetime($row->qc_at) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">
                                {{ $isConsumable ? 'Bebas QC (Consumable langsung masuk stok)' : ($row->qc_notes ?? $row->notes ?? '-') }}
                            </td>
                        </tr>
                    @empty
                        @php
                            $hasActiveFilters = request('search') || request('start_date') || request('end_date') || ($selectedQc && $selectedQc !== 'all') || ($selectedCategory && $selectedCategory !== 'all') || !empty($selectedSupplier);
                        @endphp
                        <tr>
                            <td colspan="10" class="py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center justify-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3.5">
                                        @if($hasActiveFilters)
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @else
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </div>
                                    @if($hasActiveFilters)
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada data inspeksi yang cocok</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tidak ada riwayat inspeksi QC yang sesuai dengan periode atau filter yang dipilih.</p>
                                        <a href="{{ route('reports.incoming-inspection') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                                            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Reset Filter
                                        </a>
                                    @else
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Belum ada riwayat inspeksi</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Belum ada data penerimaan material masuk dan inspeksi QC yang tercatat.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 print:hidden">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

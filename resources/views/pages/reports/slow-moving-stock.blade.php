@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analisis Barang Lambat Bergerak & Dead Stock</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Klasifikasi perputaran material gudang berdasarkan hari tidak aktif mutasi keluar untuk mitigasi penumpukan stok & risiko kadaluarsa.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reports.slow-moving-stock.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>
            <a href="{{ route('reports.slow-moving-stock.pdf', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-rose-700 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Download PDF
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
        <!-- Dead Stock Card -->
        <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5 dark:border-rose-900/50 dark:bg-rose-950/20 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Dead Stock (≥ {{ $deadStockThreshold ?? 180 }} Hari)</span>
                    <h3 class="mt-2 text-2xl font-black text-rose-700 dark:text-rose-300 font-mono">{{ number_format($kpi['dead_stock_count']) }} Item</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-rose-600 dark:text-rose-400 font-semibold font-mono">
                Total Mengendap: {{ format_quantity($kpi['dead_stock_units']) }} Unit
            </p>
        </div>

        <!-- Slow Moving Card -->
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Slow Moving ({{ ($slowMovingThreshold ?? 90) + 1 }} - {{ $deadStockThreshold ?? 180 }} Hari)</span>
                    <h3 class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-300 font-mono">{{ number_format($kpi['slow_moving_count']) }} Item</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-amber-600 dark:text-amber-400 font-semibold font-mono">
                Total Mengendap: {{ format_quantity($kpi['slow_moving_units']) }} Unit
            </p>
        </div>

        <!-- Medium Moving Card -->
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-900/40 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-400">Medium Moving (31 - 90 Hari)</span>
                    <h3 class="mt-2 text-2xl font-black text-slate-800 dark:text-slate-200 font-mono">{{ number_format($kpi['medium_moving_count']) }} Item</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-200/80 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-600 dark:text-slate-400 font-semibold font-mono">
                Total Stok: {{ format_quantity($kpi['medium_moving_units']) }} Unit
            </p>
        </div>

        <!-- Fast Moving Card -->
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 dark:border-emerald-900/50 dark:bg-emerald-950/20 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Fast Moving (≤ 30 Hari)</span>
                    <h3 class="mt-2 text-2xl font-black text-emerald-700 dark:text-emerald-300 font-mono">{{ number_format($kpi['fast_moving_count']) }} Item</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-emerald-600 dark:text-emerald-400 font-semibold font-mono">
                Total Stok: {{ format_quantity($kpi['fast_moving_units']) }} Unit
            </p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 print:hidden">
        <form method="GET" action="{{ route('reports.slow-moving-stock') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Pencarian Barang</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau kode barang..."
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Kategori</label>
                <select name="category_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$selectedCategory === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Klasifikasi -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status Klasifikasi</label>
                <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="dead_stock" {{ $selectedStatus === 'dead_stock' ? 'selected' : '' }}>Dead Stock (> 180 Hari)</option>
                    <option value="slow_moving" {{ $selectedStatus === 'slow_moving' ? 'selected' : '' }}>Slow Moving (91-180 Hari)</option>
                    <option value="medium_moving" {{ $selectedStatus === 'medium_moving' ? 'selected' : '' }}>Medium Moving (31-90 Hari)</option>
                    <option value="fast_moving" {{ $selectedStatus === 'fast_moving' ? 'selected' : '' }}>Fast Moving (≤ 30 Hari)</option>
                    <option value="out_of_stock" {{ $selectedStatus === 'out_of_stock' ? 'selected' : '' }}>Stok Kosong</option>
                </select>
            </div>

            <!-- Urutan Data -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Urutan Data</label>
                <select name="sort_by" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                    <option value="days_desc" {{ $sortBy === 'days_desc' ? 'selected' : '' }}>Inaktif Terlama</option>
                    <option value="days_asc" {{ $sortBy === 'days_asc' ? 'selected' : '' }}>Inaktif Tercepat</option>
                    <option value="stock_desc" {{ $sortBy === 'stock_desc' ? 'selected' : '' }}>Stok Terbanyak</option>
                    <option value="stock_asc" {{ $sortBy === 'stock_asc' ? 'selected' : '' }}>Stok Tersedikit</option>
                    <option value="name_asc" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                </select>
            </div>

            <!-- Maksimal Baris & Tombol Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Baris Data</label>
                <div class="flex items-center gap-1.5">
                    <select name="per_page" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                        <option value="10" {{ (int)$perPage === 10 ? 'selected' : '' }}>10 / hal</option>
                        <option value="25" {{ (int)$perPage === 25 ? 'selected' : '' }}>25 / hal</option>
                        <option value="50" {{ (int)$perPage === 50 ? 'selected' : '' }}>50 / hal</option>
                        <option value="100" {{ (int)$perPage === 100 ? 'selected' : '' }}>100 / hal</option>
                    </select>
                    <button type="submit" class="btn-save-theme rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer shrink-0 shadow-xs">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'status', 'sort_by', 'per_page']))
                        <a href="{{ route('reports.slow-moving-stock') }}" class="rounded-xl border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition shrink-0 flex items-center justify-center h-[34px] w-[34px]" title="Reset Filter">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Print Header (Visible only on print) -->
    <div class="hidden print:block mb-4 border-b-2 border-brand-800 pb-3">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold uppercase text-brand-900">{{ $company->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}</h1>
                <p class="text-xs text-gray-600">DIVISI GUDANG & LOGISTIK | LAPORAN ANALISIS SLOW-MOVING & DEAD STOCK</p>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>Dicetak: {{ format_datetime(now()) }} WIB</p>
                <p>Status: {{ $selectedStatus === 'all' ? 'Semua Klasifikasi' : strtoupper($selectedStatus) }}</p>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="theme-table-header text-[11px] uppercase tracking-wider text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="py-3 px-4 text-center w-12">No</th>
                        <th class="py-3 px-4 w-32">Kode Material</th>
                        <th class="py-3 px-4">Barang & Kategori</th>
                        <th class="py-3 px-4 text-right">Stok Fisik</th>
                        <th class="py-3 px-4 text-center">Aktivitas Terakhir</th>
                        <th class="py-3 px-4 text-center">Hari Inaktif</th>
                        <th class="py-3 px-4 text-center">Status Perputaran</th>
                        <th class="py-3 px-4 text-right">Keluar 30H / 90H</th>
                        <th class="py-3 px-4">Rekomendasi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($items as $idx => $item)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition">
                            <td class="py-3 px-4 text-center font-mono text-gray-400">
                                {{ ($items->firstItem() ?? 1) + $idx }}
                            </td>
                            <!-- KODE MATERIAL -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $item->code_item ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-gray-900 dark:text-white text-xs">
                                    {{ $item->name }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] text-gray-500 font-medium">
                                        {{ $item->category_name }}
                                    </span>
                                    @if($item->expiry_date)
                                        @if($item->expiry_status === 'expired')
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                                EXP: {{ format_date($item->expiry_date) }}
                                            </span>
                                        @elseif($item->expiry_status === 'near_expiry')
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                EXP: {{ format_date($item->expiry_date) }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ format_quantity($item->stock) }} <span class="text-[10px] font-normal text-gray-500">{{ $item->unit }}</span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($item->last_activity_date)
                                    <span class="font-mono text-gray-700 dark:text-gray-300 font-medium">
                                        {{ format_date($item->last_activity_date) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-[11px] italic">Belum Ada Transaksi</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="font-mono font-bold text-xs {{ $item->days_inactive > 180 ? 'text-rose-600 dark:text-rose-400' : ($item->days_inactive >= 91 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300') }}">
                                    {{ $item->days_inactive }} Hari
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $item->badge_class }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-[11px] text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                <span class="{{ $item->out_qty_30d > 0 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">{{ format_quantity($item->out_qty_30d) }}</span>
                                <span class="text-gray-400">/</span>
                                <span class="{{ $item->out_qty_90d > 0 ? 'text-brand-600 dark:text-brand-400 font-bold' : '' }}">{{ format_quantity($item->out_qty_90d) }}</span>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-600 dark:text-gray-300">
                                <div class="flex items-center gap-1.5">
                                    @if($item->status_key === 'dead_stock')
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    @elseif($item->status_key === 'slow_moving')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    @elseif($item->status_key === 'medium_moving')
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    @endif
                                    <span>{{ $item->recommendation }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @php
                            $hasActiveFilters = request('search') || (request('category_id') && request('category_id') !== 'all') || (request('status') && request('status') !== 'all');
                        @endphp
                        <tr>
                            <td colspan="8" class="py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center justify-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3.5">
                                        @if($hasActiveFilters)
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @else
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    @if($hasActiveFilters)
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada barang yang cocok</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tidak ada barang yang sesuai dengan klasifikasi dan filter yang dipilih.</p>
                                        <a href="{{ route('reports.slow-moving-stock') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                                            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Reset Filter
                                        </a>
                                    @else
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada data slow-moving</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Semua item persediaan dalam kondisi perputaran normal atau belum tercatat.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 print:hidden">
                {{ $items->links() }}
            </div>
        @endif

        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-800/50 print:hidden">
            <span>Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari total {{ $items->total() }} item di katalog WMS</span>
            <span class="font-mono">WMS Intelligent Inventory Analytics</span>
        </div>
    </div>

    <!-- Print Signatures Block -->
    <div class="hidden print:block mt-8 pt-4">
        <table class="w-full text-center text-xs">
            <tr>
                <td class="w-1/3 pb-16">
                    <p class="font-semibold text-gray-700">Dibuat Oleh,</p>
                    <p class="text-[10px] text-gray-400">Staff Warehouse / Admin</p>
                </td>
                <td class="w-1/3 pb-16">
                    <p class="font-semibold text-gray-700">Diperiksa Oleh,</p>
                    <p class="text-[10px] text-gray-400">Supervisor Warehouse & QC</p>
                </td>
                <td class="w-1/3 pb-16">
                    <p class="font-semibold text-gray-700">Diketahui Oleh,</p>
                    <p class="text-[10px] text-gray-400">Kepala Gudang / Project Manager</p>
                </td>
            </tr>
            <tr>
                <td class="font-bold border-t border-gray-400 pt-1 mx-4">( ........................................ )</td>
                <td class="font-bold border-t border-gray-400 pt-1 mx-4">( ........................................ )</td>
                <td class="font-bold border-t border-gray-400 pt-1 mx-4">( ........................................ )</td>
            </tr>
        </table>
    </div>

</div>
@endsection

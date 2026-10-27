@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6 max-w-full overflow-hidden">
    <!-- Header & Quick Operational Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-gray-200/80 dark:border-gray-800 pb-5">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Operasional Gudang</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/60 font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                    WMS LIVE
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                PT Mudalaya Energy Indonesia • Monitoring Stok Fisik, Arus Barang, dan Status Kritis
            </p>
        </div>

        <!-- Operational Action Shortcuts -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('material-in.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-brand-700 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                <span>Catat Barang Masuk</span>
            </a>
            <a href="{{ route('material-out.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs font-bold text-gray-700 dark:text-gray-200 shadow-xs hover:bg-gray-50 dark:hover:bg-gray-700/60 transition cursor-pointer">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                <span>Catat Pengeluaran</span>
            </a>
            <a href="{{ route('items.index', ['stock_filter' => 'low']) }}" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/60 dark:bg-rose-950/30 px-3.5 py-2.5 text-xs font-bold text-rose-700 dark:text-rose-300 shadow-xs hover:bg-rose-100 dark:hover:bg-rose-900/40 transition cursor-pointer">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Stok Kritis ({{ $lowStockCount ?? 0 }})</span>
            </a>
            <a href="{{ route('stock-opnames.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs font-bold text-gray-700 dark:text-gray-200 shadow-xs hover:bg-gray-50 dark:hover:bg-gray-700/60 transition cursor-pointer" title="Input Hasil Stock Opname Fisik">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Stock Opname</span>
            </a>
        </div>
    </div>

    <!-- 1. Arus Operasional Harian & Real-Time (Today's Warehouse Pulse) -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Arus Operasional Gudang & Logistik</h2>
                <span class="text-[11px] text-gray-400">Ref: {{ \Carbon\Carbon::parse($latestOpDate ?? now())->translatedFormat('d F Y') }}</span>
            </div>
            <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500">
                Aktivitas Bulan {{ $monthLabel ?? now()->translatedFormat('F Y') }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pulse 1: Material Masuk (Inflow) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Material Masuk</span>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white font-mono">
                                {{ $todayInCount > 0 ? $todayInCount : $recentDayInCount }}
                            </h3>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Transaksi</span>
                        </div>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ $todayInCount > 0 ? 'Hari ini' : 'Tanggal ' . \Carbon\Carbon::parse($latestOpDate)->format('d/m') }}:
                        <strong class="text-gray-700 dark:text-gray-200">{{ format_quantity($todayInCount > 0 ? $todayInQty : $recentDayInCount) }} Unit</strong>
                    </span>
                    <a href="{{ route('material-in.index') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-0.5">
                        Log Masuk &rarr;
                    </a>
                </div>
            </div>

            <!-- Pulse 2: Material Keluar (Outflow) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Material Keluar</span>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white font-mono">
                                {{ $todayOutCount > 0 ? $todayOutCount : $recentDayOutCount }}
                            </h3>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Transaksi</span>
                        </div>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ $todayOutCount > 0 ? 'Hari ini' : 'Tanggal ' . \Carbon\Carbon::parse($latestOpDate)->format('d/m') }}:
                        <strong class="text-gray-700 dark:text-gray-200">{{ format_quantity($todayOutCount > 0 ? $todayOutQty : $recentDayOutCount) }} Unit</strong>
                    </span>
                    <a href="{{ route('material-out.index') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-0.5">
                        Log Keluar &rarr;
                    </a>
                </div>
            </div>

            <!-- Pulse 3: Antrean Incoming Inspection QC -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Antrean QC Masuk</span>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-2xl font-black font-mono {{ ($pendingQcCount ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">
                                {{ number_format($pendingQcCount ?? 0) }}
                            </h3>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Kedatangan</span>
                        </div>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ ($pendingQcCount ?? 0) > 0 ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">
                        @if(($pendingQcCount ?? 0) > 0)
                            <span class="text-amber-700 dark:text-amber-400 font-bold">Menunggu Inspeksi</span>
                        @else
                            <span class="text-emerald-700 dark:text-emerald-400 font-medium">Semua Clear</span>
                        @endif
                    </span>
                    <a href="{{ route('material-in.index') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-0.5">
                        Periksa &rarr;
                    </a>
                </div>
            </div>

            <!-- Pulse 4: Material Reject & Karantina Fisik -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Reject / Karantina</span>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-2xl font-black font-mono {{ ($pendingRejectCount ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">
                                {{ number_format($pendingRejectCount ?? 0) }}
                            </h3>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Item Defect</span>
                        </div>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ ($pendingRejectCount ?? 0) > 0 ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">
                        @if(($pendingRejectCount ?? 0) > 0)
                            <span class="text-rose-700 dark:text-rose-400 font-bold">Butuh Disposisi</span>
                        @else
                            <span class="text-emerald-700 dark:text-emerald-400 font-medium">Nihil Reject</span>
                        @endif
                    </span>
                    <a href="{{ route('reports.reject-material') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-0.5">
                        BAP Reject &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Pusat Peringatan & Status Darurat Gudang (Actionable Alert Center) -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-sm overflow-hidden"
         x-data="{ activeAlertTab: 'stock' }">
        <!-- Alert Center Navigation Bar -->
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50 dark:bg-gray-800/30">
            <div>
                <div class="flex items-center gap-2">
                    <span class="flex h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Pusat Peringatan & Status Darurat Gudang</h2>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Daftar prioritas penanganan stok fisik: re-order pengadaan, kontrol FEFO cat, dan karantina material reject.
                </p>
            </div>

            <!-- Tab Buttons -->
            <div class="flex items-center p-1 rounded-xl bg-gray-200/70 dark:bg-gray-800 text-xs font-bold gap-1">
                <button type="button" @click="activeAlertTab = 'stock'"
                        :class="activeAlertTab === 'stock' ? 'bg-white text-rose-700 shadow-xs dark:bg-gray-700 dark:text-rose-300' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300'"
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                    <span>Stok Kritis / Reorder</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                        {{ $lowStockCount ?? 0 }}
                    </span>
                </button>

                <button type="button" @click="activeAlertTab = 'fefo'"
                        :class="activeAlertTab === 'fefo' ? 'bg-white text-amber-700 shadow-xs dark:bg-gray-700 dark:text-amber-300' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300'"
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                    <span>FEFO Cat & Kimia</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                        {{ ($fefoExpiringCount ?? 0) + ($fefoExpiredCount ?? 0) }}
                    </span>
                </button>

                <button type="button" @click="activeAlertTab = 'reject'"
                        :class="activeAlertTab === 'reject' ? 'bg-white text-gray-800 shadow-xs dark:bg-gray-700 dark:text-gray-100' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300'"
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                    <span>Reject & Karantina</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                        {{ $pendingRejectCount ?? 0 }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Tab 1: Stok Kritis & Reorder Point -->
        <div x-show="activeAlertTab === 'stock'" class="p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 font-bold text-rose-700 dark:text-rose-400">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        Stok Habis (0): <strong class="font-mono text-sm">{{ number_format($outOfStockCount ?? 0) }}</strong> Item
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-amber-700 dark:text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Menipis (&le; Min Stock): <strong class="font-mono text-sm">{{ number_format($lowStockOnlyCount ?? 0) }}</strong> Item
                    </span>
                </div>
                <a href="{{ route('items.index', ['stock_filter' => 'low']) }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-1">
                    <span>Buka Semua Barang Kritis</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Critical Stock Table -->
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                    <thead class="theme-table-header text-[11px] uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-3.5 py-2.5">Kode Item</th>
                            <th class="px-3.5 py-2.5">Nama Barang & Kategori</th>
                            <th class="px-3.5 py-2.5">Lokasi Gudang</th>
                            <th class="px-3.5 py-2.5 text-center">Sisa Stok / Min</th>
                            <th class="px-3.5 py-2.5 text-center">Status</th>
                            <th class="px-3.5 py-2.5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse($criticalItems ?? [] as $cItem)
                            @php
                                $stkVal = (float) $cItem->stock;
                                $minVal = (float) $cItem->min_stock;
                                $isOut = $stkVal <= 0;
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition">
                                <td class="px-3.5 py-2.5 font-mono font-bold text-gray-900 dark:text-white">
                                    {{ $cItem->code_item ?? '-' }}
                                </td>
                                <td class="px-3.5 py-2.5">
                                    <div class="font-bold text-gray-900 dark:text-white truncate max-w-[240px]">
                                        {{ $cItem->name }}
                                    </div>
                                    <span class="text-[10px] text-gray-400">
                                        {{ $cItem->category?->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 text-gray-700 dark:text-gray-300 font-medium">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        {{ $cItem->display_location ?? $cItem->storage_location }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 text-center font-mono">
                                    <span class="font-bold {{ $isOut ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ format_quantity($stkVal) }}
                                    </span>
                                    <span class="text-gray-400">/ {{ format_quantity($minVal) }} {{ $cItem->unit }}</span>
                                </td>
                                <td class="px-3.5 py-2.5 text-center">
                                    @if($isOut)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            HABIS (0)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            MENIPIS
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 text-right">
                                    <a href="{{ route('items.index', ['search' => $cItem->code_item ?: $cItem->name]) }}" class="inline-flex items-center gap-1 font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                        <span>Proses FPBJ</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-emerald-600 dark:text-emerald-400">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="font-bold">Seluruh stok barang dalam posisi aman</span>
                                        <span class="text-gray-400 text-[11px]">Tidak ada material yang berada di bawah kuantitas minimum.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: FEFO Cat & Chemical Alert -->
        <div x-show="activeAlertTab === 'fefo'" x-cloak class="p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 font-bold text-amber-700 dark:text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Mendekati Batas (&le; {{ $alertDays ?? 60 }} Hari): <strong class="font-mono text-sm">{{ number_format($fefoExpiringCount ?? 0) }}</strong> Batch
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-rose-700 dark:text-rose-400">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        Melewati {{ $shelfLifeMonths ?? 12 }} Bulan (Expired): <strong class="font-mono text-sm">{{ number_format($fefoExpiredCount ?? 0) }}</strong> Batch
                    </span>
                </div>
                <a href="{{ route('reports.cat-painting') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-1">
                    <span>Laporan Lengkap Cat & Coating</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- FEFO Items Table -->
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                    <thead class="theme-table-header text-[11px] uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-3.5 py-2.5">Nama Batch / Item</th>
                            <th class="px-3.5 py-2.5 text-center">Tgl Manufaktur</th>
                            <th class="px-3.5 py-2.5 text-center">Batas Expired</th>
                            <th class="px-3.5 py-2.5 text-center">Sisa Stok</th>
                            <th class="px-3.5 py-2.5 text-center">Status FEFO</th>
                            <th class="px-3.5 py-2.5 text-right">Prioritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse($fefoItems ?? [] as $fItem)
                            @php
                                $mfgDate = $fItem->manufacturing_date ? \Carbon\Carbon::parse($fItem->manufacturing_date) : null;
                                $expDate = $fItem->effective_expiry_date;
                                $isExp = $fItem->isExpired();
                                $daysLeft = $fItem->days_until_expiry;
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition">
                                <td class="px-3.5 py-2.5">
                                    <div class="font-bold text-gray-900 dark:text-white truncate max-w-[220px]">
                                        {{ $fItem->name }}
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-400">{{ $fItem->code_item ?? '-' }} &bull; {{ $fItem->storage_location }}</span>
                                </td>
                                <td class="px-3.5 py-2.5 text-center font-mono">
                                    {{ $mfgDate ? $mfgDate->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-3.5 py-2.5 text-center font-mono">
                                    {{ $expDate ? $expDate->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-3.5 py-2.5 text-center font-mono font-bold text-gray-900 dark:text-white">
                                    {{ format_quantity($fItem->stock) }} {{ $fItem->unit }}
                                </td>
                                <td class="px-3.5 py-2.5 text-center">
                                    @if($isExp)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            KADALUARSA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            {{ $daysLeft !== null ? $daysLeft . ' Hari Lagi' : 'Mendekati Exp' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 text-right">
                                    <span class="font-bold {{ $isExp ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ $isExp ? 'Karantina / Buat BAP' : 'Gunakan Terlebih Dahulu' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-emerald-600 dark:text-emerald-400">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="font-bold">Seluruh batch cat dan chemical dalam masa aman</span>
                                        <span class="text-gray-400 text-[11px]">Tidak ada batch yang mendekati kadaluarsa atau melewati masa simpan.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Material Reject & Karantina Fisik -->
        <div x-show="activeAlertTab === 'reject'" x-cloak class="p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 font-bold text-rose-700 dark:text-rose-400">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        Menunggu Tindak Lanjut Disposisi: <strong class="font-mono text-sm">{{ number_format($pendingRejectCount ?? 0) }}</strong> Item
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <span class="text-gray-500">Total Riwayat Reject: <strong class="font-mono text-gray-700 dark:text-gray-300">{{ number_format($totalRejectCount ?? 0) }}</strong></span>
                </div>
                <a href="{{ route('reports.reject-material') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-1">
                    <span>Kelola Dokumen BAP Reject</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Rejects Table -->
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                    <thead class="theme-table-header text-[11px] uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-3.5 py-2.5">No Referensi / Tgl</th>
                            <th class="px-3.5 py-2.5">Nama Material Defect</th>
                            <th class="px-3.5 py-2.5">Supplier / Vendor</th>
                            <th class="px-3.5 py-2.5 text-center">Jumlah Cacat</th>
                            <th class="px-3.5 py-2.5">Status Disposisi</th>
                            <th class="px-3.5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse($pendingRejectList ?? [] as $rItem)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition">
                                <td class="px-3.5 py-2.5 font-mono text-gray-900 dark:text-white">
                                    <div class="font-bold">{{ $rItem->reference_number ?? ('REJ-' . $rItem->id) }}</div>
                                    <span class="text-[10px] text-gray-400">{{ $rItem->date ? \Carbon\Carbon::parse($rItem->date)->format('d/m/Y') : '-' }}</span>
                                </td>
                                <td class="px-3.5 py-2.5">
                                    <div class="font-bold text-gray-900 dark:text-white truncate max-w-[220px]">
                                        {{ $rItem->item?->name ?? 'Material #' . $rItem->item_id }}
                                    </div>
                                    <span class="text-[10px] text-rose-600 dark:text-rose-400 font-medium">
                                        {{ $rItem->damage_type ?: 'Cacat Mutu / Fisik' }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 text-gray-700 dark:text-gray-300 font-medium">
                                    {{ $rItem->supplier ?: '-' }}
                                </td>
                                <td class="px-3.5 py-2.5 text-center font-mono font-bold text-rose-600 dark:text-rose-400">
                                    {{ format_quantity($rItem->quantity) }} {{ $rItem->unit }}
                                </td>
                                <td class="px-3.5 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $rItem->disposition_status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                        {{ $rItem->status_label ?? $rItem->disposition_label }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 text-right">
                                    <a href="{{ route('reports.reject-material') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                        Tindak Lanjut &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-xs text-emerald-600 dark:text-emerald-400">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="font-bold">Nihil material defect / reject</span>
                                        <span class="text-gray-400 text-[11px]">Seluruh incoming material lolos uji mutu dan tidak ada barang dalam karantina.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Key Inventory Metrics Grid (KPI Stok Fisik & Valuasi) -->
    @php
        $dashAuthUser = auth()->user();
        $canViewPrices = $dashAuthUser && $dashAuthUser->canViewPrices();
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $canViewPrices ? 'lg:grid-cols-3 xl:grid-cols-5' : 'lg:grid-cols-2 xl:grid-cols-4' }} md:gap-5">
        <!-- Metric 1: Total Jenis Barang -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Katalog Item</span>
                    <h3 class="mt-2 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalItems ?? 0) }}</h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 truncate">Total sku material terdaftar di gudang</p>
        </div>

        <!-- Metric 2: Total Unit Stok Fisik -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0 flex flex-col justify-between"
             x-data="{ showCategoryBreakdown: false }">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate">Total Kuantitas Fisik</span>
                    <div class="flex items-baseline gap-1.5 mt-2">
                        <h3 class="text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($totalDiscreteStock ?? 0, 0, ',', '.') }}</h3>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Pcs/Unit</span>
                    </div>
                    @if(!empty($totalDecimalStockBreakdown))
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-mono" title="Satuan Desimal / Berat / Ukuran">
                                + {{ $totalDecimalStockBreakdown }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                <button type="button" @click="showCategoryBreakdown = !showCategoryBreakdown" class="text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1 cursor-pointer">
                    <span x-text="showCategoryBreakdown ? 'Tutup Rincian' : 'Rincian per Kategori'"></span>
                    <svg class="w-3 h-3 transition-transform" :class="showCategoryBreakdown ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <span class="text-[10px] text-gray-400 font-mono">{{ count($categoryStockDetails ?? []) }} Kategori</span>
            </div>
            <!-- Category Stock Summary Popup -->
            <div x-show="showCategoryBreakdown" x-cloak class="mt-2 space-y-1 bg-gray-50 dark:bg-gray-800/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800 text-[11px]">
                @foreach(($categoryStockDetails ?? []) as $csItem)
                    <div class="flex items-center justify-between py-0.5 border-b border-gray-100/50 dark:border-gray-700/40 last:border-0">
                        <span class="text-gray-600 dark:text-gray-300 font-medium truncate max-w-[140px]">{{ $csItem['name'] }}</span>
                        <span class="font-bold font-mono text-gray-900 dark:text-white text-right">{{ $csItem['formatted'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Metric 3: Stok Kritis / Reorder Point -->
        <div class="rounded-2xl border border-rose-200 dark:border-rose-900/60 bg-white dark:bg-gray-900 p-5 shadow-xs min-w-0 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">Peringatan Stok</span>
                    <h3 class="mt-2 text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($lowStockCount ?? 0) }}</h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-rose-600 dark:text-rose-400 font-medium truncate">
                {{ $outOfStockCount ?? 0 }} barang kosong, {{ $lowStockOnlyCount ?? 0 }} menipis
            </p>
        </div>

        <!-- Metric 4: Klasifikasi Kategori -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kategori Material</span>
                    <h3 class="mt-2 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalCategories ?? 0) }}</h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 truncate">Sektor material EPC, fabrikasi & energi</p>
        </div>

        <!-- Metric 5: Nilai Aset Persediaan (Khusus Admin/Finance) -->
        @if($canViewPrices)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs min-w-0 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div class="min-w-0 pr-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate block">Nilai Aset Persediaan</span>
                    <h3 class="mt-2 text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 font-mono tracking-tight tabular-nums truncate" title="Rp {{ number_format($totalAssetValue ?? 0, 0, ',', '.') }}">
                        Rp {{ number_format($totalAssetValue ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ number_format($pricedItemsCount ?? 0) }} dari {{ number_format($totalItems ?? 0) }} item berharga beli
            </p>
        </div>
        @endif
    </div>

    <!-- 4. Warehouse Storage Capacity (STORAGE 1 vs STORAGE 2) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Storage 1 Card -->
        <a href="{{ route('items.index', ['storage_filter' => 'STORAGE 1']) }}" 
            class="group relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4.5 shadow-xs hover:border-brand-500 hover:shadow-md transition flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Gudang Utama</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-brand-100 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono">STORAGE 1</span>
                </div>
                <h4 class="text-2xl font-black text-gray-900 dark:text-white font-mono">
                    {{ number_format($storage1Count ?? 0) }} <span class="text-xs font-normal text-gray-500">Item Terdaftar</span>
                </h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    Consumable, Secondary Part, Gas Industri, Cat, Thinner, dan Fast Moving Tools.
                </p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400 group-hover:scale-105 transition-transform">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </a>

        <!-- Storage 2 Card -->
        <a href="{{ route('items.index', ['storage_filter' => 'STORAGE 2']) }}" 
            class="group relative overflow-hidden rounded-2xl border border-amber-200 dark:border-amber-900/60 bg-white dark:bg-gray-900 p-4.5 shadow-xs hover:border-amber-500 hover:shadow-md transition flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Gudang Bahan Baku</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 font-mono">STORAGE 2</span>
                </div>
                <h4 class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">
                    {{ number_format($storage2Count ?? 0) }} <span class="text-xs font-normal text-gray-500">Item Terdaftar</span>
                </h4>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    Khusus penyimpanan Raw Material, Plat Besi, Pipa, Beam, dan Bahan Fabrikasi.
                </p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 group-hover:scale-105 transition-transform">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </a>
    </div>

    <!-- 5. Main Operational Grid: Recent Movement Logs & Fast Moving Items -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 min-w-0">
        <!-- Left: Recent Stock Movement Logs & Fast-Moving Items (xl:col-span-8) -->
        <div class="xl:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-5 min-w-0 overflow-hidden flex flex-col justify-between">
            <div class="space-y-4">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>Riwayat Transaksi Mutasi Stok Terkini</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Log transaksi barang masuk dan keluar langsung dari lantai gudang</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('material-in.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                            <span>Semua Masuk</span>
                        </a>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <a href="{{ route('material-out.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1">
                            <span>Semua Keluar &rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead class="theme-table-header text-[11px] uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="px-3.5 py-2.5">Arus</th>
                                <th class="px-3.5 py-2.5">Nama Barang & Kode</th>
                                <th class="px-3.5 py-2.5 text-center">Jumlah</th>
                                <th class="px-3.5 py-2.5">Penerima / Penyedia</th>
                                <th class="px-3.5 py-2.5">Waktu Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                            @forelse($recentLogs ?? [] as $log)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition">
                                    <td class="px-3.5 py-2.5">
                                        @if($log->type == 'in')
                                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                &darr; Masuk
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                &uarr; Keluar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2.5 font-medium text-gray-900 dark:text-white">
                                        <div class="flex items-center gap-1.5">
                                            <span class="truncate max-w-[220px] font-bold">{{ $log->item?->name ?? 'Barang #' . $log->item_id }}</span>
                                            @if($log->item?->code_item)
                                                <span class="text-[10px] font-mono text-gray-500 bg-gray-100 dark:bg-gray-800 px-1 py-0.2 rounded shrink-0">{{ $log->item->code_item }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-2.5 text-center font-bold font-mono text-gray-900 dark:text-white">
                                        {{ format_quantity($log->quantity) }} <span class="text-[11px] font-normal text-gray-500">{{ $log->item?->unit }}</span>
                                    </td>
                                    <td class="px-3.5 py-2.5 text-xs text-gray-700 dark:text-gray-300 font-medium">
                                        {{ $log->recipient_or_provider ?: '-' }}
                                    </td>
                                    <td class="px-3.5 py-2.5 text-xs text-gray-400 whitespace-nowrap" title="{{ $log->created_at?->format('d M Y H:i:s') }}">
                                        {{ $log->created_at?->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">
                                        Belum ada riwayat transaksi log barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel Barang Fast-Moving (Top Volume Pemakaian) -->
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800/80 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                Barang Fast-Moving (Perputaran Tercepat)
                            </h4>
                            <span class="text-[10px] font-bold px-2 py-0.2 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono">
                                Top {{ count($fastMovingItems ?? []) }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-400">Item dengan frekuensi transaksi dan kuantitas keluar tertinggi di gudang</p>
                    </div>
                    <a href="{{ route('reports.material-out') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1 shrink-0">
                        <span>Laporan Pengeluaran Lengkap &rarr;</span>
                    </a>
                </div>

                <!-- Grid 4 Fast-Moving Items -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    @forelse($fastMovingItems ?? [] as $index => $fm)
                        @php
                            $stk = (float) ($fm->item?->stock ?? 0);
                            $minStk = (float) ($fm->item?->min_stock ?? 0);
                            $isLow = $stk <= $minStk;
                            $rankColors = [
                                0 => 'bg-amber-500 text-white',
                                1 => 'bg-slate-500 text-white',
                                2 => 'bg-brand-700 text-white',
                                3 => 'bg-gray-400 text-white',
                            ];
                            $badgeColor = $rankColors[$index] ?? 'bg-gray-400 text-white';
                        @endphp
                        <div class="rounded-xl border border-gray-200/90 bg-gray-50/60 p-3 dark:border-gray-800 dark:bg-gray-800/40 hover:border-brand-300 dark:hover:border-brand-700 transition flex flex-col justify-between gap-2.5 shadow-2xs">
                            <div class="space-y-1.5 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-extrabold text-[9px] px-1.5 py-0.5 rounded font-mono {{ $badgeColor }}">
                                        #{{ $index + 1 }}
                                    </span>
                                    <span class="text-[9px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate max-w-[90px]">
                                        {{ $fm->item?->category?->name ?? 'Material' }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-bold text-xs text-gray-900 dark:text-white truncate" title="{{ $fm->item?->name }}">
                                        {{ $fm->item?->name ?? 'Barang #' . $fm->item_id }}
                                    </div>
                                    <div class="text-[10px] font-mono text-gray-400">
                                        {{ $fm->item?->code_item ?? '-' }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1.5 pt-1.5 border-t border-gray-200/60 dark:border-gray-700/60">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500 dark:text-gray-400">Total Keluar:</span>
                                    <span class="font-black text-brand-700 dark:text-brand-300 font-mono">
                                        {{ format_quantity($fm->total_out_qty) }} {{ $fm->item?->unit }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500 dark:text-gray-400">Frekuensi:</span>
                                    <span class="font-bold text-gray-700 dark:text-gray-300">
                                        {{ $fm->total_transactions }}x Transaksi
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] pt-1 border-t border-dashed border-gray-200 dark:border-gray-700/50">
                                    <span class="text-gray-400">Sisa Stok:</span>
                                    @if($isLow)
                                        <span class="font-bold text-rose-600 dark:text-rose-400 font-mono">
                                            {{ format_quantity($stk) }} (Kritis)
                                        </span>
                                    @else
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400 font-mono">
                                            {{ format_quantity($stk) }} {{ $fm->item?->unit }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-4 text-center text-xs text-gray-400">
                            Belum ada catatan barang keluar pada periode aktif.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Category Distribution & Storage Info (xl:col-span-4) -->
        <div class="xl:col-span-4 space-y-6 min-w-0">
            <!-- Category Distribution -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Sebaran Kategori</h3>
                    <a href="{{ route('categories.index') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                        Kelola Kategori
                    </a>
                </div>
                <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                    @php
                        $paintSubcategories = collect($categories ?? [])->filter(fn($c) => method_exists($c, 'isCatSubcategory') ? $c->isCatSubcategory() : str_starts_with(strtolower($c->name), 'cat -'));
                    @endphp
                    @foreach($categories ?? [] as $cat)
                        @if(method_exists($cat, 'isCatSubcategory') ? $cat->isCatSubcategory() : str_starts_with(strtolower($cat->name), 'cat -'))
                            @continue
                        @endif

                        @if(method_exists($cat, 'isCatUmbrella') ? $cat->isCatUmbrella() : strcasecmp(trim($cat->name), 'cat') === 0)
                            <div class="rounded-xl border border-gray-200/80 bg-gray-50/70 p-2.5 dark:border-gray-800/80 dark:bg-gray-800/40 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $cat->name }}</span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 font-normal">(Total Gabungan)</span>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 font-mono">
                                        {{ $cat->items_count }} Item
                                    </span>
                                </div>
                                @if($paintSubcategories->isNotEmpty())
                                    <div class="pt-1 pl-3 border-l-2 border-brand-200 dark:border-brand-800/60 space-y-1.5 ml-1">
                                        @foreach($paintSubcategories as $subCat)
                                            <div class="flex items-center justify-between text-[11px] text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">{{ $subCat->name }}</span>
                                                <span class="font-semibold text-gray-500 dark:text-gray-400 font-mono">{{ $subCat->items_count }} Item</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100/60 dark:hover:bg-gray-800 transition">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 font-mono">
                                    {{ $cat->items_count }} Item
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Quick Access Card to Laporan Consumable & Secondary -->
            <div class="rounded-2xl border border-brand-200 dark:border-brand-900/60 bg-brand-50/60 dark:bg-brand-950/30 p-5 space-y-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white shadow-xs">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-brand-950 dark:text-brand-200">Laporan Pemakaian 31 Hari</h4>
                        <p class="text-[11px] text-brand-800 dark:text-brand-300">Analisis pengeluaran consumable & sparepart</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('reports.consumable') }}" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-3 py-2 text-xs font-bold text-white hover:bg-brand-700 transition shadow-xs">
                        <span>Buka Laporan Consumable &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Charts Section: Mutasi Stok, Stok per Kategori, Top Pengeluaran, Aging -->
    <div class="w-full max-w-full min-w-0 overflow-hidden space-y-6">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <!-- 1. Line Chart: Tren Mutasi Stok (Masuk vs Keluar) -->
            <div class="xl:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-3 min-w-0 overflow-hidden"
                 x-data="{ lineUnitMode: 'discrete' }">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Grafik Tren Mutasi Stok Material</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pemisahan arus barang masuk vs keluar berdasarkan jenis satuan hitung</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Unit Type Switcher -->
                        <div class="flex items-center p-0.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold">
                            <button type="button" @click="lineUnitMode = 'discrete'; window.switchLineChartUnit('discrete')"
                                    :class="lineUnitMode === 'discrete' ? 'bg-white text-brand-700 shadow-xs dark:bg-gray-700 dark:text-brand-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                                    class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                                Pcs / Unit
                            </button>
                            <button type="button" @click="lineUnitMode = 'decimal'; window.switchLineChartUnit('decimal')"
                                    :class="lineUnitMode === 'decimal' ? 'bg-white text-amber-700 shadow-xs dark:bg-gray-700 dark:text-amber-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                                    class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                                Kg / Meter
                            </button>
                        </div>
                        <form method="GET" action="{{ route('dashboard') }}" id="lineFilterForm" class="flex items-center gap-2">
                            <input type="hidden" name="bar_range" value="{{ $barRange }}">
                            <input type="hidden" name="item_out_range" value="{{ $itemOutRange }}">
                            <input type="hidden" name="project_range" value="{{ $projectRange }}">
                            <select name="line_range" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-bold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                                <option value="1w" {{ $lineRange === '1w' ? 'selected' : '' }}>1 Minggu</option>
                                <option value="1m" {{ $lineRange === '1m' ? 'selected' : '' }}>1 Bulan</option>
                                <option value="1y" {{ $lineRange === '1y' ? 'selected' : '' }}>1 Tahun</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="w-full min-w-0 overflow-hidden">
                    <div id="lineChartMutasiStok" class="min-h-[280px] w-full max-w-full"></div>
                </div>
            </div>

            <!-- 2. Bar Chart: Total Stok per Kategori Material -->
            <div class="xl:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-3 min-w-0 overflow-hidden"
                 x-data="{ barUnitMode: 'discrete' }">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Stok per Kategori</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kuantitas fisik stok terpisah per kelompok</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Unit Type Switcher -->
                        <div class="flex items-center p-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-[11px] font-bold">
                            <button type="button" @click="barUnitMode = 'discrete'; window.switchBarChartUnit('discrete')"
                                    :class="barUnitMode === 'discrete' ? 'bg-white text-brand-700 shadow-xs dark:bg-gray-700 dark:text-brand-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                                    class="px-2 py-0.5 rounded-md transition cursor-pointer">
                                Pcs/Unit
                            </button>
                            <button type="button" @click="barUnitMode = 'decimal'; window.switchBarChartUnit('decimal')"
                                    :class="barUnitMode === 'decimal' ? 'bg-white text-amber-700 shadow-xs dark:bg-gray-700 dark:text-amber-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                                    class="px-2 py-0.5 rounded-md transition cursor-pointer">
                                Kg/Meter
                            </button>
                        </div>
                        <div class="text-right">
                            <template x-if="barUnitMode === 'discrete'">
                                <div>
                                    <span class="text-base font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalCategoryDiscrete ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">Pcs</span>
                                </div>
                            </template>
                            <template x-if="barUnitMode === 'decimal'">
                                <div>
                                    <span class="text-base font-black text-amber-600 dark:text-amber-400 font-mono">{{ format_quantity($totalCategoryDecimal ?? 0) }}</span>
                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">Kg/M</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="w-full min-w-0 overflow-hidden">
                    <div id="barChartKategoriStok" class="min-h-[280px] w-full max-w-full"></div>
                </div>
            </div>
        </div>

        <!-- 3. Grid Row: Pengeluaran Material Terbanyak & All Inventory Aging -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 min-w-0">
            <!-- Left: Chart Pengeluaran Material Terbanyak (xl:col-span-7) -->
            <div class="xl:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-3 min-w-0 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Grafik Pengeluaran Material Terbanyak</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Peringkat 8 material keluar dengan kuantitas asli transaksi</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="GET" action="{{ route('dashboard') }}" id="itemOutFilterForm">
                            <input type="hidden" name="line_range" value="{{ $lineRange }}">
                            <input type="hidden" name="bar_range" value="{{ $barRange }}">
                            <input type="hidden" name="project_range" value="{{ $projectRange }}">
                            <select name="item_out_range" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-bold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                                <option value="1w" {{ $itemOutRange === '1w' ? 'selected' : '' }}>1 Minggu</option>
                                <option value="1m" {{ $itemOutRange === '1m' ? 'selected' : '' }}>1 Bulan</option>
                                <option value="1y" {{ $itemOutRange === '1y' ? 'selected' : '' }}>1 Tahun</option>
                            </select>
                        </form>
                        <div class="text-right">
                            <span class="text-base font-black text-brand-600 dark:text-brand-400 font-mono">{{ count($topItemNames ?? []) }}</span>
                            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">Item Teratas</span>
                        </div>
                    </div>
                </div>

                <div class="w-full min-w-0 overflow-hidden">
                    <div id="chartPengeluaranMaterial" class="min-h-[280px] w-full max-w-full"></div>
                </div>
            </div>

            <!-- Right: All Inventory Aging Card (xl:col-span-5) -->
            <div class="xl:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-4 min-w-0 overflow-hidden"
                 x-data="{ 
                     activeBucket: '>90 Days',
                     allTableData: @js($agingTableData),
                     get filteredItems() {
                         if (this.activeBucket === 'Semua') return this.allTableData;
                         return this.allTableData.filter(i => i.bucket === this.activeBucket);
                     }
                 }">
                <!-- Header -->
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">All Inventory Aging</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Analisis rasio umur simpan stok material gudang</p>
                    </div>
                    <a href="{{ route('items.index') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline inline-flex items-center gap-1">
                        Lihat Semua
                    </a>
                </div>

                <!-- Donut Chart -->
                @if($agingTotalItems > 0)
                    <div class="relative flex justify-center items-center py-1">
                        <div id="donutChartInventoryAging" class="w-full max-w-[320px]"></div>
                    </div>
                @else
                    <div class="py-10 text-center text-xs text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="font-medium text-gray-600 dark:text-gray-400">Belum Ada Data Umur Simpan</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Katalog barang belum memiliki catatan tanggal masuk.</p>
                    </div>
                @endif

                <!-- Table Header & Filter -->
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between pb-2 text-xs font-semibold text-gray-400 dark:text-gray-500">
                        <span>Item</span>
                        <div class="flex items-center gap-4">
                            <span>Qty</span>
                            <select x-model="activeBucket" class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-bold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 outline-none focus:border-brand-500 cursor-pointer">
                                <option value=">90 Days">>90 Days</option>
                                <option value="61-90 Days">61-90 Days</option>
                                <option value="31-60 Days">31-60 Days</option>
                                <option value="<30 Days"><30 Days</option>
                                <option value="Semua">Semua Bucket</option>
                            </select>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="max-h-[200px] overflow-y-auto space-y-1 pr-1 divide-y divide-gray-50 dark:divide-gray-800/50">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div class="flex items-center justify-between py-2 text-xs">
                                <span class="font-medium text-gray-900 dark:text-white truncate max-w-[140px] sm:max-w-[170px]" x-text="item.name"></span>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full bg-blue-50/90 px-2.5 py-0.5 text-xs font-bold text-blue-600 dark:bg-blue-950/40 dark:text-blue-400" x-text="item.stock + ' ' + item.unit"></span>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-extrabold" :class="item.badge_class" x-text="item.days + ' Days'"></span>
                                </div>
                            </div>
                        </template>

                        <div x-show="filteredItems.length === 0" class="py-6 text-center text-xs text-gray-400">
                            Tidak ada barang dalam kategori umur ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. Section: Penggunaan Material per Project (Clean & Subordinate) -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-5">
        <!-- Section Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Alokasi Material per Project</h2>
                        <span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-bold text-brand-800 dark:bg-brand-950/60 dark:text-brand-300">
                            {{ $totalProjectCount }} Project Terdaftar
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Informasi akumulasi kuantitas material yang dialokasikan dan digunakan pada masing-masing project</p>
                </div>
            </div>

            <!-- Mini Summary Metric Badges -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-1.5 dark:border-gray-800 dark:bg-gray-800/60">
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-400 block">Total Material Project</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-sm font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($totalProjectDiscreteQty ?? 0, 0, ',', '.') }}</span>
                        <span class="text-[10px] font-bold text-gray-500">Pcs/Unit</span>
                    </div>
                    @if(!empty($totalProjectDecimalString))
                        <span class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 font-mono">+ {{ $totalProjectDecimalString }}</span>
                    @endif
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-1.5 dark:border-gray-800 dark:bg-gray-800/60">
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-400 block">Konsumsi Terbesar</span>
                    <span class="text-xs font-black text-gray-900 dark:text-white font-mono block">{{ $topProjectRecord['total_qty_formatted'] ?? '0' }}</span>
                </div>
                <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-950/50 dark:text-brand-300 dark:hover:bg-brand-900/50 transition cursor-pointer">
                    <span>Buka Ringkasan Project &rarr;</span>
                </a>
            </div>
        </div>

        <!-- 2 Column Layout: Chart & Interactive Leaderboard -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 min-w-0">
            <!-- Left: Chart Top 8 Project (xl:col-span-7) -->
            <div class="xl:col-span-7 rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800/60 dark:bg-gray-800/20 space-y-3 min-w-0 overflow-hidden"
                 x-data="{ projectChartMode: 'discrete' }">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800/80 pb-2.5">
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Grafik Top Pemakaian Material Project</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Peringkat alokasi material gudang dengan format satuan asli</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center p-0.5 rounded-lg bg-gray-200/60 dark:bg-gray-800 text-[10px] font-bold">
                            <button type="button" @click="projectChartMode = 'discrete'; window.switchProjectChartMode('discrete')"
                                    :class="projectChartMode === 'discrete' ? 'bg-white text-brand-700 shadow-xs dark:bg-gray-700 dark:text-brand-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                                    class="px-2 py-0.5 rounded transition cursor-pointer">
                                Pcs/Unit
                            </button>
                            <button type="button" @click="projectChartMode = 'decimal'; window.switchProjectChartMode('decimal')"
                                    :class="projectChartMode === 'decimal' ? 'bg-white text-amber-700 shadow-xs dark:bg-gray-700 dark:text-amber-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                                    class="px-2 py-0.5 rounded transition cursor-pointer">
                                Kg/Meter
                            </button>
                        </div>
                        <form method="GET" action="{{ route('dashboard') }}" id="projectFilterForm">
                            <input type="hidden" name="line_range" value="{{ $lineRange }}">
                            <input type="hidden" name="bar_range" value="{{ $barRange }}">
                            <input type="hidden" name="item_out_range" value="{{ $itemOutRange }}">
                            <select name="project_range" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-white px-2.5 py-1 text-xs font-bold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer shadow-xs">
                                <option value="all" {{ $projectRange === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                                <option value="1y" {{ $projectRange === '1y' ? 'selected' : '' }}>1 Tahun</option>
                                <option value="1m" {{ $projectRange === '1m' ? 'selected' : '' }}>1 Bulan</option>
                                <option value="1w" {{ $projectRange === '1w' ? 'selected' : '' }}>1 Minggu</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="w-full min-w-0 overflow-hidden">
                    <div id="chartMaterialPerProject" class="min-h-[300px] w-full max-w-full"></div>
                </div>
            </div>

            <!-- Right: Interactive Project Consumption List & Search (xl:col-span-5) -->
            <div class="xl:col-span-5 rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800/60 dark:bg-gray-800/20 flex flex-col justify-between space-y-3 min-w-0 overflow-hidden"
                 x-data="{
                     search: '',
                     allProjects: @js($projectListData),
                     get filteredProjects() {
                         if (!this.search.trim()) return this.allProjects;
                         const q = this.search.toLowerCase();
                         return this.allProjects.filter(p => 
                             p.name.toLowerCase().includes(q) || 
                             p.code.toLowerCase().includes(q) ||
                             (p.client && p.client.toLowerCase().includes(q))
                         );
                     }
                 }">
                <!-- Header & Real-time Search -->
                <div class="space-y-2 pb-2 border-b border-gray-100 dark:border-gray-800/80">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Daftar Penggunaan per Project</h3>
                        <span class="text-[11px] font-semibold text-gray-400" x-text="filteredProjects.length + ' ditampilkan'"></span>
                    </div>
                    <div class="relative">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" 
                               x-model="search" 
                               placeholder="Cari project (contoh: LCI, WATER TANK, IMPROVEMENT)..." 
                               class="w-full pl-8 pr-7 py-1.5 text-xs rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-500 shadow-xs">
                        <button x-show="search.length > 0" @click="search = ''" type="button" class="absolute right-2 top-2 p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Scrollable Project Consumption Cards -->
                <div class="max-h-[310px] overflow-y-auto space-y-2.5 pr-1.5 divide-y divide-gray-100 dark:divide-gray-800/60">
                    <template x-for="p in filteredProjects" :key="p.id">
                        <div class="pt-2.5 first:pt-0 space-y-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-start gap-2 min-w-0">
                                    <!-- Rank Indicator -->
                                    <span class="shrink-0 flex items-center justify-center w-5 h-5 rounded-md text-[10px] font-black font-mono mt-0.5"
                                          :class="{
                                              'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300': p.rank === 1,
                                              'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-300': p.rank === 2,
                                              'bg-amber-50 text-amber-900 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200': p.rank === 3,
                                              'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400': p.rank > 3
                                          }"
                                          x-text="'#' + p.rank">
                                    </span>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate" :title="p.name" x-text="p.name"></h4>
                                        <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                            Project ini menggunakan <span class="font-extrabold text-brand-600 dark:text-brand-400 font-mono text-xs" x-text="p.total_qty_formatted"></span> material
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-flex items-center rounded-lg bg-brand-50 px-2 py-0.5 text-[11px] font-extrabold text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 font-mono" x-text="p.percentage + '%'"></span>
                                </div>
                            </div>

                            <!-- Progress Bar of total consumption -->
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-brand-600 dark:bg-brand-500 h-1.5 rounded-full transition-all duration-300" :style="'width: ' + Math.min(100, Math.max(3, p.percentage)) + '%'"></div>
                            </div>

                            <!-- Meta Details & Action -->
                            <div class="flex items-center justify-between text-[10px] text-gray-400">
                                <div class="flex items-center gap-2">
                                    <span x-text="p.total_trans + ' Transaksi'"></span>
                                    <span>&bull;</span>
                                    <span x-text="p.total_items + ' Jenis Item'"></span>
                                </div>
                                <a :href="p.url" class="inline-flex items-center gap-1 font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline">
                                    Rincian
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </template>

                    <div x-show="filteredProjects.length === 0" class="py-8 text-center text-xs text-gray-400">
                        Tidak ada project yang sesuai dengan pencarian "<span class="font-bold text-gray-600 dark:text-gray-300" x-text="search"></span>".
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-gray-500">Klik rincian untuk detail barang project.</span>
                    <a href="{{ route('projects.index') }}" class="font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline inline-flex items-center gap-1">
                        <span>Semua Project &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts Script Initialization -->
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const ApexCharts = await (window.getApexCharts ? window.getApexCharts() : Promise.resolve(window.ApexCharts));
    if (!ApexCharts) return;
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
    const gridColor = isDarkMode ? '#374151' : '#F3F4F6';

    // 1. Line Chart: Tren Mutasi Stok (Barang Masuk vs Barang Keluar)
    const lineDiscreteIn = @json($monthlyInDiscrete ?? []);
    const lineDiscreteOut = @json($monthlyOutDiscrete ?? []);
    const lineDecimalIn = @json($monthlyInDecimal ?? []);
    const lineDecimalOut = @json($monthlyOutDecimal ?? []);

    let currentLineUnit = 'discrete';

    const lineChartOptions = {
        series: [
            { name: "Barang Masuk", data: lineDiscreteIn },
            { name: "Barang Keluar", data: lineDiscreteOut }
        ],
        chart: {
            type: "area",
            height: 280,
            fontFamily: "Outfit, sans-serif",
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: ["#275538", "#F59E0B"],
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        stroke: { curve: "smooth", width: 3 },
        dataLabels: { enabled: false },
        xaxis: {
            categories: @json($lineCategories ?? []),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: textColor, fontSize: "11px" } }
        },
        yaxis: {
            labels: { 
                style: { colors: textColor, fontSize: "11px" },
                formatter: (val) => currentLineUnit === 'discrete' ? Math.round(val) : (Number.isInteger(val) ? val : val.toFixed(1))
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4
        },
        legend: { show: false },
        tooltip: {
            theme: isDarkMode ? 'dark' : 'light',
            y: { 
                formatter: (val) => {
                    const unitLabel = currentLineUnit === 'discrete' ? ' Pcs/Unit' : ' Kg/Meter';
                    const numFormatted = currentLineUnit === 'discrete' 
                        ? new Intl.NumberFormat('id-ID').format(Math.round(val))
                        : (Number.isInteger(val) ? new Intl.NumberFormat('id-ID').format(val) : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 3 }).format(val));
                    return numFormatted + unitLabel;
                }
            }
        }
    };

    let lineChartInstance = null;
    const lineChartEl = document.querySelector("#lineChartMutasiStok");
    if (lineChartEl && typeof ApexCharts !== 'undefined') {
        lineChartInstance = new ApexCharts(lineChartEl, lineChartOptions);
        lineChartInstance.render();
    }

    window.switchLineChartUnit = function(mode) {
        currentLineUnit = mode;
        if (!lineChartInstance) return;
        if (mode === 'discrete') {
            lineChartInstance.updateSeries([
                { name: "Barang Masuk", data: lineDiscreteIn },
                { name: "Barang Keluar", data: lineDiscreteOut }
            ]);
        } else {
            lineChartInstance.updateSeries([
                { name: "Barang Masuk", data: lineDecimalIn },
                { name: "Barang Keluar", data: lineDecimalOut }
            ]);
        }
    };

    // 2. Bar Chart: Total Stok per Kategori
    const catStocksDiscrete = @json($categoryStocksDiscrete ?? []);
    const catStocksDecimal = @json($categoryStocksDecimal ?? []);
    const catStockFormatted = @json($categoryStockFormatted ?? []);
    const catFullNames = @json($categoryNames ?? []);
    let currentBarUnit = 'discrete';

    const barChartColors = [
        "#275538", "#347b52", "#459365", "#5ca87d", "#77be96", "#95d3af", "#F59E0B", "#D97706", "#475467", "#667085"
    ];

    const barChartOptions = {
        series: [{ name: "Jumlah Stok", data: catStocksDiscrete }],
        chart: {
            type: "bar",
            height: 280,
            fontFamily: "Outfit, sans-serif",
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
        colors: barChartColors,
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: "50%",
                distributed: true,
                dataLabels: { position: 'center' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                if (val <= 0) return '';
                if (currentBarUnit === 'discrete') {
                    return new Intl.NumberFormat('id-ID').format(Math.round(val));
                } else {
                    return Number.isInteger(val) ? new Intl.NumberFormat('id-ID').format(val) : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(val);
                }
            },
            style: {
                fontSize: '10px',
                fontFamily: 'Outfit, sans-serif',
                fontWeight: 'bold',
                colors: ['#FFFFFF']
            },
            dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000000', opacity: 0.6 }
        },
        xaxis: {
            categories: @json($categoryShortNames ?? []),
            axisBorder: { show: true, color: gridColor },
            axisTicks: { show: true, color: gridColor },
            labels: {
                style: { colors: textColor, fontSize: "10px", fontWeight: 700 },
                rotate: 0,
                rotateAlways: false,
                hideOverlappingLabels: true,
                trim: false
            }
        },
        yaxis: {
            min: 0,
            labels: { 
                style: { colors: textColor, fontSize: "11px" },
                formatter: (val) => currentBarUnit === 'discrete' ? Math.round(val) : (Number.isInteger(val) ? val : val.toFixed(1))
            }
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
                    const idx = opts.dataPointIndex;
                    return catFullNames[idx] || val;
                }
            },
            y: {
                formatter: function(val, opts) {
                    const idx = opts.dataPointIndex;
                    return catStockFormatted[idx] || (val + " Unit");
                }
            }
        }
    };

    let barChartInstance = null;
    const barChartEl = document.querySelector("#barChartKategoriStok");
    if (barChartEl && typeof ApexCharts !== 'undefined') {
        barChartInstance = new ApexCharts(barChartEl, barChartOptions);
        setTimeout(() => {
            barChartInstance.render();
        }, 60);
    }

    window.switchBarChartUnit = function(mode) {
        currentBarUnit = mode;
        if (!barChartInstance) return;
        if (mode === 'discrete') {
            barChartInstance.updateSeries([{ name: "Stok (Pcs/Unit)", data: catStocksDiscrete }]);
        } else {
            barChartInstance.updateSeries([{ name: "Stok (Kg/Meter)", data: catStocksDecimal }]);
        }
    };

    // 3. Bar Chart: Pengeluaran Material Terbanyak
    const topItemTotals = @json($topItemTotals ?? []);
    const topItemFormatted = @json($topItemFormatted ?? []);
    const topItemUnits = @json($topItemUnits ?? []);
    const topFullItemNames = @json($topItemNames ?? []);

    const itemOutChartColors = [
        "#D97706", "#F59E0B", "#FBBF24", "#275538", "#347b52", "#459365", "#667085", "#98A2B3"
    ];

    const itemOutChartOptions = {
        series: [{ name: "Total Material Keluar", data: topItemTotals }],
        chart: {
            type: "bar",
            height: 280,
            fontFamily: "Outfit, sans-serif",
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
        colors: itemOutChartColors,
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: "50%",
                distributed: true,
                dataLabels: { position: 'center' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                const idx = opts.dataPointIndex;
                if (val <= 0) return '';
                return topItemFormatted[idx] ? topItemFormatted[idx] : val;
            },
            style: {
                fontSize: '10px',
                fontFamily: 'Outfit, sans-serif',
                fontWeight: 'bold',
                colors: ['#FFFFFF']
            },
            dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000000', opacity: 0.6 }
        },
        xaxis: {
            categories: @json($topItemShortNames ?? []),
            axisBorder: { show: true, color: gridColor },
            axisTicks: { show: true, color: gridColor },
            labels: {
                style: { colors: textColor, fontSize: "10px", fontWeight: 700 },
                rotate: 0,
                rotateAlways: false,
                hideOverlappingLabels: true,
                trim: false
            }
        },
        yaxis: {
            min: 0,
            labels: { 
                style: { colors: textColor, fontSize: "11px" },
                formatter: (val) => Number.isInteger(val) ? val : val.toFixed(1)
            }
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
                    const idx = opts.dataPointIndex;
                    return topFullItemNames[idx] || val;
                }
            },
            y: { 
                formatter: function(val, opts) {
                    const idx = opts.dataPointIndex;
                    return topItemFormatted[idx] ? (topItemFormatted[idx] + " Keluar") : (val + " Keluar");
                }
            }
        }
    };

    const itemOutChartEl = document.querySelector("#chartPengeluaranMaterial");
    if (itemOutChartEl && typeof ApexCharts !== 'undefined') {
        setTimeout(() => {
            new ApexCharts(itemOutChartEl, itemOutChartOptions).render();
        }, 120);
    }

    // 4. Donut Chart: All Inventory Aging
    const agingSeriesData = @json($agingSeries);
    const agingTotalCount = {{ $agingTotalItems }};
    const formattedTotalCount = new Intl.NumberFormat('id-ID').format(agingTotalCount);

    const donutAgingChartOptions = {
        series: agingSeriesData,
        labels: ['<30 Days', '31-60 Days', '61-90 Days', '>90 Days'],
        chart: {
            type: "donut",
            height: 270,
            fontFamily: "Outfit, sans-serif"
        },
        colors: ["#275538", "#459365", "#F59E0B", "#475467"],
        stroke: {
            colors: [isDarkMode ? '#1F2937' : '#FFFFFF'],
            width: 2
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '12px',
                            fontFamily: 'Outfit, sans-serif',
                            fontWeight: '500',
                            color: textColor,
                            offsetY: -4
                        },
                        value: {
                            show: true,
                            fontSize: '22px',
                            fontFamily: 'Outfit, sans-serif',
                            fontWeight: '700',
                            color: isDarkMode ? '#459365' : '#275538',
                            offsetY: 6,
                            formatter: function () {
                                return formattedTotalCount;
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total Items',
                            fontSize: '12px',
                            fontFamily: 'Outfit, sans-serif',
                            fontWeight: '500',
                            color: textColor,
                            formatter: function () {
                                return formattedTotalCount;
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Math.round(val) + '%';
            },
            style: {
                fontSize: '11px',
                fontFamily: 'Outfit, sans-serif',
                fontWeight: 'bold',
                colors: ['#FFFFFF']
            },
            dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000000', opacity: 0.5 }
        },
        legend: {
            show: true,
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '11px',
            fontFamily: 'Outfit, sans-serif',
            fontWeight: 500,
            labels: { colors: textColor },
            markers: { width: 8, height: 8, radius: 4 },
            itemMargin: { horizontal: 6, vertical: 2 }
        },
        tooltip: {
            theme: isDarkMode ? 'dark' : 'light',
            y: { formatter: (val) => val + " Item" }
        }
    };

    const donutAgingChartEl = document.querySelector("#donutChartInventoryAging");
    if (donutAgingChartEl && typeof ApexCharts !== 'undefined' && agingTotalCount > 0) {
        setTimeout(() => {
            new ApexCharts(donutAgingChartEl, donutAgingChartOptions).render();
        }, 180);
    }

    // 5. Bar Chart: Pemakaian Material per Project
    const prjDiscreteTotals = @json($chartProjectDiscreteTotals ?? []);
    const prjDecimalTotals = @json($chartProjectDecimalTotals ?? []);
    const prjFormattedTotals = @json($chartProjectFormatted ?? []);
    const prjFullNames = @json($chartProjectFullNames ?? []);
    let currentPrjMode = 'discrete';

    const projectChartColors = [
        "#275538", "#347b52", "#459365", "#5ca87d", "#77be96", "#95d3af", "#F59E0B", "#D97706"
    ];

    const projectChartOptions = {
        series: [{ name: "Material Digunakan (Pcs/Unit)", data: prjDiscreteTotals }],
        chart: {
            type: "bar",
            height: 300,
            fontFamily: "Outfit, sans-serif",
            toolbar: {
                show: true,
                offsetX: -15,
                tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false }
            }
        },
        colors: projectChartColors,
        plotOptions: {
            bar: {
                borderRadius: 5,
                columnWidth: "50%",
                distributed: true,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                if (val <= 0) return '';
                if (currentPrjMode === 'discrete') {
                    return new Intl.NumberFormat('id-ID').format(Math.round(val));
                } else {
                    return Number.isInteger(val) ? new Intl.NumberFormat('id-ID').format(val) : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(val);
                }
            },
            offsetY: -18,
            style: {
                fontSize: '10px',
                fontFamily: 'Outfit, sans-serif',
                fontWeight: 'bold',
                colors: [isDarkMode ? '#D1D5DB' : '#374151']
            }
        },
        xaxis: {
            categories: @json($chartProjectNames ?? []),
            axisBorder: { show: true, color: gridColor },
            axisTicks: { show: true, color: gridColor },
            labels: {
                style: { colors: textColor, fontSize: "10px", fontWeight: 700 },
                rotate: -20,
                rotateAlways: false,
                hideOverlappingLabels: true,
                trim: true
            }
        },
        yaxis: {
            min: 0,
            labels: { 
                style: { colors: textColor, fontSize: "11px" },
                formatter: (val) => currentPrjMode === 'discrete' ? Math.round(val) : (Number.isInteger(val) ? val : val.toFixed(1))
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 3,
            padding: { left: 10, right: 15, top: 18 }
        },
        legend: { show: false },
        tooltip: {
            theme: isDarkMode ? 'dark' : 'light',
            x: {
                formatter: function(val, opts) {
                    const idx = opts.dataPointIndex;
                    return prjFullNames[idx] || val;
                }
            },
            y: { 
                formatter: function(val, opts) {
                    const idx = opts.dataPointIndex;
                    return prjFormattedTotals[idx] || (val + " Material");
                }
            }
        }
    };

    let projectChartInstance = null;
    const projectChartEl = document.querySelector("#chartMaterialPerProject");
    if (projectChartEl && typeof ApexCharts !== 'undefined') {
        projectChartInstance = new ApexCharts(projectChartEl, projectChartOptions);
        setTimeout(() => {
            projectChartInstance.render();
        }, 240);
    }

    window.switchProjectChartMode = function(mode) {
        currentPrjMode = mode;
        if (!projectChartInstance) return;
        if (mode === 'discrete') {
            projectChartInstance.updateSeries([{ name: "Material Digunakan (Pcs/Unit)", data: prjDiscreteTotals }]);
        } else {
            projectChartInstance.updateSeries([{ name: "Material Digunakan (Kg/Meter)", data: prjDecimalTotals }]);
        }
    };
});
</script>
@endsection

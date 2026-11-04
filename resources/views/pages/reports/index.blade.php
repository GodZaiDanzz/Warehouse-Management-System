@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Mutasi & Stok Gudang</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Rekapitulasi penerimaan, pengeluaran, dan audit persediaan barang</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Laporan
            </button>
            <a href="{{ route('items.export') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6 print:hidden">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Katalog Item</span>
            <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalItemsCount) }} Item</h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Barang Masuk (Periode)</span>
            <h3 class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">+{{ format_quantity($totalIn) }} Unit</h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Barang Keluar (Periode)</span>
            <h3 class="mt-2 text-2xl font-bold text-rose-600 dark:text-rose-400">-{{ format_quantity($totalOut) }} Unit</h3>
        </div>
    </div>

    <!-- MODUL LAPORAN & ANALISIS GUDANG (WMS) -->
    <div class="space-y-3 print:hidden">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Modul Laporan Resmi & Analisis Gudang WMS</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Format cetak & unduh laporan resmi sesuai standar audit, mutu QC, dan kebutuhan persediaan PPIC</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <!-- 1. Laporan Inspeksi Incoming -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between hover:border-brand-500 transition">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                            QC & INBOUND
                        </span>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Laporan Inspeksi Incoming & Cat</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Pemeriksaan fisik material masuk, status QC (OK/Reject), dan tanggal pembuatan cat.</p>
                </div>
                <div class="pt-4 mt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <a href="{{ route('reports.incoming-inspection') }}" class="flex-1 text-center rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                        Buka
                    </a>
                    <a href="{{ route('reports.incoming-inspection.export') }}" title="Unduh Excel" class="p-1.5 rounded-xl border border-gray-200 hover:bg-emerald-50 dark:border-gray-700 dark:hover:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                    <a href="{{ route('reports.incoming-inspection.pdf') }}" title="Unduh PDF" class="p-1.5 rounded-xl border border-gray-200 hover:bg-rose-50 dark:border-gray-700 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- 2. Laporan Pengeluaran Cat -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between hover:border-brand-500 transition">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c.6 0 1.16.13 1.67.36l6.63-6.63A2 2 0 0116.71 6a2 2 0 011.41.59l.88.88a2 2 0 010 2.83l-6.63 6.63c.23.51.36 1.07.36 1.67a4 4 0 01-4 4z"/></svg>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                            PAINTING
                        </span>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Laporan Pengeluaran Cat</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Mutasi keluar material cat, tracking Manufacturing Date, dan project pengguna.</p>
                </div>
                <div class="pt-4 mt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <a href="{{ route('reports.cat-painting') }}" class="flex-1 text-center rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                        Buka
                    </a>
                    <a href="{{ route('reports.cat-painting.export') }}" title="Unduh Excel" class="p-1.5 rounded-xl border border-gray-200 hover:bg-emerald-50 dark:border-gray-700 dark:hover:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                    <a href="{{ route('reports.cat-painting.pdf') }}" title="Unduh PDF" class="p-1.5 rounded-xl border border-gray-200 hover:bg-rose-50 dark:border-gray-700 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- 3. Laporan Pengeluaran Secondary Part -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between hover:border-brand-500 transition">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                            SECONDARY PART
                        </span>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Laporan Pengeluaran Secondary Part</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Pencatatan mutasi keluar komponen secondary part dan alokasi per project.</p>
                </div>
                <div class="pt-4 mt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <a href="{{ route('reports.secondary-part') }}" class="flex-1 text-center rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                        Buka
                    </a>
                    <a href="{{ route('reports.secondary-part.export') }}" title="Unduh Excel" class="p-1.5 rounded-xl border border-gray-200 hover:bg-emerald-50 dark:border-gray-700 dark:hover:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                    <a href="{{ route('reports.secondary-part.pdf') }}" title="Unduh PDF" class="p-1.5 rounded-xl border border-gray-200 hover:bg-rose-50 dark:border-gray-700 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- 4. Laporan Pengeluaran Consumable -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between hover:border-brand-500 transition">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                            CONSUMABLE
                        </span>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Laporan Pengeluaran Consumable</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Rekapitulasi consumable las & fabrikasi per blok material, grafik, & kesimpulan rata.</p>
                </div>
                <div class="pt-4 mt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <a href="{{ route('reports.consumable') }}" class="flex-1 text-center rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                        Buka
                    </a>
                    <a href="{{ route('reports.consumable.export') }}" title="Unduh Excel" class="p-1.5 rounded-xl border border-gray-200 hover:bg-emerald-50 dark:border-gray-700 dark:hover:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                    <a href="{{ route('reports.consumable.pdf') }}" title="Unduh PDF" class="p-1.5 rounded-xl border border-gray-200 hover:bg-rose-50 dark:border-gray-700 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- 5. Analisis Slow-Moving & Dead Stock -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between hover:border-brand-500 transition">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            INVENTORY AGING
                        </span>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Analisis Slow & Dead Stock</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Klasifikasi barang lambat/tidak bergerak > 180 hari dan rekomendasi tindakan stok.</p>
                </div>
                <div class="pt-4 mt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <a href="{{ route('reports.slow-moving-stock') }}" class="flex-1 text-center rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                        Buka
                    </a>
                    <a href="{{ route('reports.slow-moving-stock.export') }}" title="Unduh Excel" class="p-1.5 rounded-xl border border-gray-200 hover:bg-emerald-50 dark:border-gray-700 dark:hover:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                    <a href="{{ route('reports.slow-moving-stock.pdf') }}" title="Unduh PDF" class="p-1.5 rounded-xl border border-gray-200 hover:bg-rose-50 dark:border-gray-700 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form with Quick Date Buttons -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm print:hidden" x-data="{ 
        startDate: '{{ request('start_date') }}', 
        endDate: '{{ request('end_date') }}',
        setToday() {
            let now = new Date();
            let d = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
            this.startDate = d;
            this.endDate = d;
        },
        setLast7Days() {
            let today = new Date();
            let past = new Date();
            past.setDate(today.getDate() - 7);
            this.endDate = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
            this.startDate = past.getFullYear() + '-' + String(past.getMonth() + 1).padStart(2, '0') + '-' + String(past.getDate()).padStart(2, '0');
        },
        setThisMonth() {
            let today = new Date();
            let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            let lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            this.startDate = firstDay.getFullYear() + '-' + String(firstDay.getMonth() + 1).padStart(2, '0') + '-' + String(firstDay.getDate()).padStart(2, '0');
            this.endDate = lastDay.getFullYear() + '-' + String(lastDay.getMonth() + 1).padStart(2, '0') + '-' + String(lastDay.getDate()).padStart(2, '0');
        }
    }">
        <!-- Quick Date Shortcut Buttons -->
        <div class="flex items-center gap-2 mb-3 pb-3 border-b border-gray-100 dark:border-gray-800 overflow-x-auto">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 whitespace-nowrap">Pilihan Cepat Tanggal:</span>
            <button type="button" @click="setToday()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-brand-50 hover:text-brand-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer whitespace-nowrap">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Hari Ini</span>
            </button>
            <button type="button" @click="setLast7Days()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-brand-50 hover:text-brand-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer whitespace-nowrap">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>7 Hari Terakhir</span>
            </button>
            <button type="button" @click="setThisMonth()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-brand-50 hover:text-brand-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer whitespace-nowrap">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Bulan Ini</span>
            </button>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Search Text -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode barang, penerima, catatan..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
            </div>

            <!-- Transaction Type -->
            <div class="w-full sm:w-44">
                <select name="type" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>↓ Barang Masuk</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>↑ Barang Keluar</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="w-full sm:w-48">
                <select name="category_id" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range Inputs (Flatpickr day/month/year calendar) -->
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <div class="relative" x-data="{
                    fp: null,
                    init() {
                        const initPicker = () => {
                            if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                            this.fp = flatpickr(this.$refs.picker, {
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd/m/Y',
                                altInputClass: 'w-36 sm:w-38 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white cursor-pointer transition',
                                defaultDate: startDate || null,
                                onChange: (dates, dateStr) => { startDate = dateStr; }
                            });
                        };
                        this.$nextTick(() => initPicker());
                        this.$watch('startDate', val => {
                            if (this.fp && val !== this.fp.input.value) { this.fp.setDate(val, false); }
                        });
                    },
                    destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                }">
                    <input x-ref="picker" type="text" name="start_date" class="hidden" :value="startDate">
                </div>

                <span class="text-xs text-gray-400 font-bold">s/d</span>

                <div class="relative" x-data="{
                    fp: null,
                    init() {
                        const initPicker = () => {
                            if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                            this.fp = flatpickr(this.$refs.picker, {
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd/m/Y',
                                altInputClass: 'w-36 sm:w-38 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white cursor-pointer transition',
                                defaultDate: endDate || null,
                                onChange: (dates, dateStr) => { endDate = dateStr; }
                            });
                        };
                        this.$nextTick(() => initPicker());
                        this.$watch('endDate', val => {
                            if (this.fp && val !== this.fp.input.value) { this.fp.setDate(val, false); }
                        });
                    },
                    destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                }">
                    <input x-ref="picker" type="text" name="end_date" class="hidden" :value="endDate">
                </div>
            </div>

            <!-- Data Display / Per Page Selector -->
            <div class="w-full sm:w-44">
                <select name="per_page" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 data per halaman</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 data per halaman</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 data per halaman</option>
                    <option value="all" {{ $perPage === 'all' ? 'selected' : '' }}>Semua Data (Full)</option>
                </select>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="btn-other-theme rounded-xl bg-brand-600 hover:bg-brand-700 px-5 py-2.5 text-sm font-semibold text-white transition cursor-pointer shadow-xs">
                    Cari
                </button>
                @if(request('type') || request('category_id') || request('start_date') || request('end_date') || request('search') || request('per_page'))
                    <a href="{{ route('reports.index') }}" class="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Printable Report Sheet -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-sm p-6 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="text-center border-b border-gray-200 dark:border-gray-800 pb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">LAPORAN MUTASI & PERSEDIAAN STOK GUDANG</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">PT MUDALAYA ENERGY INDONESIA - DIVISI EPC & GENERAL CONTRACTOR</p>
            <p class="text-[11px] text-gray-400 mt-1">Dicetak pada: {{ format_datetime(now()) }} WIB</p>
        </div>

        <!-- Showing / Pagination Info Sub-Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pb-2 border-b border-gray-100 dark:border-gray-800 print:hidden text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-2 flex-wrap">
                @if($perPage === 'all' || $logs->total() <= $logs->perPage())
                    <span>Menampilkan seluruh <strong>{{ number_format($logs->total()) }}</strong> transaksi mutasi</span>
                @else
                    <span>Menampilkan data ke-<strong>{{ $logs->firstItem() ?? 0 }}</strong> sampai <strong>{{ $logs->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($logs->total()) }}</strong> transaksi</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                        Halaman {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                    </span>
                @endif
                @if($isFiltered)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                        <svg class="w-3 h-3 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Hasil Filter</span>
                    </span>
                @endif
            </div>

            <div class="text-[11px] text-gray-400">
                @if($perPage === 'all')
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Laporan menampilkan seluruh data</span>
                    </span>
                @else
                    <span>{{ $logs->perPage() }} data per halaman</span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Kode Material</th>
                        <th class="px-4 py-3">Nama Barang</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
                        <th class="px-4 py-3">Penyedia / Penerima</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $index => $log)
                        <tr>
                            <td class="px-4 py-3 text-xs font-bold">{{ $logs->firstItem() ? $logs->firstItem() + $index : $index + 1 }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-bold {{ $log->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $log->type == 'in' ? 'BARANG MASUK' : 'BARANG KELUAR' }}
                            </td>
                            <!-- KODE MATERIAL -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $log->item?->code_item ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $log->item?->name }}</div>
                                @if($log->item && $log->item->isCat())
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Tgl Pembuatan: <span class="font-medium text-gray-700 dark:text-gray-300">{{ format_date($log->item->manufacturing_date, 'Belum diisi') }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $log->item?->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center font-bold {{ $log->type == 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $log->type == 'in' ? '+' : '-' }}{{ format_quantity($log->quantity) }} {{ $log->item?->unit }}
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold">{{ $log->recipient_or_provider }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $log->notes ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap">{{ format_datetime($log->created_at) }}</td>
                        </tr>
                    @empty
                        @php
                            $hasActiveReportFilters = request('search') || request('type') || request('category_id') || request('start_date') || request('end_date');
                        @endphp
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="max-w-md mx-auto flex flex-col items-center justify-center text-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    @if($hasActiveReportFilters)
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak ada transaksi mutasi yang cocok</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tidak ditemukan riwayat mutasi barang masuk/keluar pada rentang tanggal atau kriteria filter terpilih.</p>
                                        <div class="mt-4">
                                            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs transition">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reset Filter</span>
                                            </a>
                                        </div>
                                    @else
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Riwayat Transaksi Mutasi</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sistem belum mencatat transaksi penerimaan maupun pengeluaran barang gudang.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        @if($logs->hasPages())
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 print:hidden">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Page Header & Actions -->
    <div class="print:hidden space-y-3">
        <!-- Breadcrumb / Shortcut Nav -->
        <div class="flex items-center justify-between gap-2">
            <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-600 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('reports.index') }}" class="hover:text-brand-600 transition">Laporan</a>
                <span>/</span>
                <span class="font-bold text-gray-800 dark:text-gray-200">Material Rusak & Reject</span>
            </nav>
            <a href="{{ route('material-in.index', ['tab' => 'rejects']) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kelola Reject di Material Masuk</span>
            </a>
        </div>

        <!-- Main Title & Document Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Laporan Material Rusak / Reject</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pencatatan kasus cacat barang masuk, disposisi karantina/retur supplier, serta Berita Acara Pengembalian (BAP).</p>
            </div>

            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <a href="{{ route('reports.reject-material.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-2xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Excel</span>
                </a>
                <a href="{{ route('reports.reject-material.pdf', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-rose-700 shadow-2xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Download PDF</span>
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Structured KPI Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 print:hidden">
        <!-- Primary Total Kasus Reject -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kasus Reject</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalCount) }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">Insiden barang cacat tercatat</p>
            </div>
        </div>

        <!-- Total Kuantitas Terdampak -->
        <div class="rounded-2xl border border-gray-200/80 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kuantitas Rusak</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ format_quantity($totalQty) }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">Unit fisik terdampak</p>
            </div>
        </div>

        <!-- Pipeline: Menunggu Tindak Lanjut -->
        <div class="rounded-2xl border border-rose-100 bg-rose-50/30 p-4.5 dark:border-rose-900/40 dark:bg-rose-950/20 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Karantina Gudang</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100/80 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($totalPending) }}</h3>
                <p class="text-xs text-rose-600/80 dark:text-rose-400/80 mt-0.5">Menunggu keputusan/tindak lanjut</p>
            </div>
        </div>

        <!-- Pipeline: Dalam Proses Retur / Klaim -->
        <div class="rounded-2xl border border-blue-100 bg-blue-50/30 p-4.5 dark:border-blue-900/40 dark:bg-blue-950/20 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">Proses Retur / Klaim</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/80 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400 font-mono">{{ number_format($totalInProgress) }}</h3>
                <p class="text-xs text-blue-600/80 dark:text-blue-400/80 mt-0.5">Proses koordinasi vendor</p>
            </div>
        </div>

        <!-- Pipeline: Selesai / Tuntas -->
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-4.5 dark:border-emerald-900/40 dark:bg-emerald-950/20 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Selesai / Tuntas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100/80 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($totalCompleted) }}</h3>
                <p class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">Diselesaikan / diganti baru</p>
            </div>
        </div>
    </div>

    <!-- Unified Filter Panel -->
    <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-2xs dark:border-gray-800 dark:bg-gray-900 print:hidden space-y-4">
        <form method="GET" action="{{ route('reports.reject-material') }}" class="space-y-4">
            <!-- Row 1: Search & Date Range -->
            <div class="grid grid-cols-1 gap-3 md:grid-cols-12 items-end">
                <!-- Search Input -->
                <div class="md:col-span-6">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Pencarian Kata Kunci</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. BAP, nama barang, kode item, PO, atau supplier..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:border-brand-500 transition">
                    </div>
                </div>

                <!-- Date Range (Flatpickr) -->
                <div class="md:col-span-6">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Periode Tanggal Reject</label>
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

                        <span class="text-xs font-bold text-gray-400 shrink-0">s/d</span>

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
            </div>

            <!-- Row 2: Secondary Filters & Action Buttons -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 items-end pt-1">
                <!-- Status Disposisi -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status Disposisi</label>
                    <select name="disposition_status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu Tindak Lanjut</option>
                        <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Dalam Proses Retur/Klaim</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai (Tuntas)</option>
                    </select>
                </div>

                <!-- Jenis Kerusakan -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Jenis Kerusakan</label>
                    <select name="damage_type" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="all" {{ $damageType === 'all' ? 'selected' : '' }}>Semua Kerusakan</option>
                        @foreach($damageTypesList as $dt)
                            <option value="{{ $dt }}" {{ $damageType === $dt ? 'selected' : '' }}>{{ $dt }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Supplier / Vendor -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Supplier / Vendor</label>
                    <select name="supplier" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliersList as $s)
                            <option value="{{ $s }}" {{ $supplier === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kategori Barang (Supporting Cat Umbrella) -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Kategori Barang</label>
                    <select name="category_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="all">Semua Kategori</option>
                        @foreach($categoriesList ?? [] as $c)
                            <option value="{{ $c->id }}" {{ (string)($categoryId ?? 'all') === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->isCatUmbrella() ? '(Semua Cat)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-brand-600 px-3 py-2 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer shadow-2xs">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('reports.reject-material') }}" class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition text-center cursor-pointer inline-flex items-center gap-1.5 shadow-2xs" title="Reset Filter">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset</span>
                    </a>
                </div>
            </div>

            <!-- Active Filters Summary Tag (if active) -->
            @php
                $hasActiveFilters = request('search') || ($status !== 'all') || ($damageType !== 'all') || !empty($supplier) || (($categoryId ?? 'all') !== 'all');
            @endphp
            @if($hasActiveFilters)
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-gray-400 font-medium">Filter aktif:</span>
                    @if(request('search'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-[11px]">
                            Cari: "{{ request('search') }}"
                        </span>
                    @endif
                    @if($status !== 'all')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 text-[11px] font-semibold">
                            Status: {{ $status }}
                        </span>
                    @endif
                    @if($damageType !== 'all')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 text-[11px] font-semibold">
                            Kerusakan: {{ $damageType }}
                        </span>
                    @endif
                    @if($supplier)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 text-[11px] font-semibold">
                            Supplier: {{ $supplier }}
                        </span>
                    @endif
                    @if(($categoryId ?? 'all') !== 'all')
                        @php $activeCat = ($categoriesList ?? collect())->firstWhere('id', $categoryId); @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 text-[11px] font-semibold">
                            Kategori: {{ $activeCat->name ?? 'Terpilih' }}
                        </span>
                    @endif
                </div>
            @endif
        </form>
    </div>

    <!-- Print Only Header -->
    <div class="hidden print:block text-center border-b-2 border-gray-900 pb-4 mb-4">
        <h2 class="text-lg font-black uppercase tracking-wider">PT MUDALAYA ENERGY INDONESIA</h2>
        <h3 class="text-sm font-bold uppercase mt-1">LAPORAN REKAPITULASI MATERIAL RUSAK / REJECT & RETUR VENDOR</h3>
        <p class="text-xs text-gray-600 mt-0.5">Periode: {{ format_date($startDate) }} s/d {{ format_date($endDate) }}</p>
    </div>

    <!-- Data Table Card -->
    <div class="rounded-2xl border border-gray-200/80 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-400">
                <thead class="theme-table-header text-[11px] font-bold uppercase tracking-wider text-white border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-3.5 text-center w-12">No</th>
                        <th class="px-3 py-3.5">Tgl & No. BAP</th>
                        <th class="px-3 py-3.5">Supplier / Vendor</th>
                        <th class="px-3 py-3.5 w-28">Kode Material</th>
                        <th class="px-3 py-3.5">Nama Barang</th>
                        <th class="px-3 py-3.5 text-center">Kuantitas</th>
                        <th class="px-3 py-3.5">Jenis Kerusakan</th>
                        <th class="px-3 py-3.5">Status Disposisi</th>
                        <th class="px-3 py-3.5">Catatan Penyelesaian</th>
                        <th class="px-3 py-3.5 text-center print:hidden w-28">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($records as $idx => $row)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition">
                            <td class="px-3 py-3 text-center font-bold text-gray-500">
                                {{ $records->firstItem() + $idx }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-bold text-gray-900 dark:text-white">{{ format_date($row->date) }}</div>
                                <span class="font-mono text-[10px] text-brand-600 dark:text-brand-400 font-bold bg-brand-50/80 dark:bg-brand-950/40 px-1.5 py-0.5 rounded">
                                    {{ $row->reference_number }}
                                </span>
                            </td>
                            <td class="px-3 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                <div>{{ $row->supplier }}</div>
                                @if($row->po_number || ($row->materialIn && $row->materialIn->po_number))
                                    <div class="text-[10px] text-gray-500 font-normal mt-0.5">PO: {{ $row->po_number ?? $row->materialIn->po_number }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 font-mono font-bold text-gray-800 dark:text-gray-200">
                                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $row->item->code_item ?? '-' }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $row->item->name ?? '-' }}</div>
                                <div class="mt-0.5 flex items-center gap-1.5 text-[10px] text-gray-500">
                                    <span>{{ $row->item->category->name ?? 'Consumable' }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-black font-mono text-xs">
                                    {{ format_quantity($row->quantity) }} {{ $row->unit }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                    {{ $row->damage_type }}
                                </span>
                                @if($row->description)
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $row->description }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @if($row->disposition_status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                        <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        {{ $row->status_label }}
                                    </span>
                                @elseif($row->disposition_status === 'in_progress')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                        <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        {{ $row->status_label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                                        <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $row->status_label }}
                                    </span>
                                @endif
                                <div class="text-[10px] text-gray-500 mt-1">Rencana: {{ $row->disposition_label }}</div>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                {{ $row->resolution_notes ?: '-' }}
                                @if($row->resolved_at)
                                    <div class="text-[10px] text-gray-400 mt-0.5">Selesai: {{ format_date($row->resolved_at) }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center print:hidden">
                                <a href="{{ route('material-in.rejects.bap-pdf', $row->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white border border-rose-200 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300 text-[11px] font-bold transition cursor-pointer shadow-2xs" title="Unduh Berita Acara Pengembalian (BAP) Resmi">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span>Cetak BAP</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-14 text-center">
                                <div class="max-w-md mx-auto flex flex-col items-center justify-center text-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    @if($hasActiveFilters)
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak ada material reject yang cocok</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tidak ditemukan data reject berdasarkan filter tanggal, status, atau kata kunci yang Anda pilih.</p>
                                        <div class="mt-4">
                                            <a href="{{ route('reports.reject-material') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 shadow-2xs transition">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reset Filter</span>
                                            </a>
                                        </div>
                                    @else
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Catatan Material Rusak / Reject</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Seluruh material yang masuk gudang saat ini tercatat dalam kondisi baik dan lolos uji kesesuaian mutu QC.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-800 print:hidden">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    <!-- Printable Signature Section -->
    <div class="hidden print:grid grid-cols-4 gap-6 pt-10 text-center text-xs">
        <div>
            <p class="text-gray-600">Petugas Gudang,</p>
            <div class="h-16"></div>
            <p class="font-bold border-t border-gray-800 pt-1">( ____________________ )</p>
        </div>
        <div>
            <p class="text-gray-600">Quality Control (QC),</p>
            <div class="h-16"></div>
            <p class="font-bold border-t border-gray-800 pt-1">( ____________________ )</p>
        </div>
        <div>
            <p class="text-gray-600">PPIC Planning,</p>
            <div class="h-16"></div>
            <p class="font-bold border-t border-gray-800 pt-1">( ____________________ )</p>
        </div>
        <div>
            <p class="text-gray-600">Kepala Gudang & Logistik,</p>
            <div class="h-16"></div>
            <p class="font-bold border-t border-gray-800 pt-1">( ____________________ )</p>
        </div>
    </div>

</div>
@endsection

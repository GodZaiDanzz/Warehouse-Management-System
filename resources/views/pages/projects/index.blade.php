@extends('layouts.app')

@section('content')
<div x-data="{ 
    openCreateModal: false, 
    openEditModal: false, 
    editData: { id: '', code: '', name: '', location: '', status: 'Ongoing', description: '' }
}" class="p-4 sm:p-6 space-y-6">



    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white mt-1">Rekapitulasi Penyerapan Material per Project</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan total volume material yang digunakan oleh setiap project aktif PT Mudalaya Energy Indonesia.</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
        <!-- Total Projects -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Project</span>
                    <h3 class="mt-2 text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($totalOverallProjects) }} Project</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Seluruh project terdaftar di sistem</p>
        </div>

        <!-- Ongoing Projects -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Project Berjalan</span>
                    <h3 class="mt-2 text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($ongoingProjectsCount) }} Project</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Status Berjalan / Belum Selesai</p>
        </div>

        <!-- Completed Projects -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Project Selesai</span>
                    <h3 class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($completedProjectsCount) }} Project</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Pekerjaan Rampung (Completed)</p>
        </div>

        <!-- Total Material Out -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Material Terpakai</span>
                    <div class="flex items-baseline gap-1.5 mt-2">
                        <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($overallDiscreteQty ?? 0, 0, ',', '.') }}</h3>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Pcs/Unit</span>
                    </div>
                    @if(!empty($overallDecimalString))
                        <div class="mt-1">
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 font-mono">
                                + {{ $overallDecimalString }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Akumulasi pengeluaran material</p>
        </div>
    </div>

    <!-- Chart Top Projects -->
    @if(!empty($chartProjectNames))
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 print:hidden space-y-3"
             x-data="{ prjUnitMode: 'discrete' }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
                <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Grafik Top 8 Penyerapan Material per Project</span>
                </h3>
                <div class="flex items-center p-0.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold">
                    <button type="button" @click="prjUnitMode = 'discrete'; window.switchProjectChartUnit('discrete')"
                            :class="prjUnitMode === 'discrete' ? 'bg-white text-brand-700 shadow-xs dark:bg-gray-700 dark:text-brand-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                        Pcs / Unit
                    </button>
                    <button type="button" @click="prjUnitMode = 'decimal'; window.switchProjectChartUnit('decimal')"
                            :class="prjUnitMode === 'decimal' ? 'bg-white text-amber-700 shadow-xs dark:bg-gray-700 dark:text-amber-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                            class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                        Kg / Meter
                    </button>
                </div>
            </div>
            <div id="projectConsumptionChart" class="h-64 w-full"></div>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 print:hidden space-y-4">
        <form method="GET" action="{{ route('projects.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6 items-end">
                <!-- Periode Tanggal -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Periode Pengeluaran</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <span class="text-xs font-bold text-gray-400">s/d</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status Project</label>
                    <select name="status" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua Status --</option>
                        <option value="Ongoing" {{ request('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing (Berjalan)</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                        <option value="Planning" {{ request('status') == 'Planning' ? 'selected' : '' }}>Planning (Perencanaan)</option>
                        <option value="On Hold" {{ request('status') == 'On Hold' ? 'selected' : '' }}>On Hold (Ditunda)</option>
                    </select>
                </div>

                <!-- Tempat Fabrikasi Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Tempat Fabrikasi</label>
                    <select name="location" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua Fabrikasi --</option>
                        @foreach($fabricationLocations ?? [] as $loc)
                            <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Kategori Material</label>
                    <select name="category_id" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Cari Project</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, tempat fabrikasi..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-100 dark:border-gray-800 pt-3">
                <div class="flex items-center gap-2">
                    <button type="button" @click="openCreateModal = true" class="btn-add-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah Project Baru
                    </button>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition cursor-pointer shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Laporan
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('projects.index') }}" class="rounded-xl border border-gray-200 bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">
                Daftar & Penyerapan Material Project
            </h3>
            <span class="text-xs font-bold text-gray-500 font-mono">Total {{ $projects->total() }} Project</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="theme-table-header text-white font-extrabold uppercase border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-center w-12 border-r border-white/10">NO</th>
                        <th class="px-4 py-3 min-w-[260px] border-r border-white/10">PROJECT & TEMPAT FABRIKASI</th>
                        <th class="px-4 py-3 text-center w-36 border-r border-white/10">STATUS</th>
                        <th class="px-4 py-3 text-center w-36 border-r border-white/10">TRANSAKSI</th>
                        <th class="px-4 py-3 text-center w-40 border-r border-white/10">TERPAKAI</th>
                        <th class="px-4 py-3 text-center w-36 border-r border-white/10">RIWAYAT</th>
                        <th class="px-4 py-3 text-center min-w-[320px]">UBAH STATUS & AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($projects as $index => $row)
                        <tr class="hover:bg-gray-50/80 transition dark:hover:bg-gray-800/50">
                            <!-- NO -->
                            <td class="px-4 py-3 text-center font-bold text-gray-500 border-r border-gray-100 dark:border-gray-800">
                                {{ $projects->firstItem() ? $projects->firstItem() + $index : $index + 1 }}
                            </td>
                            <!-- PROJECT NAME, CODE & FABRICATION LOCATION -->
                            <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-800">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('projects.show', $row->id) }}" class="font-extrabold text-gray-900 hover:text-brand-600 hover:underline dark:text-white dark:hover:text-brand-400 uppercase text-xs">
                                            {{ $row->name }}
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if($row->code)
                                            <span class="inline-flex items-center gap-1 font-mono text-[10px] font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-700 tracking-wider">
                                                <svg class="w-2.5 h-2.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                {{ $row->code }}
                                            </span>
                                        @endif
                                        @if($row->location)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded border border-indigo-200 dark:border-indigo-800 shadow-2xs" title="Tempat Fabrikasi">
                                                <svg class="w-2.5 h-2.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                <span>{{ $row->location }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <!-- STATUS BADGE -->
                            <td class="px-4 py-3 text-center border-r border-gray-100 dark:border-gray-800">
                                @if($row->status === 'Completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-700 shadow-2xs">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Completed</span>
                                    </span>
                                @elseif($row->status === 'Ongoing')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-brand-50 text-brand-700 border border-brand-200 dark:bg-brand-950/80 dark:text-brand-300 dark:border-brand-800 shadow-2xs">
                                        <!-- <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> -->
                                        <span>Ongoing</span>
                                    </span>
                                @elseif($row->status === 'Planning')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/80 dark:text-indigo-300 dark:border-indigo-700 shadow-2xs">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <span>Planning</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-gray-100 text-gray-800 border border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 shadow-2xs">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"/></svg>
                                        <span>On Hold</span>
                                    </span>
                                @endif
                            </td>
                            <!-- TOTAL TRANSAKSI -->
                            <td class="px-4 py-3 text-center font-mono font-bold text-gray-700 dark:text-gray-300 border-r border-gray-100 dark:border-gray-800">
                                {{ number_format($row->total_trans ?? 0) }} Kali
                            </td>
                            <!-- TOTAL VOLUME -->
                            <td class="px-4 py-3 text-center font-mono border-r border-gray-100 dark:border-gray-800">
                                @if(($row->discrete_qty ?? 0) > 0 || !empty($row->decimal_string))
                                    <div class="inline-flex flex-col items-center gap-1">
                                        @if(($row->discrete_qty ?? 0) > 0)
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300">
                                                -{{ number_format($row->discrete_qty, 0, ',', '.') }} Unit
                                            </span>
                                        @endif
                                        @if(!empty($row->decimal_string))
                                            <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-400">
                                                + {{ $row->decimal_string }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 font-bold">0 Unit</span>
                                @endif
                            </td>
                            <!-- TRANSAKSI TERAKHIR -->
                            <td class="px-4 py-3 text-center font-mono text-gray-600 dark:text-gray-400 border-r border-gray-100 dark:border-gray-800">
                                {{ format_date($row->last_date) }}
                            </td>
                            <!-- AKSI & QUICK STATUS TOGGLE -->
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- BUTTON 1: QUICK TOGGLE STATUS -->
                                    <form method="POST" action="{{ route('projects.toggle-status', $row->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($row->status === 'Completed')
                                            <button type="submit" title="Ubah status menjadi Berjalan (Ongoing)" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/40 transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        @else
                                            <button type="submit" title="Tandai Project Sudah Selesai (Completed)" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/40 transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        @endif
                                    </form>

                                    <!-- BUTTON 2: EDIT RINCIAN PROJECT -->
                                    <button type="button" @click="editData = {
                                        id: '{{ $row->id }}',
                                        code: '{{ addslashes($row->code ?? '') }}',
                                        name: '{{ addslashes($row->name) }}',
                                        location: '{{ addslashes($row->location ?? '') }}',
                                        status: '{{ $row->status }}',
                                        description: '{{ addslashes($row->description ?? '') }}'
                                    }; openEditModal = true" class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-950/40 transition cursor-pointer" title="Edit Rincian Data Project">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- BUTTON 3: DETAIL PROJECT -->
                                    <a href="{{ route('projects.show', $row->id) }}" class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40 transition cursor-pointer" title="Lihat Detail Rincian Material Project">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>

                                    <!-- BUTTON 4: HAPUS PROJECT -->
                                    <form id="delete-project-form-{{ $row->id }}" action="{{ route('projects.destroy', $row->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" @click="confirmDelete('Apakah Anda yakin ingin menghapus project {{ addslashes($row->name) }}?', () => { document.getElementById('delete-project-form-{{ $row->id }}').submit(); })" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus Project">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @php
                            $hasActiveFilters = request('search') || request('status') || request('location') || request('category_id');
                        @endphp
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center justify-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3.5">
                                        @if($hasActiveFilters)
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @else
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        @endif
                                    </div>
                                    @if($hasActiveFilters)
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada project yang cocok</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tidak ada project yang memenuhi kriteria pencarian atau filter yang dipilih.</p>
                                        <a href="{{ route('projects.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                                            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Reset Filter
                                        </a>
                                    @else
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Belum ada project terdaftar</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mulai daftarkan project baru untuk mengelola alokasi material gudang.</p>
                                        <button type="button" @click="openCreateModal = true" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-700 cursor-pointer transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Tambah Project Baru
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($projects->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL TAMBAH PROJECT BARU -->
    <template x-teleport="body">
        <div x-show="openCreateModal" x-cloak @click.self="openCreateModal = false" @keydown.escape.window="openCreateModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar my-8">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-950/80 dark:text-brand-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Tambah Project Baru</h3>
                    </div>
                    <button type="button" @click="openCreateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('projects.store') }}" class="space-y-4">
                    @csrf
                    <!-- Kode Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kode Project <span class="text-xs font-normal text-gray-400">(Opsional - Otomatis dibuat jika dikosongkan)</span></label>
                        <input type="text" name="code" placeholder="Contoh: PRJ-2026-001 (Kosongkan untuk otomatis)" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Nama Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Project / Pekerjaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" placeholder="Contoh: Pekerjaan Fabrikasi Piping Belawan" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Tempat Fabrikasi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tempat Fabrikasi <span class="text-xs font-normal text-gray-400">(Workshop / Lokasi Fabrikasi)</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="location" list="fabrication_locations_list" placeholder="Pilih atau ketik nama tempat fabrikasi (misal: Workshop 1, Workshop 2)..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 pl-9 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Status Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Project <span class="text-rose-500">*</span></label>
                        <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="Ongoing" selected>Ongoing (Berjalan)</option>
                            <option value="Completed">Completed (Selesai)</option>
                            <option value="Planning">Planning (Perencanaan)</option>
                            <option value="On Hold">On Hold (Ditunda)</option>
                        </select>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Keterangan / Deskripsi Project</label>
                        <textarea name="description" rows="2" placeholder="Catatan tambahan mengenai lingkup project..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openCreateModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="btn-save-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white hover:bg-brand-700 focus:outline-none shadow-sm transition cursor-pointer">
                            Simpan Project Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT PROJECT -->
    <template x-teleport="body">
        <div x-show="openEditModal" x-cloak @click.self="openEditModal = false" @keydown.escape.window="openEditModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar my-8">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Edit Data Project</h3>
                    </div>
                    <button type="button" @click="openEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/projects') }}/' + editData.id" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Kode Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kode Project</label>
                        <input type="text" name="code" x-model="editData.code" placeholder="PRJ-..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono font-bold uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Nama Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Project / Pekerjaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editData.name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Tempat Fabrikasi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tempat Fabrikasi <span class="text-xs font-normal text-gray-400">(Workshop / Lokasi Fabrikasi)</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="location" x-model="editData.location" list="fabrication_locations_list" placeholder="Pilih atau ketik nama tempat fabrikasi (misal: Workshop 1, Workshop 2)..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 pl-9 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Status Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Project <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="editData.status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="Ongoing">Ongoing (Berjalan)</option>
                            <option value="Completed">Completed (Selesai)</option>
                            <option value="Planning">Planning (Perencanaan)</option>
                            <option value="On Hold">On Hold (Ditunda)</option>
                        </select>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Keterangan / Deskripsi Project</label>
                        <textarea name="description" x-model="editData.description" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openEditModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="btn-save-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white hover:bg-brand-700 focus:outline-none shadow-sm transition cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Datalist Rekomendasi Tempat Fabrikasi -->
    <datalist id="fabrication_locations_list">
        @foreach($allLocationSuggestions ?? [] as $sug)
            <option value="{{ $sug }}"></option>
        @endforeach
    </datalist>

</div>

@push('scripts')
@if(!empty($chartProjectNames))
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const prjDiscrete = @json($chartProjectDiscreteTotals ?? []);
        const prjDecimal = @json($chartProjectDecimalTotals ?? []);
        const prjFormatted = @json($chartProjectFormattedTotals ?? []);
        const prjFullNames = @json($chartProjectFullNames ?? []);
        let currentPrjUnit = 'discrete';

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9CA3AF' : '#6B7280';
        const gridColor = isDark ? '#374151' : '#F3F4F6';

        const options = {
            series: [{
                name: 'Volume Terpakai (Pcs/Unit)',
                data: prjDiscrete
            }],
            chart: {
                type: 'bar',
                height: 260,
                fontFamily: 'Outfit, sans-serif',
                toolbar: { show: false }
            },
            colors: ['#275538'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%',
                    distributed: false,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -18,
                formatter: function (val) {
                    if (val <= 0) return '';
                    return currentPrjUnit === 'discrete'
                        ? new Intl.NumberFormat('id-ID').format(Math.round(val))
                        : (Number.isInteger(val) ? new Intl.NumberFormat('id-ID').format(val) : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(val));
                },
                style: {
                    fontSize: '10px',
                    fontFamily: 'Outfit, sans-serif',
                    fontWeight: 'bold',
                    colors: [isDark ? '#D1D5DB' : '#374151']
                }
            },
            legend: { show: false },
            xaxis: {
                categories: @json($chartProjectNames),
                axisBorder: { show: true, color: gridColor },
                axisTicks: { show: true, color: gridColor },
                labels: { style: { colors: textColor, fontSize: '11px', fontWeight: 600 } }
            },
            yaxis: {
                labels: {
                    style: { colors: textColor, fontSize: '11px' },
                    formatter: (val) => currentPrjUnit === 'discrete' ? Math.round(val) : (Number.isInteger(val) ? val : val.toFixed(1))
                }
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 3,
                padding: { left: 10, right: 15, top: 18 }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                x: {
                    formatter: function(val, opts) {
                        const idx = opts.dataPointIndex;
                        return prjFullNames[idx] || val;
                    }
                },
                y: {
                    formatter: function(val, opts) {
                        const idx = opts.dataPointIndex;
                        return prjFormatted[idx] ? (prjFormatted[idx] + " Terpakai") : (val + " Terpakai");
                    }
                }
            }
        };

        const chartEl = document.querySelector("#projectConsumptionChart");
        let chartInstance = null;
        if (chartEl && typeof ApexCharts !== 'undefined') {
            chartInstance = new ApexCharts(chartEl, options);
            chartInstance.render();
        }

        window.switchProjectChartUnit = function(mode) {
            currentPrjUnit = mode;
            if (!chartInstance) return;
            if (mode === 'discrete') {
                chartInstance.updateOptions({
                    colors: ['#275538'],
                    series: [{ name: 'Volume Terpakai (Pcs/Unit)', data: prjDiscrete }]
                });
            } else {
                chartInstance.updateOptions({
                    colors: ['#D97706'],
                    series: [{ name: 'Volume Terpakai (Kg/Meter)', data: prjDecimal }]
                });
            }
        };
    });
</script>
@endif
@endpush
@endsection

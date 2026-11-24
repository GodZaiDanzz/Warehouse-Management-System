@extends('layouts.app')

@php
    $canViewPrices = $canViewPrices ?? (auth()->user() && auth()->user()->canViewPrices());
@endphp

@section('content')
<div class="p-4 sm:p-6 space-y-6" x-data="{ openAddMaterialModal: false, openEditModal: false, openAddFinishGoodModal: false, selectedUnit: 'PCS' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $project->name }}</h1>
            </div>
            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                @if($project->po_number)
                    <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-lg border border-amber-200 dark:border-amber-800" title="Nomor PO">
                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        PO: {{ $project->po_number }}
                    </span>
                @endif
                @if($project->client)
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/60 px-2.5 py-0.5 rounded-lg border border-sky-200 dark:border-sky-800" title="Client / Pemilik Pekerjaan">
                        <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Client: {{ $project->client }}
                    </span>
                @endif
                @if($project->code)
                    <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 rounded-lg border border-gray-200 dark:border-gray-700 tracking-wider">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        {{ $project->code }}
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                ← Kembali ke Daftar Project
            </a>
            <button type="button" @click="openEditModal = true" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition cursor-pointer">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Rincian Project
            </button>
            <button type="button" @click="openAddFinishGoodModal = true" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-indigo-700 transition cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Tambah Barang Jadi
            </button>
            <button type="button" @click="openAddMaterialModal = true" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-emerald-700 transition cursor-pointer btn-add-theme">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah catatan Material
            </button>
        </div>
    </div>

    <!-- Alert Component -->
    <x-alert />

    <!-- Project Information Card & Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Project Details Card -->
        <div class="lg:col-span-7 rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Informasi & Detail Project</h3>
                <div class="flex items-center gap-2">
                    @if($project->location)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/80 dark:text-indigo-300 dark:border-indigo-800 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Fabrikasi: {{ $project->location }}</span>
                        </span>
                    @endif
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/80 dark:text-brand-300">
                        Status: {{ $project->status ?? 'Ongoing' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold block text-[11px] uppercase tracking-wider">Nomor PO</span>
                    <p class="font-mono font-extrabold text-amber-700 dark:text-amber-400 mt-1 flex items-center gap-1.5 text-sm">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>{{ $project->po_number ?: '-' }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold block text-[11px] uppercase tracking-wider">Client / Pemesan</span>
                    <p class="font-extrabold text-sky-700 dark:text-sky-300 mt-1 flex items-center gap-1.5 text-sm">
                        <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>{{ $project->client ?: '-' }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold block text-[11px] uppercase tracking-wider">Tempat Fabrikasi</span>
                    <p class="font-extrabold text-gray-900 dark:text-white mt-1 flex items-center gap-1.5 text-sm">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>{{ $project->location ?: 'Belum ditentukan' }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold block text-[11px] uppercase tracking-wider">Kode Project</span>
                    <p class="font-mono font-extrabold text-gray-900 dark:text-white mt-1 text-sm">
                        {{ $project->code ?: '-' }}
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold block text-[11px] uppercase tracking-wider">Tanggal Mulai</span>
                    <p class="font-mono font-extrabold text-gray-900 dark:text-white mt-1 flex items-center gap-1.5 text-sm">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $project->start_date ? format_date($project->start_date) : 'Belum diatur' }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold block text-[11px] uppercase tracking-wider">
                        {{ $project->status === 'Completed' ? 'Tanggal Selesai' : 'Target / Tanggal Selesai' }}
                    </span>
                    <p class="font-mono font-extrabold {{ $project->status === 'Completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-white' }} mt-1 flex items-center gap-1.5 text-sm">
                        @if($project->status === 'Completed')
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $project->end_date ? format_date($project->end_date) : 'Selesai' }}</span>
                        @else
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $project->end_date ? format_date($project->end_date) : 'Masih berjalan' }}</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($project->description)
                <div class="text-xs pt-2">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold">Catatan / Deskripsi:</span>
                    <p class="text-gray-700 dark:text-gray-300 mt-1 leading-relaxed">{{ $project->description }}</p>
                </div>
            @else
                <div class="text-xs text-gray-400 italic">
                    Tidak ada catatan/deskripsi tambahan untuk project ini.
                </div>
            @endif
        </div>

        <!-- Material Consumption Metrics -->
        <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
            @if($canViewPrices)
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Nilai Biaya Material</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">Finance & Admin</span>
                    </div>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <h4 class="text-2xl font-black text-emerald-700 dark:text-emerald-400 font-mono">Rp {{ number_format($summary['total_cost'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-0.5">Total valuasi penyerapan riil</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            @endif

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Material Terpakai</span>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <h4 class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($summary['discrete_qty'] ?? 0, 0, ',', '.') }}</h4>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Pcs/Unit</span>
                    </div>
                    @if(!empty($summary['decimal_string']))
                        <div class="mt-1">
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 font-mono">
                                + {{ $summary['decimal_string'] }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Transaksi Keluar</span>
                    <h4 class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-0.5">{{ number_format($summary['total_transactions']) }} Transaksi</h4>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Jenis Barang Digunakan</span>
                    <h4 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($summary['unique_items']) }} Jenis</h4>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- RINGKASAN BIAYA WIP & HPP (FINANCE & PRODUKSI) -->
    @if($canViewPrices && isset($wipSummary))
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Ringkasan Biaya WIP & Akumulasi HPP (Finance)</span>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 dark:text-indigo-400 px-2.5 py-0.5 rounded-full border border-indigo-200 dark:border-indigo-800">
                        Work In Process
                    </span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Pelacakan penyerapan biaya material dan upah tenaga kerja (manpower) per tahapan proses fabrikasi internal.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Akumulasi WIP</span>
                    <span class="text-base font-black text-indigo-600 dark:text-indigo-400 font-mono">
                        Rp {{ number_format($wipSummary['total_wip_cost'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 4 Tahapan WIP Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $stageMeta = [
                    'Cutting' => [
                        'desc' => 'Potong Raw Material & Offcut',
                        'dot' => 'bg-amber-500',
                        'text' => 'text-amber-600 dark:text-amber-400',
                    ],
                    'Welding' => [
                        'desc' => 'Kawat Las, Gas CO2/Argon & Welder',
                        'dot' => 'bg-rose-500',
                        'text' => 'text-rose-600 dark:text-rose-400',
                    ],
                    'Painting' => [
                        'desc' => 'Blasting, Primer, Top Coat & Painter',
                        'dot' => 'bg-sky-500',
                        'text' => 'text-sky-600 dark:text-sky-400',
                    ],
                    'Packing' => [
                        'desc' => 'Pallet, Wrapping & Packer',
                        'dot' => 'bg-emerald-500',
                        'text' => 'text-emerald-600 dark:text-emerald-400',
                    ],
                ];
            @endphp

            @foreach($wipSummary['stages'] as $stageKey => $sData)
                @php
                    $meta = $stageMeta[$stageKey] ?? ['desc' => '', 'dot' => 'bg-gray-400', 'text' => 'text-gray-600'];
                @endphp
                <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-800/40 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $meta['dot'] }}"></span>
                                {{ $stageKey }}
                            </span>
                            <span class="text-[11px] font-mono font-bold text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 px-2 py-0.5 rounded-lg border border-gray-100 dark:border-gray-800">
                                {{ number_format($sData['manpower_hours'], 1) }} Jam
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $meta['desc'] }}</p>

                        <div class="mt-3 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Biaya Material:</span>
                                <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">
                                    Rp {{ number_format($sData['material_cost'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Biaya Manpower:</span>
                                <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">
                                    Rp {{ number_format($sData['manpower_cost'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300">Subtotal WIP:</span>
                        <span class="text-xs font-extrabold {{ $meta['text'] }} font-mono">
                            Rp {{ number_format($sData['total'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Catatan Vendor Eksternal & Valuasi HPP Finish Good -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 pt-2">
            <!-- Info Tahap 2 External -->
            <div class="lg:col-span-7 rounded-xl border border-sky-100 bg-sky-50/50 p-3.5 dark:border-sky-900/40 dark:bg-sky-950/20 text-xs flex items-start gap-3">
                <div class="p-1 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300 shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="font-bold text-sky-900 dark:text-sky-200">Tahap 2 (Subcont Eksternal)</span>
                    <p class="text-[11px] text-sky-800/80 dark:text-sky-300/80 mt-0.5 leading-relaxed">
                        Pekerjaan eksternal (Galvanis, Hot-dip, Bending khusus) dilakukan oleh vendor luar dan biaya tidak dibebankan pada WIP internal gudang ini sesuai instruksi Finance.
                    </p>
                </div>
            </div>

            <!-- Total HPP Finish Goods -->
            <div class="lg:col-span-5 rounded-xl border border-emerald-100 bg-emerald-50/50 p-3.5 dark:border-emerald-900/40 dark:bg-emerald-950/20 text-xs flex items-center justify-between">
                <div>
                    <span class="font-bold text-emerald-900 dark:text-emerald-200">Total HPP Barang Jadi</span>
                    <p class="text-[11px] text-emerald-800/80 dark:text-emerald-300/80 mt-0.5">
                        Akumulasi HPP produk yang sudah dibukukan ke Finish Goods.
                    </p>
                </div>
                <div class="text-right shrink-0 pl-2">
                    <span class="text-base font-black text-emerald-700 dark:text-emerald-400 font-mono block">
                        Rp {{ number_format($wipSummary['total_finish_good_hpp'] ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                        {{ $project->finishGoods->count() }} Produk Jadi
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- AKUMULASI MATERIAL TERPAKAI (SUBTOTAL QTY PER JENIS BARANG) -->
    <div x-data="{ searchSummary: '' }" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>Akumulasi Subtotal QTY Material Terpakai</span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/50 dark:text-emerald-400 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                        {{ $materialSummaryPerItem->count() }} Jenis Barang
                    </span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total akumulasi kuantitas tiap jenis material yang habis khusus digunakan pada project {{ $project->name }}.</p>
            </div>
            <div class="w-full sm:w-64">
                <input type="text" x-model="searchSummary" placeholder="Cari nama material..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
        </div>

        @if($materialSummaryPerItem->count() > 0)
            <!-- Top Highlight Cards (4 Barang Terbanyak Digunakan) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($materialSummaryPerItem->take(4) as $topSub)
                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-3 dark:border-rose-900/40 dark:bg-rose-950/20 flex items-center justify-between shadow-2xs">
                        <div class="min-w-0 pr-2">
                            <span class="text-[10px] font-extrabold text-rose-500 dark:text-rose-400 uppercase tracking-wider block">Top #{{ $loop->iteration }} Terbanyak</span>
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate mt-0.5" title="{{ $topSub->item->name ?? '-' }}">
                                {{ $topSub->item->name ?? '-' }}
                            </h4>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $topSub->total_trans }}x Transaksi</span>
                            @if($canViewPrices)
                                <div class="mt-1">
                                    <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300 font-mono">
                                        Rp {{ number_format(($topSub->total_qty ?? 0) * ($topSub->item->unit_price ?? 0), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-lg font-black text-rose-600 dark:text-rose-400 font-mono">-{{ format_unit_quantity($topSub->total_qty, $topSub->unit) }}</span>
                            <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300 block uppercase">{{ strtoupper($topSub->unit) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tabel Akumulasi Subtotal per Barang -->
            <div class="overflow-x-auto max-h-72 overflow-y-auto custom-scrollbar rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-left text-xs">
                    <thead class="theme-table-header font-bold uppercase text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2.5 text-center w-12 border-r border-gray-200 dark:border-gray-700">NO</th>
                            <th class="px-4 py-2.5 border-r border-gray-200 dark:border-gray-700">KODE MATERIAL</th>
                            <th class="px-4 py-2.5 border-r border-gray-200 dark:border-gray-700">NAMA BARANG</th>
                            <th class="px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700">SUBTOTAL QTY TERPAKAI</th>
                            <th class="px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700">SATUAN</th>
                            @if($canViewPrices)
                                <th class="px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700">HARGA SATUAN</th>
                                <th class="px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700">TOTAL BIAYA</th>
                            @endif
                            <th class="px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700">FREKUENSI TRANSAKSI</th>
                            <th class="px-4 py-2.5 text-center">PENGGUNAAN TERAKHIR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                        @foreach($materialSummaryPerItem as $idx => $sum)
                            <tr x-show="searchSummary === '' || {{ json_encode(strtolower($sum->item->name ?? '')) }}.includes(searchSummary.toLowerCase())" class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                                <td class="px-4 py-2 text-center text-gray-500 border-r border-gray-100 dark:border-gray-800">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2 whitespace-nowrap border-r border-gray-100 dark:border-gray-800">
                                    <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                        {{ $sum->item->code_item ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 font-bold text-gray-900 dark:text-white border-r border-gray-100 dark:border-gray-800">{{ $sum->item->name ?? '-' }}</td>
                                <td class="px-4 py-2 text-center font-black text-rose-600 dark:text-rose-400 text-xs border-r border-gray-100 dark:border-gray-800 font-mono">
                                    -{{ format_unit_quantity($sum->total_qty, $sum->unit) }}
                                </td>
                                <td class="px-4 py-2 text-center font-bold text-gray-700 dark:text-gray-300 border-r border-gray-100 dark:border-gray-800">
                                    {{ strtoupper($sum->unit) }}
                                </td>
                                @if($canViewPrices)
                                    <td class="px-4 py-2 text-right font-mono font-bold text-gray-700 dark:text-gray-300 border-r border-gray-100 dark:border-gray-800">
                                        Rp {{ number_format($sum->item->unit_price ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono font-black text-emerald-700 dark:text-emerald-400 border-r border-gray-100 dark:border-gray-800">
                                        Rp {{ number_format(($sum->total_qty ?? 0) * ($sum->item->unit_price ?? 0), 0, ',', '.') }}
                                    </td>
                                @endif
                                <td class="px-4 py-2 text-center text-gray-700 dark:text-gray-300 border-r border-gray-100 dark:border-gray-800">
                                    <span class="rounded-lg bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 font-semibold text-gray-800 dark:text-gray-200">
                                        {{ number_format($sum->total_trans) }}x Transaksi
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    {{ format_date($sum->last_used_date) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($canViewPrices)
                    <tfoot class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 font-bold text-xs">
                        <tr>
                            <td colspan="4" class="px-4 py-2.5 text-right uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Total Akumulasi Nilai Material:
                            </td>
                            <td colspan="4" class="px-4 py-2.5 text-left font-mono font-black text-emerald-700 dark:text-emerald-400 text-sm">
                                Rp {{ number_format($summary['total_cost'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div class="p-6 text-center text-xs text-gray-400">
                Belum ada data akumulasi material terpakai untuk project ini.
            </div>
        @endif
    </div>

    <!-- Materials Used Table -->
    <div class="rounded-2xl border border-gray-100 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Rincian Material Terpakai untuk Project {{ $project->name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Filter transaksi pengeluaran material berdasarkan tahapan proses fabrikasi (WIP).</p>
            </div>
            
            <!-- Filter Tabs Tahap WIP -->
            <div class="flex items-center gap-1.5 flex-wrap">
                @php $curWip = request('wip_stage'); @endphp
                <a href="{{ route('projects.show', ['project' => $project->id]) }}" 
                   class="px-2.5 py-1 rounded-lg text-xs font-bold transition {{ empty($curWip) ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                    Semua Tahap
                </a>
                <a href="{{ route('projects.show', ['project' => $project->id, 'wip_stage' => 'Cutting']) }}" 
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $curWip === 'Cutting' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 dark:bg-amber-950/60 dark:text-amber-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Tahap 1: Cutting
                </a>
                <a href="{{ route('projects.show', ['project' => $project->id, 'wip_stage' => 'Vendor Eksternal']) }}" 
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $curWip === 'Vendor Eksternal' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-800 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    Tahap 2: Vendor
                </a>
                <a href="{{ route('projects.show', ['project' => $project->id, 'wip_stage' => 'Welding']) }}" 
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $curWip === 'Welding' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                    Tahap 3: Welding
                </a>
                <a href="{{ route('projects.show', ['project' => $project->id, 'wip_stage' => 'Painting']) }}" 
                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $curWip === 'Painting' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-800 hover:bg-purple-100 dark:bg-purple-950/60 dark:text-purple-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                    Tahap 4: Painting
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="theme-table-header font-bold uppercase text-white border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">NO</th>
                        <th class="px-4 py-3.5">TANGGAL</th>
                        <th class="px-4 py-3.5">TAHAP WIP</th>
                        <th class="px-4 py-3.5">KODE MATERIAL</th>
                        <th class="px-4 py-3.5">NAMA BARANG</th>
                        <th class="px-4 py-3.5 text-center">JUMLAH TERPAKAI</th>
                        <th class="px-4 py-3.5 text-center">SATUAN</th>
                        <th class="px-4 py-3.5 text-center">MANPOWER (JAM)</th>
                        <th class="px-4 py-3.5 text-right">BIAYA MANPOWER</th>
                        @if($canViewPrices)
                            <th class="px-4 py-3.5 text-right">HARGA SATUAN</th>
                            <th class="px-4 py-3.5 text-right">TOTAL NILAI</th>
                        @endif
                        <th class="px-4 py-3.5">PIC PENERIMA</th>
                        <th class="px-4 py-3.5">CATATAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                    @forelse($materialOuts as $index => $mo)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3 text-center text-gray-500">{{ $materialOuts->firstItem() + $index }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ format_date($mo->date) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($mo->wip_stage)
                                    @php
                                        $wipBadgeStyle = match($mo->wip_stage) {
                                            'Cutting' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
                                            'Vendor Eksternal' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
                                            'Welding' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
                                            'Painting' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $wipBadgeStyle }}">
                                        {{ $mo->wip_stage }}
                                        @if($mo->manpower_hours > 0)
                                            <span class="opacity-75 font-mono text-[10px]">({{ (float)$mo->manpower_hours }} Jam)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $mo->item->code_item ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                {{ $mo->item->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-rose-600 dark:text-rose-400 font-mono">
                                -{{ format_unit_quantity($mo->quantity, $mo->unit) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-md bg-gray-100 dark:bg-gray-800 px-2 py-0.5 font-bold text-gray-700 dark:text-gray-300">
                                    {{ strtoupper($mo->unit) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($mo->manpower_hours > 0)
                                    <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 rounded-md border border-rose-200 dark:border-rose-800">
                                        {{ (float)$mo->manpower_hours }} Jam
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs italic">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if($mo->manpower_cost > 0)
                                    <span class="font-mono font-bold text-xs text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format($mo->manpower_cost, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            @if($canViewPrices)
                                <td class="px-4 py-3 text-right font-mono font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    Rp {{ number_format($mo->item->unit_price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-black text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
                                    Rp {{ number_format(($mo->quantity ?? 0) * ($mo->item->unit_price ?? 0), 0, ',', '.') }}
                                </td>
                            @endif
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-900 dark:bg-gray-800 dark:text-white border border-gray-300 dark:border-gray-700">
                                    <svg class="h-3.5 w-3.5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $mo->pic }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $mo->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewPrices ? 9 : 7 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada material yang dicatat untuk project ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($materialOuts->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $materialOuts->links() }}
            </div>
        @endif
    </div>

    <!-- BARANG JADI (FINISH GOODS) HASIL FABRIKASI PROJECT -->
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Barang Jadi (Finish Goods) Hasil Fabrikasi</span>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/50 dark:text-indigo-400 px-2.5 py-0.5 rounded-full border border-indigo-200 dark:border-indigo-800">
                        {{ $project->finishGoods->count() }} Item Tercatat
                    </span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Daftar produk, komponen, atau hasil fabrikasi yang telah selesai dikerjakan untuk project ini.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finish-goods.index', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Buka Modul Finish Goods
                </a>
                <button type="button" @click="openAddFinishGoodModal = true" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-indigo-700 transition cursor-pointer">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Barang Jadi
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider dark:bg-gray-800/80 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">NO</th>
                        <th class="px-4 py-3.5">NAMA BARANG JADI</th>
                        <th class="px-4 py-3.5">KODE / SERI</th>
                        <th class="px-4 py-3.5 text-center">JUMLAH HASIL</th>
                        <th class="px-4 py-3.5 text-center">TGL SELESAI</th>
                        <th class="px-4 py-3.5 text-center">STATUS QC</th>
                        <th class="px-4 py-3.5 text-center">STATUS GUDANG</th>
                        <th class="px-4 py-3.5">INFO PENGIRIMAN</th>
                        <th class="px-4 py-3.5">CATATAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                    @forelse($project->finishGoods as $fg)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3 text-center text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-extrabold text-gray-900 dark:text-white uppercase">
                                {{ $fg->item_name }}
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400">
                                {{ $fg->item_code ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                {{ number_format($fg->quantity, 2, ',', '.') }} {{ $fg->unit }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-gray-700 dark:text-gray-300">
                                {{ format_date($fg->production_date) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($fg->qc_status === 'Passed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-700">
                                        Passed QC
                                    </span>
                                @elseif($fg->qc_status === 'Conditional')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-700">
                                        Conditional
                                    </span>
                                @elseif($fg->qc_status === 'Failed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-700">
                                        Failed QC
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                        Pending QC
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($fg->status === 'Delivered')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/80 dark:text-sky-300 dark:border-sky-700">
                                        Terkirim (Delivered)
                                    </span>
                                @elseif($fg->status === 'Ready for Delivery')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/80 dark:text-indigo-300 dark:border-indigo-700">
                                        Ready for Delivery
                                    </span>
                                @elseif($fg->status === 'In Stock')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-950/80 dark:text-teal-300 dark:border-teal-700">
                                        In Stock (Gudang)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-600 border border-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                        {{ $fg->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                @if($fg->delivery_date || $fg->delivery_order_number)
                                    <div class="text-[11px] leading-tight">
                                        @if($fg->delivery_order_number)
                                            <span class="font-mono font-bold text-gray-900 dark:text-white block">DO: {{ $fg->delivery_order_number }}</span>
                                        @endif
                                        @if($fg->delivery_date)
                                            <span class="text-gray-500 dark:text-gray-400">Tgl: {{ format_date($fg->delivery_date) }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Belum dikirim</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $fg->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <p class="font-bold text-gray-700 dark:text-gray-300">Belum ada barang jadi (finish goods) yang dicatat untuk project ini.</p>
                                <p class="text-xs text-gray-400 mt-0.5">Catat hasil fabrikasi yang sudah selesai agar terpantau stok dan status pengirimannya.</p>
                                <button type="button" @click="openAddFinishGoodModal = true" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 border border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800 hover:bg-indigo-100 transition cursor-pointer">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Catat Barang Jadi Sekarang
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Record Material Out for this Project -->
    <template x-teleport="body">
        <div x-show="openAddMaterialModal" x-cloak @click.self="openAddMaterialModal = false" @keydown.escape.window="openAddMaterialModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Catat Pengeluaran Material Project</h3>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Project: {{ $project->name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="openAddMaterialModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('projects.add-material', $project->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
                        <div class="relative" x-data="{
                            fp: null,
                            init() {
                                const initPicker = () => {
                                    if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                    this.fp = flatpickr(this.$refs.picker, {
                                        dateFormat: 'Y-m-d',
                                        altInput: true,
                                        altFormat: 'd/m/Y',
                                        altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-emerald-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition',
                                        defaultDate: '{{ date('Y-m-d') }}'
                                    });
                                };
                                this.$nextTick(() => initPicker());
                            },
                            destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                        }">
                            <input x-ref="picker" type="text" name="date" class="hidden" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Tahap WIP -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tahapan Proses Fabrikasi (WIP)</label>
                        <select name="wip_stage" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">-- Umum / Tanpa Tahapan --</option>
                            <option value="Cutting" {{ request('wip_stage') === 'Cutting' ? 'selected' : '' }}>Tahap 1: Cutting & Profiling</option>
                            <option value="Vendor Eksternal" {{ request('wip_stage') === 'Vendor Eksternal' ? 'selected' : '' }}>Tahap 2: Vendor Eksternal (Subkon)</option>
                            <option value="Welding" {{ request('wip_stage') === 'Welding' ? 'selected' : '' }}>Tahap 3: Welding & Assembling</option>
                            <option value="Painting" {{ request('wip_stage') === 'Painting' ? 'selected' : '' }}>Tahap 4: Painting & Finishing</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pilih Barang dari Stok Gudang <span class="text-rose-500">*</span></label>
                        <select name="item_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-emerald-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($itemsList as $itm)
                                <option value="{{ $itm->id }}">
                                    {{ $itm->code_item ? '[' . $itm->code_item . '] ' : '' }}{{ $itm->name }} (Stok: {{ format_quantity($itm->stock) }} {{ $itm->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jumlah Terpakai <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="quantity" min="0.0001" value="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-emerald-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <select name="unit" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-emerald-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @foreach(['PCS', 'LEMBAR', 'BATANG', 'ROLL', 'TABUNG', 'KG', 'LITER', 'ONS', 'CM', 'M', 'MM', 'BOX', 'ML', 'PASANG'] as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Upah Tenaga Kerja / Manpower -->
                    <div x-data="{ mpHours: 0, mpRate: 25000 }" class="rounded-xl border border-amber-200 bg-amber-50/60 p-3.5 dark:border-amber-800/60 dark:bg-amber-950/30 space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-200">Perhitungan Upah Tenaga Kerja (Manpower WIP)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Jam Kerja Tenaga Kerja (Hours)</label>
                                <input type="number" step="0.1" min="0" name="manpower_hours" x-model.number="mpHours" placeholder="0" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Tarif Upah per Jam (Rp / Jam)</label>
                                <input type="number" step="1000" min="0" name="manpower_rate_per_hour" x-model.number="mpRate" placeholder="25000" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-amber-200/60 dark:border-amber-800/60">
                            <span class="text-gray-600 dark:text-gray-400">Total Biaya Tenaga Kerja:</span>
                            <span class="font-extrabold text-amber-700 dark:text-amber-300 font-mono"
                                  x-text="'Rp ' + ((mpHours || 0) * (mpRate || 0)).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">PIC / Orang yang Mengambil <span class="text-rose-500">*</span></label>
                        <input type="text" name="pic" required placeholder="Nama PIC Pengambil Barang..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-emerald-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" placeholder="Catatan pemakaian..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-emerald-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openAddMaterialModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs cursor-pointer btn-save-theme">
                            Simpan & Potong Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT PROJECT -->
    <template x-teleport="body">
        <div x-show="openEditModal" x-cloak @click.self="openEditModal = false" @keydown.escape.window="openEditModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/80 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Data Project</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Perbarui identitas, tempat fabrikasi, status, atau jadwal project</p>
                        </div>
                    </div>
                    <button type="button" @click="openEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('projects.update', $project->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Kode Project -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kode Project</label>
                            <input type="text" name="code" value="{{ $project->code }}" placeholder="PRJ-..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono font-bold uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <!-- Nomor PO -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor PO <span class="text-xs font-normal text-gray-400">(Client / Kontrak)</span></label>
                            <input type="text" name="po_number" value="{{ $project->po_number }}" placeholder="PO-..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Nama Project -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Project / Pekerjaan <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ $project->name }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <!-- Client -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Client / Pemesan <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                            <input type="text" name="client" value="{{ $project->client }}" placeholder="Contoh: PT PLN / Pertamina" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Tempat Fabrikasi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Tempat Fabrikasi <span class="text-xs font-normal text-gray-400">(Workshop / Lokasi Fabrikasi)</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="location" value="{{ $project->location }}" placeholder="Pilih atau ketik nama tempat fabrikasi (misal: Workshop 1, Workshop 2)..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 pl-9 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
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
                            <option value="Ongoing" {{ $project->status === 'Ongoing' ? 'selected' : '' }}>Ongoing (Berjalan)</option>
                            <option value="Completed" {{ $project->status === 'Completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                            <option value="Planning" {{ $project->status === 'Planning' ? 'selected' : '' }}>Planning (Perencanaan)</option>
                            <option value="On Hold" {{ $project->status === 'On Hold' ? 'selected' : '' }}>On Hold (Ditunda)</option>
                        </select>
                    </div>

                    <!-- Tanggal Mulai & Tanggal Selesai (Interactive Flatpickr Calendar) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div x-data="{
                                 fp: null,
                                 init() {
                                     const initPicker = () => {
                                         if (typeof flatpickr === 'undefined') {
                                             setTimeout(initPicker, 100);
                                             return;
                                         }
                                         this.fp = flatpickr(this.$refs.editStartPicker, {
                                             dateFormat: 'Y-m-d',
                                             altInput: true,
                                             altFormat: 'd/m/Y',
                                             allowInput: false,
                                             locale: typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id ? flatpickr.l10ns.id : 'default',
                                             altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 pl-3.5 pr-10 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer select-none transition shadow-2xs',
                                             defaultDate: '{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}',
                                             onChange: (selectedDates, dateStr) => {
                                                 this.$refs.editStartPicker.value = dateStr;
                                             }
                                         });
                                     };
                                     this.$nextTick(() => initPicker());
                                 },
                                 openPicker() {
                                     if (this.fp) this.fp.open();
                                 }
                             }">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 cursor-pointer" @click="openPicker()">
                                Tanggal Mulai <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                            </label>
                            <div class="relative cursor-pointer" @click="openPicker()">
                                <input type="text" x-ref="editStartPicker" name="start_date" value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}" class="hidden">
                                <button type="button" @click.stop="openPicker()" class="absolute right-3 top-2.5 text-gray-400 hover:text-brand-600 transition cursor-pointer" title="Buka Kalender">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div x-data="{
                                 fp: null,
                                 init() {
                                     const initPicker = () => {
                                         if (typeof flatpickr === 'undefined') {
                                             setTimeout(initPicker, 100);
                                             return;
                                         }
                                         this.fp = flatpickr(this.$refs.editEndPicker, {
                                             dateFormat: 'Y-m-d',
                                             altInput: true,
                                             altFormat: 'd/m/Y',
                                             allowInput: false,
                                             locale: typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id ? flatpickr.l10ns.id : 'default',
                                             altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 pl-3.5 pr-10 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer select-none transition shadow-2xs',
                                             defaultDate: '{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}',
                                             onChange: (selectedDates, dateStr) => {
                                                 this.$refs.editEndPicker.value = dateStr;
                                             }
                                         });
                                     };
                                     this.$nextTick(() => initPicker());
                                 },
                                 openPicker() {
                                     if (this.fp) this.fp.open();
                                 }
                             }">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 cursor-pointer" @click="openPicker()">
                                Tanggal Selesai <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                            </label>
                            <div class="relative cursor-pointer" @click="openPicker()">
                                <input type="text" x-ref="editEndPicker" name="end_date" value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}" class="hidden">
                                <button type="button" @click.stop="openPicker()" class="absolute right-3 top-2.5 text-gray-400 hover:text-brand-600 transition cursor-pointer" title="Buka Kalender">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Keterangan / Deskripsi Project</label>
                        <textarea name="description" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ $project->description }}</textarea>
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

    <!-- MODAL QUICK TAMBAH BARANG JADI (FINISH GOOD) KHUSUS PROJECT INI -->
    <template x-teleport="body">
        <div x-show="openAddFinishGoodModal" x-cloak @click.self="openAddFinishGoodModal = false" @keydown.escape.window="openAddFinishGoodModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Catat Barang Jadi (Finish Good)</h3>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">Project: {{ $project->name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="openAddFinishGoodModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('finish-goods.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Barang Jadi / Komponen Hasil Fabrikasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="item_name" placeholder="Contoh: Tangki Storage 5000L / Spool Piping 4 Inch" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor / Kode Seri Produk <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                            <input type="text" name="item_code" placeholder="Contoh: FG-2026-TNK-01" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono uppercase text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div x-data="{
                                 fp: null,
                                 init() {
                                     const initPicker = () => {
                                         if (typeof flatpickr === 'undefined') {
                                             setTimeout(initPicker, 100);
                                             return;
                                         }
                                         this.fp = flatpickr(this.$refs.fgProdDate, {
                                             dateFormat: 'Y-m-d',
                                             altInput: true,
                                             altFormat: 'd/m/Y',
                                             allowInput: false,
                                             locale: typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id ? flatpickr.l10ns.id : 'default',
                                             altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 pl-3.5 pr-10 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer select-none transition shadow-2xs',
                                             defaultDate: '{{ date('Y-m-d') }}',
                                             onChange: (selectedDates, dateStr) => {
                                                 this.$refs.fgProdDate.value = dateStr;
                                             }
                                         });
                                     };
                                     this.$nextTick(() => initPicker());
                                 },
                                 openPicker() {
                                     if (this.fp) this.fp.open();
                                 }
                             }">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 cursor-pointer" @click="openPicker()">
                                Tanggal Selesai Produksi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative cursor-pointer" @click="openPicker()">
                                <input type="text" x-ref="fgProdDate" name="production_date" value="{{ date('Y-m-d') }}" required class="hidden">
                                <button type="button" @click.stop="openPicker()" class="absolute right-3 top-2.5 text-gray-400 hover:text-indigo-600 transition cursor-pointer" title="Buka Kalender">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jumlah Hasil <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="quantity" min="0.01" value="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="unit" list="finish_good_unit_suggestions" value="UNIT" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold uppercase text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <datalist id="finish_good_unit_suggestions">
                                <option value="UNIT"></option>
                                <option value="PCS"></option>
                                <option value="SET"></option>
                                <option value="BATANG"></option>
                                <option value="LEMBAR"></option>
                                <option value="ROLL"></option>
                            </datalist>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Hasil QC <span class="text-rose-500">*</span></label>
                            <select name="qc_status" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="Passed" selected>Passed (Lolos Uji QC)</option>
                                <option value="Conditional">Conditional (Lolos Bersyarat)</option>
                                <option value="Pending">Pending (Dalam Pemeriksaan QC)</option>
                                <option value="Failed">Failed (Gagal QC / Reject)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Gudang <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="In Stock" selected>In Stock (Tersimpan di Gudang/Workshop)</option>
                                <option value="Ready for Delivery">Ready for Delivery (Siap Kirim)</option>
                                <option value="Delivered">Delivered (Sudah Dikirim ke Client)</option>
                                <option value="Scrapped">Scrapped (Dibuang / Rusak)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan / Keterangan Teknis</label>
                        <textarea name="notes" rows="2" placeholder="Catatan spesifikasi produk, dimensi, atau keterangan delivery..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-indigo-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openAddFinishGoodModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2 text-xs font-bold text-white hover:bg-indigo-700 shadow-xs cursor-pointer">
                            Simpan Barang Jadi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection

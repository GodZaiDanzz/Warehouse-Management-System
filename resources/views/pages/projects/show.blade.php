@extends('layouts.app')

@php
    $canViewPrices = $canViewPrices ?? (auth()->user() && auth()->user()->canViewPrices());
@endphp

@section('content')
<div class="p-4 sm:p-6 space-y-6" x-data="{ openAddMaterialModal: false, openEditModal: false, selectedUnit: 'PCS' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $project->name }}</h1>
            </div>
            @if($project->code)
                <div class="mt-1">
                    <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 rounded-lg border border-gray-200 dark:border-gray-700 tracking-wider">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        {{ $project->code }}
                    </span>
                </div>
            @endif
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition">
                ← Kembali ke Daftar Project
            </a>
            <button type="button" @click="openEditModal = true" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition cursor-pointer">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Rincian Project
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
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
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Rincian Material Terpakai untuk Project {{ $project->name }}</h3>
            <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Pemotongan Stok Gudang Otomatis Active</span>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="theme-table-header font-bold uppercase text-white border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">NO</th>
                        <th class="px-4 py-3.5">TANGGAL</th>
                        <th class="px-4 py-3.5">NAMA BARANG</th>
                        <th class="px-4 py-3.5 text-center">JUMLAH TERPAKAI</th>
                        <th class="px-4 py-3.5 text-center">SATUAN</th>
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

    <!-- Modal: Record Material Out for this Project -->
    <template x-teleport="body">
        <div x-show="openAddMaterialModal" x-cloak @click.self="openAddMaterialModal = false" @keydown.escape.window="openAddMaterialModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar my-8">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Catat Pengeluaran Material Project</h3>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Project: {{ $project->name }}</p>
                    </div>
                    <button type="button" @click="openAddMaterialModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
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

                <form method="POST" action="{{ route('projects.update', $project->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Kode Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kode Project</label>
                        <input type="text" name="code" value="{{ $project->code }}" placeholder="PRJ-..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono font-bold uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Nama Project -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Project / Pekerjaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ $project->name }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
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

                    <!-- Tanggal Mulai & Tanggal Selesai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Tanggal Mulai <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                            </label>
                            <input type="date" name="start_date" value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Tanggal Selesai <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                            </label>
                            <input type="date" name="end_date" value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
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
</div>
@endsection

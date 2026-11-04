@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6" x-data="{ openLogModal: false, logType: 'in', showStickModal: false }">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pencatatan Gudang & Log Mutasi</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Riwayat transaksi mutasi barang masuk (penerimaan) dan barang keluar (pengeluaran)</p>
        </div>
        
        <div class="flex items-center gap-2.5">
            <!-- Button Kalkulator Stick (Kg) -->
            <button @click="showStickModal = true" type="button" class="inline-flex items-center gap-2 rounded-xl border border-amber-300 dark:border-amber-700/80 bg-amber-50 hover:bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 px-3.5 py-2.5 text-xs font-bold transition shadow-2xs cursor-pointer" title="Buka Kalkulator Konversi Stick / Batang ke Kilogram (Kg)">
                <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Kalkulator Stick (Kg)</span>
            </button>

            <!-- Button Catat Mutasi Baru (Primary Action) -->
            <button @click="openLogModal = true" type="button" class="btn-add-theme inline-flex items-center justify-center gap-2 rounded-xl border border-brand-600 bg-brand-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-brand-700 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Mutasi Barang</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications Component -->
    <x-alert />

    <!-- Metrics Cards Summary -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
        <!-- Metric 1: Total Mutasi -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Mutasi Recorded</span>
                    <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalLogsCount ?? 0) }} Transaksi</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
        </div>

        <!-- Metric 2: Total Barang Masuk -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Akumulasi Barang Masuk</span>
                    <h3 class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">+{{ format_quantity($totalInQty ?? 0) }} Unit</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                </div>
            </div>
        </div>

        <!-- Metric 3: Total Barang Keluar -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Akumulasi Barang Keluar</span>
                    <h3 class="mt-2 text-2xl font-bold text-rose-600 dark:text-rose-400">-{{ format_quantity($totalOutQty ?? 0) }} Unit</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar with Quick Date Buttons -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm" x-data="{ 
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
            <button type="button" @click="setToday()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 text-gray-700 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer whitespace-nowrap shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Hari Ini</span>
            </button>
            <button type="button" @click="setLast7Days()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 text-gray-700 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer whitespace-nowrap shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>7 Hari Terakhir</span>
            </button>
            <button type="button" @click="setThisMonth()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 text-gray-700 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer whitespace-nowrap shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Bulan Ini</span>
            </button>
        </div>

        <form method="GET" action="{{ route('logs.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Search Text -->
            <div class="flex-1 min-w-[220px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, kode, PO/MRF, penyedia, atau catatan..." class="w-full rounded-xl border border-gray-300 bg-gray-50 pl-4 pr-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
            </div>

            <!-- Transaction Type -->
            <div class="w-full sm:w-48">
                <select name="type" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>↓ Barang Masuk</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>↑ Barang Keluar</option>
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
                                altInputClass: 'w-36 sm:w-40 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white cursor-pointer transition focus:bg-white',
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
                                altInputClass: 'w-36 sm:w-40 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white cursor-pointer transition focus:bg-white',
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

            <!-- Per Page Selector -->
            <div class="w-full sm:w-36">
                <select name="per_page" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition">
                    <option value="15" {{ (int)($perPage ?? 15) === 15 ? 'selected' : '' }}>15 / hal</option>
                    <option value="25" {{ (int)($perPage ?? 15) === 25 ? 'selected' : '' }}>25 / hal</option>
                    <option value="50" {{ (int)($perPage ?? 15) === 50 ? 'selected' : '' }}>50 / hal</option>
                    <option value="100" {{ (int)($perPage ?? 15) === 100 ? 'selected' : '' }}>100 / hal</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="btn-other-theme rounded-xl bg-brand-600 hover:bg-brand-700 px-5 py-2 text-xs font-bold text-white transition cursor-pointer shadow-xs">
                    Filter
                </button>
                @if(request('search') || request('type') || request('start_date') || request('end_date') || request('per_page'))
                    <a href="{{ route('logs.index') }}" class="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-center transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-center">Tipe</th>
                        <th class="px-6 py-4 text-center">No. PO / MRF Ref</th>
                        <th class="px-6 py-4 text-center">Kode Material</th>
                        <th class="px-6 py-4">Nama Barang</th>
                        <th class="px-6 py-4 text-center">Jumlah</th>
                        <th class="px-6 py-4 text-center">Penyedia / Penerima</th>
                        <th class="px-6 py-4 text-center">Waktu Transaksi</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->type == 'in')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        ↓ Barang Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        ↑ Barang Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-800 dark:text-gray-200">
                                {{ $log->reference_number ?? '-' }}
                            </td>
                            <!-- KODE MATERIAL -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2.5 py-1 rounded-lg font-mono text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $log->item?->code_item ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $log->item?->name ?? 'Barang #' . $log->item_id }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $log->item?->location ?? 'Gudang Utama' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold whitespace-nowrap {{ $log->type == 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $log->type == 'in' ? '+' : '-' }}{{ format_quantity($log->quantity) }} {{ $log->item?->unit }}
                            </td>
                            <td class="px-6 py-4 text-center text-xs font-semibold text-gray-800 dark:text-gray-200">
                                {{ $log->recipient_or_provider }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <div>{{ format_datetime($log->created_at) }} WIB</div>
                                <div class="text-[11px] text-gray-400">{{ $log->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('logs.pdf', $log->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/50 dark:text-rose-300 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800 text-xs font-bold transition shadow-2xs" title="Cetak Surat Jalan / Bukti Serah Terima PDF">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Cetak PDF</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                Belum ada riwayat mutasi pencatatan stok gudang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: CATAT MUTASI BARANG MASUK / KELUAR -->
    <template x-teleport="body">
        <div x-show="openLogModal" x-cloak 
            class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-5 bg-gray-950/80 backdrop-blur-sm overflow-y-auto animate-fadeIn" 
            style="z-index: 999999;"
            @click.self="openLogModal = false" 
            @keydown.escape.window="openLogModal = false" 
            role="dialog" 
            aria-modal="true">

            <div x-show="openLogModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-800 overflow-hidden my-auto"
                @click.stop>
                
                <form action="{{ route('logs.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <!-- Header -->
                    <div class="px-5 py-4 sm:px-6 flex items-center justify-between border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white leading-tight">Pencatatan Mutasi Stok Gudang</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Penerimaan barang masuk atau pengeluaran material ke proyek</p>
                            </div>
                        </div>
                        <button type="button" @click="openLogModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer" title="Tutup (Esc)">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-5 sm:p-6 space-y-4 max-h-[75vh] overflow-y-auto bg-white dark:bg-gray-900">
                        <!-- Type Selection Tabs -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Jenis Mutasi Transaksi <span class="text-rose-500">*</span></label>
                            <select name="type" x-model="logType" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white font-bold focus:border-brand-500 focus:outline-none cursor-pointer">
                                <option value="in">↓ Barang Masuk (Penerimaan Supplier / PO)</option>
                                <option value="out">↑ Barang Keluar (Pengeluaran Project / MRF)</option>
                            </select>
                        </div>

                        <!-- Choose Item -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pilih Barang Material <span class="text-rose-500">*</span></label>
                            <select name="item_id" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none cursor-pointer">
                                <option value="">-- Pilih Barang Stok Gudang --</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }} (Stok: {{ $item->stock }} {{ $item->unit }}) - Rak: {{ $item->location ?? 'Gudang' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Reference Number (No PO / No MRF) -->
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300" x-text="logType === 'in' ? 'No. PO / Ref Supplier' : 'No. Project / MRF Ref'"></label>
                                <input type="text" name="reference_number" :placeholder="logType === 'in' ? 'PO-2026-0089' : 'MRF-MEI-042'" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            </div>

                            <!-- Quantity with Stick-to-Kg Calculator helper -->
                            <div class="space-y-1" x-data="{ 
                                showStickCalc: false, 
                                sticks: '', 
                                gram: 25, 
                                calcMode: 'gram',
                                stickPerKg: 40,
                                get kg() { 
                                    if (this.calcMode === 'gram') {
                                        const s = parseFloat(this.sticks) || 0; 
                                        const g = parseFloat(this.gram) || 0; 
                                        return s > 0 && g > 0 ? (s * g / 1000).toFixed(3) : '0.000';
                                    } else {
                                        const s = parseFloat(this.sticks) || 0; 
                                        const spk = parseFloat(this.stickPerKg) || 0; 
                                        return s > 0 && spk > 0 ? (s / spk).toFixed(3) : '0.000';
                                    }
                                },
                                setGram(val) {
                                    this.gram = val;
                                    this.calcMode = 'gram';
                                },
                                addSticks(n) {
                                    this.sticks = (parseFloat(this.sticks) || 0) + n;
                                },
                                applyToQuantity() {
                                    const val = parseFloat(this.kg);
                                    if (val > 0) {
                                        document.getElementById('logQuantityInput').value = val;
                                        this.showStickCalc = false;
                                    }
                                }
                            }">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Jumlah / Kuantitas <span class="text-rose-500">*</span></label>
                                    <button type="button" @click="showStickCalc = !showStickCalc" class="text-[10px] font-bold text-amber-700 dark:text-amber-400 hover:underline cursor-pointer inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="showStickCalc ? 'Tutup Hitung' : 'Hitung Stick (Kg)'"></span>
                                    </button>
                                </div>
                                <input type="number" id="logQuantityInput" name="quantity" value="1" min="0.001" step="any" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white font-mono font-bold focus:border-brand-500 focus:outline-none">
                                
                                <div x-show="showStickCalc" x-cloak class="p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50/80 dark:bg-amber-950/40 text-xs space-y-2 mt-1 shadow-xs">
                                    <div class="flex items-center justify-between text-amber-900 dark:text-amber-300 font-bold text-[11px] pb-1 border-b border-amber-200 dark:border-amber-800/60">
                                        <span>Konversi Stick ke Kg (Kawat Las)</span>
                                        <span class="text-[10px] font-normal text-amber-700 dark:text-amber-400">1 stick = berat gram</span>
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400">Jml Stick Diambil:</span>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="addSticks(5)" class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 hover:bg-amber-200 cursor-pointer">+5</button>
                                                <button type="button" @click="addSticks(10)" class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 hover:bg-amber-200 cursor-pointer">+10</button>
                                                <button type="button" @click="addSticks(20)" class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 hover:bg-amber-200 cursor-pointer">+20</button>
                                                <button type="button" @click="sticks = ''" class="px-1 py-0.5 text-[9px] font-bold text-rose-500 hover:underline cursor-pointer">Reset</button>
                                            </div>
                                        </div>
                                        <input type="number" x-model="sticks" placeholder="Misal: 15 stick" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 font-bold font-mono text-gray-900 dark:text-white focus:outline-none">
                                    </div>

                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400">Berat 1 Stick (Gram):</span>
                                            <span class="text-[9px] text-amber-700 dark:text-amber-400 font-mono" x-show="gram > 0" x-text="'≈ ' + Math.round(1000/gram) + ' stick/kg'"></span>
                                        </div>
                                        <input type="number" x-model="gram" step="any" placeholder="25" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 font-bold font-mono text-gray-900 dark:text-white focus:outline-none">
                                        <!-- Presets -->
                                        <div class="flex flex-wrap gap-1 pt-0.5">
                                            <button type="button" @click="setGram(20)" :class="gram == 20 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700'" class="px-1.5 py-0.5 rounded text-[9px] font-bold cursor-pointer">20g (Ø2.0mm)</button>
                                            <button type="button" @click="setGram(25)" :class="gram == 25 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700'" class="px-1.5 py-0.5 rounded text-[9px] font-bold cursor-pointer">25g (Ø2.6mm)</button>
                                            <button type="button" @click="setGram(35)" :class="gram == 35 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700'" class="px-1.5 py-0.5 rounded text-[9px] font-bold cursor-pointer">35g (Ø3.2mm)</button>
                                            <button type="button" @click="setGram(50)" :class="gram == 50 ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700'" class="px-1.5 py-0.5 rounded text-[9px] font-bold cursor-pointer">50g (Ø4.0mm)</button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-1.5 border-t border-amber-200 dark:border-amber-800">
                                        <div>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block">Hasil:</span>
                                            <strong class="text-amber-700 dark:text-amber-400 font-mono text-xs" x-text="kg + ' KG'"></strong>
                                        </div>
                                        <button type="button" @click="applyToQuantity()" :disabled="parseFloat(kg) <= 0" class="px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-bold text-[10px] cursor-pointer shadow-xs transition">
                                            Gunakan Nilai
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recipient or Provider -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300" x-text="logType === 'in' ? 'Nama Supplier / Vendor *' : 'Nama Penerima / Field Officer *'"></label>
                            <input type="text" name="recipient_or_provider" required :placeholder="logType === 'in' ? 'PT Swadaya Utama' : 'Ir. Ahmad Fauzi (Site Eng)'" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>

                        <!-- Notes -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Catatan / Keterangan Tambahan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan peruntukan, kondisi material, atau lokasi instalasi..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50/80 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openLogModal = false" class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting" class="btn-save-theme inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-brand-700 disabled:opacity-75 transition cursor-pointer">
                            <template x-if="submitting"><x-spinner /></template>
                            <span x-text="submitting ? 'Memproses Transaksi...' : 'Simpan Transaksi Mutasi'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- STANDALONE MODAL: KALKULATOR STICK KE KG -->
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
                applyToMutasi() {
                    const kgVal = parseFloat(this.calculatedKg);
                    if (kgVal > 0) {
                        const input = document.getElementById('logQuantityInput');
                        if (input) input.value = kgVal;
                        this.showStickModal = false;
                        openLogModal = true;
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
                                Konversi barang satuan KG yang diambil per batang / stick (kawat las, rod, dll)
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showStickModal = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer" title="Tutup Kalkulator">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
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

                    <!-- Input 1: Jumlah Stick Diambil -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Jumlah Stick / Batang yang Diambil <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-mono">Misal: 10, 15, 25 stick</span>
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
                        <button type="button" 
                                @click="applyToMutasi()" 
                                :disabled="parseFloat(calculatedKg) <= 0"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed px-4 py-2 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Catat Mutasi dg Nilai Ini</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

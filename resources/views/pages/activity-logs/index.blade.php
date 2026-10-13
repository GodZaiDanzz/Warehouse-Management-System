@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Log Aktivitas User & System</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Audit trail riwayat aktivitas pengguna, pengolahan data, import Excel, dan perubahan pengaturan sistem WMS</p>
        </div>
        
        <div>
            <!-- Form Hidden Clear Log Aktivitas -->
            <form id="clear-activity-logs-form" action="{{ route('activity-logs.clear') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <!-- Button Clear Log Aktivitas -->
            <button type="button" onclick="confirmClearLogs()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 dark:border-rose-900/30 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/40 shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Bersihkan Log
            </button>
        </div>
    </div>

    <!-- Alert Notifications Component -->
    <x-alert />

    <!-- Metrics Cards Summary -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
        <!-- Metric 1: Total Activity Logs -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Audit Log</span>
                    <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalLogsCount ?? 0) }} Aktivitas</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>

        <!-- Metric 2: Today's Logs -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Aktivitas Hari Ini</span>
                    <h3 class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($todayLogsCount ?? 0) }} Aktivitas</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Metric 3: Active Modules -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Modul Terpantau</span>
                    <h3 class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ count($modulesList ?? []) }} Modul</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Search Keyword -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cari Aktivitas / User / IP</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, aktivitas, IP..." class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>

            <!-- Filter Modul -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori / Modul</label>
                <select name="module" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Semua Modul System</option>
                    @foreach($modulesList as $mod)
                        <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                <div class="relative" x-data="{
                    fp: null,
                    init() {
                        const initPicker = () => {
                            if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                            this.fp = flatpickr(this.$refs.picker, {
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd/m/Y',
                                altInputClass: 'w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition',
                                defaultDate: '{{ request('start_date') }}'
                            });
                        };
                        this.$nextTick(() => initPicker());
                    },
                    destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                }">
                    <input x-ref="picker" type="text" name="start_date" class="hidden" value="{{ request('start_date') }}">
                </div>
            </div>

            <!-- End Date & Submit -->
            <div class="flex items-end gap-2">
                <div class="w-full">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                    <div class="relative" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                this.fp = flatpickr(this.$refs.picker, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition',
                                    defaultDate: '{{ request('end_date') }}'
                                });
                            };
                            this.$nextTick(() => initPicker());
                        },
                        destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                    }">
                        <input x-ref="picker" type="text" name="end_date" class="hidden" value="{{ request('end_date') }}">
                    </div>
                </div>
                <button type="submit" class="btn-save-theme rounded-xl border border-brand-600 bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-sm cursor-pointer h-[38px] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
                @if(request()->anyFilled(['search', 'module', 'start_date', 'end_date']))
                    <a href="{{ route('activity-logs.index') }}" class="rounded-xl border border-gray-300 bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 cursor-pointer h-[38px] flex items-center justify-center" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Activity Logs Data Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Waktu & Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Pengguna (User)</th>
                        <th class="px-6 py-4 font-semibold">Modul</th>
                        <th class="px-6 py-4 font-semibold">Aktivitas</th>
                        <th class="px-6 py-4 font-semibold">Detail Deskripsi</th>
                        <th class="px-6 py-4 font-semibold">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ format_datetime($log->created_at) }}</div>
                                <div class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-300 font-bold text-xs">
                                        {{ strtoupper(substr($log->user->name ?? 'System', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $log->user->name ?? 'System / Guest' }}</div>
                                        <div class="text-xs text-gray-400">{{ $log->user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @php
                                    $moduleClass = match($log->module) {
                                        'Stok Barang', 'Pencatatan Gudang' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $moduleClass }}">
                                    {{ $log->module }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                {{ $log->activity }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs leading-relaxed">
                                {{ $log->description ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-xs font-mono text-gray-500 dark:text-gray-400">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-base font-semibold">Belum Ada Record Log Aktivitas</p>
                                    <p class="text-xs text-gray-400">Aktivitas pengguna di sistem WMS akan tercatat di halaman ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Script Konfirmasi Clear Log Aktivitas via SweetAlert2 -->
    <script>
        function confirmClearLogs() {
            if (typeof window.Swal !== 'undefined') {
                window.Swal.fire({
                    title: 'Bersihkan Log Aktivitas?',
                    text: 'Tindakan ini akan menghapus seluruh riwayat log aktivitas pengguna dari database.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#9ca3af',
                    confirmButtonText: 'Ya, Bersihkan Log',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl p-6 font-sans text-center dark:bg-gray-900 dark:text-white dark:border dark:border-gray-800',
                        title: 'text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-2',
                        htmlContainer: 'text-sm text-gray-500 dark:text-gray-400',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer',
                        cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs cursor-pointer'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('clear-activity-logs-form').submit();
                    }
                });
            } else if (typeof window.confirmDelete === 'function') {
                window.confirmDelete(
                    'Tindakan ini akan menghapus seluruh riwayat log aktivitas pengguna dari database.',
                    () => document.getElementById('clear-activity-logs-form').submit(),
                    'Bersihkan Log Aktivitas?',
                    'Ya, Bersihkan Log',
                    'Batal'
                );
            } else {
                if (confirm('Tindakan ini akan menghapus seluruh riwayat log aktivitas pengguna dari database. Lanjutkan?')) {
                    document.getElementById('clear-activity-logs-form').submit();
                }
            }
        }
    </script>
</div>
@endsection

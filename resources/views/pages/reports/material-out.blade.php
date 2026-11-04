@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Pengeluaran Material</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pencatatan mutasi pengeluaran barang & material gudang untuk kebutuhan fabrikasi/project (Consumable, Secondary Part, dan Cat & Coating).</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reports.material-out.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>
            <a href="{{ route('reports.material-out.pdf', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-rose-700 shadow-xs transition cursor-pointer">
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
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 print:hidden">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Material Dikeluarkan</span>
                    <h3 class="mt-2 text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">-{{ format_quantity($totalQty) }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m16 0l-4-4m4 4l-4 4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Akumulasi kuantitas barang keluar</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Transaksi Keluar</span>
                    <h3 class="mt-2 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($totalTrans) }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Jumlah penyerahan material ke tim project</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Project Terkait</span>
                    <h3 class="mt-2 text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($totalProjects) }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Project penerima pengeluaran material</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 print:hidden space-y-4">
        <form method="GET" action="{{ route('reports.material-out') }}" class="space-y-4">
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <!-- Dropdown Jenis Kategori Pengeluaran (Primary Filter) -->
                <div>
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-brand-700 dark:text-brand-400 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>Jenis Kategori Laporan:</span>
                    </label>
                    <select name="category" onchange="this.form.submit()" class="w-full rounded-xl border border-brand-500 bg-brand-50/50 dark:bg-gray-800 px-3.5 py-2 text-xs font-extrabold text-brand-900 dark:text-white focus:border-brand-600 focus:bg-white focus:outline-none dark:border-brand-700 shadow-2xs cursor-pointer transition">
                        <option value="all" {{ ($category ?? 'all') === 'all' ? 'selected' : '' }}>Semua Kategori (All)</option>
                        <option value="consumable" {{ ($category ?? '') === 'consumable' ? 'selected' : '' }}>Material Consumable</option>
                        <option value="secondary" {{ ($category ?? '') === 'secondary' ? 'selected' : '' }}>Secondary Part</option>
                        <option value="cat" {{ ($category ?? '') === 'cat' ? 'selected' : '' }}>Cat & Coating</option>
                    </select>
                </div>

                <!-- Periode Tanggal -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Periode Tanggal Pengeluaran</label>
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
                            <input x-ref="picker" type="text" name="start_date" value="{{ $startDate }}" placeholder="Tanggal Mulai" readonly>
                        </div>
                        <span class="text-xs text-gray-400 font-bold">s/d</span>
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
                            <input x-ref="picker" type="text" name="end_date" value="{{ $endDate }}" placeholder="Tanggal Sampai" readonly>
                        </div>
                    </div>
                </div>

                <!-- Filter Project -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Pilih Project</label>
                    <select name="project" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">Semua Project</option>
                        @foreach($projectsList as $proj)
                            @if(!empty($proj))
                                <option value="{{ $proj }}" {{ request('project') == $proj ? 'selected' : '' }}>{{ $proj }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Filter PIC Penerima -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">PIC / Penerima</label>
                    <select name="pic" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">Semua PIC Penerima</option>
                        @foreach($picsList as $picItem)
                            @if(!empty($picItem))
                                <option value="{{ $picItem }}" {{ request('pic') == $picItem ? 'selected' : '' }}>{{ $picItem }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                <!-- Search Box -->
                <div class="relative w-full sm:w-80">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang atau project..." class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-3 py-2 text-xs font-semibold text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('reports.material-out') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition">
                        Reset Filter
                    </a>
                    <button type="submit" class="btn-save-theme rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white hover:bg-brand-700 shadow-sm transition cursor-pointer">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Data Riwayat Pengeluaran Material</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300">
                    {{ strtoupper($category ?? 'all') === 'ALL' ? 'Semua Kategori' : strtoupper($category) }}
                </span>
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">Total: <strong>{{ $logs->total() }}</strong> Baris Records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                <thead class="theme-table-header uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Tanggal & Waktu</th>
                        <th class="px-4 py-3.5">Kode Material</th>
                        <th class="px-4 py-3.5">Nama Barang</th>
                        <th class="px-4 py-3.5 text-center">Kategori</th>
                        <th class="px-4 py-3.5 text-center">Jumlah Keluar</th>
                        <th class="px-4 py-3.5">Project / Penggunaan</th>
                        <th class="px-4 py-3.5">PIC Penerima</th>
                        <th class="px-4 py-3.5 text-center">No. Ref Mutasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $index => $log)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-gray-900 dark:text-white">
                                {{ $logs->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-gray-700 dark:text-gray-300">
                                {{ format_date($log->date) }}
                                <span class="text-[10px] text-gray-400 block">{{ \Carbon\Carbon::parse($log->created_at)->format(system_time_format()) }} WIB</span>
                            </td>
                            <!-- KODE MATERIAL -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $log->item->code_item ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $log->item->name ?? '-' }}</div>
                                @if($log->item && $log->item->isCat())
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-normal mt-0.5">
                                        Tgl Pembuatan: <span class="font-medium text-gray-700 dark:text-gray-300">{{ format_date($log->item->manufacturing_date, 'Belum diisi') }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                    {{ $log->item->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold font-mono text-rose-600 dark:text-rose-400 text-sm">
                                -{{ format_quantity($log->quantity) }} {{ $log->unit ?: ($log->item->unit ?? 'Pcs') }}
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-gray-800 dark:text-gray-200">
                                {{ $log->project ?: '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-medium text-gray-700 dark:text-gray-300">
                                {{ $log->pic ?: '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono text-[11px] text-gray-500">
                                {{ $log->reference_number ?: ('OUT-' . $log->id) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                Tidak ada riwayat pengeluaran material yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

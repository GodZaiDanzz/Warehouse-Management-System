@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Pengeluaran Cat / Painting</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pencatatan mutasi keluar material cat/painting khusus, lengkap dengan pelacakan Tanggal Pembuatan (Manufacturing Date) dan konversi satuan.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reports.cat-painting.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>
            <a href="{{ route('reports.cat-painting.pdf', request()->all()) }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-rose-700 shadow-xs transition cursor-pointer">
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
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Cat Dikeluarkan</span>
                    <h3 class="mt-2 text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">-{{ format_quantity($totalQty) }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4h10a4 4 0 014 4 4 4 0 01-4 4H7zM7 13V5a2 2 0 012-2h6a2 2 0 012 2v8"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Akumulasi volume/kaleng keluar</p>
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
            <p class="mt-3 text-xs text-gray-400">Pemberian material cat ke tim lapangan</p>
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
            <p class="mt-3 text-xs text-gray-400">Project aktif pengecatan & painting</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 print:hidden space-y-4">
        <form method="GET" action="{{ route('reports.cat-painting') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
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
                            <input x-ref="picker" type="text" name="start_date" class="hidden" value="{{ $startDate }}">
                        </div>

                        <span class="text-xs font-bold text-gray-400">s/d</span>

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

                <!-- Project Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Nama Project</label>
                    <select name="project" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua Project --</option>
                        @foreach($projectsList as $prj)
                            <option value="{{ $prj }}" {{ request('project') == $prj ? 'selected' : '' }}>{{ $prj }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- PIC Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Nama PIC</label>
                    <select name="pic" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                        <option value="">-- Semua PIC --</option>
                        @foreach($picsList as $p)
                            <option value="{{ $p }}" {{ request('pic') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Search & Actions -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                <div class="flex-1 min-w-[240px] max-w-md">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama cat, kode, project, PIC..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-save-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer shadow-xs">
                        Cari & Terapkan
                    </button>
                    <a href="{{ route('reports.cat-painting') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden print:border print:border-gray-300 print:shadow-none">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 rounded-full bg-brand-600 ring-4 ring-brand-600/20"></span>
                <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wide">
                    Log Pemakaian & Pengeluaran Material Cat
                </h3>
            </div>
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-mono">
                Menampilkan {{ $logs->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="theme-table-header">
                    <tr class="border-b border-gray-950 text-white font-extrabold uppercase print:bg-gray-200 print:text-black">
                        <th scope="col" class="px-4 py-3 text-center w-12 border-r border-white/10">NO</th>
                        <th scope="col" class="px-4 py-3 min-w-[100px] border-r border-white/10">TANGGAL KELUAR</th>
                        <th scope="col" class="px-4 py-3 min-w-[200px] border-r border-white/10">NAMA CAT</th>
                        <th scope="col" class="px-4 py-3 text-center min-w-[130px] border-r border-white/10">TGL PEMBUATAN (CAT)</th>
                        <th scope="col" class="px-4 py-3 text-center min-w-[140px] border-r border-white/10">TGL KADALUARSA</th>
                        <th scope="col" class="px-4 py-3 text-center w-28 border-r border-white/10">JUMLAH KELUAR</th>
                        <th scope="col" class="px-4 py-3 min-w-[200px] border-r border-white/10">PROJECT / KEPERLUAN</th>
                        <th scope="col" class="px-4 py-3 min-w-[130px] border-r border-white/10">PIC PENERIMA</th>
                        <th scope="col" class="px-4 py-3 min-w-[150px]">CATATAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $index => $row)
                        @php
                            $mfgDate = ($row->item && $row->item->manufacturing_date) ? format_date($row->item->manufacturing_date) : '-';
                            $prjId = $row->project_id ?? \App\Models\Project::where('name', $row->project)->value('id');
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition dark:hover:bg-gray-800/50">
                            <td class="px-4 py-2.5 text-center font-semibold text-gray-600 dark:text-gray-400 border-r border-gray-100 dark:border-gray-800">
                                {{ $logs->firstItem() ? $logs->firstItem() + $index : $index + 1 }}
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap border-r border-gray-100 dark:border-gray-800">
                                {{ format_date($row->date) }}
                            </td>
                            <td class="px-4 py-2.5 font-bold text-gray-900 dark:text-white uppercase border-r border-gray-100 dark:border-gray-800">
                                {{ $row->item->name ?? '-' }}
                            </td>
                            <td class="px-4 py-2.5 text-center border-r border-gray-100 dark:border-gray-800 font-mono">
                                @if($mfgDate !== '-')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-brand-50 text-brand-700 border border-brand-200 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800">
                                        {{ $mfgDate }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center border-r border-gray-100 dark:border-gray-800 font-mono">
                                @if($row->item && $row->item->expiry_date)
                                    @php
                                        $expStatus = $row->item->expiry_status;
                                        $expBadgeClass = match($expStatus) {
                                            'expired' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                                            'near_expiry' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                            default => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                        };
                                        $expLabel = match($expStatus) {
                                            'expired' => 'Kadaluarsa',
                                            'near_expiry' => 'Near Expiry',
                                            default => 'Aman',
                                        };
                                    @endphp
                                    <span class="inline-flex flex-col items-center">
                                        <span class="text-[11px] font-bold text-gray-800 dark:text-gray-200">{{ format_date($row->item->expiry_date) }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold border {{ $expBadgeClass }} mt-0.5">
                                            {{ $expLabel }}
                                        </span>
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center border-r border-gray-100 dark:border-gray-800">
                                <span class="font-black text-brand-600 dark:text-brand-400 text-xs">-{{ format_quantity($row->quantity) }} {{ strtoupper($row->unit) }}</span>
                                @if($row->wholesale_qty && $row->wholesale_unit)
                                    <div class="text-[10px] font-bold text-gray-500">
                                        ({{ format_quantity($row->wholesale_qty) }} {{ $row->wholesale_unit }})
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white border-r border-gray-100 dark:border-gray-800">
                                @if($prjId)
                                    <a href="{{ route('projects.show', $prjId) }}" class="font-bold text-brand-600 hover:text-brand-800 dark:text-brand-400 hover:underline">
                                        {{ $row->project }}
                                    </a>
                                @else
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $row->project }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-gray-800 dark:text-gray-200 border-r border-gray-100 dark:border-gray-800">
                                {{ $row->pic }}
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-400">
                                {{ $row->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                Tidak ada data pengeluaran material cat pada periode dan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 print:hidden">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

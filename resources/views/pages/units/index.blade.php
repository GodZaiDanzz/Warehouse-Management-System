@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6" x-data="{ 
    openCreateModal: false, 
    openEditModal: false, 
    editUnit: { id: null, name: '', symbol: '', description: '', is_active: true }
}">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1zM2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1zM7 21h10M12 3v18M3 7h18" />
                    </svg>
                </span>
                <span>Master Data Satuan Barang</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pengelolaan satuan stok terpusat. Perubahan simbol satuan otomatis diperbarui pada seluruh transaksi gudang secara aman.
            </p>
        </div>
        
        <div>
            <button @click="openCreateModal = true" type="button" class="btn-add-theme inline-flex items-center justify-center gap-2 rounded-xl border border-brand-600 bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Satuan</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    <x-alert />

    <!-- Stat Highlights (Unified Brand Palette) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Satuan -->
        <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs flex items-center justify-between transition-all hover:border-brand-200">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Satuan Terdaftar</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ number_format($totalUnits) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Master unit terkonfigurasi</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-100 dark:border-brand-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>

        <!-- Card 2: Satuan Aktif -->
        <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs flex items-center justify-between transition-all hover:border-emerald-200">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Satuan Status Aktif</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5">{{ number_format($totalActiveUnits) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Dapat dipilih pada transaksi</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 3: Barang Terelasi -->
        <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs flex items-center justify-between transition-all hover:border-brand-200">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Barang Terelasi Master</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ number_format($totalItemsWithUnit) }} <span class="text-sm font-bold text-gray-500">Item</span></p>
                <p class="text-[11px] text-gray-400 mt-0.5">Item aktif di katalog gudang</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-100 dark:border-brand-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs">
        <form method="GET" action="{{ route('units.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="relative w-full sm:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, simbol, atau deskripsi..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-8 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                @if(request('search'))
                    <a href="{{ route('units.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Bersihkan pencarian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="btn-other-theme inline-flex items-center justify-center gap-2 w-full sm:w-auto rounded-xl bg-brand-600 hover:bg-brand-700 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari Satuan</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('units.index') }}" class="w-full sm:w-auto text-center rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition shadow-2xs">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Units Table -->
    <div class="rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4">Nama Satuan</th>
                        <th class="px-6 py-4">Simbol / Kode</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4 text-center">Jumlah Barang Terdaftar</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($units as $unit)
                        @php
                            $unitJson = json_encode([
                                'id' => $unit->id,
                                'name' => $unit->name,
                                'symbol' => $unit->symbol,
                                'description' => $unit->description ?? '',
                                'is_active' => (bool)$unit->is_active,
                            ], JSON_UNESCAPED_UNICODE);
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                {{ $unit->name }}
                            </td>
                            <td class="px-6 py-4 font-mono">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $unit->symbol }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $unit->description ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('items.index', ['unit_id' => $unit->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-800 border border-brand-200/60 hover:bg-brand-100 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800/50 transition shadow-2xs" title="Lihat barang dengan satuan ini">
                                    <span>{{ number_format($unit->items_count) }} Item</span>
                                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($unit->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button (Tactile) -->
                                    <button data-unit="{{ $unitJson }}" @click="
                                        editUnit = JSON.parse($el.getAttribute('data-unit'));
                                        openEditModal = true;
                                    " type="button" class="p-2 rounded-xl text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200/80 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800/60 transition cursor-pointer shadow-2xs" title="Edit Satuan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Delete Button (Tactile) -->
                                    <form id="delete-unit-form-{{ $unit->id }}" action="{{ route('units.destroy', $unit->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button data-name="{{ $unit->name }} ({{ $unit->symbol }})" @click="
                                        confirmDelete('Apakah Anda yakin ingin menghapus master satuan ' + $el.getAttribute('data-name') + '?', () => {
                                            document.getElementById('delete-unit-form-{{ $unit->id }}').submit();
                                        });
                                    " type="button" class="p-2 rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60 transition cursor-pointer shadow-2xs" title="Hapus Satuan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p class="font-medium text-xs">Belum ada data satuan yang cocok dengan pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($units->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                {{ $units->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: TAMBAH SATUAN BARU -->
    <template x-teleport="body">
        <div x-show="openCreateModal" x-cloak @click.self="openCreateModal = false" @keydown.escape.window="openCreateModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-5 bg-gray-950/80 backdrop-blur-sm overflow-y-auto animate-fadeIn" style="z-index: 999999;" aria-modal="true">
            <div x-show="openCreateModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200/80 bg-white text-left shadow-2xl transition-all dark:border-gray-800 dark:bg-gray-900 my-auto">
                <form action="{{ route('units.store') }}" method="POST">
                    @csrf
                    <!-- Modal Header -->
                    <div class="border-b border-gray-200/80 px-6 py-4 dark:border-gray-800 bg-gray-50/90 dark:bg-gray-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Tambah Satuan Baru</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Konfigurasi nama & simbol satuan barang</p>
                            </div>
                        </div>
                        <button type="button" @click="openCreateModal = false" class="rounded-xl p-2 text-gray-400 hover:bg-gray-200/70 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Nama Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Kilogram, Pieces, Liter..." class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Simbol / Kode Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="symbol" required placeholder="Contoh: KG, PCS, LITER, MTR..." class="w-full uppercase font-mono rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                            <p class="text-[11px] text-gray-400 mt-1">Simbol ini otomatis muncul pada kartu stok, FPBJ, dan laporan.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Keterangan / Deskripsi (Opsional)</label>
                            <textarea name="description" rows="3" placeholder="Penjelasan atau catatan penggunaan satuan..." class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition"></textarea>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90 px-6 py-4 flex items-center justify-end gap-3">
                        <button type="button" @click="openCreateModal = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 shadow-2xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="btn-save-theme rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700 shadow-xs transition cursor-pointer">
                            Simpan Satuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: EDIT SATUAN -->
    <template x-teleport="body">
        <div x-show="openEditModal" x-cloak @click.self="openEditModal = false" @keydown.escape.window="openEditModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-5 bg-gray-950/80 backdrop-blur-sm overflow-y-auto animate-fadeIn" style="z-index: 999999;" aria-modal="true">
            <div x-show="openEditModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200/80 bg-white text-left shadow-2xl transition-all dark:border-gray-800 dark:bg-gray-900 my-auto">
                <form :action="'{{ url('/units') }}/' + editUnit.id" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Modal Header -->
                    <div class="border-b border-gray-200/80 px-6 py-4 dark:border-gray-800 bg-gray-50/90 dark:bg-gray-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Master Satuan</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pembaruan data satuan secara terpusat</p>
                            </div>
                        </div>
                        <button type="button" @click="openEditModal = false" class="rounded-xl p-2 text-gray-400 hover:bg-gray-200/70 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Nama Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editUnit.name" required class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Simbol / Kode Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="symbol" x-model="editUnit.symbol" required class="w-full uppercase font-mono rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                            <div class="mt-2 rounded-lg bg-amber-50 p-2.5 border border-amber-200 text-[11px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/50 flex items-start gap-1.5">
                                <svg class="w-4 h-4 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Mengubah simbol satuan akan otomatis memperbarui tampilan seluruh barang dan data transaksi tanpa merusak jumlah nilai yang ada.</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Keterangan / Deskripsi</label>
                            <textarea name="description" x-model="editUnit.description" rows="3" class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition"></textarea>
                        </div>

                        <div class="flex items-center gap-2.5 pt-1">
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1" x-model="editUnit.is_active" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                            <label for="edit_is_active" class="text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                Status Satuan Aktif (Dapat dipilih saat input barang & transaksi)
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90 px-6 py-4 flex items-center justify-end gap-3">
                        <button type="button" @click="openEditModal = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 shadow-2xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="btn-save-theme rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700 shadow-xs transition cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection

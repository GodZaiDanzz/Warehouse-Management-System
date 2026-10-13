@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6" x-data="{ 
    openCreateModal: false, 
    openEditModal: false, 
    editCategory: { id: null, name: '', description: '' }
}">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
                <span>Kategori Barang Gudang</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pengelompokan sektor & klasifikasi material PT Mudalaya Energy Indonesia secara terstruktur.
            </p>
        </div>
        
        <div>
            <button @click="openCreateModal = true" type="button" class="btn-add-theme inline-flex items-center justify-center gap-2 rounded-xl border border-brand-600 bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Kategori</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    <x-alert />

    <!-- Stat Highlights (Unified Brand Palette) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Card 1: Total Kategori -->
        <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs flex items-center justify-between transition-all hover:border-brand-200">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kategori Terdaftar</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ number_format($totalCategories ?? $categories->total()) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Klasifikasi sektor aktif gudang</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-100 dark:border-brand-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </div>
        </div>

        <!-- Card 2: Akumulasi Item -->
        <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs flex items-center justify-between transition-all hover:border-brand-200">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Barang Terklasifikasi</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1.5">{{ number_format($totalCategorizedItems ?? 0) }} <span class="text-sm font-bold text-gray-500">Item</span></p>
                <p class="text-[11px] text-gray-400 mt-0.5">Total barang dengan kategori</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-100 dark:border-brand-900/50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs">
        <form method="GET" action="{{ route('categories.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="relative w-full sm:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau deskripsi kategori..." class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-8 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                @if(request('search'))
                    <a href="{{ route('categories.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Bersihkan pencarian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="btn-other-theme inline-flex items-center justify-center gap-2 w-full sm:w-auto rounded-xl bg-brand-600 hover:bg-brand-700 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari Kategori</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('categories.index') }}" class="w-full sm:w-auto text-center rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition shadow-2xs">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="rounded-2xl border border-gray-200/80 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4">Nama Kategori</th>
                        <th class="px-6 py-4">Deskripsi / Penjelasan</th>
                        <th class="px-6 py-4 text-center">Jumlah Item Terdaftar</th>
                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($categories as $cat)
                        @php
                            $catJson = json_encode([
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'description' => $cat->description ?? '',
                            ], JSON_UNESCAPED_UNICODE);
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                {{ $cat->name }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-md truncate">
                                {{ $cat->description ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('items.index', ['category_id' => $cat->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-800 border border-brand-200/60 hover:bg-brand-100 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800/50 transition shadow-2xs" title="Lihat barang dalam kategori ini">
                                    <span>{{ number_format($cat->items_count) }} Item</span>
                                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button (Tactile) -->
                                    <button data-cat="{{ $catJson }}" @click="
                                        editCategory = JSON.parse($el.getAttribute('data-cat'));
                                        openEditModal = true;
                                    " type="button" class="p-2 rounded-xl text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200/80 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800/60 transition cursor-pointer shadow-2xs" title="Edit Kategori">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Delete Button (Tactile) -->
                                    <form id="delete-category-form-{{ $cat->id }}" action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button data-name="{{ $cat->name }}" @click="
                                        confirmDelete('Apakah Anda yakin ingin menghapus kategori ' + $el.getAttribute('data-name') + '?', () => {
                                            document.getElementById('delete-category-form-{{ $cat->id }}').submit();
                                        });
                                    " type="button" class="p-2 rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60 transition cursor-pointer shadow-2xs" title="Hapus Kategori">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <p class="font-medium text-xs">Belum ada kategori barang yang cocok dengan pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: TAMBAH KATEGORI BARU -->
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
                <form action="{{ route('categories.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <!-- Modal Header -->
                    <div class="border-b border-gray-200/80 px-6 py-4 dark:border-gray-800 bg-gray-50/90 dark:bg-gray-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Tambah Kategori Baru</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Klasifikasi kelompok material gudang</p>
                            </div>
                        </div>
                        <button type="button" @click="openCreateModal = false" class="rounded-xl p-2 text-gray-400 hover:bg-gray-200/70 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Piping & Valves, Consumable..." class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi / Keterangan (Opsional)</label>
                            <textarea name="description" rows="3" placeholder="Penjelasan singkat kategori material..." class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition"></textarea>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90 px-6 py-4 flex items-center justify-end gap-3">
                        <button type="button" @click="openCreateModal = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 shadow-2xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting" class="btn-save-theme inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700 shadow-xs disabled:opacity-50 transition cursor-pointer">
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Kategori'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: EDIT KATEGORI -->
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
                <form :action="'/categories/' + editCategory.id" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @method('PUT')
                    <!-- Modal Header -->
                    <div class="border-b border-gray-200/80 px-6 py-4 dark:border-gray-800 bg-gray-50/90 dark:bg-gray-800/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Master Kategori</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Perbarui klasifikasi kategori material</p>
                            </div>
                        </div>
                        <button type="button" @click="openEditModal = false" class="rounded-xl p-2 text-gray-400 hover:bg-gray-200/70 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editCategory.name" required class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi / Keterangan</label>
                            <textarea name="description" x-model="editCategory.description" rows="3" class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition"></textarea>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90 px-6 py-4 flex items-center justify-end gap-3">
                        <button type="button" @click="openEditModal = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 shadow-2xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting" class="btn-save-theme inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700 shadow-xs disabled:opacity-50 transition cursor-pointer">
                            <span x-text="submitting ? 'Memperbarui...' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection

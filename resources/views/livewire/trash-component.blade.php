<div class="space-y-6">
    <!-- Flash Messages (SweetAlert2 Trigger) -->
    @if (session()->has('success'))
        @php
            $successMsg = session('success');
            $isDelete = preg_match('/hapus|delete|deleted|dihapus/i', $successMsg);
            $swalTitle = $isDelete ? 'Terhapus!' : 'Berhasil!';
        @endphp
        <div x-init="if (typeof window.showSwalSuccess === 'function') window.showSwalSuccess(@json($swalTitle), @json($successMsg))" class="hidden"></div>
    @endif

    @if (session()->has('error'))
        <div x-init="if (typeof window.showSwalError === 'function') window.showSwalError('Gagal!', @json(session('error')))" class="hidden"></div>
    @endif

    <!-- Page Header & Action -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tong Sampah Data (Recycle Bin)</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pusat pemulihan data dan manajemen data yang terhapus sementara di seluruh modul WMS</p>
        </div>

        @if($totalCount > 0)
            <div>
                <!-- Tombol Hapus Semua Data di Recycle Bin -->
                <button type="button" 
                    @click="confirmAction('Apakah Anda yakin ingin menghapus PERMANEN SELURUH data di Tong Sampah? Data yang sudah dihapus tidak dapat dikembalikan lagi.', () => $wire.emptyTrash(), 'KONFIRMASI HAPUS SEMUA', 'Ya, Hapus Semua Data')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100 dark:border-rose-900/30 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/40 shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Semua Data
                </button>
            </div>
        @endif
    </div>

    <!-- Filter & Search Bar -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau referensi..."
                    class="w-full sm:w-72 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                <select wire:model.live="filterType"
                    class="w-full sm:w-48 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="all">Semua Modul Data</option>
                    <option value="items">Master Barang</option>
                    <option value="categories">Kategori Barang</option>
                    <option value="projects">Project</option>
                    <option value="material_ins">Material Masuk</option>
                    <option value="material_outs">Material Keluar</option>
                </select>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                Total Data Terhapus: <span class="font-bold text-gray-900 dark:text-white font-mono">{{ $totalCount }}</span>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="relative rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5 text-center w-36">Modul Data</th>
                        <th class="px-4 py-3.5">Deskripsi Data</th>
                        <th class="px-4 py-3.5 text-center w-44">Waktu Dihapus</th>
                        <th class="px-4 py-3.5 text-center w-44">Aksi Pemulihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($trashedItems as $index => $item)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3 text-center font-mono text-gray-500">
                                {{ $trashedItems->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    {{ $item->type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-900 dark:text-white text-xs">
                                    {{ $item->title }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 font-mono">
                                    {{ $item->subtitle }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-[11px] text-gray-600 dark:text-gray-400">
                                {{ format_datetime($item->deleted_at) }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Pulihkan dengan SweetAlert2 -->
                                    <button type="button"
                                        @click="confirmAction('Apakah Anda yakin ingin memulihkan data ini kembali ke daftar aktif?', () => $wire.restoreRecord('{{ $item->type }}', {{ $item->id }}), 'PULIHKAN DATA', 'Ya, Pulihkan')"
                                        class="px-3 py-1.5 rounded-xl bg-brand-600 text-white hover:bg-brand-700 text-xs font-bold transition cursor-pointer shadow-xs"
                                        title="Kembalikan data ke daftar aktif">
                                        Pulihkan
                                    </button>
                                    <!-- Tombol Hapus Permanen dengan SweetAlert2 -->
                                    <button type="button"
                                        @click="confirmAction('Apakah Anda yakin ingin menghapus PERMANEN data ini? Data tidak dapat dikembalikan lagi.', () => $wire.forceDeleteRecord('{{ $item->type }}', {{ $item->id }}), 'HAPUS PERMANEN', 'Ya, Hapus Permanen')"
                                        class="px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white text-xs font-bold transition cursor-pointer shadow-xs"
                                        title="Hapus permanen dari database">
                                        Hapus Permanen
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data di Tong Sampah saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trashedItems->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                {{ $trashedItems->links() }}
            </div>
        @endif
    </div>
</div>

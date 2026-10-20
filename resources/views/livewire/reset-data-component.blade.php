<div>
    <!-- Flash Notifications (SweetAlert2 Trigger) -->
    @if (session()->has('success'))
        <div x-init="if (typeof window.showSwalSuccess === 'function') window.showSwalSuccess('Berhasil!', @json(session('success')))" class="hidden"></div>
    @endif

    @if (session()->has('error'))
        <div x-init="if (typeof window.showSwalError === 'function') window.showSwalError('Gagal!', @json(session('error')))" class="hidden"></div>
    @endif

    @php
        $totalOperationalRecords = $itemCount + $materialInCount + $materialOutCount + $materialRejectCount + $opnameCount + $projectCount + $stockLogCount + $activityLogCount;
    @endphp

    <!-- Administrative Notice Panel -->
    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/50 dark:bg-amber-950/20 mb-6">
        <div class="flex items-start gap-3.5">
            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white shadow-xs">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="space-y-1 text-xs text-amber-900 dark:text-amber-200">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white">Perhatian: Prosedur Reset Data Sistem</h3>
                <p class="leading-relaxed text-gray-700 dark:text-gray-300">
                    Proses ini mengosongkan seluruh tabel operasional gudang (stok barang, transaksi material masuk dan keluar, material reject atau rusak, stock opname, rekap project, serta log mutasi). 
                    <strong>Data akun pengguna (Users)</strong> dan <strong>Profil Perusahaan</strong> dilindungi oleh sistem dan tidak akan dihapus.
                </p>
                <div class="pt-1 flex items-center gap-2">
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Rekomendasi Keamanan:</span>
                    <a href="{{ route('backup.index') }}" class="underline font-semibold text-amber-800 hover:text-amber-950 dark:text-amber-300 dark:hover:text-amber-100">
                        Unduh salinan cadangan basis data (.sql)
                    </a>
                    <span>sebelum melanjutkan proses ini.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Strip -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <!-- Metric 1: Operational Records to Clear -->
        <div class="rounded-xl border border-gray-200 bg-white p-4.5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Baris Operasional</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400">{{ number_format($totalOperationalRecords) }}</span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">baris</span>
                    </div>
                    <span class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 mt-1 block">Akan dikosongkan total</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Metric 2: Protected User Accounts -->
        <div class="rounded-xl border border-gray-200 bg-white p-4.5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Akun Pengguna Terdaftar</span>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($userCount) }}</span>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">pengguna</span>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1 block">Dilindungi (Hak akses utuh)</span>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Metric 3: Company Profile Status -->
        <div class="rounded-xl border border-gray-200 bg-white p-4.5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div class="min-w-0 pr-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Profil Perusahaan</span>
                    <h4 class="mt-1 text-base font-bold text-gray-900 dark:text-white truncate">
                        {{ $companyProfile->name ?? 'PT Mudalaya Energy Indonesia' }}
                    </h4>
                    <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1 block">Dilindungi (Konfigurasi & logo utuh)</span>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Manifest & Audit Table -->
    <div class="rounded-xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden mb-6">
        <div class="border-b border-gray-200 px-5 py-3.5 dark:border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Rincian Dampak Reset Terhadap Modul Data</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Daftar tabel basis data beserta jumlah rekaman dan tindakan yang akan dieksekusi</p>
            </div>
            <span class="text-xs font-mono font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded">
                10 Entitas Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-600 dark:bg-gray-800/60 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th scope="col" class="py-3 px-5">Modul / Data Gudang</th>
                        <th scope="col" class="py-3 px-4">Tabel Basis Data</th>
                        <th scope="col" class="py-3 px-4 text-right">Rekaman Real</th>
                        <th scope="col" class="py-3 px-4 text-center">Tindakan Reset</th>
                        <th scope="col" class="py-3 px-5">Dampak Operasional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal text-gray-700 dark:text-gray-300">
                    <!-- 1. Items -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Master Barang dan Stok
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">items</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ number_format($itemCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                                Dikosongkan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-500 dark:text-gray-400">Katalog material, nomor rak, dan saldo stok dihapus permanen.</td>
                    </tr>

                    <!-- 2. Material In -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Transaksi Material Masuk
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">material_ins</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ number_format($materialInCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                                Dikosongkan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-500 dark:text-gray-400">Riwayat penerimaan barang dari supplier atau vendor dihapus.</td>
                    </tr>

                    <!-- 3. Material Out -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Transaksi Material Keluar
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">material_outs</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ number_format($materialOutCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                                Dikosongkan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-500 dark:text-gray-400">Riwayat pengeluaran barang project dan pemakaian fabrikasi dihapus.</td>
                    </tr>

                    <!-- 4. Material Reject -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 bg-rose-50/15 dark:bg-rose-950/5">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Material Reject dan Rusak
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">material_rejects</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-rose-600 dark:text-rose-400">{{ number_format($materialRejectCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                                Dikosongkan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-600 dark:text-gray-300">Seluruh laporan cacat, karantina, dan disposisi retur dihapus total.</td>
                    </tr>

                    <!-- 5. Daily Stock Opname -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Stock Opname Harian
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">daily_stock_opnames</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ number_format($opnameCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                                Dikosongkan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-500 dark:text-gray-400">Laporan verifikasi fisik gudang dan selisih opname dihapus.</td>
                    </tr>

                    <!-- 6. Projects -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Rekap Project dan Alokasi
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">projects</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ number_format($projectCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                                Dikosongkan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-500 dark:text-gray-400">Daftar project pengerjaan dan mapping material dikosongkan.</td>
                    </tr>

                    <!-- 7. Logs & Audit Trail -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Mutasi Stok dan Log Aktivitas
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">stock_logs, activity_logs</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ number_format($stockLogCount + $activityLogCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60">
                                Di-reset Ulang
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-500 dark:text-gray-400">Log mutasi dikosongkan; mencatat satu rekaman audit trail peristiwa reset.</td>
                    </tr>

                    <!-- 8. Categories -->
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="py-3.5 px-5 font-semibold text-gray-900 dark:text-white">
                            Kategori Material
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">categories</td>
                        <td class="py-3.5 px-4 text-center font-mono font-semibold">{{ number_format($categoryCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                Standardisasi
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-gray-500 dark:text-gray-400">Kategori non-standar dibersihkan; 7 kategori standar WMS dipulihkan.</td>
                    </tr>

                    <!-- 9. Users (Protected) -->
                    <tr class="bg-emerald-50/20 dark:bg-emerald-950/10 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                        <td class="py-3.5 px-5 font-semibold text-emerald-950 dark:text-emerald-200">
                            Akun Pengguna (Users)
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">users</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($userCount) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60">
                                Dipertahankan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-emerald-800 dark:text-emerald-300 font-medium">Akun login, password hash, dan seluruh role hak akses tetap aman.</td>
                    </tr>

                    <!-- 10. Company Profile (Protected) -->
                    <tr class="bg-emerald-50/20 dark:bg-emerald-950/10 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20">
                        <td class="py-3.5 px-5 font-semibold text-emerald-950 dark:text-emerald-200">
                            Profil Perusahaan
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500 dark:text-gray-400">company_profiles</td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-700 dark:text-emerald-400">1</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60">
                                Dipertahankan
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-emerald-800 dark:text-emerald-300 font-medium">Nama perusahaan, logo, tema sistem, dan penandatangan tetap aman.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action & Execution Control Panel -->
    <div class="rounded-xl border border-rose-200 bg-white p-5 shadow-xs dark:border-rose-900/40 dark:bg-gray-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Eksekusi Pembersihan Data Sistem</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Aksi ini akan menghapus permanen {{ number_format($totalOperationalRecords) }} baris data operasional termasuk reject material. Tindakan tidak dapat dibatalkan.
                </p>
            </div>
            <div>
                <button type="button" wire:click="openConfirmModal" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/30 transition shadow-xs cursor-pointer">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Reset Data Operasional</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Confirmation Dialog -->
    @if ($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/70 overflow-y-auto" wire:keydown.escape="closeModal" wire:click.self="closeModal" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900 dark:border dark:border-gray-800 my-auto max-h-[92vh] overflow-y-auto custom-scrollbar">
                <!-- Modal Header -->
                <div class="flex items-start gap-3.5 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-headline" class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Pembersihan Data Sistem</h3>
                        <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">Tindakan ini permanen dan tidak dapat dipulihkan secara otomatis.</p>
                    </div>
                </div>

                <!-- Explanation Box -->
                <div class="space-y-2.5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-5">
                    <p>
                        Sistem akan mengosongkan seluruh data operasional gudang sebanyak <strong>{{ number_format($totalOperationalRecords) }} baris data</strong>, yang mencakup:
                    </p>
                    <ul class="list-disc pl-5 space-y-1 text-gray-500 dark:text-gray-400">
                        <li>Master stok dan katalog barang</li>
                        <li>Transaksi material masuk dan transaksi material keluar</li>
                        <li>Laporan material reject atau rusak beserta disposisi</li>
                        <li>Catatan stock opname dan alokasi project</li>
                        <li>Riwayat mutasi stok dan kartu stok barang</li>
                    </ul>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/70 p-3 text-[11px] font-medium text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                        Data akun pengguna (Users) dan konfigurasi Profil Perusahaan tetap tersimpan aman dan tidak disentuh oleh prosedur ini.
                    </div>
                </div>

                <!-- Verification Form -->
                <form wire:submit.prevent="executeReset" class="space-y-4">
                    <div>
                        <label for="confirmInput" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Ketik kata <span class="font-mono font-bold text-rose-600 dark:text-rose-400">RESET</span> dengan huruf kapital untuk mengonfirmasi:
                        </label>
                        <input id="confirmInput" type="text" wire:model="confirmText" placeholder="RESET" autofocus autocomplete="off" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3.5 py-2 font-mono text-xs text-gray-900 uppercase tracking-widest focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @error('confirmText')
                            <span class="text-[11px] font-semibold text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" wire:click="closeModal" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/30 transition shadow-xs cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="executeReset">Ya, Bersihkan Data Sistem</span>
                            <span wire:loading wire:target="executeReset" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses Pembersihan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

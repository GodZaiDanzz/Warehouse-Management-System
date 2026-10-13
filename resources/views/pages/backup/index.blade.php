@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cadangan & Backup Database</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pengaturan pencadangan otomatis (Auto Backup), riwayat cadangan, dan pemulihan data sistem WMS</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('backup.create') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-add-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer shadow-xs" title="Buat cadangan database baru sekarang dan simpan ke server">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Backup Sekarang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Component -->
    <x-alert />

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Database Aktif</span>
            <div class="mt-2 flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white font-mono">{{ $dbName }}</h3>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">MySQL Engine Server</p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Tabel Data</span>
            <h3 class="mt-2 text-2xl font-bold text-brand-600 dark:text-brand-400 font-mono">{{ $totalTables }} Tabel</h3>
            <p class="text-[11px] text-gray-400 mt-1">Struktur tabel WMS</p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Baris Record</span>
            <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ number_format($totalRows) }} Baris</h3>
            <p class="text-[11px] text-gray-400 mt-1">Akumulasi entri data</p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Berkas Cadangan</span>
            <div class="mt-2 flex items-center justify-between">
                <h3 class="text-2xl font-bold text-brand-600 dark:text-brand-400 font-mono">{{ count($backupFiles) }} File</h3>
                <span class="text-xs font-bold font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">
                    Maks. {{ $profile->auto_backup_max_files ?? 10 }}
                </span>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Tersimpan di server storage</p>
        </div>
    </div>

    <!-- SECTION 1: AUTO BACKUP CONFIGURATION & SAVED BACKUP FILES -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- LEFT: AUTO BACKUP SETTINGS CARD (col-span-5) -->
        <div class="lg:col-span-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/80 dark:text-brand-400 font-bold text-base">
                        <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Pengaturan Auto Backup</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Jadwal pencadangan otomatis tanpa intervensi manual</p>
                    </div>
                </div>
            </div>

            <!-- Status Banner -->
            <div class="p-3.5 rounded-xl border {{ $profile->auto_backup_enabled ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800/60' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/60 dark:border-gray-700' }}">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $profile->auto_backup_enabled ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        <span class="text-xs font-extrabold {{ $profile->auto_backup_enabled ? 'text-emerald-800 dark:text-emerald-300' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ $profile->auto_backup_enabled ? 'Auto Backup Aktif' : 'Auto Backup Dinonaktifkan' }}
                        </span>
                    </div>
                    <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-full {{ $profile->auto_backup_enabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        Setiap {{ $profile->auto_backup_frequency_value ?? 7 }} {{ ($profile->auto_backup_frequency_unit ?? 'days') === 'months' ? 'Bulan' : 'Hari' }}
                    </span>
                </div>
                <div class="mt-2.5 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40 grid grid-cols-2 gap-2 text-[11px]">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block">Terakhir Berhasil:</span>
                        <strong class="text-gray-800 dark:text-gray-200 font-mono">
                            {{ $profile->last_backup_at ? \Carbon\Carbon::parse($profile->last_backup_at)->translatedFormat('d M Y, H:i') : 'Belum pernah' }}
                        </strong>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block">Jadwal Berikutnya:</span>
                        <strong class="text-gray-800 dark:text-gray-200 font-mono">
                            {{ $nextBackupDate ? $nextBackupDate->translatedFormat('d M Y, H:i') : '-' }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <form action="{{ route('backup.settings') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Enable / Disable Switch -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">Aktifkan Auto Backup</label>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Sistem otomatis mencadangkan database saat jadwal tiba</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_backup_enabled" value="1" {{ ($profile->auto_backup_enabled ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                </div>

                <!-- Frequency Value & Unit -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        Frekuensi Cadangan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input type="number" name="auto_backup_frequency_value" min="1" max="365" required
                                value="{{ old('auto_backup_frequency_value', $profile->auto_backup_frequency_value ?? 7) }}"
                                placeholder="Contoh: 7"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <span class="text-[10px] text-gray-400 mt-0.5 block">Nilai rentang (angka)</span>
                        </div>
                        <div>
                            <select name="auto_backup_frequency_unit" required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="days" {{ ($profile->auto_backup_frequency_unit ?? 'days') === 'days' ? 'selected' : '' }}>Hari (Days)</option>
                                <option value="months" {{ ($profile->auto_backup_frequency_unit ?? 'days') === 'months' ? 'selected' : '' }}>Bulan (Months)</option>
                            </select>
                            <span class="text-[10px] text-gray-400 mt-0.5 block">Satuan waktu interval</span>
                        </div>
                    </div>
                </div>

                <!-- Retention Limit -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        Batas Retensi File Cadangan (Maksimal Disimpan) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="auto_backup_max_files" min="1" max="50" required
                            value="{{ old('auto_backup_max_files', $profile->auto_backup_max_files ?? 10) }}"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white pr-12">
                        <span class="absolute right-3.5 top-2.5 text-xs font-bold text-gray-400">Berkas</span>
                    </div>
                    <p class="text-[11px] text-gray-400">File cadangan terlama akan otomatis dihapus bila jumlahnya melampaui batas ini agar ruang penyimpanan server tetap hemat.</p>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="btn-save-theme w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-2.5 transition cursor-pointer shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Pengaturan Backup</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT: SAVED LOCAL BACKUP FILES TABLE (col-span-7) -->
        <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/80 dark:text-brand-400 font-bold text-base">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Berkas Cadangan di Server</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Daftar file .sql hasil auto-backup dan manual backup di penyimpanan lokal</p>
                    </div>
                </div>
                <span class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-lg">
                    {{ count($backupFiles) }} File Tersedia
                </span>
            </div>

            <!-- Table of Local Backups -->
            <div class="overflow-x-auto max-h-[460px] custom-scrollbar rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="sticky top-0 theme-table-header text-white text-[11px] uppercase font-bold tracking-wider z-10 border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-3 py-3 w-10 text-center">#</th>
                            <th class="px-3 py-3 min-w-[200px]">Nama Berkas</th>
                            <th class="px-3 py-3 w-28 text-center">Ukuran</th>
                            <th class="px-3 py-3 w-36 text-center">Dibuat Pada</th>
                            <th class="px-3 py-3 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse ($backupFiles as $idx => $file)
                            <tr class="hover:bg-brand-50/20 dark:hover:bg-brand-950/20 transition">
                                <td class="px-3 py-3 text-center text-gray-400 font-bold">{{ $idx + 1 }}</td>
                                <td class="px-3 py-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            @if($file['is_auto'])
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                                    AUTO
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                    MANUAL
                                                </span>
                                            @endif
                                            <span class="font-mono font-bold text-gray-900 dark:text-white text-xs truncate max-w-[260px]" title="{{ $file['filename'] }}">
                                                {{ $file['filename'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center font-mono font-bold text-gray-700 dark:text-gray-300">
                                    {{ $file['size_formatted'] }}
                                </td>
                                <td class="px-3 py-3 text-center font-mono text-[11px] text-gray-600 dark:text-gray-400">
                                    {{ $file['created_at']->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Unduh -->
                                        <a href="{{ route('backup.file.download', $file['filename']) }}" 
                                            class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-950/40 transition cursor-pointer"
                                            title="Unduh file {{ $file['filename'] }}">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>

                                        <!-- Restore -->
                                        <form id="restore-file-form-{{ $idx }}" action="{{ route('backup.file.restore', $file['filename']) }}" method="POST" class="inline">
                                            @csrf
                                        </form>
                                        <button type="button" 
                                            onclick="confirmAction('Apakah Anda yakin ingin memulihkan (restore) database dari file {{ $file['filename'] }}? Data saat ini akan digantikan dengan data pada titik pemulihan ini.', () => document.getElementById('restore-file-form-{{ $idx }}').submit(), 'RESTORE DATABASE DARI FILE SERVER', 'Ya, Restore Sekarang')"
                                            class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/40 transition cursor-pointer"
                                            title="Pulihkan database dari file ini">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </button>

                                        <!-- Hapus -->
                                        <form id="delete-file-form-{{ $idx }}" action="{{ route('backup.file.delete', $file['filename']) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button type="button" 
                                            onclick="confirmAction('Hapus berkas cadangan {{ $file['filename'] }} dari server secara permanen?', () => document.getElementById('delete-file-form-{{ $idx }}').submit(), 'HAPUS BERKAS CADANGAN', 'Ya, Hapus File')"
                                            class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                            title="Hapus file cadangan ini">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                        </div>
                                        <p class="font-bold text-xs text-gray-600 dark:text-gray-300">Belum ada file cadangan yang tersimpan di server.</p>
                                        <p class="text-[11px] text-gray-400">Klik tombol <strong>"Backup Sekarang"</strong> di kanan atas untuk membuat cadangan pertama Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECTION 2: MANUAL DOWNLOAD TO PC & RESTORE FROM PC -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card: Download Direct to PC -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-400 text-xs font-bold">
                    Ekspor Mandiri
                </span>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Unduh Cadangan Database (.sql) ke Komputer</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Mengekspor seluruh struktur tabel dan data inventaris, mutasi, serta project langsung ke dalam satu file SQL murni dan mengunduhnya ke browser Anda.
                </p>
            </div>

            <div class="pt-2">
                <a href="{{ route('backup.download') }}" class="btn-save-theme inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download File Backup (.sql)</span>
                </a>
            </div>
        </div>

        <!-- Card: Restore Database from File -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 text-xs font-bold">
                    Pemulihan Data (Restore)
                </span>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Restore Database dari File Komputer (.sql)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Unggah file cadangan <code class="font-mono text-gray-800 dark:text-gray-200">.sql</code> dari komputer Anda untuk memulihkan seluruh struktur dan isi data database.
                </p>
            </div>

            <form id="restoreDbForm" action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-2 pt-2">
                @csrf
                <input type="file" name="sql_file" accept=".sql,.txt" required
                    class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl p-1 bg-gray-50 dark:bg-gray-800">
                <button type="button"
                    onclick="confirmAction('Apakah Anda yakin ingin memulihkan database dari file ini? Semua data saat ini akan digantikan sesuai file cadangan yang diunggah.', () => document.getElementById('restoreDbForm').submit(), 'RESTORE DATABASE DARI FILE', 'Ya, Restore Sekarang')"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-800 text-white hover:bg-gray-900 text-xs font-bold whitespace-nowrap transition cursor-pointer">
                    Restore Data
                </button>
            </form>
        </div>
    </div>

    <!-- SECTION 3: TABLE DETAILS & GUIDE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Rincian Tabel Database -->
        <div class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    <span>Rincian Tabel Database Sistem</span>
                </h3>
                <span class="text-xs text-gray-400">{{ $totalTables }} Tabel Terdeteksi</span>
            </div>

            <div class="overflow-x-auto max-h-96 custom-scrollbar rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="theme-table-header text-[11px] uppercase font-bold text-white sticky top-0">
                        <tr>
                            <th class="px-4 py-2.5">No</th>
                            <th class="px-4 py-2.5">Nama Tabel</th>
                            <th class="px-4 py-2.5 text-center">Jumlah Baris</th>
                            <th class="px-4 py-2.5 text-right">Ukuran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($tablesInfo as $idx => $t)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-4 py-2 text-gray-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2 font-mono font-semibold text-gray-900 dark:text-white">
                                    {{ $t->name }}
                                </td>
                                <td class="px-4 py-2 text-center font-mono">
                                    {{ number_format($t->rows_count) }}
                                </td>
                                <td class="px-4 py-2 text-right font-mono text-gray-500 dark:text-gray-400">
                                    {{ $t->size_mb }} MB
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panduan Pemulihan (Restore) -->
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 pb-3 border-b border-gray-100 dark:border-gray-800">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Panduan Cadangan & Restore</span>
            </h3>

            <div class="space-y-3 text-xs text-gray-600 dark:text-gray-300">
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 space-y-1">
                    <span class="font-bold text-brand-600 dark:text-brand-400">Pencadangan Otomatis (Auto Backup):</span>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                        Sistem berjalan di latar belakang memeriksa jadwal interval (misal: setiap 7 hari atau per bulan). File disimpan rapi di server dan file terlama otomatis dibersihkan sesuai batas retensi.
                    </p>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 space-y-1">
                    <span class="font-bold text-brand-600 dark:text-brand-400">Pemulihan Instan (1-Click Restore):</span>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                        Anda dapat langsung mengklik ikon panah melingkar (Restore) pada baris file di tabel "Berkas Cadangan di Server" untuk memulihkan database secara instan tanpa perlu mengunggah ulang file.
                    </p>
                </div>

                <div class="p-3 rounded-xl bg-brand-50 text-brand-800 dark:bg-brand-950/30 dark:text-brand-300 text-[11px] border border-brand-200 dark:border-brand-800">
                    <span class="font-bold block mb-1">Keamanan & Arsip:</span>
                    Sebaiknya sesekali unduh file cadangan ke flashdisk / media luar kantor sebagai salinan cadangan (*off-site disaster recovery*).
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

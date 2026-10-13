<div class="flex flex-col h-full max-h-[90vh] overflow-hidden">
    <!-- Modal Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200/90 dark:border-gray-800 bg-gray-50/90 dark:bg-gray-800/80 shrink-0">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-100 dark:border-brand-900/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Import Data Barang Excel</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Unggah file Excel stok barang (.xlsx, .xls, .csv) ke sistem</p>
            </div>
        </div>
        <button type="button" @click="openImportModal = false" class="rounded-xl p-2 text-gray-400 hover:bg-gray-200/70 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition cursor-pointer" title="Tutup">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    @if (session()->has('success'))
        <div x-init="if (typeof window.showSwalSuccess === 'function') window.showSwalSuccess('Berhasil!', @json(session('success')))" class="hidden"></div>
    @endif

    @if ($errorMessage)
        <div x-init="if (typeof window.showSwalError === 'function') window.showSwalError('Gagal!', @json($errorMessage))" class="hidden"></div>
    @endif

    <!-- Modal Body (Scrollable) -->
    <div class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar">
        <form wire:submit="startImport" id="formImportExcel" class="space-y-4">
            <!-- File Drop / Upload Box -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                    Pilih File Excel / CSV <span class="text-rose-500">*</span>
                </label>
                <div class="relative rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/70 p-6 text-center transition hover:border-brand-500 hover:bg-brand-50/20 dark:border-gray-700 dark:bg-gray-800/40 dark:hover:border-brand-500">
                    <input type="file" wire:model="file" id="excelFileInput" accept=".xlsx,.xls,.csv"
                           class="absolute inset-0 z-10 h-full w-full opacity-0 cursor-pointer">
                    <div class="flex flex-col items-center justify-center space-y-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-brand-600 shadow-xs dark:bg-gray-700 dark:text-brand-400 border border-gray-100 dark:border-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div class="text-xs">
                            <span class="font-bold text-brand-600 dark:text-brand-400">Pilih file</span> atau tarik file ke area ini
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-gray-400 dark:text-gray-500">
                            <span class="px-1.5 py-0.5 rounded bg-gray-200/80 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono text-[10px] font-semibold">.XLSX</span>
                            <span class="px-1.5 py-0.5 rounded bg-gray-200/80 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono text-[10px] font-semibold">.XLS</span>
                            <span class="px-1.5 py-0.5 rounded bg-gray-200/80 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono text-[10px] font-semibold">.CSV</span>
                        </div>
                        
                        <!-- File Name Display if selected -->
                        @if($file)
                            <div class="mt-2 inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 shadow-2xs">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="truncate max-w-[280px]">{{ $file->getClientOriginalName() }}</span>
                            </div>
                        @endif

                        <!-- Livewire Upload Loading State -->
                        <div wire:loading wire:target="file" class="mt-2 text-xs font-semibold text-brand-600 dark:text-brand-400 inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span>Mengunggah file ke sistem...</span>
                        </div>
                    </div>
                </div>
                @error('file') <span class="text-xs text-rose-500 font-semibold mt-1.5 block">{{ $message }}</span> @enderror
            </div>

            <!-- Petunjuk Format Singkat -->
            <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-3.5 dark:border-blue-900/30 dark:bg-blue-950/20 text-xs text-blue-800 dark:text-blue-300 space-y-1">
                <span class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Petunjuk Kolom Import:
                </span>
                <p class="text-[11px] text-blue-700/90 dark:text-blue-300/80 leading-relaxed">
                    Pastikan kolom file Excel memuat: <strong class="text-blue-900 dark:text-blue-100">Kode Barang, Nama Barang, Kategori, Satuan, Stok, Min Stok</strong>. Proses import berjalan secara aman tanpa duplikasi data.
                </p>
            </div>
        </form>

        <!-- Display Progress Bar untuk Import Aktif / Yang Baru Selesai -->
        @if($activeImport)
            @php
                $isProcessing = in_array($activeImport->status, ['pending', 'processing']);
                $percent = $activeImport->total_rows > 0 
                    ? min(100, round(($activeImport->processed_rows / $activeImport->total_rows) * 100)) 
                    : ($activeImport->status === 'completed' ? 100 : 0);
            @endphp

            @if($isProcessing || $activeImportId)
                <!-- Kartu Status Import Aktif / Selesai Sesi Ini -->
                <div @if($isProcessing) wire:poll.1s @endif class="rounded-2xl border p-4 space-y-3 transition-all {{ $isProcessing ? 'border-brand-300 bg-brand-50/50 dark:border-brand-800 dark:bg-brand-900/20' : 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-900/10' }}">
                    <div class="flex items-center justify-between text-xs font-bold text-gray-900 dark:text-white">
                        <div class="flex items-center gap-2">
                            @if($isProcessing)
                                <svg class="w-4 h-4 text-brand-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span class="text-brand-700 dark:text-brand-300">Proses Import Berjalan: {{ $activeImport->filename }}</span>
                            @else
                                <span class="text-emerald-700 dark:text-emerald-400 inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Import Selesai ({{ $activeImport->filename }})
                                </span>
                            @endif
                        </div>
                        <span class="font-mono text-sm font-extrabold {{ $isProcessing ? 'text-brand-600' : 'text-emerald-600' }}">{{ $percent }}%</span>
                    </div>

                    <!-- Progress Bar Graphic -->
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden shadow-inner">
                        <div class="h-2.5 rounded-full transition-all duration-300 {{ $isProcessing ? 'bg-brand-600 animate-pulse' : 'bg-emerald-500' }}" style="width: {{ $percent }}%"></div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300 font-medium">
                        <div>
                            <span>Terproses: <strong>{{ number_format($activeImport->processed_rows) }}</strong> / {{ number_format($activeImport->total_rows) }} Baris</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                {{ number_format($activeImport->successful_rows) }} Berhasil
                            </span>
                            @if($activeImport->failed_rows > 0)
                                <span class="text-rose-600 dark:text-rose-400 font-bold inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    {{ number_format($activeImport->failed_rows) }} Gagal
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Ringkasan Singkat Import Terakhir (Jika Tidak Ada Import Aktif) -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs">
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                        <span class="text-emerald-600">✓</span>
                        <span>Import terakhir: <strong class="text-gray-900 dark:text-white">{{ $activeImport->filename }}</strong></span>
                    </div>
                    <span class="text-[11px] text-gray-400">{{ $activeImport->created_at->diffForHumans() }} ({{ number_format($activeImport->successful_rows) }} data)</span>
                </div>
            @endif
        @endif

        <!-- Riwayat Batch Import (Collapsible) -->
        @if($recentImports->count() > 0)
            <div x-data="{ showHistory: false }" class="pt-2 border-t border-gray-100 dark:border-gray-800 space-y-2">
                <button type="button" @click="showHistory = !showHistory" class="w-full flex items-center justify-between py-1 text-xs font-bold text-gray-700 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 cursor-pointer">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Riwayat Batch Import ({{ $recentImports->count() }})
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showHistory }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="showHistory" x-transition class="space-y-2 pt-1 max-h-48 overflow-y-auto pr-1">
                    @foreach($recentImports as $imp)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-xs">
                            <div class="min-w-0 pr-2">
                                <span class="font-bold text-gray-900 dark:text-white truncate block">{{ $imp->filename }}</span>
                                <span class="text-gray-400 block text-[10px]">{{ $imp->created_at->diffForHumans() }} • Total: {{ number_format($imp->total_rows) }} baris</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $imp->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : ($imp->status === 'processing' ? 'bg-brand-50 text-brand-700 border border-brand-200 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800') }}">
                                    {{ strtoupper($imp->status) }}
                                </span>
                                @if($imp->failed_rows > 0)
                                    <button type="button" wire:click="downloadErrorReport({{ $imp->id }})" class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold text-[10px] cursor-pointer">
                                        Error ({{ $imp->failed_rows }})
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Footer -->
    <div class="p-4 sm:px-6 bg-gray-50 dark:bg-gray-800/90 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3 shrink-0">
        <span class="text-[11px] text-gray-400 dark:text-gray-500 hidden sm:inline-block">Maksimal file: 10MB (.xlsx, .xls, .csv)</span>
        <div class="flex items-center gap-3 ml-auto">
            <button type="button" @click="openImportModal = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 shadow-2xs transition cursor-pointer">
                Tutup
            </button>
            <button type="submit" form="formImportExcel" wire:loading.attr="disabled" wire:target="file, startImport"
                    class="btn-save-theme inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-xs transition cursor-pointer min-w-[170px]">
                <span wire:loading.remove wire:target="file, startImport" class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Upload & Jalankan Import</span>
                </span>
                <span wire:loading wire:target="file, startImport" class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span>Memproses...</span>
                </span>
            </button>
        </div>
    </div>
</div>

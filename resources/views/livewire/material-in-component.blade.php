<div>
    <!-- Flash Messages (SweetAlert2 Trigger) -->
    @if (session()->has('success'))
        @php
            $successMsg = session('success');
            $isDelete = preg_match('/hapus|delete|deleted|dihapus/i', $successMsg);
            $swalTitle = $isDelete ? 'Deleted!' : 'Berhasil!';
        @endphp
        <div x-init="if (typeof window.showSwalSuccess === 'function') window.showSwalSuccess(@json($swalTitle), @json($successMsg))" class="hidden"></div>
    @endif

    @if (session()->has('error'))
        <div x-init="if (typeof window.showSwalError === 'function') window.showSwalError('Gagal!', @json(session('error')))" class="hidden"></div>
    @endif

    @if (session()->has('warning'))
        <div x-init="if (typeof window.showSwalWarning === 'function') window.showSwalWarning('Peringatan!', @json(session('warning')))" class="hidden"></div>
    @endif

    <!-- Module Tab Switcher: Inbound vs Reject Material -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 pb-3 dark:border-gray-800">
        <div class="flex items-center gap-2">
            <button type="button" wire:click="setTab('inbound')" 
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer {{ $activeTab === 'inbound' ? 'bg-brand-600 text-white shadow-brand-500/20' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v11m0 0l-4-4m4 4l4-4M4 14v4a2 2 0 002 2h12a2 2 0 002-2v-4"/></svg>
                <span>Penerimaan Material (Inbound & QC)</span>
                @if($globalPendingQcCount > 0)
                    <span class="rounded-full bg-amber-500/90 text-white px-1.5 py-0.5 text-[10px] font-black">{{ $globalPendingQcCount }}</span>
                @endif
            </button>

            <button type="button" wire:click="setTab('rejects')" 
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer {{ $activeTab === 'rejects' ? 'bg-rose-600 text-white shadow-rose-500/20' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Material Rusak / Reject</span>
                @if($totalPendingDisposition > 0)
                    <span class="rounded-full bg-rose-500 text-white px-1.5 py-0.5 text-[10px] font-black">{{ $totalPendingDisposition }}</span>
                @endif
            </button>
        </div>

        <div class="flex items-center gap-3">
            @if($activeTab === 'rejects')
                <a href="{{ route('reports.reject-material') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Buka Laporan & BAP Rekapitulasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @endif
        </div>
    </div>

    @if ($activeTab === 'inbound')
    <!-- QC / Warehouse Inbound Mini Dashboard KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <!-- Card 1: Menunggu QC -->
        <div wire:click="$set('selectedQcStatus', '{{ $selectedQcStatus === 'pending_qc' ? '' : 'pending_qc' }}')" 
            class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $selectedQcStatus === 'pending_qc' ? 'border-amber-500 bg-amber-50/90 dark:bg-amber-950/40 ring-2 ring-amber-500/20' : 'border-gray-200 bg-white hover:border-amber-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-800' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Menunggu QC</span>
                    <h4 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ number_format($totalPendingQcPeriod) }} <span class="text-xs font-normal text-gray-500">Batch</span>
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        @if($totalPendingQcPeriod > 0)
                            <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu pemeriksaan kualitas</span>
                        @else
                            <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tidak ada antrean pending</span>
                        @endif
                    </p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            @if($selectedQcStatus === 'pending_qc')
                <div class="mt-2 text-[10px] font-bold text-amber-700 dark:text-amber-300 flex items-center gap-1">
                    <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Filter Aktif</span> (Klik untuk reset)
                </div>
            @endif
        </div>

        <!-- Card 2: Lolos QC (Approved) -->
        <div wire:click="$set('selectedQcStatus', '{{ $selectedQcStatus === 'approved' ? '' : 'approved' }}')" 
            class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $selectedQcStatus === 'approved' ? 'border-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20' : 'border-gray-200 bg-white hover:border-emerald-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-800' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Persetujuan QC (OK)</span>
                    <h4 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ number_format($totalApprovedPeriod) }} <span class="text-xs font-normal text-gray-500">Batch</span>
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        +{{ format_quantity($totalQtyApproved) }} unit bertambah ke stok
                    </p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            @if($selectedQcStatus === 'approved')
                <div class="mt-2 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-1">
                    <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Filter Aktif</span> (Klik untuk reset)
                </div>
            @endif
        </div>

        <!-- Card 3: Ditolak QC (Rejected) -->
        <div wire:click="$set('selectedQcStatus', '{{ $selectedQcStatus === 'rejected' ? '' : 'rejected' }}')" 
            class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $selectedQcStatus === 'rejected' ? 'border-rose-500 bg-rose-50/90 dark:bg-rose-950/40 ring-2 ring-rose-500/20' : 'border-gray-200 bg-white hover:border-rose-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-rose-800' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ditolak QC (Reject)</span>
                    <h4 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        {{ number_format($totalRejectedPeriod) }} <span class="text-xs font-normal text-gray-500">Batch</span>
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        @if($totalRejectedPeriod > 0)
                            <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Karantina / return vendor</span>
                        @else
                            <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 0 barang ditolak</span>
                        @endif
                    </p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            @if($selectedQcStatus === 'rejected')
                <div class="mt-2 text-[10px] font-bold text-rose-700 dark:text-rose-300 flex items-center gap-1">
                    <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Filter Aktif</span> (Klik untuk reset)
                </div>
            @endif
        </div>

        <!-- Card 4: Total Kedatangan -->
        <div wire:click="$set('selectedQcStatus', '')" 
            class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ empty($selectedQcStatus) ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-950/20 ring-2 ring-brand-500/20' : 'border-gray-200 bg-white hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-800' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kedatangan</span>
                    <h4 class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1">
                        {{ number_format($totalTransactionsPeriod) }} <span class="text-xs font-normal text-gray-500">Batch</span>
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        Semua status transaksi masuk
                    </p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>
            @if(empty($selectedQcStatus))
                <div class="mt-2 text-[10px] font-bold text-brand-700 dark:text-brand-300 flex items-center gap-1">
                    <span>Semua Status Ditampilkan</span>
                </div>
            @endif
        </div>
    </div>

    <!-- QC Inspection Queue Alert Banner -->
    @if($globalPendingQcCount > 0)
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/60 text-amber-950 dark:text-amber-200 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white font-black text-sm">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h5 class="text-xs font-extrabold uppercase tracking-wide">Antrean Inspeksi Mutu & Standar QC</h5>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">
                        Terdapat <strong>{{ $globalPendingQcCount }} transaksi material masuk</strong> yang sedang berstatus <span class="font-bold underline">Menunggu QC</span>. Segera lakukan inspeksi visual/teknis dan berikan verifikasi status QC.
                    </p>
                </div>
            </div>
            <button type="button" wire:click="$set('selectedQcStatus', 'pending_qc')" class="shrink-0 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-xs cursor-pointer inline-flex items-center gap-1.5">
                <span>Buka Antrean Menunggu QC</span>
            </button>
        </div>
    @else
        <div class="mb-5 flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/60 text-emerald-900 dark:text-emerald-200 text-xs shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-xs">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span class="font-semibold">Seluruh Material Telah Diperiksa: Tidak ada antrean kedatangan barang yang menunggu inspeksi QC.</span>
            </div>
            <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-bold hidden sm:inline">QC Inspection Clear</span>
        </div>
    @endif

    <!-- Main Table Card with Filter Bar -->
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <!-- Action Row: Search, Action Buttons -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 flex-1 max-w-md">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari surat jalan, no. ref, supplier, PO, barang, penerima..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:border-brand-500">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" wire:click="export" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </button>

                @if(!auth()->user() || !auth()->user()->isPpic())
                    <button type="button" wire:click="openReceivePoModal" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-purple-600 text-white hover:bg-purple-700 transition shadow-xs cursor-pointer" title="Terima Barang Datang Berdasarkan Purchase Order (PO) & Hitung Fisik untuk Langsung ACC ke Stok">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path><rect x="9" y="3" width="6" height="4" rx="1"></rect><path d="M9 12h6"></path><path d="M9 16h6"></path></svg>
                        <span>Terima dari PO</span>
                    </button>

                    <button type="button" wire:click="openOffCutModal" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl bg-amber-600 text-white hover:bg-amber-700 transition shadow-xs cursor-pointer" title="Catat Pengembalian Sisa Potongan Raw Material dari Workshop ke Storage 2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758M12 12l2.879-2.879a3 3 0 10-4.242-4.242L7.758 10.758"/></svg>
                        <span>Retur Sisa (Off-Cut)</span>
                    </button>

                    <button type="button" wire:click="openCreateModal" class="btn-add-theme inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-brand-600 text-white hover:bg-brand-700 transition shadow-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Catat Penerimaan Barang
                    </button>
                @endif
            </div>
        </div>

        <!-- Filter Bar Row: Date Range, Satuan, Supplier, Status QC -->
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
            <!-- Filter Tanggal Mulai s/d Sampai -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Rentang Tanggal (d/m/Y)</label>
                <div class="grid grid-cols-2 gap-2">
                    <div wire:ignore class="relative" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                this.fp = flatpickr(this.$refs.picker, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition',
                                    defaultDate: @js($dateFrom),
                                    onChange: (dates, dateStr) => {
                                        if (dateStr !== $wire.dateFrom) {
                                            $wire.set('dateFrom', dateStr);
                                        }
                                    }
                                });
                            };
                            this.$nextTick(() => initPicker());
                            this.$watch('$wire.dateFrom', val => {
                                if (this.fp && val !== this.fp.input.value) {
                                    this.fp.setDate(val, false);
                                }
                            });
                        },
                        destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                    }">
                        <input x-ref="picker" type="text" class="hidden" value="{{ $dateFrom }}">
                    </div>

                    <div wire:ignore class="relative" x-data="{
                        fp: null,
                        init() {
                            const initPicker = () => {
                                if (typeof flatpickr === 'undefined') { setTimeout(initPicker, 50); return; }
                                this.fp = flatpickr(this.$refs.picker, {
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition',
                                    defaultDate: @js($dateTo),
                                    onChange: (dates, dateStr) => {
                                        if (dateStr !== $wire.dateTo) {
                                            $wire.set('dateTo', dateStr);
                                        }
                                    }
                                });
                            };
                            this.$nextTick(() => initPicker());
                            this.$watch('$wire.dateTo', val => {
                                if (this.fp && val !== this.fp.input.value) {
                                    this.fp.setDate(val, false);
                                }
                            });
                        },
                        destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
                    }">
                        <input x-ref="picker" type="text" class="hidden" value="{{ $dateTo }}">
                    </div>
                </div>
            </div>

            <!-- Filter Satuan Barang -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Satuan</label>
                <select wire:model.live="selectedUnit" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                    <option value="">Semua Satuan</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->symbol }} ({{ $u->name }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status QC -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status QC</label>
                <select wire:model.live="selectedQcStatus" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Semua Status QC</option>
                    <option value="approved">OK (Approved)</option>
                    <option value="pending_qc">Menunggu QC</option>
                    <option value="rejected">REJECT (Ditolak)</option>
                </select>
            </div>

            <!-- Filter Supplier / Vendor -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Supplier</label>
                <select wire:model.live="selectedSupplier" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Semua Supplier</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup }}">{{ $sup }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Preset Shortcut Buttons -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Preset</label>
                <div class="flex items-center gap-1.5">
                    <button type="button" wire:click="setFilterLast7Days" class="px-2 py-2 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer">
                        7 Hari
                    </button>
                    <button type="button" wire:click="setFilterThisMonth" class="px-2 py-2 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer">
                        Bulan Ini
                    </button>
                    <button type="button" wire:click="resetFilters" class="px-2 py-2 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Data -->
        <div class="relative overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
            <!-- Livewire Loading Overlay for Material In -->
            <div wire:loading.flex wire:target="search, selectedCategory, selectedQcStatus, selectedSupplier, dateFrom, dateTo, resetFilters, setFilterLast7Days, setFilterThisMonth" class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-gray-900/60 backdrop-blur-2xs rounded-xl" style="display: none;">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-200">
                    <svg class="w-4 h-4 animate-spin text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Memuat data material masuk...</span>
                </div>
            </div>

            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="theme-table-header text-[11px] uppercase font-bold text-white">
                    <tr>
                        <th class="px-4 py-3">TANGGAL</th>
                        <th class="px-4 py-3">NO. SURAT JALAN</th>
                        <th class="px-4 py-3">KODE MATERIAL</th>
                        <th class="px-4 py-3">Barang Diterima</th>
                        <th class="px-4 py-3 text-center">Jumlah Fisik</th>
                        <th class="px-4 py-3 text-center">Status QC</th>
                        <th class="px-4 py-3">SUPPLIER</th>
                        <th class="px-4 py-3">Penerima</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($records as $row)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                            <td class="px-4 py-3 font-medium whitespace-nowrap text-gray-800 dark:text-gray-200">
                                {{ format_date($row->date) }}
                            </td>
                            <!-- NO. SURAT JALAN -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($row->delivery_order_number)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60" title="Nomor Surat Jalan / Delivery Order">
                                        <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>{{ $row->delivery_order_number }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-mono italic">-</span>
                                @endif
                                @if($row->reference_number)
                                    <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-mono mt-0.5" title="No. Referensi Sistem">{{ $row->reference_number }}</span>
                                @endif
                            </td>
                            <!-- KODE MATERIAL -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $row->item->code_item ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if(($row->item && $row->item->is_offcut) || $row->is_offcut)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-gray-900 dark:text-white">{ $row->item->name ?? '-' }</span>
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0 bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-700">OFF-CUT</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] text-gray-500 dark:text-gray-400 flex-wrap">
                                        @if($row->dimension || ($row->item && $row->item->dimension))
                                            <span class="font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-1 py-0.2 rounded border border-amber-200 dark:border-amber-800/50">
                                                { $row->dimension ?: $row->item->dimension }
                                            </span>
                                        @endif
                                        @if($row->origin_project || ($row->item && $row->item->origin_project))
                                            <span>Proyek: <strong class="text-gray-700 dark:text-gray-300">{ $row->origin_project ?: $row->item->origin_project }</strong></span>
                                        @endif
                                        @if($row->heat_number || ($row->item && $row->item->heat_number))
                                            <span class="font-mono">Heat: { $row->heat_number ?: $row->item->heat_number }</span>
                                        @endif
                                    </div>
                                @else
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $row->item->name ?? '-' }}</span>
                                @endif
                                @php
                                    $isCat = ($row->item && $row->item->isCat()) || ($row->item && str_contains(strtolower($row->item->category->name ?? ''), 'cat'));
                                    $mfgDate = $row->manufacturing_date ?? $row->item?->manufacturing_date;
                                @endphp
                                @if($isCat)
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-gray-500 dark:text-gray-400">
                                        <span>Tgl Pembuatan: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ format_date($mfgDate, 'Belum diisi') }}</span></span>
                                        <span class="inline-block px-1.5 py-0.5 rounded-full text-[9px] bg-brand-100 text-brand-800 dark:bg-brand-950/60 dark:text-brand-300 font-bold">Cat</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-black font-mono text-xs">
                                    +{{ format_quantity($row->quantity) }} {{ $row->unit }}
                                </span>
                                @if(!empty($row->wholesale_qty) && !empty($row->wholesale_unit))
                                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        ({{ format_quantity($row->wholesale_qty) }} {{ $row->wholesale_unit }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if(str_contains($row->qc_notes ?? '', 'Direct Allocation'))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300" title="{{ $row->qc_notes }}">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                                        Direct Proyek
                                    </span>
                                @elseif($row->is_offcut)
                                    <span class="block text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Internal Retur (Bengkel)</span>
                                @elseif($row->item && $row->item->isConsumable())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300" title="Bebas QC - Otomatis Langsung Masuk Stok">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Bebas QC
                                    </span>
                                @elseif($row->qc_status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        OK (Approved)
                                    </span>
                                @elseif($row->qc_status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300">
                                        <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        REJECT
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Menunggu QC
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $row->supplier ?: '-' }}</span>
                                @if($row->purchaseOrder)
                                    <span class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60" title="Diterima dari PO {{ $row->purchaseOrder->po_number }}">
                                        PO: {{ $row->purchaseOrder->po_number }}
                                    </span>
                                @endif

                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="block font-semibold">{{ $row->received_by }}</span>
                                @if(str_contains($row->qc_notes ?? '', 'Direct Allocation'))
                                    <span class="block text-[10px] text-amber-600 dark:text-amber-400 font-semibold truncate max-w-[150px]" title="{{ $row->qc_notes }}">
                                        {{ Str::limit($row->qc_notes, 28) }}
                                    </span>
                                @elseif($row->is_offcut)
                                    <span class="block text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Internal Retur (Bengkel)</span>
                                @elseif($row->item && $row->item->isConsumable())
                                    <span class="block text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Bebas QC (Sistem)</span>
                                @elseif($row->qcUser)
                                    <span class="block text-[10px] text-gray-500 dark:text-gray-400">QC: {{ $row->qcUser->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if(!empty(trim($row->notes ?? '')))
                                    <div class="max-w-[200px]" title="{{ $row->notes }}">
                                        <div class="flex items-start gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                            </svg>
                                            <span class="text-xs text-gray-700 dark:text-gray-300 font-medium line-clamp-2 leading-snug break-words">
                                                {{ $row->notes }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-300 dark:text-gray-600 font-mono">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <!-- Print Tanda Terima Fisik -->
                                    <button type="button" wire:click="openPrintReceiptModal({{ $row->id }})" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-gray-800 transition cursor-pointer" title="Cetak Catatan Tanda Terima Fisik">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </button>

                                    <!-- Action Buttons -->
                                    @if(!auth()->user() || !auth()->user()->isPpic())
                                        <!-- QC Approval Action (Hanya untuk non-consumable) -->
                                        @if(!$row->item || !$row->item->isConsumable())
                                            <button type="button" wire:click="openQcModal({{ $row->id }})" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-gray-800 transition cursor-pointer" title="Inspeksi & Approval QC">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            </button>
                                        @endif

                                        <!-- Edit Data -->
                                        <button type="button" wire:click="openEditModal({{ $row->id }})" class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-950/40 transition cursor-pointer" title="Edit Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>

                                        <!-- Hapus Data -->
                                        <button type="button" @click="confirmDelete('Yakin ingin menghapus data penerimaan ini? Stok barang akan disesuaikan kembali.', () => $wire.delete({{ $row->id }}))" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        @php
                            $hasActiveFiltersIn = !empty($search) || !empty($selectedCategory) || !empty($selectedQcStatus) || !empty($selectedSupplier) || !empty($dateFrom) || !empty($dateTo);
                        @endphp
                        <tr>
                            <td colspan="10" class="px-6 py-14 text-center">
                                <div class="max-w-md mx-auto flex flex-col items-center justify-center text-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3">
                                        @if($hasActiveFiltersIn)
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @else
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                        @endif
                                    </div>
                                    @if($hasActiveFiltersIn)
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak ada material masuk yang cocok dengan filter</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kombinasi kata kunci pencarian, rentang tanggal, status QC, atau supplier tidak menemukan data.</p>
                                        <div class="mt-4">
                                            <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reset Filter</span>
                                            </button>
                                        </div>
                                    @else
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Riwayat Material Masuk</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Belum ada transaksi penerimaan barang dari supplier yang tercatat di sistem gudang.</p>
                                        @if(!auth()->user() || !auth()->user()->isPpic())
                                            <div class="mt-4">
                                                <button type="button" wire:click="openCreateModal" class="btn-add-theme inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 text-xs font-bold shadow-xs transition cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span>Catat Penerimaan Barang Baru</span>
                                                </button>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-2">
            {{ $records->links() }}
        </div>
    </div>
    @endif

    @if ($activeTab === 'rejects')
        <!-- REJECT MATERIAL MINI DASHBOARD KPI CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Card 1: Total Kasus Reject -->
            <div wire:click="$set('rejectStatus', '')" 
                class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ empty($rejectStatus) ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/30 ring-2 ring-rose-500/20' : 'border-gray-200 bg-white hover:border-rose-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-rose-800' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Kasus Reject</span>
                        <h4 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                            {{ number_format($totalRejectCases) }} <span class="text-xs font-normal text-gray-500">Kasus</span>
                        </h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            Pencatatan barang cacat/rusak
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Qty Rusak -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Total Qty Rusak</span>
                        <h4 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                            {{ format_quantity($totalRejectQty) }} <span class="text-xs font-normal text-gray-500">Fisik</span>
                        </h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            Unit terdampak karantina/retur
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
            </div>

            <!-- Card 3: Menunggu Tindak Lanjut -->
            <div wire:click="$set('rejectStatus', '{{ $rejectStatus === 'pending' ? '' : 'pending' }}')" 
                class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $rejectStatus === 'pending' ? 'border-amber-500 bg-amber-50/80 dark:bg-amber-950/40 ring-2 ring-amber-500/20' : 'border-gray-200 bg-white hover:border-amber-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-800' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Menunggu Tindak Lanjut</span>
                        <h4 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                            {{ number_format($totalPendingDisposition) }} <span class="text-xs font-normal text-gray-500">Kasus</span>
                        </h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                            @if($totalPendingDisposition > 0)
                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Menunggu disposisi/retur</span>
                            @else
                                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Semua kasus sudah diproses</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Card 4: Selesai / Tuntas -->
            <div wire:click="$set('rejectStatus', '{{ $rejectStatus === 'completed' ? '' : 'completed' }}')" 
                class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $rejectStatus === 'completed' ? 'border-emerald-500 bg-emerald-50/80 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20' : 'border-gray-200 bg-white hover:border-emerald-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-800' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Selesai / Tuntas</span>
                        <h4 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                            {{ number_format($totalCompletedDisposition) }} <span class="text-xs font-normal text-gray-500">Kasus</span>
                        </h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            Barang diganti / retur tuntas
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- REJECT TABLE & FILTER BAR CARD -->
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
            <!-- Action Row: Search & Buttons -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="rejectSearch" placeholder="Cari no. reject, ref inbound, barang, supplier..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:border-rose-500">
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" wire:click="exportRejects" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export CSV
                    </button>

                    <a href="{{ route('reports.reject-material') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 dark:bg-gray-800 dark:border-rose-900/60 dark:text-rose-400 dark:hover:bg-rose-950/40 transition shadow-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Laporan BAP & Rekap
                    </a>

                    @if(!auth()->user() || !auth()->user()->isPpic())
                        <button type="button" wire:click="openCreateRejectModal" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 transition shadow-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Catat Material Rusak / Reject
                        </button>
                    @endif
                </div>
            </div>

            <!-- Filter Row: Date Range, Supplier, Status -->
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <!-- Rentang Tanggal -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Filter Tanggal Reject</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" wire:model.live="rejectDateFrom" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <input type="date" wire:model.live="rejectDateTo" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>

                <!-- Filter Status Disposisi -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status Disposisi</label>
                    <select wire:model.live="rejectStatus" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Tindak Lanjut</option>
                        <option value="in_progress">Dalam Proses Retur/Klaim</option>
                        <option value="completed">Selesai (Tuntas)</option>
                    </select>
                </div>

                <!-- Filter Supplier -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Supplier / Vendor</label>
                    <select wire:model.live="rejectSupplier" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                        <option value="">Semua Supplier</option>
                        @foreach($rejectSuppliers as $rs)
                            <option value="{{ $rs }}">{{ $rs }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Reset Filter -->
                <div class="flex items-center">
                    <button type="button" wire:click="$set('rejectDateFrom', ''); $set('rejectDateTo', ''); $set('rejectSupplier', ''); $set('rejectStatus', ''); $set('rejectSearch', '');" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer inline-flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Data Material Rusak / Reject -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-400">
                    <thead class="theme-table-header text-[11px] font-bold uppercase tracking-wider text-white border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-3 py-3 w-10 text-center">No</th>
                            <th class="px-3 py-3">Tgl & No. BAP</th>
                            <th class="px-3 py-3">Supplier / Vendor</th>
                            <th class="px-3 py-3">Kode Material</th>
                            <th class="px-3 py-3">Nama Barang</th>
                            <th class="px-3 py-3 text-center">Qty Rusak</th>
                            <th class="px-3 py-3">Jenis Kerusakan</th>
                            <th class="px-3 py-3">Status Disposisi</th>
                            <th class="px-3 py-3 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($rejectRecords as $idx => $row)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                <td class="px-3 py-3 text-center font-bold text-gray-500">
                                    {{ $rejectRecords->firstItem() + $idx }}
                                </td>
                                <td class="px-3 py-3">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ format_date($row->date) }}</div>
                                    <span class="font-mono text-[10px] text-rose-600 dark:text-rose-400 font-bold">{{ $row->reference_number }}</span>
                                </td>
                                <td class="px-3 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                    <div>{{ $row->supplier }}</div>
                                    @if($row->po_number || ($row->materialIn && $row->materialIn->po_number))
                                        <div class="text-[10px] text-gray-500 font-normal">PO: {{ $row->po_number ?? $row->materialIn->po_number }}</div>
                                    @endif
                                </td>
                                <!-- KODE MATERIAL -->
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                        {{ $row->item->code_item ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $row->item->name ?? '-' }}</div>
                                    <div class="mt-0.5 text-[10px] text-gray-500">
                                        <span>{{ $row->item->category->name ?? 'Consumable' }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-black font-mono text-xs">
                                        {{ format_quantity($row->quantity) }} {{ $row->unit }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                        {{ $row->damage_type }}
                                    </span>
                                    @if($row->description)
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $row->description }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if($row->disposition_status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            {{ $row->status_label }}
                                        </span>
                                    @elseif($row->disposition_status === 'in_progress')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                            <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            {{ $row->status_label }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                                            <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $row->status_label }}
                                        </span>
                                    @endif
                                    <div class="text-[10px] text-gray-500 mt-0.5">Tindakan: {{ $row->disposition_label }}</div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Update Disposisi -->
                                        @if(!auth()->user() || !auth()->user()->isPpic())
                                            <button type="button" wire:click="openDispositionModal({{ $row->id }})" class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-950/40 transition cursor-pointer" title="Update Disposisi & Hasil Retur">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </button>
                                        @endif

                                        <!-- Cetak BAP PDF -->
                                        <a href="{{ route('material-in.rejects.bap-pdf', $row->id) }}" target="_blank" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Cetak Berita Acara (BAP)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </a>

                                        <!-- Edit Reject -->
                                        @if(!auth()->user() || !auth()->user()->isPpic())
                                            <button type="button" wire:click="openEditRejectModal({{ $row->id }})" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/40 transition cursor-pointer" title="Edit Data Reject">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                        @endif

                                        <!-- Hapus Reject (Web Admin Only) -->
                                        @if(auth()->user() && auth()->user()->isAdmin())
                                            <button type="button" @click="confirmDelete('Yakin ingin menghapus data reject ini?', () => $wire.deleteReject({{ $row->id }}))" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus Data (Khusus Admin)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="font-bold">Tidak ada data material reject yang tercatat</span>
                                        <span class="text-xs">Kondisi barang masuk dalam keadaan baik atau belum ada reject yang diinput.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pt-2">
                {{ $rejectRecords->links() }}
            </div>
        </div>
    @endif

    <!-- MODAL 1: FORM CATAT / EDIT MATERIAL MASUK -->
    @if ($isModalOpen)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn"
             x-init="window.warehouseItemsList = @js($itemsJsonList ?? []); window.warehouseProjectsList = @js($projectsJsonList ?? []); window.warehousePicList = @js($picSuggestionsList ?? []);">
            <div class="w-full {{ $isEditMode ? 'max-w-xl' : 'max-w-4xl lg:max-w-5xl' }} rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 max-h-[92vh] overflow-y-auto transition-all duration-200">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                            @if($isEditMode)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                                {{ $isEditMode ? 'Edit Penerimaan Material Masuk' : 'Catat Penerimaan Fisik Barang Masuk (Batch / Banyak Barang)' }}
                            </h3>
                            @if (!$isEditMode)
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Input satu atau banyak barang sekaligus dari satu surat jalan / vendor pengirim.</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    @if ($isEditMode)
                        <!-- EDIT MODE: SINGLE ITEM FORM -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Penerimaan <span class="text-rose-500">*</span></label>
                                <input type="date" wire:model="date" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                @error('date') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pilih Barang <span class="text-rose-500">*</span></label>
                                
                                <div class="relative" 
                                     x-data="itemSearchCombobox(@js($item_id ?? ''), 'item_id')"
                                     @scroll.window="if(open) updatePosition()"
                                     @resize.window="if(open) updatePosition()">
                                    
                                    <input type="hidden" 
                                           x-ref="hiddenInput"
                                           wire:model.live="item_id" 
                                           :value="selectedId">

                                    <div x-ref="triggerWrap" 
                                         @click="toggle()"
                                         style="min-height: 42px;"
                                         class="relative flex items-center justify-between w-full min-h-[42px] px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 cursor-pointer hover:border-brand-400 focus-within:border-brand-500 focus-within:bg-white transition shadow-2xs">
                                        
                                        <div class="flex-1 min-w-0 pr-2">
                                            <template x-if="selectedItem">
                                                <span class="font-bold text-xs text-gray-900 dark:text-white truncate block" :title="selectedItem.name" x-text="selectedItem.name"></span>
                                            </template>
                                            <template x-if="!selectedItem">
                                                <span class="text-gray-400 dark:text-gray-500 text-xs truncate block">Cari / Pilih Barang...</span>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <button type="button" 
                                                    x-show="selectedId" 
                                                    @click.stop="clearSelection()" 
                                                    class="p-1 text-gray-400 hover:text-rose-500 rounded text-xs transition cursor-pointer"
                                                    title="Hapus pilihan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            <button type="button" 
                                                    @click.stop="toggle()" 
                                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition cursor-pointer"
                                                    title="Buka daftar barang">
                                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Teleported Floating Dropdown Menu -->
                                    <div wire:ignore>
                                    <template x-teleport="body">
                                        <div x-show="open" 
                                             x-cloak 
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                             :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                             class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                            
                                            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Pilih Barang
                                                </span>
                                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredItems.length + ' Barang'"></span>
                                            </div>

                                            <div class="relative">
                                                <input type="text" 
                                                       x-ref="searchField" 
                                                       x-model="search" 
                                                       @keydown.enter.prevent="if (filteredItems.length > 0) { selectItem(filteredItems[0]); }"
                                                       @keydown.escape="close()"
                                                       placeholder="Ketik nama, kode, atau kategori barang..." 
                                                       class="w-full pl-3 pr-7 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-500 focus:bg-white dark:focus:bg-gray-800 shadow-xs">
                                                <button x-show="search.length > 0" @click="search = ''" type="button" class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            <div class="max-h-60 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                <template x-for="item in filteredItems" :key="item.id">
                                                    <div @click="selectItem(item)"
                                                         class="p-2 rounded-xl hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-brand-200 dark:hover:border-brand-800"
                                                         :class="String(selectedId) === String(item.id) ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0"
                                                                      :class="item.category && item.category.toLowerCase().includes('cat') ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : (item.category && item.category.toLowerCase().includes('secondary') ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')"
                                                                      x-text="item.category"></span>
                                                                <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="item.name"></span>
                                                            </div>
                                                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-2">
                                                                <span x-show="item.code" class="font-mono text-gray-400" x-text="'Kode: ' + item.code"></span>
                                                                <span x-show="item.code">&bull;</span>
                                                                <span>Stok: <strong class="text-gray-700 dark:text-gray-200 font-semibold" x-text="item.stock + ' ' + item.unit"></strong></span>
                                                            </div>
                                                        </div>
                                                        <div class="shrink-0 flex items-center">
                                                            <span x-show="String(selectedId) === String(item.id)" class="text-brand-600 dark:text-brand-400">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="filteredItems.length === 0" class="py-6 text-center text-xs text-gray-400">
                                                    Tidak ada barang yang cocok dengan pencarian "<span x-text="search" class="font-bold"></span>"
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    </div>
                                </div>

                                @error('item_id') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if ($selectedItemStock !== null)
                            <div class="rounded-xl bg-emerald-50 p-3 text-xs text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-between">
                                <span>Stok saat ini di master:</span>
                                <span class="font-extrabold font-mono text-sm">{{ format_quantity($selectedItemStock) }} {{ $selectedItemUnit }}</span>
                            </div>
                        @endif

                        @if($wholesale_unit && $conversion_factor > 1)
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Pilihan Satuan Input</span>
                                    <div class="inline-flex rounded-lg bg-gray-200 dark:bg-gray-700 p-0.5 text-xs">
                                        <button type="button" wire:click="setInputMode('base')" class="px-2.5 py-1 rounded-md transition font-semibold {{ $input_mode === 'base' ? 'bg-white text-gray-900 shadow-2xs dark:bg-gray-900 dark:text-white' : 'text-gray-500' }}">
                                            Satuan Terkecil ({{ $unit }})
                                        </button>
                                        <button type="button" wire:click="setInputMode('wholesale')" class="px-2.5 py-1 rounded-md transition font-semibold {{ $input_mode === 'wholesale' ? 'bg-white text-gray-900 shadow-2xs dark:bg-gray-900 dark:text-white' : 'text-gray-500' }}">
                                            Grosir ({{ $wholesale_unit }})
                                        </button>
                                    </div>
                                </div>

                                @if($input_mode === 'wholesale')
                                    <div class="grid grid-cols-2 gap-3 items-center pt-1">
                                        <div>
                                            <label class="block text-[11px] text-gray-500">Jumlah {{ $wholesale_unit }} Diterima</label>
                                            <input type="number" wire:model.live="wholesale_qty" min="0.001" step="any" placeholder="Misal: 2" class="w-full rounded-lg border border-brand-300 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs font-bold">
                                        </div>
                                        <div class="text-[11px] text-brand-600 dark:text-brand-400 font-bold bg-brand-50 dark:bg-brand-950/40 p-2 rounded-lg">
                                            1 {{ $wholesale_unit }} = {{ $conversion_factor }} {{ $unit }}<br>
                                            Total: <strong class="text-sm font-black">{{ format_quantity((float) $wholesale_qty * (float) $conversion_factor) }} {{ $unit }}</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($input_mode === 'base' || empty($wholesale_unit))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jumlah Fisik Diterima <span class="text-rose-500">*</span></label>
                                    <input type="number" wire:model="quantity" min="0.001" step="any" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @error('quantity') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan</label>
                                    <input type="text" wire:model="unit" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                            </div>
                        @endif

                        @if($isCatItem)
                            <div class="rounded-xl p-3.5 bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4c1.1 0 2 .9 2 2a2 2 0 002 2 2 2 0 002-2c0-1.1.9-2 2-2a4 4 0 014 4 4 4 0 01-4 4H7z"/></svg>
                                        <span>Parameter Usia Cat & Coating</span>
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-200/60 dark:bg-purple-900/60 text-purple-800 dark:text-purple-300">
                                        Shelf-Life Control
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-purple-900 dark:text-purple-300 mb-1">
                                            Tgl Pembuatan (Mfg Date) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" wire:model="manufacturing_date" class="w-full rounded-xl border border-purple-300 dark:border-purple-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-purple-900 dark:text-purple-300 mb-1">
                                            Tgl Kadaluarsa (Expiry Date)
                                        </label>
                                        <input type="date" wire:model="expiry_date" class="w-full rounded-xl border border-purple-300 dark:border-purple-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                <p class="text-[10px] text-purple-600 dark:text-purple-400">Penting untuk mencegah pemakaian material coating kadaluarsa pada tangki atau konstruksi.</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">No. Surat Jalan / DO</label>
                                <input type="text" wire:model="delivery_order_number" placeholder="Contoh: SJ-2026/01/008" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs font-mono text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @error('delivery_order_number') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Supplier / Vendor Penyedia <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="supplier" placeholder="PT Schneider / CV Jaya" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @error('supplier') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Diterima Oleh (Petugas Gudang) <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="received_by" placeholder="Nama petugas penerima material" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('received_by') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if ($isConsumableItem)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 dark:border-emerald-800/60 dark:bg-emerald-950/30 flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white font-black text-xs">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Bebas QC (Kategori Consumable)</h4>
                                    <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5 leading-relaxed">
                                        Material consumable <strong>tidak memerlukan proses inspeksi QC</strong>. Stok barang akan <strong>otomatis langsung bertambah</strong> ke stok master saat disimpan.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Persetujuan QC (Quality Control)</label>
                                <select wire:model="qc_status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                    <option value="approved">Langsung Setujui (OK) & Aktifkan Stok Master Sekarang</option>
                                    <option value="pending_qc">Simpan Fisik Saja (Menunggu Approval QC Baru Tambah Stok)</option>
                                </select>
                                <p class="text-[11px] text-gray-500 mt-1">Jika memilih Menunggu QC, stok aktif baru akan bertambah setelah tim QC melakukan inspeksi persetujuan (OK).</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Kondisi / Kelengkapan (Opsional)</label>
                            <textarea wire:model="notes" rows="2" placeholder="Catatan kondisi material saat diterima..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        </div>
                    @else
                        <!-- CREATE MODE: BATCH MULTI-ITEM FORM -->
                        <!-- Bagian 1: Header Tanda Terima / Surat Jalan -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-gray-200 pb-2 border-b border-gray-200/70 dark:border-gray-700/70">
                                <span class="p-1 rounded-md bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <span>Informasi Pengiriman & Supplier (Header Bersama)</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Tanggal Penerimaan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" wire:model="date" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                    @error('date') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        No. Surat Jalan / DO
                                    </label>
                                    <input type="text" wire:model="delivery_order_number" placeholder="Contoh: SJ-2026/01/008" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-mono text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @error('delivery_order_number') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Supplier / Vendor Penyedia <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" wire:model="supplier" placeholder="PT Schneider / CV Jaya" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @error('supplier') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Diterima Oleh (Gudang) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" wire:model="received_by" placeholder="Nama petugas gudang" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @error('received_by') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Persetujuan QC (Non-Consumable)</label>
                                    @if($directToProject)
                                        <div class="w-full rounded-xl border border-amber-300 bg-amber-50/70 dark:bg-amber-950/40 dark:border-amber-800 px-3 py-2 text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center justify-between">
                                            <span>Otomatis Disetujui (Bebas QC)</span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-100 font-extrabold uppercase">Direct Out</span>
                                        </div>
                                        <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5">*Opsi A: Alokasi langsung ke proyek otomatis menyetujui penerimaan.</p>
                                    @else
                                        <select wire:model="qc_status" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                            <option value="pending_qc">Simpan Fisik Saja (Menunggu Approval QC Baru Tambah Stok)</option>
                                            <option value="approved">Langsung Setujui (OK) & Aktifkan Stok Master Sekarang</option>
                                        </select>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">*Item consumable otomatis bebas QC dan langsung menambah stok master.</p>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Pengiriman / No. Surat Jalan (Opsional)</label>
                                    <input type="text" wire:model="notes" placeholder="Contoh: Surat Jalan SJ-1092 / PO-2026-081" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <!-- OPSI: ALOKASI LANGSUNG KE PROYEK (DIRECT ISSUE / CROSS-DOCKING) -->
                        <div class="p-4 rounded-xl border transition-all duration-200 {{ $directToProject ? 'bg-amber-50/70 border-amber-300 dark:bg-amber-950/30 dark:border-amber-700/60 shadow-xs' : 'bg-gray-50 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700/60' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5">
                                    <div class="mt-0.5 p-1.5 rounded-lg {{ $directToProject ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <label for="direct_to_project_toggle" class="text-xs font-extrabold text-gray-900 dark:text-white cursor-pointer select-none flex items-center gap-2">
                                            Alokasikan Langsung ke Proyek (Material Langsung Keluar)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide {{ $directToProject ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                                Cross-Docking
                                            </span>
                                        </label>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                                            Aktifkan jika barang dari vendor langsung diserahkan ke proyek. Sistem akan otomatis mencatat tanda terima masuk, langsung menerbitkan transaksi Material Keluar, dan memperbarui rekap material proyek.
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0 pt-0.5">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="direct_to_project_toggle" wire:model.live="directToProject" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-600"></div>
                                    </label>
                                </div>
                            </div>

                            @if ($directToProject)
                                <div class="mt-4 pt-3.5 border-t border-amber-200/80 dark:border-amber-800/60 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                                    <!-- Proyek Tujuan Search Combobox -->
                                    <div class="sm:col-span-1">
                                        <label class="block text-[11px] font-bold text-gray-800 dark:text-gray-200 mb-1">
                                            Proyek Tujuan <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative" 
                                             x-data="projectSearchCombobox(@js($directProjectName ?: ($projects->firstWhere('id', $directProjectId)?->name ?? '')), 'directProjectName', 'directProjectId')"
                                             @scroll.window="if(open) updatePosition()"
                                             @resize.window="if(open) updatePosition()">
                                            
                                            <input type="hidden" 
                                                   x-ref="hiddenIdInput"
                                                   wire:model="directProjectId" 
                                                   :value="selectedId">

                                            <div x-ref="triggerWrap" class="relative flex items-center">
                                                <input type="text" 
                                                       x-ref="actualInput"
                                                       wire:model="directProjectName" 
                                                       x-model="selected"
                                                       @input="onInput($event)"
                                                       @focus="onFocus()"
                                                       placeholder="Ketik nama atau pilih project..." 
                                                       class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 pl-3 pr-14 py-2 text-xs font-semibold text-gray-900 dark:text-white outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 shadow-2xs">
                                                
                                                <div class="absolute right-2 flex items-center gap-1">
                                                    <button type="button" 
                                                            x-show="selected && selected.length > 0" 
                                                            @click.stop="clearSelection()" 
                                                            class="p-0.5 text-gray-400 hover:text-rose-500 rounded text-xs transition cursor-pointer"
                                                            title="Hapus pilihan">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                    <button type="button" 
                                                            @click.stop="toggle()" 
                                                            class="p-1 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 rounded transition cursor-pointer"
                                                            title="Buka daftar project">
                                                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180 text-amber-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Teleported Floating Dropdown Menu -->
                                            <div wire:ignore>
                                                <template x-teleport="body">
                                                    <div x-show="open" 
                                                         x-cloak 
                                                         x-transition:enter="transition ease-out duration-150"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-100"
                                                         x-transition:leave-start="opacity-100 scale-100"
                                                         x-transition:leave-end="opacity-0 scale-95"
                                                         @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                         :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                         class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                                        
                                                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                            <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                                <span>Daftar / Rekomendasi Project</span>
                                                            </span>
                                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-mono" x-text="filteredProjects.length + ' Project'"></span>
                                                        </div>

                                                        <div x-show="selected && selected.trim().length > 0 && !hasExactMatch">
                                                            <div class="px-2.5 py-1.5 text-[11px] text-amber-900 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/50 rounded-xl border border-amber-200 dark:border-amber-800/60 flex items-center justify-between gap-2">
                                                                <div class="flex items-center gap-1.5 min-w-0">
                                                                    <span class="text-amber-600 font-bold text-xs">✓</span>
                                                                    <span class="truncate">Gunakan: <strong class="underline" x-text="selected.trim()"></strong></span>
                                                                </div>
                                                                <span class="text-[9px] font-bold uppercase bg-amber-600 text-white px-1.5 py-0.5 rounded shrink-0">Custom</span>
                                                            </div>
                                                        </div>

                                                        <div class="max-h-56 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                            <template x-for="p in filteredProjects" :key="p.name">
                                                                <div @click="selectProject(p)"
                                                                     class="p-2 rounded-xl hover:bg-amber-50/90 dark:hover:bg-amber-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-amber-200 dark:hover:border-amber-800"
                                                                     :class="selected === p.name ? 'bg-amber-50 dark:bg-amber-950/60 border-amber-300 dark:border-amber-700' : ''">
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="p.name"></span>
                                                                        </div>
                                                                        <div class="flex items-center gap-2 mt-0.5 text-[10px] text-gray-400">
                                                                            <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.2 rounded" x-show="p.code && p.code !== '-'" x-text="p.code"></span>
                                                                            <span x-show="p.client" class="truncate" x-text="'• ' + p.client"></span>
                                                                            <span x-show="p.status && p.status !== 'Historical'" class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase"
                                                                                  :class="p.status === 'Completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'"
                                                                                  x-text="p.status"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div x-show="selected === p.name" class="text-amber-600 dark:text-amber-400 font-extrabold text-sm shrink-0">
                                                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            <div x-show="filteredProjects.length === 0" class="py-3 px-2 text-center text-xs text-gray-400">
                                                                <span x-show="selected && selected.trim().length > 0">Tidak ada project yang cocok di master. Teks Anda akan disimpan sebagai keperluan / project baru.</span>
                                                                <span x-show="!selected || selected.trim().length === 0">Belum ada daftar project terdaftar. Silakan ketik nama project / keperluan langsung di kolom.</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('directProjectId') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        @error('directProjectName') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- PIC Penerima Lapangan Autocomplete -->
                                    <div class="sm:col-span-1">
                                        <label class="block text-[11px] font-bold text-gray-800 dark:text-gray-200 mb-1">
                                            PIC Penerima Lapangan <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative" 
                                             x-data="picAutocomplete(@js($directPic ?? ''), 'directPic', false)"
                                             @scroll.window="if(open) updatePosition()"
                                             @resize.window="if(open) updatePosition()">
                                            
                                            <div x-ref="triggerWrap" class="relative flex items-center">
                                                <input type="text" 
                                                       x-ref="picInput"
                                                       wire:model="directPic" 
                                                       x-model="value"
                                                       @input="onInput($event)"
                                                       @focus="onFocus()"
                                                       @keydown="onKeyDown($event)"
                                                       placeholder="Nama personil / supervisor proyek..." 
                                                       class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 pl-3 pr-8 py-2 text-xs text-gray-900 dark:text-white font-medium outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 shadow-2xs">
                                                
                                                <div class="absolute right-2 flex items-center gap-0.5">
                                                    <button type="button" 
                                                            x-show="value && value.length > 0" 
                                                            @click.stop="clearPic()" 
                                                            class="p-1 text-gray-400 hover:text-rose-500 rounded transition cursor-pointer"
                                                            title="Hapus nama PIC">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Floating Recommendations Dropdown -->
                                            <div wire:ignore>
                                                <template x-teleport="body">
                                                    <div x-show="open" 
                                                         x-cloak 
                                                         x-transition:enter="transition ease-out duration-150"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-100"
                                                         x-transition:leave-start="opacity-100 scale-100"
                                                         x-transition:leave-end="opacity-0 scale-95"
                                                         @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                         :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                         class="rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-900 space-y-1.5">
                                                        
                                                        <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                            <span class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 text-[11px]">
                                                                <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                                Saran PIC
                                                            </span>
                                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-mono" x-text="filteredSuggestions.length + ' Pilihan'"></span>
                                                        </div>

                                                        <div class="max-h-48 overflow-y-auto space-y-0.5 pr-0.5 custom-scrollbar">
                                                            <template x-for="(name, sIndex) in filteredSuggestions" :key="name">
                                                                <div @click="selectPic(name)"
                                                                     class="px-2 py-1.5 rounded-lg hover:bg-amber-50/90 dark:hover:bg-amber-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent"
                                                                     :class="focusedIndex === sIndex ? 'bg-amber-100 dark:bg-amber-900/40 border-amber-300' : ''">
                                                                    <span class="text-xs text-gray-800 dark:text-gray-200 truncate" x-text="name"></span>
                                                                    <span class="text-amber-600 dark:text-amber-400 font-bold text-xs" x-show="value.toLowerCase() === name.toLowerCase()">✓</span>
                                                                </div>
                                                            </template>

                                                            <div x-show="filteredSuggestions.length === 0" class="py-2 text-center text-[11px] text-gray-400">
                                                                Tidak ada saran nama PIC yang cocok
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        @error('directPic') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="sm:col-span-2 lg:col-span-1">
                                        <label class="block text-[11px] font-bold text-gray-800 dark:text-gray-200 mb-1">
                                            Catatan Pengeluaran Proyek
                                        </label>
                                        <input type="text" wire:model="directNotes" placeholder="Misal: Kebutuhan mendesak instalasi..." class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    </div>

                                    <div class="sm:col-span-2 lg:col-span-3">
                                        <div class="p-2.5 rounded-lg bg-amber-100/70 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                            <span><strong>Opsi A Aktif:</strong> Material masuk otomatis disetujui (Bebas Antrean QC) dan seketika diterbitkan transaksi Material Keluar ke rekap proyek tujuan. Stok netto gudang tidak berubah.</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Bagian 2: Daftar Barang (Multi-Item Repeater) -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between pb-1 border-b border-gray-200/70 dark:border-gray-700/70">
                                <div class="flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-gray-200">
                                    <span class="p-1 rounded-md bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </span>
                                    <span>Daftar Rincian Barang yang Diterima</span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        {{ count($inboundItems) }} Barang
                                    </span>
                                </div>

                                <button type="button" wire:click="addInboundItemRow" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/40 dark:text-brand-300 dark:hover:bg-brand-900/60 border border-brand-200/60 dark:border-brand-800 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Barang
                                </button>
                            </div>

                            <!-- List Item Rows -->
                            <div class="space-y-3 max-h-[44vh] overflow-y-auto pr-1">
                                @foreach ($inboundItems as $index => $row)
                                    <div class="relative p-3.5 rounded-xl border border-gray-200/90 dark:border-gray-700/80 bg-white dark:bg-gray-800/80 shadow-xs hover:border-brand-300 transition" wire:key="inbound-row-{{ $index }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[11px] font-black shrink-0 mt-1">
                                                {{ $index + 1 }}
                                            </span>

                                            <div class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-3">
                                                <!-- Pilih Barang (col-6) -->
                                                <div class="md:col-span-6">
                                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                                        Pilih Barang <span class="text-rose-500">*</span>
                                                    </label>
                                                    
                                                    <div class="relative" 
                                                         x-data="itemSearchCombobox(@js($row['item_id'] ?? ''), 'inboundItems.{{ $index }}.item_id', {{ $index }})"
                                                         @scroll.window="if(open) updatePosition()"
                                                         @resize.window="if(open) updatePosition()">
                                                        
                                                        <input type="hidden" 
                                                               x-ref="hiddenInput"
                                                               wire:model.live="inboundItems.{{ $index }}.item_id" 
                                                               :value="selectedId">

                                                        <div x-ref="triggerWrap" 
                                                             @click="toggle()"
                                                             style="min-height: 38px;"
                                                             class="relative flex items-center justify-between w-full min-h-[38px] px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 cursor-pointer hover:border-brand-400 focus-within:border-brand-500 focus-within:bg-white transition shadow-2xs">
                                                            
                                                            <div class="flex-1 min-w-0 pr-2">
                                                                <template x-if="selectedItem">
                                                                    <span class="font-bold text-xs text-gray-900 dark:text-white truncate block" :title="selectedItem.name" x-text="selectedItem.name"></span>
                                                                </template>
                                                                <template x-if="!selectedItem">
                                                                    <span class="text-gray-400 dark:text-gray-500 text-xs truncate block">Cari / Pilih Barang...</span>
                                                                </template>
                                                            </div>

                                                            <div class="flex items-center gap-1 shrink-0">
                                                                <button type="button" 
                                                                        x-show="selectedId" 
                                                                        @click.stop="clearSelection()" 
                                                                        class="p-1 text-gray-400 hover:text-rose-500 rounded text-xs transition cursor-pointer"
                                                                        title="Hapus pilihan">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                                <button type="button" 
                                                                        @click.stop="toggle()" 
                                                                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition cursor-pointer"
                                                                        title="Buka daftar barang">
                                                                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Teleported Floating Dropdown Menu -->
                                                        <div wire:ignore>
                                                        <template x-teleport="body">
                                                            <div x-show="open" 
                                                                 x-cloak 
                                                                 x-transition:enter="transition ease-out duration-150"
                                                                 x-transition:enter-start="opacity-0 scale-95"
                                                                 x-transition:enter-end="opacity-100 scale-100"
                                                                 x-transition:leave="transition ease-in duration-100"
                                                                 x-transition:leave-start="opacity-100 scale-100"
                                                                 x-transition:leave-end="opacity-0 scale-95"
                                                                 @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                                                 :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                                                 class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                                                
                                                                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                                                    <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Pilih Barang
                                                                    </span>
                                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-mono" x-text="filteredItems.length + ' Barang'"></span>
                                                                </div>

                                                                <div class="relative">
                                                                    <input type="text" 
                                                                           x-ref="searchField" 
                                                                           x-model="search" 
                                                                           @keydown.enter.prevent="if (filteredItems.length > 0) { selectItem(filteredItems[0]); }"
                                                                           @keydown.escape="close()"
                                                                           placeholder="Ketik nama, kode, atau kategori barang..." 
                                                                           class="w-full pl-3 pr-7 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-500 focus:bg-white dark:focus:bg-gray-800 shadow-xs">
                                                                    <button x-show="search.length > 0" @click="search = ''" type="button" class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    </button>
                                                                </div>

                                                                <div class="max-h-60 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                                                    <template x-for="item in filteredItems" :key="item.id">
                                                                        <div @click="selectItem(item)"
                                                                             class="p-2 rounded-xl hover:bg-brand-50/90 dark:hover:bg-brand-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-brand-200 dark:hover:border-brand-800"
                                                                             :class="String(selectedId) === String(item.id) ? 'bg-brand-50 dark:bg-brand-950/60 border-brand-300 dark:border-brand-700' : ''">
                                                                            <div class="min-w-0 flex-1">
                                                                                <div class="flex items-center gap-1.5">
                                                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0"
                                                                                          :class="item.category && item.category.toLowerCase().includes('cat') ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : (item.category && item.category.toLowerCase().includes('secondary') ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')"
                                                                                          x-text="item.category"></span>
                                                                                    <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="item.name"></span>
                                                                                </div>
                                                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-2">
                                                                                    <span x-show="item.code" class="font-mono text-gray-400" x-text="'Kode: ' + item.code"></span>
                                                                                    <span x-show="item.code">&bull;</span>
                                                                                    <span>Stok: <strong class="text-gray-700 dark:text-gray-200 font-semibold" x-text="item.stock + ' ' + item.unit"></strong></span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="shrink-0 flex items-center">
                                                                                <span x-show="String(selectedId) === String(item.id)" class="text-brand-600 dark:text-brand-400">
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </template>
                                                                    <div x-show="filteredItems.length === 0" class="py-6 text-center text-xs text-gray-400">
                                                                        Tidak ada barang yang cocok dengan pencarian "<span x-text="search" class="font-bold"></span>"
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        </div>
                                                    </div>
                                                    @error("inboundItems.{$index}.item_id") <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror

                                                    @if(!empty($row['is_consumable']))
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                            <span>Consumable (Bebas QC langsung masuk stok master)</span>
                                                        </span>
                                                    @elseif(!empty($row['item_id']) && isset($row['current_stock']))
                                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 block">
                                                            Stok master saat ini: <strong class="text-gray-700 dark:text-gray-200">{{ format_quantity($row['current_stock']) }} {{ $row['unit'] }}</strong>
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Jumlah Fisik Diterima (col-3) -->
                                                <div class="md:col-span-3">
                                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                                        Jumlah Diterima <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="number" wire:model="inboundItems.{{ $index }}.quantity" min="0.001" step="any" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                    @error("inboundItems.{$index}.quantity") <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                                </div>

                                                <!-- Satuan (col-3) -->
                                                <div class="md:col-span-3">
                                                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">
                                                        Satuan
                                                    </label>
                                                    <input type="text" wire:model="inboundItems.{{ $index }}.unit" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                </div>
                                            </div>

                                            <!-- Tombol Hapus Baris -->
                                            <div class="shrink-0 pt-6">
                                                @if (count($inboundItems) > 1)
                                                    <button type="button" wire:click="removeInboundItemRow({{ $index }})" title="Hapus Baris Barang" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                @else
                                                    <div class="w-8"></div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Parameter Cat & Kimia (Shelf-Life Control) -->
                                        @if(!empty($row['is_cat']))
                                            <div class="mt-3 pt-3 border-t border-purple-100 dark:border-purple-900/60 rounded-lg p-2.5 bg-purple-50/70 dark:bg-purple-950/30 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-purple-900 dark:text-purple-300 mb-1">
                                                        Tgl Pembuatan (Mfg Date)
                                                    </label>
                                                    <input type="date" wire:model="inboundItems.{{ $index }}.manufacturing_date" class="w-full rounded-lg border border-purple-200 dark:border-purple-800 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-purple-900 dark:text-purple-300 mb-1">
                                                        Tgl Kadaluarsa (Expiry Date)
                                                    </label>
                                                    <input type="date" wire:model="inboundItems.{{ $index }}.expiry_date" class="w-full rounded-lg border border-purple-200 dark:border-purple-800 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Tombol Tambah Baris Bawah -->
                            <div class="pt-1">
                                <button type="button" wire:click="addInboundItemRow" class="w-full py-2.5 border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-brand-500 dark:hover:border-brand-500 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 flex items-center justify-center gap-2 transition cursor-pointer bg-gray-50/50 hover:bg-brand-50/30 dark:bg-gray-800/40">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Baris Barang Masuk Lainnya
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                        @if (!$isEditMode)
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Total: <strong class="text-gray-900 dark:text-white">{{ count($inboundItems) }}</strong> jenis barang dalam tanda terima ini
                            </span>
                        @else
                            <div></div>
                        @endif

                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="btn-save-theme px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-sm cursor-pointer">
                                @if ($isEditMode)
                                    Simpan Perubahan
                                @elseif ($directToProject)
                                    Simpan & Alokasikan ke Proyek ({{ count($inboundItems) }} Barang)
                                @else
                                    Simpan Penerimaan Material ({{ count($inboundItems) }} Barang)
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: INSPEKSI & APPROVAL QC -->
    @if ($isQcModalOpen && $qcSelectedRecord)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Form Inspeksi & Persetujuan Quality Control (QC)</span>
                    </h3>
                    <button type="button" wire:click="closeQcModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <!-- Detail Informasi Barang -->
                    <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nama Material:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $qcSelectedRecord->item->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kuantitas Fisik:</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">+{{ format_quantity($qcSelectedRecord->quantity) }} {{ $qcSelectedRecord->unit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Supplier / Vendor:</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $qcSelectedRecord->supplier }}</span>
                        </div>
                        @if($qcSelectedRecord->delivery_order_number)
                            <div class="flex justify-between">
                                <span class="text-gray-500">No. Surat Jalan / DO:</span>
                                <span class="font-mono font-bold text-sky-700 dark:text-sky-300">{{ $qcSelectedRecord->delivery_order_number }}</span>
                            </div>
                        @endif
                        @if($qcSelectedRecord->manufacturing_date)
                            <div class="flex justify-between pt-1 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-purple-600 font-bold">Tanggal Pembuatan (Cat):</span>
                                <span class="font-mono font-bold text-purple-700 dark:text-purple-300">{{ format_date($qcSelectedRecord->manufacturing_date) }}</span>
                            </div>
                        @endif
                        @if(!empty(trim($qcSelectedRecord->notes ?? '')))
                            <div class="flex items-start justify-between gap-2 pt-1 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-gray-500 shrink-0">Catatan Penerimaan / SJ:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 text-right">{{ $qcSelectedRecord->notes }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Form Keputusan QC -->
                    <form wire:submit.prevent="submitQc" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Keputusan Inspeksi QC <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $qcDecision === 'approved' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300' : 'border-gray-200 dark:border-gray-700' }}">
                                    <input type="radio" wire:model="qcDecision" value="approved" class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="font-bold text-xs flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>SETUJUI (OK)</span></div>
                                        <div class="text-[10px] text-gray-500">Barang memenuhi spek. Tambah stok master aktif.</div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition {{ $qcDecision === 'rejected' ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300' : 'border-gray-200 dark:border-gray-700' }}">
                                    <input type="radio" wire:model="qcDecision" value="rejected" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <div class="font-bold text-xs flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span>REJECT (DITOLAK)</span></div>
                                        <div class="text-[10px] text-gray-500">Cacat / tidak sesuai. Jangan tambah stok.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Hasil Inspeksi / Alasan Penolakan <span class="text-rose-500">*</span></label>
                            <textarea wire:model="qcNotes" rows="3" required placeholder="Contoh: Kondisi fisik bagus, segel utuh, spesifikasi sesuai..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" wire:click="closeQcModal" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white rounded-xl transition shadow-sm cursor-pointer {{ $qcDecision === 'approved' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }}">
                                {{ $qcDecision === 'approved' ? 'Setujui (OK) & Aktifkan Stok' : 'Tolak Penerimaan (Reject)' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: CETAK CATATAN TANDA TERIMA BARANG FISIK -->
    @if ($isPrintReceiptModalOpen && $receiptRecord)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Catatan Tanda Terima Barang Fisik
                    </h3>
                    <button type="button" wire:click="closePrintReceiptModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Printable Document Area -->
                <div id="printableReceiptArea" class="mt-4 p-6 border-2 border-gray-800 bg-white text-gray-900 font-sans space-y-4 rounded-xl">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b-2 border-gray-800 pb-3">
                        <div>
                            <h2 class="text-lg font-black tracking-wider uppercase">PT MUDALAYA ENERGY INDONESIA</h2>
                            <p class="text-[11px] text-gray-600">WAREHOUSE MANAGEMENT SYSTEM (WMS) - LOGISTIK & PERGUDANGAN</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 bg-gray-900 text-white font-mono text-xs font-bold rounded">
                                BUKTI TERIMA FISIK
                            </span>
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="space-y-1">
                            <div><span class="text-gray-500">Tanggal Penerimaan:</span> <strong>{{ format_date($receiptRecord->date) }}</strong></div>
                            <div><span class="text-gray-500">Supplier / Vendor:</span> <strong>{{ $receiptRecord->supplier }}</strong></div>
                        </div>
                        <div class="space-y-1 text-right">
                            <div><span class="text-gray-500">Petugas Gudang:</span> <strong>{{ $receiptRecord->received_by }}</strong></div>
                            <div><span class="text-gray-500">Status QC:</span> 
                                <strong>{{ $receiptRecord->qc_status === 'approved' ? 'OK (APPROVED)' : ($receiptRecord->qc_status === 'rejected' ? 'REJECTED' : 'MENUNGGU INSPEKSI QC') }}</strong>
                            </div>
                            @if($receiptRecord->manufacturing_date)
                                <div><span class="text-gray-500">Tanggal Pembuatan:</span> <strong>{{ format_date($receiptRecord->manufacturing_date) }}</strong></div>
                            @endif
                        </div>
                    </div>

                    <!-- Item Table -->
                    <table class="w-full text-left text-xs border border-gray-800">
                        <thead class="bg-gray-100 border-b border-gray-800">
                            <tr>
                                <th class="p-2 border-r border-gray-800">No</th>
                                <th class="p-2 border-r border-gray-800">Deskripsi Barang</th>
                                <th class="p-2 border-r border-gray-800 text-center">Kategori</th>
                                <th class="p-2 text-center">Jumlah Fisik Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-2 border-r border-gray-800 text-center font-bold">1</td>
                                <td class="p-2 border-r border-gray-800 font-bold">
                                    {{ $receiptRecord->item->name ?? '-' }}
                                    @if($receiptRecord->wholesale_qty && $receiptRecord->wholesale_unit)
                                        <div class="text-[10px] text-gray-500 font-normal">Input Grosir: {{ format_quantity($receiptRecord->wholesale_qty) }} {{ $receiptRecord->wholesale_unit }}</div>
                                    @endif
                                </td>
                                <td class="p-2 border-r border-gray-800 text-center">{{ $receiptRecord->item->category->name ?? 'Consumable' }}</td>
                                <td class="p-2 text-center font-black font-mono text-sm bg-gray-50">
                                    {{ format_quantity($receiptRecord->quantity) }} {{ $receiptRecord->unit }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Catatan Fisik / QC -->
                    <div class="p-2 bg-gray-50 border border-gray-300 rounded text-xs">
                        <strong>Catatan Kondisi / Kelengkapan:</strong>
                        <p class="text-gray-600 mt-0.5">{{ $receiptRecord->notes ?: ($receiptRecord->qc_notes ?: 'Barang fisik telah diterima dalam keadaan baik dan utuh.') }}</p>
                    </div>

                    <!-- Signatures -->
                    <div class="grid grid-cols-2 gap-8 pt-6 text-center text-xs">
                        <div>
                            <p class="text-gray-600">Diserahkan Oleh (Driver / Vendor):</p>
                            <div class="h-14"></div>
                            <p class="font-bold border-t border-gray-800 pt-1">( {{ $receiptRecord->supplier }} )</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Diterima Oleh (Petugas Gudang):</p>
                            <div class="h-14"></div>
                            <p class="font-bold border-t border-gray-800 pt-1">( {{ $receiptRecord->received_by }} )</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800 mt-4">
                    <button type="button" wire:click="closePrintReceiptModal" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                    <button type="button" onclick="window.print()" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Tanda Terima Sekarang
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 4: CATAT / EDIT MATERIAL RUSAK / REJECT -->
    @if ($isRejectModalOpen)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn"
             x-init="window.warehouseItemsList = @js($itemsJsonList ?? []);">
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-950/50 text-sm">
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        {{ $isRejectEditMode ? 'Edit Laporan Material Rusak / Reject' : 'Catat Material Rusak / Reject (Report/Reject Material)' }}
                    </h3>
                    <button type="button" wire:click="closeRejectModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveReject" class="mt-4 space-y-4">
                    <!-- Tanggal & Nomor PO -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Pemeriksaan Fisik <span class="text-rose-500">*</span></label>
                            <input type="date" wire:model="rejectDate" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('rejectDate') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor PO (Opsional)</label>
                            <input type="text" wire:model="rejectPoNumber" placeholder="PO-2026-..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Pilih Master Barang & Supplier -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pilih Master Barang <span class="text-rose-500">*</span></label>
                            
                            <div class="relative" 
                                 x-data="itemSearchCombobox(@js($rejectItemId ?? ''), 'rejectItemId')"
                                 @scroll.window="if(open) updatePosition()"
                                 @resize.window="if(open) updatePosition()">
                                
                                <input type="hidden" 
                                       x-ref="hiddenInput"
                                       wire:model.live="rejectItemId" 
                                       :value="selectedId">

                                <div x-ref="triggerWrap" 
                                     @click="toggle()"
                                     style="min-height: 38px;"
                                     class="relative flex items-center justify-between w-full min-h-[38px] px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 cursor-pointer hover:border-rose-400 focus-within:border-rose-500 focus-within:bg-white transition shadow-2xs">
                                    
                                    <div class="flex-1 min-w-0 pr-2">
                                        <template x-if="selectedItem">
                                            <span class="font-bold text-xs text-gray-900 dark:text-white truncate block" :title="selectedItem.name" x-text="selectedItem.name"></span>
                                        </template>
                                        <template x-if="!selectedItem">
                                            <span class="text-gray-400 dark:text-gray-500 text-xs truncate block">Cari / Pilih Master Barang...</span>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" 
                                                x-show="selectedId" 
                                                @click.stop="clearSelection()" 
                                                class="p-1 text-gray-400 hover:text-rose-500 rounded text-xs transition cursor-pointer"
                                                title="Hapus pilihan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        <button type="button" 
                                                @click.stop="toggle()" 
                                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded transition cursor-pointer"
                                                title="Buka daftar barang">
                                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180 text-rose-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Teleported Floating Dropdown Menu -->
                                <div wire:ignore>
                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-cloak 
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-100"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         @click.outside="if (!getTriggerEl()?.contains($event.target)) close()"
                                         :style="'position: fixed; top: ' + fixedTop + 'px; left: ' + fixedLeft + 'px; width: ' + fixedWidth + 'px; z-index: 99999999;'"
                                         class="rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl dark:border-gray-700 dark:bg-gray-900 space-y-2">
                                        
                                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800 text-xs">
                                            <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Pilih Master Barang
                                            </span>
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-mono" x-text="filteredItems.length + ' Barang'"></span>
                                        </div>

                                        <div class="relative">
                                            <input type="text" 
                                                   x-ref="searchField" 
                                                   x-model="search" 
                                                   @keydown.enter.prevent="if (filteredItems.length > 0) { selectItem(filteredItems[0]); }"
                                                   @keydown.escape="close()"
                                                   placeholder="Ketik nama, kode, atau kategori barang..." 
                                                   class="w-full pl-3 pr-7 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-rose-500 focus:bg-white dark:focus:bg-gray-800 shadow-xs">
                                            <button x-show="search.length > 0" @click="search = ''" type="button" class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>

                                        <div class="max-h-60 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                            <template x-for="item in filteredItems" :key="item.id">
                                                <div @click="selectItem(item)"
                                                     class="p-2 rounded-xl hover:bg-rose-50/90 dark:hover:bg-rose-950/50 cursor-pointer transition flex items-center justify-between gap-2 border border-transparent hover:border-rose-200 dark:hover:border-rose-800"
                                                     :class="String(selectedId) === String(item.id) ? 'bg-rose-50 dark:bg-rose-950/60 border-rose-300 dark:border-rose-700' : ''">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase shrink-0"
                                                                  :class="item.category && item.category.toLowerCase().includes('cat') ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : (item.category && item.category.toLowerCase().includes('secondary') ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')"
                                                                  x-text="item.category"></span>
                                                            <span class="font-bold text-xs text-gray-900 dark:text-white truncate" x-text="item.name"></span>
                                                        </div>
                                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-2">
                                                            <span x-show="item.code" class="font-mono text-gray-400" x-text="'Kode: ' + item.code"></span>
                                                            <span x-show="item.code">&bull;</span>
                                                            <span>Stok: <strong class="text-gray-700 dark:text-gray-200 font-semibold" x-text="item.stock + ' ' + item.unit"></strong></span>
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0 flex items-center">
                                                        <span x-show="String(selectedId) === String(item.id)" class="text-rose-600 dark:text-rose-400">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="filteredItems.length === 0" class="py-6 text-center text-xs text-gray-400">
                                                Tidak ada barang yang cocok dengan pencarian "<span x-text="search" class="font-bold"></span>"
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                </div>
                            </div>

                            @error('rejectItemId') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Supplier / Vendor <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="rejectSupplierName" placeholder="Nama Supplier penyedia material" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('rejectSupplierName') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Jumlah Rusak & Satuan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kuantitas Rusak <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="rejectQuantity" min="0.001" step="any" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('rejectQuantity') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan</label>
                            <input type="text" wire:model="rejectUnit" placeholder="PCS / SET / LTR" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Jenis Kerusakan & Rencana Disposisi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jenis Kerusakan / Cacat <span class="text-rose-500">*</span></label>
                            <select wire:model="rejectDamageTypeChoice" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="Cacat Fisik / Pecah">Cacat Fisik / Pecah</option>
                                <option value="Tidak Sesuai Spek">Tidak Sesuai Spek</option>
                                <option value="Kadaluarsa / Expired">Kadaluarsa / Expired</option>
                                <option value="Kemasan Bocor / Rusak">Kemasan Bocor / Rusak</option>
                                <option value="Karat / Korosi">Karat / Korosi</option>
                                <option value="Gagal Uji Fungsi">Gagal Uji Fungsi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Keputusan Disposisi Awal <span class="text-rose-500">*</span></label>
                            <select wire:model="rejectDispositionChoice" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="karantina">Karantina Gudang</option>
                                <option value="retur_supplier">Retur ke Supplier / Vendor</option>
                                <option value="replacement">Klaim Garansi / Ganti Baru</option>
                                <option value="scrap">Pemusnahan / Scrap</option>
                            </select>
                        </div>
                    </div>

                    <!-- Uraian Cacat / Kerusakan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Uraian Temuan Kerusakan / Cacat Fisik <span class="text-rose-500">*</span></label>
                        <textarea wire:model="rejectDescription" rows="3" placeholder="Jelaskan detail bagian cacat, keretakan, dimensi selisih, atau kebocoran..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        @error('rejectDescription') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" wire:click="closeRejectModal" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm cursor-pointer">
                            {{ $isRejectEditMode ? 'Simpan Perubahan Laporan' : 'Simpan Laporan Material Reject' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 5: UPDATE DISPOSISI & PENYELESAIAN RETUR -->
    @if ($isDispositionModalOpen && $dispositionRecord)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Update Disposisi Material Reject</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">No. Dokumen: {{ $dispositionRecord->reference_number }}</p>
                    </div>
                    <button type="button" wire:click="closeDispositionModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Material:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $dispositionRecord->item->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jumlah Ditolak:</span>
                        <span class="font-mono font-bold text-rose-600 dark:text-rose-400">{{ format_quantity($dispositionRecord->quantity) }} {{ $dispositionRecord->unit }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Supplier:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $dispositionRecord->supplier }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jenis Cacat:</span>
                        <span class="font-semibold text-amber-700 dark:text-amber-400">{{ $dispositionRecord->damage_type }}</span>
                    </div>
                </div>

                <form wire:submit.prevent="updateDisposition" class="mt-4 space-y-4">
                    <!-- Tindakan Disposisi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tindakan / Keputusan Disposisi <span class="text-rose-500">*</span></label>
                        <select wire:model="dispositionAction" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="karantina">Karantina Gudang</option>
                            <option value="retur_supplier">Retur ke Supplier / Vendor</option>
                            <option value="replacement">Klaim Garansi / Penggantian Baru</option>
                            <option value="scrap">Pemusnahan / Scrap</option>
                        </select>
                    </div>

                    <!-- Status Penyelesaian -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Progres Penyelesaian <span class="text-rose-500">*</span></label>
                        <select wire:model="dispositionStatus" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="pending">Menunggu Tindak Lanjut</option>
                            <option value="in_progress">Dalam Proses Retur/Klaim</option>
                            <option value="completed">Selesai (Tuntas / Barang Diganti)</option>
                        </select>
                    </div>

                    <!-- Catatan Penyelesaian / No Resi / Bukti Retur -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Penyelesaian & Nomor Resi / Nota Kredit</label>
                        <textarea wire:model="resolutionNotes" rows="3" placeholder="Contoh: Barang telah diserahkan kembali ke driver CV Jaya pada 15/09 dengan No Resi RET-092. Barang pengganti dijanjikan tiba 20/09..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" wire:click="closeDispositionModal" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-sm cursor-pointer">
                            Simpan Status Disposisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    
    <!-- MODAL 6: RETUR SISA POTONGAN RAW MATERIAL (OFF-CUT RETURN) -->
    @if ($isOffCutModalOpen)
        <div class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn"
             role="dialog" aria-modal="true">
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 text-base">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758M12 12l2.879-2.879a3 3 0 10-4.242-4.242L7.758 10.758"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                                Retur Sisa Potongan (Off-Cut Return)
                            </h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Catat sisa potongan raw material layak pakai dari workshop ke Storage 2
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeOffCutModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitOffCutReturn" class="mt-4 space-y-4">
                    <!-- Alert Petunjuk -->
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 text-[11px] text-amber-900 dark:text-amber-200 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <div>
                            <strong>Pemisahan Stok Bersih:</strong> Material sisa potongan otomatis masuk ke kategori <strong>Raw Material - Off Cut (STORAGE 2)</strong> dengan kode unik terpisah, sehingga spesifikasi material utuh tetap bersih dan akurat.
                        </div>
                    </div>

                    <!-- Material Asal yang Dipotong -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Material Asal yang Dipotong (Parent Item) <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="offCutParentItemId" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="">-- Pilih Raw Material Asal di Storage 2 --</option>
                            @foreach($rawMaterialParents as $pMat)
                                <option value="{{ $pMat->id }}">
                                    {{ $pMat->code_item ? '[' . $pMat->code_item . '] ' : '' }}{{ $pMat->name }} (Stok Induk: {{ format_quantity($pMat->stock) }} {{ $pMat->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('offCutParentItemId') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Dimensi / Ukuran Sisa & Satuan -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Dimensi / Ukuran Sisa Potongan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="offCutDimension" placeholder="Contoh: Panjang 2.4 m atau 1200 x 850 mm" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('offCutDimension') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Satuan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="offCutUnit" placeholder="Pcs / Btg / Lbr" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('offCutUnit') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Jumlah / Qty Sisa & Tanggal Retur -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Jumlah Potongan (Qty) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" wire:model="offCutQuantity" placeholder="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('offCutQuantity') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Tanggal Pengembalian
                            </label>
                            <input type="date" wire:model="date" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Proyek Asal Pemotongan & Heat Number -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Proyek Asal Pemotongan
                            </label>
                            <input type="text" wire:model="offCutOriginProjectName" list="offcut-projects-list" placeholder="Pilih atau ketik nama proyek..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <datalist id="offcut-projects-list">
                                @foreach($projects as $prj)
                                    <option value="{{ $prj->name }}">{{ $prj->name }} ({{ $prj->code ?? '-' }})</option>
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Heat No / No. Sertifikat
                            </label>
                            <input type="text" wire:model="offCutHeatNumber" placeholder="Contoh: HT-9912-B (Opsional)" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                        </div>
                    </div>

                    <!-- Lokasi Penyimpanan di Gudang 2 -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Lokasi Penyimpanan di Storage 2
                        </label>
                        <input type="text" wire:model="offCutLocation" placeholder="STORAGE 2 (Rak Off-Cut / Area Sisa)" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Catatan Tambahan
                        </label>
                        <textarea wire:model="offCutNotes" rows="2" placeholder="Kondisi sisa, tanda spidol/marker di fisik, atau keterangan pengerjaan..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-800 outline-none focus:border-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" wire:click="closeOffCutModal" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition shadow-sm cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan ke Stok Off-Cut</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    <!-- ========================================================================= -->
    <!-- MODAL: TERIMA BARANG FISIK DARI PURCHASE ORDER (PO) & HITUNG FISIK GUDANG -->
    <!-- ========================================================================= -->
    @if ($isReceivePoModalOpen)
        <div class="fixed inset-0 z-[999999] overflow-y-auto bg-gray-950/80 backdrop-blur-xs animate-fadeIn"
             role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 md:p-6 text-center">
                <div class="relative z-10 inline-block w-full max-w-4xl lg:max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 my-auto max-h-[90vh] flex flex-col">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800 bg-purple-50/40 dark:bg-purple-950/20 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path><rect x="9" y="3" width="6" height="4" rx="1"></rect><path d="M9 12h6"></path><path d="M9 16h6"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                Penerimaan Barang Fisik dari Purchase Order (PO)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    Langsung ACC ke Stok
                                </span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Pilih nomor PO yang datang, input Surat Jalan vendor, lalu sesuaikan jumlah fisik yang dihitung untuk langsung menambah stok gudang.
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeReceivePoModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body (Scrollable with min-h-0) -->
                <div class="p-4 sm:p-6 overflow-y-auto space-y-4 custom-scrollbar flex-1 min-h-0">
                    
                    <!-- Section 1: Pilih PO & Surat Jalan Vendor -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-4 rounded-xl bg-gray-50/80 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800">
                        <!-- Dropdown Purchase Order -->
                        <div class="sm:col-span-7">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Pilih Purchase Order (PO) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                wire:model.live="selectedPoId"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-900 focus:border-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                            >
                                <option value="">-- Pilih Purchase Order Aktif --</option>
                                @foreach($openPurchaseOrders as $opo)
                                    <option value="{{ $opo->id }}">
                                        {{ $opo->po_number }} - {{ $opo->supplier }} ({{ $opo->project ? 'Proyek: ' . $opo->project->name : 'Persediaan Umum' }}) [{{ $opo->status === 'partial' ? 'Parsial' : 'Open' }}]
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedPoId') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- No. Surat Jalan / DO Vendor -->
                        <div class="sm:col-span-5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                No. Surat Jalan / DO Vendor <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="poDeliveryOrderNumber"
                                placeholder="Contoh: SJ-2026/XI/0091"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-900 focus:border-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('poDeliveryOrderNumber') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tanggal Terima Fisik -->
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Tanggal Penerimaan Fisik <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="poReceivedDate"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('poReceivedDate') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Petugas Penerima Gudang -->
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Petugas Penerima Gudang <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="poReceivedBy"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('poReceivedBy') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Lokasi Gudang -->
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Lokasi Penyimpanan Fisik <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="poLocation" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="STORAGE 1">STORAGE 1 (Utama / Finishing & Fast Moving)</option>
                                <option value="STORAGE 2">STORAGE 2 (Raw Material & Heavy Steel)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Section 2: Items Table -->
                    @if(empty($selectedPoId))
                        <div class="p-8 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 text-center space-y-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 mx-auto">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Silakan pilih Purchase Order terlebih dahulu</p>
                            <p class="text-[11px] text-gray-500 max-w-sm mx-auto">
                                Rincian item pesanan, sisa yang belum dikirim, dan kolom perhitungan fisik akan otomatis muncul setelah Anda memilih nomor PO di atas.
                            </p>
                        </div>
                    @elseif(empty($poItemsToReceive))
                        <div class="p-6 rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-800/60 dark:bg-emerald-950/30 text-center">
                            <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Semua item pesanan pada Purchase Order ini telah selesai diterima!</p>
                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1">Status PO telah terpenuhi penuh (Completed).</p>
                        </div>
                    @else
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                        Perhitungan Fisik Item Barang Datang
                                    </h4>
                                    <p class="text-[11px] text-gray-500">
                                        Periksa fisik barang yang tiba, sesuaikan angka di kolom <strong>"Qty Fisik Dihitung"</strong> jika pengiriman bertahap (parsial).
                                    </p>
                                </div>
                                <span class="text-[11px] font-semibold text-purple-600 dark:text-purple-400">
                                    {{ count($poItemsToReceive) }} item belum terpenuhi
                                </span>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-400 font-bold uppercase text-[10px]">
                                        <tr>
                                            <th class="px-3 py-2 text-center w-8">Terima</th>
                                            <th class="px-3 py-2">Kode & Nama Barang</th>
                                            <th class="px-3 py-2 text-center">Dipesan</th>
                                            <th class="px-3 py-2 text-center">Sisa Order</th>
                                            <th class="px-3 py-2 text-center min-w-[130px]">Qty Fisik Dihitung</th>
                                            <th class="px-3 py-2 text-center">Satuan</th>
                                            <th class="px-3 py-2">Keterangan / Kondisi Fisik</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($poItemsToReceive as $idx => $itemRow)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 {{ empty($itemRow['selected']) ? 'opacity-50' : '' }}">
                                                <!-- Checkbox -->
                                                <td class="px-3 py-2 text-center">
                                                    <input
                                                        type="checkbox"
                                                        wire:model.live="poItemsToReceive.{{ $idx }}.selected"
                                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer h-4 w-4"
                                                    />
                                                </td>

                                                <!-- Item Code & Name -->
                                                <td class="px-3 py-2">
                                                    <div class="font-bold text-gray-900 dark:text-white">{{ $itemRow['item_name'] }}</div>
                                                    <div class="text-[10px] font-mono text-gray-500">{{ $itemRow['item_code'] }}</div>
                                                </td>

                                                <!-- Quantity Ordered -->
                                                <td class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">
                                                    {{ format_number($itemRow['quantity_ordered']) }}
                                                </td>

                                                <!-- Quantity Remaining -->
                                                <td class="px-3 py-2 text-center font-bold text-amber-700 dark:text-amber-400">
                                                    {{ format_number($itemRow['quantity_remaining']) }}
                                                </td>

                                                <!-- Input Qty Fisik Dihitung -->
                                                <td class="px-3 py-2 text-center">
                                                    <input
                                                        type="number"
                                                        step="any"
                                                        min="0"
                                                        max="{{ $itemRow['quantity_remaining'] }}"
                                                        wire:model="poItemsToReceive.{{ $idx }}.quantity_to_receive"
                                                        class="w-24 text-center rounded-lg border border-purple-300 bg-white dark:border-purple-700 dark:bg-gray-800 font-bold text-xs text-purple-700 dark:text-purple-300 py-1 focus:ring-1 focus:ring-purple-500"
                                                    />
                                                    @error("poItemsToReceive.{$idx}.quantity_to_receive")
                                                        <span class="text-[10px] text-rose-500 block mt-0.5">{{ $message }}</span>
                                                    @enderror
                                                </td>

                                                <!-- Unit -->
                                                <td class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $itemRow['unit'] }}
                                                </td>

                                                <!-- Notes per item -->
                                                <td class="px-3 py-2">
                                                    <input
                                                        type="text"
                                                        wire:model="poItemsToReceive.{{ $idx }}.notes"
                                                        placeholder="Kondisi baik, lot, dll..."
                                                        class="w-full rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 px-2 py-1 text-[11px] text-gray-800 dark:text-gray-200"
                                                    />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Alert Info Otomatis ACC -->
                            <div class="mt-3 p-3 rounded-xl bg-purple-50 border border-purple-200 dark:bg-purple-950/30 dark:border-purple-800/60 flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div class="text-[11px] text-purple-900 dark:text-purple-300">
                                    <strong>ACC Otomatis ke Stok:</strong> Seluruh barang yang dicentang di atas akan langsung berstatus <em>Approved</em> dan otomatis menambah stok master barang gudang tanpa perlu melalui antrean inspeksi QC terpisah.
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-800 flex items-center justify-end gap-3 rounded-b-2xl shrink-0">
                    <button
                        type="button"
                        wire:click="closeReceivePoModal"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border border-gray-300 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    @if(!empty($selectedPoId) && !empty($poItemsToReceive))
                        <button
                            type="button"
                            wire:click="submitReceivePo"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 text-xs font-bold rounded-xl bg-purple-600 hover:bg-purple-700 text-white shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span wire:loading.remove wire:target="submitReceivePo">ACC & Masukkan ke Stok Gudang</span>
                            <span wire:loading wire:target="submitReceivePo">Memproses & Tambah Stok...</span>
                        </button>
                    @endif
                </div>

                </div>
            </div>
        </div>
    @endif


    <script>
        window.warehouseItemsList = @json($itemsJsonList ?? []);
        window.warehouseProjectsList = @json($projectsJsonList ?? []);
        window.warehousePicList = @json($picSuggestionsList ?? []);

        const registerProjectCombobox = () => {
            if (typeof Alpine !== 'undefined' && !Alpine.data('projectSearchCombobox')) {
                Alpine.data('projectSearchCombobox', (initialVal = '', livewireKey = null, idKey = null) => ({
                    open: false,
                    search: '',
                    selected: initialVal || '',
                    selectedId: '',
                    livewireKey: livewireKey,
                    idKey: idKey,
                    projects: window.warehouseProjectsList || [],
                    fixedTop: 0,
                    fixedLeft: 0,
                    fixedWidth: 320,

                    init() {
                        this.projects = window.warehouseProjectsList || [];

                        if (this.selected) {
                            const found = this.projects.find(p => p.name === this.selected);
                            if (found && found.id) {
                                this.selectedId = found.id;
                            }
                        }

                        this.$watch('selected', (val) => {
                            const input = this.getInputEl();
                            if (input && input.value !== val) {
                                input.value = val;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                                this.$wire.set(this.livewireKey, val);
                            }
                        });

                        if (this.idKey && this.$wire) {
                            this.$watch('selectedId', (val) => {
                                const hiddenInput = this.getHiddenIdEl();
                                if (hiddenInput && hiddenInput.value !== String(val)) {
                                    hiddenInput.value = val ? String(val) : '';
                                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                                this.$wire.set(this.idKey, val ? String(val) : '');
                            });
                        }

                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$watch('$wire.' + this.livewireKey, (val) => {
                                this.selected = val || '';
                            });
                        }

                        const onScroll = () => {
                            if (this.open) {
                                this.updatePosition();
                            }
                        };
                        window.addEventListener('scroll', onScroll, { capture: true, passive: true });
                        window.addEventListener('resize', onScroll, { passive: true });
                    },

                    getTriggerEl() {
                        if (this.$refs.triggerWrap && this.$refs.triggerWrap.isConnected) {
                            return this.$refs.triggerWrap;
                        }
                        return this.$el.querySelector('[x-ref="triggerWrap"]') || this.$el;
                    },

                    getInputEl() {
                        if (this.$refs.actualInput && this.$refs.actualInput.isConnected) {
                            return this.$refs.actualInput;
                        }
                        return this.$el.querySelector('input[type="text"]') || this.$refs.actualInput;
                    },

                    getHiddenIdEl() {
                        if (this.$refs.hiddenIdInput && this.$refs.hiddenIdInput.isConnected) {
                            return this.$refs.hiddenIdInput;
                        }
                        return this.$el.querySelector('input[type="hidden"]') || this.$refs.hiddenIdInput;
                    },

                    get filteredProjects() {
                        const list = (this.projects && this.projects.length) ? this.projects : (window.warehouseProjectsList || []);
                        const q = (this.search !== '' ? this.search : (this.selected || '')).toLowerCase().trim();
                        if (!q) {
                            return list;
                        }
                        return list.filter(p => 
                            p.name.toLowerCase().includes(q) || 
                            (p.code && p.code.toLowerCase().includes(q)) ||
                            (p.client && p.client.toLowerCase().includes(q))
                        );
                    },

                    get hasExactMatch() {
                        const q = (this.selected || this.search || '').toLowerCase().trim();
                        if (!q) return true;
                        const list = (this.projects && this.projects.length) ? this.projects : (window.warehouseProjectsList || []);
                        return list.some(p => p.name.toLowerCase() === q);
                    },

                    onInput(e) {
                        this.selected = e.target.value;
                        this.search = e.target.value;
                        this.projects = window.warehouseProjectsList || [];
                        this.open = true;
                        this.updatePosition();

                        const q = (e.target.value || '').toLowerCase().trim();
                        const match = this.projects.find(p => p.name.toLowerCase() === q);
                        this.selectedId = match && match.id ? match.id : '';

                        if (this.idKey && this.$wire) {
                            this.$wire.set(this.idKey, this.selectedId);
                        }
                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$wire.set(this.livewireKey, this.selected);
                        }
                    },

                    onFocus() {
                        this.search = this.selected || '';
                        this.projects = window.warehouseProjectsList || [];
                        this.open = true;
                        this.updatePosition();
                    },

                    toggle() {
                        if (this.open) {
                            this.close();
                        } else {
                            this.openDropdown();
                        }
                    },

                    openDropdown() {
                        this.open = true;
                        this.search = this.selected || '';
                        this.projects = window.warehouseProjectsList || [];
                        this.updatePosition();
                    },

                    updatePosition() {
                        const el = this.getTriggerEl();
                        if (!el || !el.isConnected) return;
                        const rect = el.getBoundingClientRect();
                        if (rect.width === 0 && rect.height === 0) return;
                        
                        this.fixedWidth = Math.max(rect.width, 320);
                        
                        let left = rect.left;
                        if (left + this.fixedWidth > window.innerWidth - 16) {
                            left = window.innerWidth - this.fixedWidth - 16;
                        }
                        this.fixedLeft = Math.max(16, left);

                        const dropdownHeight = 280;
                        const spaceBelow = window.innerHeight - rect.bottom;
                        if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                            this.fixedTop = Math.max(16, rect.top - dropdownHeight - 6);
                        } else {
                            this.fixedTop = Math.min(window.innerHeight - dropdownHeight - 16, rect.bottom + 4);
                        }
                    },

                    selectProject(p) {
                        const name = (typeof p === 'object' && p !== null) ? p.name : p;
                        const id = (typeof p === 'object' && p !== null && p.id) ? p.id : '';
                        this.selected = name;
                        this.selectedId = id;
                        this.search = name;

                        const input = this.getInputEl();
                        if (input) {
                            input.value = name;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        const hiddenInput = this.getHiddenIdEl();
                        if (hiddenInput) {
                            hiddenInput.value = id;
                            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$wire.set(this.livewireKey, name);
                        }
                        if (this.idKey && this.$wire) {
                            this.$wire.set(this.idKey, id);
                        }
                        this.close();
                    },

                    clearSelection() {
                        this.selected = '';
                        this.selectedId = '';
                        this.search = '';
                        const input = this.getInputEl();
                        if (input) {
                            input.value = '';
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        const hiddenInput = this.getHiddenIdEl();
                        if (hiddenInput) {
                            hiddenInput.value = '';
                            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$wire.set(this.livewireKey, '');
                        }
                        if (this.idKey && this.$wire) {
                            this.$wire.set(this.idKey, '');
                        }
                        this.close();
                    },

                    close() {
                        this.open = false;
                    }
                }));
            }
        };

        if (window.Alpine) {
            registerProjectCombobox();
        } else {
            document.addEventListener('alpine:init', registerProjectCombobox);
        }

        const registerPicAutocomplete = () => {
            if (typeof Alpine !== 'undefined' && !Alpine.data('picAutocomplete')) {
                Alpine.data('picAutocomplete', (initialVal = '', livewireKey = null, isFirstRow = false) => ({
                    open: false,
                    value: initialVal || '',
                    livewireKey: livewireKey,
                    isFirstRow: isFirstRow,
                    focusedIndex: -1,
                    fixedTop: 0,
                    fixedLeft: 0,
                    fixedWidth: 220,

                    init() {
                        this.$watch('value', (val) => {
                            const input = this.getInputEl();
                            if (input && input.value !== val) {
                                input.value = val;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                            if (this.isFirstRow) {
                                window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: val || '' }));
                            }
                        });

                        if (this.isFirstRow && this.value) {
                            window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: this.value }));
                        }

                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$watch('$wire.' + this.livewireKey, (val) => {
                                this.value = val || '';
                            });
                        }

                        const onScroll = () => {
                            if (this.open) {
                                this.updatePosition();
                            }
                        };
                        window.addEventListener('scroll', onScroll, { capture: true, passive: true });
                        window.addEventListener('resize', onScroll, { passive: true });
                    },

                    getTriggerEl() {
                        if (this.$refs.triggerWrap && this.$refs.triggerWrap.isConnected) {
                            return this.$refs.triggerWrap;
                        }
                        return this.$el.querySelector('[x-ref="triggerWrap"]') || this.$el;
                    },

                    getInputEl() {
                        if (this.$refs.picInput && this.$refs.picInput.isConnected) {
                            return this.$refs.picInput;
                        }
                        return this.$el.querySelector('input') || this.$refs.picInput;
                    },

                    get suggestions() {
                        return window.warehousePicList || [];
                    },

                    get filteredSuggestions() {
                        const q = (this.value || '').toLowerCase().trim();
                        if (!q) return [];
                        const list = this.suggestions;
                        const startsWith = [];
                        const contains = [];
                        for (let i = 0; i < list.length; i++) {
                            const name = list[i];
                            const lower = name.toLowerCase();
                            if (lower === q) continue;
                            if (lower.startsWith(q)) {
                                startsWith.push(name);
                            } else if (lower.includes(q)) {
                                contains.push(name);
                            }
                        }
                        return [...startsWith, ...contains].slice(0, 8);
                    },

                    updatePosition() {
                        const el = this.getTriggerEl();
                        if (!el || !el.isConnected) return;
                        const rect = el.getBoundingClientRect();
                        if (rect.width === 0 && rect.height === 0) return;
                        
                        this.fixedWidth = Math.max(rect.width, 220);
                        
                        let left = rect.left;
                        if (left + this.fixedWidth > window.innerWidth - 16) {
                            left = window.innerWidth - this.fixedWidth - 16;
                        }
                        this.fixedLeft = Math.max(16, left);

                        const dropdownHeight = 220;
                        const spaceBelow = window.innerHeight - rect.bottom;
                        if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                            this.fixedTop = Math.max(16, rect.top - dropdownHeight - 4);
                        } else {
                            this.fixedTop = Math.min(window.innerHeight - dropdownHeight - 16, rect.bottom + 4);
                        }
                    },

                    onInput(e) {
                        this.value = e.target.value;
                        this.focusedIndex = -1;
                        if (this.filteredSuggestions.length > 0) {
                            this.open = true;
                            this.updatePosition();
                        } else {
                            this.open = false;
                        }
                        if (this.isFirstRow) {
                            window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: this.value }));
                        }
                    },

                    onFocus() {
                        if (this.filteredSuggestions.length > 0) {
                            this.open = true;
                            this.updatePosition();
                        }
                    },

                    selectPic(name) {
                        this.value = name;
                        const input = this.getInputEl();
                        if (input) {
                            input.value = name;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$wire.set(this.livewireKey, name);
                        }
                        this.close();
                        if (this.isFirstRow) {
                            window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: name }));
                        }
                    },

                    clearPic() {
                        this.value = '';
                        const input = this.getInputEl();
                        if (input) {
                            input.value = '';
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$wire.set(this.livewireKey, '');
                        }
                        this.close();
                        if (this.isFirstRow) {
                            window.dispatchEvent(new CustomEvent('first-pic-updated', { detail: '' }));
                        }
                    },

                    close() {
                        this.open = false;
                        this.focusedIndex = -1;
                    },

                    onKeyDown(e) {
                        const list = this.filteredSuggestions;
                        if (!this.open && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                            if (list.length > 0) {
                                this.open = true;
                                this.updatePosition();
                                e.preventDefault();
                                return;
                            }
                        }
                        if (!this.open) return;

                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.focusedIndex = (this.focusedIndex + 1) % list.length;
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.focusedIndex = (this.focusedIndex - 1 + list.length) % list.length;
                        } else if (e.key === 'Enter') {
                            if (this.focusedIndex >= 0 && this.focusedIndex < list.length) {
                                e.preventDefault();
                                this.selectPic(list[this.focusedIndex]);
                            }
                        } else if (e.key === 'Tab') {
                            if (this.focusedIndex >= 0 && this.focusedIndex < list.length) {
                                e.preventDefault();
                                this.selectPic(list[this.focusedIndex]);
                            } else if (list.length > 0) {
                                e.preventDefault();
                                this.selectPic(list[0]);
                            }
                        } else if (e.key === 'Escape') {
                            this.close();
                        }
                    }
                }));
            }
        };

        if (window.Alpine) {
            registerPicAutocomplete();
        } else {
            document.addEventListener('alpine:init', registerPicAutocomplete);
        }

        const registerItemCombobox = () => {
            if (typeof Alpine !== 'undefined' && !Alpine.data('itemSearchCombobox')) {
                Alpine.data('itemSearchCombobox', (initialId = '', livewireKey = null, rowIndex = null) => ({
                    open: false,
                    search: '',
                    selectedId: initialId ? String(initialId) : '',
                    livewireKey: livewireKey,
                    rowIndex: rowIndex,
                    items: window.warehouseItemsList || [],
                    fixedTop: 0,
                    fixedLeft: 0,
                    fixedWidth: 380,

                    init() {
                        this.items = window.warehouseItemsList || [];

                        this.$watch('selectedId', (val) => {
                            const input = this.getHiddenInputEl();
                            if (input && input.value !== val) {
                                input.value = val;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        });

                        if (this.livewireKey && !this.livewireKey.includes('.') && this.$wire) {
                            this.$watch('$wire.' + this.livewireKey, (val) => {
                                this.selectedId = val ? String(val) : '';
                            });
                        }

                        const onScroll = () => {
                            if (this.open) {
                                this.updatePosition();
                            }
                        };
                        window.addEventListener('scroll', onScroll, { capture: true, passive: true });
                        window.addEventListener('resize', onScroll, { passive: true });
                    },

                    getTriggerEl() {
                        if (this.$refs.triggerWrap && this.$refs.triggerWrap.isConnected) {
                            return this.$refs.triggerWrap;
                        }
                        return this.$el.querySelector('[x-ref="triggerWrap"]') || this.$el;
                    },

                    getHiddenInputEl() {
                        if (this.$refs.hiddenInput && this.$refs.hiddenInput.isConnected) {
                            return this.$refs.hiddenInput;
                        }
                        return this.$el.querySelector('input[type="hidden"]') || this.$refs.hiddenInput;
                    },

                    get selectedItem() {
                        if (!this.selectedId) return null;
                        const items = (this.items && this.items.length) ? this.items : (window.warehouseItemsList || []);
                        return items.find(i => String(i.id) === String(this.selectedId)) || null;
                    },

                    get filteredItems() {
                        const items = (this.items && this.items.length) ? this.items : (window.warehouseItemsList || []);
                        if (!this.search || !this.search.trim()) {
                            return items;
                        }
                        const q = this.search.toLowerCase().trim();
                        return items.filter(i => 
                            (i.name && i.name.toLowerCase().includes(q)) || 
                            (i.code && i.code.toLowerCase().includes(q)) ||
                            (i.category && i.category.toLowerCase().includes(q))
                        );
                    },

                    toggle() {
                        if (this.open) {
                            this.close();
                        } else {
                            this.openDropdown();
                        }
                    },

                    openDropdown() {
                        this.open = true;
                        this.search = '';
                        this.items = window.warehouseItemsList || [];
                        this.updatePosition();
                        this.$nextTick(() => {
                            if (this.$refs.searchField) {
                                this.$refs.searchField.focus();
                            }
                        });
                    },

                    updatePosition() {
                        const el = this.getTriggerEl();
                        if (!el || !el.isConnected) return;
                        const rect = el.getBoundingClientRect();
                        if (rect.width === 0 && rect.height === 0) return;
                        
                        this.fixedWidth = Math.max(rect.width, 380);
                        
                        let left = rect.left;
                        if (left + this.fixedWidth > window.innerWidth - 16) {
                            left = window.innerWidth - this.fixedWidth - 16;
                        }
                        this.fixedLeft = Math.max(16, left);

                        const dropdownHeight = 320;
                        const spaceBelow = window.innerHeight - rect.bottom;
                        if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                            this.fixedTop = Math.max(16, rect.top - dropdownHeight - 6);
                        } else {
                            this.fixedTop = Math.min(window.innerHeight - dropdownHeight - 16, rect.bottom + 4);
                        }
                    },

                    selectItem(item) {
                        this.selectedId = String(item.id);
                        const input = this.getHiddenInputEl();
                        if (input) {
                            input.value = this.selectedId;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (this.livewireKey && this.$wire) {
                            this.$wire.set(this.livewireKey, item.id);
                        }
                        this.close();
                    },

                    clearSelection() {
                        this.selectedId = '';
                        this.search = '';
                        const input = this.getHiddenInputEl();
                        if (input) {
                            input.value = '';
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (this.livewireKey && this.$wire) {
                            this.$wire.set(this.livewireKey, '');
                        }
                        this.close();
                    },

                    close() {
                        this.open = false;
                    }
                }));
            }
        };

        if (window.Alpine) {
            registerItemCombobox();
        } else {
            document.addEventListener('alpine:init', registerItemCombobox);
        }
    </script>
</div>

<div class="space-y-6">

    <!-- Flash Notifications -->
    @if(session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm font-semibold">{{ session('success') }}</div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300 flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm font-semibold">{{ session('error') }}</div>
        </div>
    @endif

    <!-- 1. KPI STATS CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- Total PO -->
        <button type="button" wire:click="$set('statusFilter', 'all')" class="text-left rounded-2xl border p-4 transition-all duration-200 cursor-pointer shadow-xs {{ $statusFilter === 'all' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/30 ring-2 ring-brand-500/20' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Semua PO</span>
                <span class="p-1.5 rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">{{ $metrics['total'] }}</div>
            <p class="text-[11px] text-gray-500 mt-1">Seluruh arsip pengadaan</p>
        </button>

        <!-- Open PO -->
        <button type="button" wire:click="$set('statusFilter', 'open')" class="text-left rounded-2xl border p-4 transition-all duration-200 cursor-pointer shadow-xs {{ $statusFilter === 'open' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30 ring-2 ring-blue-500/20' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider">PO Terbuka</span>
                <span class="p-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-blue-800 dark:text-blue-300">{{ $metrics['open'] }}</div>
            <p class="text-[11px] text-gray-500 mt-1">Belum ada barang masuk</p>
        </button>

        <!-- Partial Delivery -->
        <button type="button" wire:click="$set('statusFilter', 'partial')" class="text-left rounded-2xl border p-4 transition-all duration-200 cursor-pointer shadow-xs {{ $statusFilter === 'partial' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 ring-2 ring-amber-500/20' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Sebagian Diterima</span>
                <span class="p-1.5 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-800 dark:text-amber-300">{{ $metrics['partial'] }}</div>
            <p class="text-[11px] text-gray-500 mt-1">Menunggu sisa pengiriman</p>
        </button>

        <!-- Completed PO -->
        <button type="button" wire:click="$set('statusFilter', 'completed')" class="text-left rounded-2xl border p-4 transition-all duration-200 cursor-pointer shadow-xs {{ $statusFilter === 'completed' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Selesai Penuh</span>
                <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-800 dark:text-emerald-300">{{ $metrics['completed'] }}</div>
            <p class="text-[11px] text-gray-500 mt-1">Semua pesanan terpenuhi</p>
        </button>

        <!-- Cancelled PO -->
        <button type="button" wire:click="$set('statusFilter', 'cancelled')" class="text-left rounded-2xl border p-4 transition-all duration-200 cursor-pointer shadow-xs col-span-2 lg:col-span-1 {{ $statusFilter === 'cancelled' ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-950/30 ring-2 ring-rose-500/20' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Dibatalkan</span>
                <span class="p-1.5 rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-rose-800 dark:text-rose-300">{{ $metrics['cancelled'] }}</div>
            <p class="text-[11px] text-gray-500 mt-1">PO batal diproses</p>
        </button>
    </div>

    <!-- 2. CONTROL TOOLBAR (SEARCH, FILTERS, BUTTONS) -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            
            <!-- Left: Search & Filter Dropdowns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex items-center gap-2.5 flex-1">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[220px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari No. PO, supplier, atau barang..."
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-300 bg-white text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                </div>

                <!-- Status Filter -->
                <select wire:model.live="statusFilter" class="py-2 px-3 text-xs rounded-xl border border-gray-300 bg-white text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="all">Semua Status PO</option>
                    <option value="open">Open (Terbuka)</option>
                    <option value="partial">Partial (Sebagian Diterima)</option>
                    <option value="completed">Completed (Selesai)</option>
                    <option value="cancelled">Cancelled (Dibatalkan)</option>
                </select>

                <!-- Project Filter -->
                <select wire:model.live="projectFilter" class="py-2 px-3 text-xs rounded-xl border border-gray-300 bg-white text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    <option value="">Semua Peruntukan Proyek</option>
                    <option value="general">Persediaan Umum Gudang</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>

                <!-- Reset Filters Button -->
                @if(!empty($search) || $statusFilter !== 'all' || !empty($projectFilter) || !empty($dateFrom) || !empty($dateTo))
                    <button type="button" wire:click="resetFilters" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl dark:bg-rose-950/40 dark:text-rose-300 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset Filter
                    </button>
                @endif
            </div>

            <!-- Right: Action Button -->
            <div class="flex items-center gap-2">
                @if(auth()->check() && auth()->user()->canManageProcurement())
                    <button
                        type="button"
                        wire:click="openCreateModal"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 text-xs font-bold shadow-sm transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Buat Purchase Order Baru</span>
                    </button>
                @endif
            </div>

        </div>
    </div>

    <!-- 3. DATA TABLE -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="border-b border-gray-200 bg-gray-50/75 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 min-w-[140px]">No. Purchase Order</th>
                        <th class="px-4 py-3 min-w-[120px]">Tanggal & ETA</th>
                        <th class="px-4 py-3 min-w-[160px]">Supplier / Vendor</th>
                        <th class="px-4 py-3 min-w-[150px]">Peruntukan Proyek</th>
                        <th class="px-4 py-3 min-w-[180px]">Item & Pemenuhan</th>
                        <th class="px-4 py-3 text-center min-w-[110px]">Status</th>
                        <th class="px-4 py-3 text-center min-w-[120px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($purchaseOrders as $index => $po)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                            <!-- No -->
                            <td class="px-4 py-3.5 text-center font-mono text-gray-400">
                                {{ $purchaseOrders->firstItem() + $index }}
                            </td>

                            <!-- PO Number -->
                            <td class="px-4 py-3.5">
                                <button type="button" wire:click="openDetailModal({{ $po->id }})" class="font-bold text-gray-900 hover:text-purple-600 dark:text-white dark:hover:text-purple-400 text-left cursor-pointer flex items-center gap-1.5 font-mono">
                                    {{ $po->po_number }}
                                </button>
                                <span class="text-[10px] text-gray-400 block mt-0.5">Dibuat: {{ $po->creator ? $po->creator->name : '-' }}</span>
                            </td>

                            <!-- Dates -->
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ format_date($po->po_date) }}</div>
                                @if($po->eta_date)
                                    <div class="text-[10px] text-gray-500 flex items-center gap-1 mt-0.5" title="Estimasi Kedatangan">
                                        <span>ETA: {{ format_date($po->eta_date) }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Supplier -->
                            <td class="px-4 py-3.5 font-semibold text-gray-900 dark:text-white">
                                {{ $po->supplier }}
                            </td>

                            <!-- Project -->
                            <td class="px-4 py-3.5">
                                @if($po->project)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                                        {{ $po->project->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500 italic">Persediaan Umum</span>
                                @endif
                            </td>

                            <!-- Items & Progress -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $po->items_count }} Item Barang
                                    </span>
                                    <span class="font-bold {{ $po->isCompleted() ? 'text-emerald-600 dark:text-emerald-400' : 'text-purple-600 dark:text-purple-400' }}">
                                        {{ $po->progress_percentage }}%
                                    </span>
                                </div>
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full transition-all duration-300 {{ $po->isCompleted() ? 'bg-emerald-500' : ($po->isPartial() ? 'bg-amber-500' : 'bg-purple-600') }}" style="width: {{ $po->progress_percentage }}%"></div>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-3.5 text-center">
                                @if($po->isCompleted())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Selesai
                                    </span>
                                @elseif($po->isPartial())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Parsial
                                    </span>
                                @elseif($po->isCancelled())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Batal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Open
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <!-- View Detail -->
                                    <button
                                        type="button"
                                        wire:click="openDetailModal({{ $po->id }})"
                                        class="p-1.5 rounded-lg text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition cursor-pointer"
                                        title="Lihat Rincian & Riwayat"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>

                                    <!-- Print PO -->
                                    <a
                                        href="{{ route('purchase-orders.print', $po->id) }}"
                                        target="_blank"
                                        class="p-1.5 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition cursor-pointer"
                                        title="Cetak Surat PO"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>

                                    @if(auth()->check() && auth()->user()->canManageProcurement())
                                        <!-- Edit PO (if not completed and not cancelled) -->
                                        @if(!$po->isCompleted() && !$po->isCancelled())
                                            <button
                                                type="button"
                                                wire:click="openEditModal({{ $po->id }})"
                                                class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition cursor-pointer"
                                                title="Edit PO"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        @endif

                                        <!-- Cancel PO (only if no receipts have occurred and not cancelled) -->
                                        @if(!$po->isCompleted() && !$po->isCancelled() && $po->items->sum('quantity_received') == 0)
                                            <button
                                                type="button"
                                                wire:click="openCancelModal({{ $po->id }})"
                                                class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                                title="Batalkan PO"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Tidak ada Purchase Order yang ditemukan</p>
                                    <p class="text-xs text-gray-500 mt-1 max-w-sm">
                                        Silakan ubah filter pencarian atau buat Purchase Order baru untuk memulai pengadaan barang.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($purchaseOrders->hasPages())
            <div class="border-t border-gray-200 dark:border-gray-800 px-4 py-3">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: BUAT PURCHASE ORDER BARU -->
    <!-- ========================================================================= -->
    @if($isCreateModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/80 backdrop-blur-xs animate-fadeIn" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 md:p-6 text-center">
                <div class="relative w-full max-w-4xl lg:max-w-5xl rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 my-auto max-h-[90vh] flex flex-col overflow-hidden">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-200 dark:border-gray-800 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-700 dark:bg-purple-950/80 dark:text-purple-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Buat Purchase Order (PO) Baru</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Penerbitan surat pesanan pembelian barang ke vendor / supplier</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body (Scrollable with min-h-0) -->
                <div class="p-4 sm:p-6 overflow-y-auto space-y-4 custom-scrollbar flex-1 min-h-0">
                    
                    <!-- Top Form Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- No PO -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                No. PO <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="po_number"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('po_number') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tanggal PO -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Tanggal PO <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="po_date"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('po_date') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Estimasi Kedatangan (ETA) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Estimasi Tiba (ETA)
                            </label>
                            <input
                                type="date"
                                wire:model="eta_date"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('eta_date') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Supplier / Vendor -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Supplier / Vendor <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="supplier"
                                placeholder="Contoh: PT Krakatau Steel, CV Maju Jaya..."
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('supplier') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Peruntukan Proyek -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Peruntukan Proyek (Opsional)
                            </label>
                            <select wire:model="project_id" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="">Persediaan Umum Gudang (Non-Project)</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Catatan PO -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Pengadaan</label>
                        <input
                            type="text"
                            wire:model="notes"
                            placeholder="Catatan instruksi pengiriman, terms of payment, dll..."
                            class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>

                    <!-- Dynamic Items Section -->
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                    Daftar Item Barang Pesanan <span class="text-rose-500">*</span>
                                </h4>
                                <p class="text-[11px] text-gray-500">Pilih item katalog, masukkan quantity dan estimasi harga per unit</p>
                            </div>
                            <button
                                type="button"
                                wire:click="addItemRow"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-bold dark:bg-purple-950/60 dark:text-purple-300 transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Baris Item
                            </button>
                        </div>

                        @error('items') <span class="text-[11px] text-rose-500 block mb-2">{{ $message }}</span> @enderror

                        <!-- Items Table in Modal -->
                        <div class="space-y-2">
                            @foreach($items as $i => $itemRow)
                                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400">Item #{{ $i + 1 }}</span>
                                        @if(count($items) > 1)
                                            <button
                                                type="button"
                                                wire:click="removeItemRow({{ $i }})"
                                                class="text-rose-500 hover:text-rose-700 text-xs flex items-center gap-1 cursor-pointer"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus Baris
                                            </button>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <!-- Item Dropdown -->
                                        <div class="sm:col-span-5">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Barang</label>
                                            <select
                                                wire:model.live="items.{{ $i }}.item_id"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                                            >
                                                <option value="">-- Pilih Barang dari Master --</option>
                                                @foreach($availableItems as $ai)
                                                    <option value="{{ $ai->id }}">{{ $ai->code_item }} - {{ $ai->name }} (Stok: {{ format_number($ai->stock) }} {{ $ai->unit }})</option>
                                                @endforeach
                                            </select>
                                            @error("items.{$i}.item_id") <span class="text-[10px] text-rose-500 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Quantity Ordered -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Qty Order</label>
                                            <input
                                                type="number"
                                                step="any"
                                                wire:model.live.debounce.300ms="items.{{ $i }}.quantity_ordered"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                            @error("items.{$i}.quantity_ordered") <span class="text-[10px] text-rose-500 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Unit -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Satuan</label>
                                            <input
                                                type="text"
                                                wire:model="items.{{ $i }}.unit"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white uppercase"
                                            />
                                            @error("items.{$i}.unit") <span class="text-[10px] text-rose-500 block">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Unit Price -->
                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Harga / Satuan (Rp)</label>
                                            <input
                                                type="number"
                                                step="any"
                                                wire:model.live.debounce.300ms="items.{{ $i }}.unit_price"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                        </div>
                                    </div>

                                    <!-- Notes & Subtotal per item -->
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 pt-1 border-t border-gray-200/60 dark:border-gray-750">
                                        <div class="flex-1">
                                            <input
                                                type="text"
                                                wire:model="items.{{ $i }}.notes"
                                                placeholder="Catatan spesifikasi/keterangan tambahan per item (opsional)..."
                                                class="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-[11px] text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                        </div>
                                        <div class="text-[11px] text-gray-600 dark:text-gray-300 shrink-0 text-right font-medium">
                                            Subtotal: <span class="font-bold font-mono text-purple-700 dark:text-purple-300">Rp {{ number_format((float)($itemRow['quantity_ordered'] ?? 0) * (float)($itemRow['unit_price'] ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Summary Banner Total PO -->
                        <div class="mt-3 p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <span class="text-xs font-semibold text-purple-950 dark:text-purple-200">Total Estimasi Nilai Purchase Order:</span>
                                <span class="block text-[11px] text-purple-600 dark:text-purple-400">Akumulasi otomatis dari {{ count($items) }} item pesanan</span>
                            </div>
                            <div class="text-base sm:text-lg font-bold font-mono text-purple-700 dark:text-purple-300">
                                Rp {{ number_format($this->totalEstimatedAmount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3 rounded-b-2xl shrink-0">
                    <div class="text-xs text-gray-500 hidden sm:block">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ count($items) }} Item</span> dipesan • Estimasi: <strong class="font-mono text-purple-700 dark:text-purple-400">Rp {{ number_format($this->totalEstimatedAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                        <button
                            type="button"
                            wire:click="closeCreateModal"
                            class="px-4 py-2 text-xs font-semibold rounded-xl border border-gray-300 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="saveCreate"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 text-xs font-bold rounded-xl bg-purple-600 hover:bg-purple-700 text-white shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveCreate">Terbitkan Purchase Order</span>
                            <span wire:loading wire:target="saveCreate">Memproses...</span>
                        </button>
                    </div>
                </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 2: DETAIL PURCHASE ORDER & RIWAYAT PENERIMAAN GUDANG -->
    <!-- ========================================================================= -->
    @if($isDetailModalOpen && $detailPo)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/80 backdrop-blur-xs animate-fadeIn" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 md:p-6 text-center">
                <div class="relative w-full max-w-4xl lg:max-w-5xl rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 my-auto max-h-[90vh] flex flex-col overflow-hidden">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-200 dark:border-gray-800 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-700 dark:bg-purple-950/80 dark:text-purple-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white font-mono">{{ $detailPo->po_number }}</h3>
                                @if($detailPo->isCompleted())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Selesai</span>
                                @elseif($detailPo->isPartial())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Parsial ({{ $detailPo->progress_percentage }}%)</span>
                                @elseif($detailPo->isCancelled())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">Dibatalkan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">Open (0%)</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Supplier: <strong class="text-gray-800 dark:text-gray-200">{{ $detailPo->supplier }}</strong> | Tanggal: {{ format_date($detailPo->po_date) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('purchase-orders.print', $detailPo->id) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak PO
                        </a>
                        <button type="button" wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body (with min-h-0) -->
                <div class="p-4 sm:p-6 overflow-y-auto space-y-5 custom-scrollbar flex-1 min-h-0">
                    
                    <!-- PO Details Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 text-xs">
                        <div>
                            <span class="text-gray-500 block">Peruntukan Proyek:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $detailPo->project ? $detailPo->project->name : 'Persediaan Umum Gudang' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Estimasi Tiba (ETA):</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $detailPo->eta_date ? format_date($detailPo->eta_date) : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Diterbitkan Oleh:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $detailPo->creator ? $detailPo->creator->name : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Catatan PO:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $detailPo->notes ?: '-' }}</span>
                        </div>
                    </div>

                    <!-- Items Progress Table -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200 mb-2">
                            Status Pemenuhan Item Pesanan
                        </h4>
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-400 font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="px-3 py-2 text-center w-8">No</th>
                                        <th class="px-3 py-2">Kode & Nama Barang</th>
                                        <th class="px-3 py-2 text-center">Dipesan</th>
                                        <th class="px-3 py-2 text-center">Diterima Gudang</th>
                                        <th class="px-3 py-2 text-center">Sisa Menunggu</th>
                                        <th class="px-3 py-2 text-center">Status Item</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($detailPo->items as $idx => $it)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                            <td class="px-3 py-2.5 text-center text-gray-400">{{ $idx + 1 }}</td>
                                            <td class="px-3 py-2.5">
                                                <div class="font-bold text-gray-900 dark:text-white">{{ $it->item ? $it->item->name : '-' }}</div>
                                                <div class="text-[10px] font-mono text-gray-500">{{ $it->item ? $it->item->code_item : '-' }} {{ $it->notes ? "({$it->notes})" : '' }}</div>
                                            </td>
                                            <td class="px-3 py-2.5 text-center font-bold text-gray-900 dark:text-white">
                                                {{ format_number($it->quantity_ordered) }} {{ $it->unit }}
                                            </td>
                                            <td class="px-3 py-2.5 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ format_number($it->quantity_received) }} {{ $it->unit }}
                                            </td>
                                            <td class="px-3 py-2.5 text-center font-bold {{ $it->quantity_remaining > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                                                {{ format_number($it->quantity_remaining) }} {{ $it->unit }}
                                            </td>
                                            <td class="px-3 py-2.5 text-center">
                                                @if($it->is_fulfilled)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                        Terpenuhi
                                                    </span>
                                                @elseif($it->quantity_received > 0)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                                        Parsial
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        Menunggu
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Warehouse Receipts History Section -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200 mb-2">
                            Riwayat Penerimaan Fisik di Gudang (Material In)
                        </h4>
                        @if($detailPo->materialIns && $detailPo->materialIns->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-400 font-bold uppercase text-[10px]">
                                        <tr>
                                            <th class="px-3 py-2 text-center w-8">No</th>
                                            <th class="px-3 py-2">Tgl Diterima</th>
                                            <th class="px-3 py-2">No. Surat Jalan / DO</th>
                                            <th class="px-3 py-2">Barang Diterima</th>
                                            <th class="px-3 py-2 text-center">Qty Diterima</th>
                                            <th class="px-3 py-2 text-center">Lokasi</th>
                                            <th class="px-3 py-2">Penerima Gudang</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($detailPo->materialIns as $rIdx => $receipt)
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                                <td class="px-3 py-2 text-center text-gray-400">{{ $rIdx + 1 }}</td>
                                                <td class="px-3 py-2 font-medium">{{ format_date($receipt->date) }}</td>
                                                <td class="px-3 py-2 font-mono font-bold text-gray-800 dark:text-gray-200">{{ $receipt->delivery_order_number ?: '-' }}</td>
                                                <td class="px-3 py-2">{{ $receipt->item ? $receipt->item->name : '-' }}</td>
                                                <td class="px-3 py-2 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                                    +{{ format_number($receipt->quantity) }} {{ $receipt->unit }}
                                                </td>
                                                <td class="px-3 py-2 text-center">{{ $receipt->location }}</td>
                                                <td class="px-3 py-2">{{ $receipt->received_by }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-800 text-center text-xs text-gray-500">
                                Belum ada barang fisik yang diterima gudang untuk Purchase Order ini.
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-800 flex items-center justify-end rounded-b-2xl shrink-0">
                    <button
                        type="button"
                        wire:click="closeDetailModal"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border border-gray-300 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 3: EDIT PURCHASE ORDER -->
    <!-- ========================================================================= -->
    @if($isEditModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/80 backdrop-blur-xs animate-fadeIn" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 md:p-6 text-center">
                <div class="relative w-full max-w-4xl lg:max-w-5xl rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 my-auto max-h-[90vh] flex flex-col overflow-hidden">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-200 dark:border-gray-800 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/80 dark:text-amber-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Purchase Order</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Perbarui rincian pemesanan barang</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body (with min-h-0) -->
                <div class="p-4 sm:p-6 overflow-y-auto space-y-4 custom-scrollbar flex-1 min-h-0">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">No. PO</label>
                            <input
                                type="text"
                                wire:model="po_number"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-mono font-bold text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('po_number') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal PO</label>
                            <input
                                type="date"
                                wire:model="po_date"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('po_date') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Estimasi Tiba (ETA)</label>
                            <input
                                type="date"
                                wire:model="eta_date"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Supplier / Vendor</label>
                            <input
                                type="text"
                                wire:model="supplier"
                                class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('supplier') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Peruntukan Proyek</label>
                            <select wire:model="project_id" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="">Persediaan Umum Gudang</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                        <input
                            type="text"
                            wire:model="notes"
                            class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>

                    <!-- Items Editor in Edit Modal -->
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Rincian Item Barang
                            </h4>
                            <button
                                type="button"
                                wire:click="addItemRow"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 text-xs font-bold dark:bg-amber-950/60 dark:text-amber-300 transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Item
                            </button>
                        </div>

                        <div class="space-y-2">
                            @foreach($items as $i => $itemRow)
                                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400">
                                            Item #{{ $i + 1 }}
                                            @if(!empty($itemRow['quantity_received']) && $itemRow['quantity_received'] > 0)
                                                <span class="text-[10px] text-emerald-600 font-bold ml-1">(Diterima: {{ $itemRow['quantity_received'] }})</span>
                                            @endif
                                        </span>
                                        @if(empty($itemRow['quantity_received']) || $itemRow['quantity_received'] == 0)
                                            @if(count($items) > 1)
                                                <button
                                                    type="button"
                                                    wire:click="removeItemRow({{ $i }})"
                                                    class="text-rose-500 hover:text-rose-700 text-xs flex items-center gap-1 cursor-pointer"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Hapus
                                                </button>
                                            @endif
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-5">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Barang</label>
                                            <select
                                                wire:model.live="items.{{ $i }}.item_id"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                                            >
                                                <option value="">-- Pilih Barang --</option>
                                                @foreach($availableItems as $ai)
                                                    <option value="{{ $ai->id }}">{{ $ai->code_item }} - {{ $ai->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Qty Order</label>
                                            <input
                                                type="number"
                                                step="any"
                                                wire:model.live.debounce.300ms="items.{{ $i }}.quantity_ordered"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Satuan</label>
                                            <input
                                                type="text"
                                                wire:model="items.{{ $i }}.unit"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white uppercase"
                                            />
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Harga / Satuan (Rp)</label>
                                            <input
                                                type="number"
                                                step="any"
                                                wire:model.live.debounce.300ms="items.{{ $i }}.unit_price"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                        </div>
                                    </div>

                                    <!-- Notes & Subtotal per item in Edit -->
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 pt-1 border-t border-gray-200/60 dark:border-gray-750">
                                        <div class="flex-1">
                                            <input
                                                type="text"
                                                wire:model="items.{{ $i }}.notes"
                                                placeholder="Catatan spesifikasi/keterangan tambahan per item (opsional)..."
                                                class="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-[11px] text-gray-900 focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                        </div>
                                        <div class="text-[11px] text-gray-600 dark:text-gray-300 shrink-0 text-right font-medium">
                                            Subtotal: <span class="font-bold font-mono text-amber-700 dark:text-amber-400">Rp {{ number_format((float)($itemRow['quantity_ordered'] ?? 0) * (float)($itemRow['unit_price'] ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Summary Banner Total PO Edit -->
                        <div class="mt-3 p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <span class="text-xs font-semibold text-amber-950 dark:text-amber-200">Total Estimasi Nilai Purchase Order:</span>
                                <span class="block text-[11px] text-amber-600 dark:text-amber-400">Akumulasi otomatis dari {{ count($items) }} item pesanan</span>
                            </div>
                            <div class="text-base sm:text-lg font-bold font-mono text-amber-700 dark:text-amber-300">
                                Rp {{ number_format($this->totalEstimatedAmount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3 rounded-b-2xl shrink-0">
                    <div class="text-xs text-gray-500 hidden sm:block">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ count($items) }} Item</span> dipesan • Estimasi: <strong class="font-mono text-amber-700 dark:text-amber-400">Rp {{ number_format($this->totalEstimatedAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                        <button
                            type="button"
                            wire:click="closeEditModal"
                            class="px-4 py-2 text-xs font-semibold rounded-xl border border-gray-300 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="saveEdit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 text-xs font-bold rounded-xl bg-amber-600 hover:bg-amber-700 text-white shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveEdit">Menyimpan...</span>
                        </button>
                    </div>
                </div>

                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 4: BATALKAN PURCHASE ORDER -->
    <!-- ========================================================================= -->
    @if($isCancelModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/80 backdrop-blur-xs animate-fadeIn" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
                <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 my-auto p-6 space-y-4">
                
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/80 dark:text-rose-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Batalkan Purchase Order?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status PO akan diubah menjadi Cancelled.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Pembatalan (Opsional):</label>
                    <input
                        type="text"
                        wire:model="cancelReason"
                        placeholder="Contoh: Vendor tidak dapat memenuhi stok..."
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-rose-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="closeCancelModal"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border border-gray-300 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 transition cursor-pointer"
                    >
                        Kembali
                    </button>
                    <button
                        type="button"
                        wire:click="confirmCancel"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="confirmCancel">Ya, Batalkan PO</span>
                        <span wire:loading wire:target="confirmCancel">Memproses...</span>
                    </button>
                </div>

                </div>
            </div>
        </div>
    @endif

</div>

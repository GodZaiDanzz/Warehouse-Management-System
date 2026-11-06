<div class="space-y-4" 
    x-data="itemTableData()"
    @unlock-body-scroll.window="document.body.classList.remove('overflow-hidden'); document.documentElement.classList.remove('overflow-hidden')"
    @reopen-scanner.window="openScanner()"
    @play-success-sound.window="if (typeof window.playSuccessSound === 'function') window.playSuccessSound()">
@php
    $itemAuthUser = auth()->user();
    $canManageWarehouse = $itemAuthUser ? $itemAuthUser->canManageWarehouse() : false;
    $canViewPrices = $itemAuthUser ? $itemAuthUser->canViewPrices() : false;
@endphp
    <!-- PPIC / Warehouse Inventory Mini Dashboard KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $canViewPrices ? 'lg:grid-cols-3 xl:grid-cols-5' : 'lg:grid-cols-2 xl:grid-cols-4' }} gap-4">
        <!-- Card 1: Stok Kritis / Re-Order Point -->
        <div wire:click="$set('stock_status', '{{ $stock_status === 'low' ? '' : 'low' }}')" 
            class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $stock_status === 'low' ? 'border-rose-500 bg-rose-50/90 dark:bg-rose-950/40 ring-2 ring-rose-500/20' : 'border-gray-200 bg-white hover:border-rose-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-rose-800' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warning Stok</span>
                    <h4 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        {{ number_format($criticalTotalCount) }} <span class="text-xs font-normal text-gray-500">Item</span>
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        @if($criticalTotalCount > 0)
                            Stok &le; batas minimum reorder
                        @else
                            <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Semua stok di atas ambang aman</span>
                        @endif
                    </p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            @if($stock_status === 'low')
                <div class="mt-2 text-[10px] font-bold text-rose-700 dark:text-rose-300 flex items-center gap-1">
                    <svg class="w-3 h-3 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Filter Aktif</span> (Klik untuk reset)
                </div>
            @endif
        </div>

        <!-- Card 2: Stok Habis (0 Unit) -->
        <div wire:click="$set('stock_status', '{{ $stock_status === 'empty' ? '' : 'empty' }}')" 
            class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $stock_status === 'empty' ? 'border-red-600 bg-red-50/90 dark:bg-red-950/40 ring-2 ring-red-500/20' : 'border-gray-200 bg-white hover:border-red-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-red-800' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Stok Habis</span>
                    <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                        {{ number_format($outOfStockCount) }} <span class="text-xs font-normal text-gray-500">Item</span>
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        @if($outOfStockCount > 0)
                            Sisa stok 0 unit di gudang
                        @else
                            <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Tidak ada item kosong</span>
                        @endif
                    </p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
            @if($stock_status === 'empty')
                <div class="mt-2 text-[10px] font-bold text-red-700 dark:text-red-300 flex items-center gap-1">
                    <svg class="w-3 h-3 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Filter Aktif</span> (Klik untuk reset)
                </div>
            @endif
        </div>

        <!-- Card 3: Total Master Barang & Rincian Stok per Satuan (Anti-Slop: Tidak Mencampur Satuan Fisik) -->
        <div x-data="{ showUnitModal: false }" class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4.5 shadow-xs dark:border-gray-800 dark:bg-gray-900 transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            @if(!empty($unit_id) && $selectedUnitObj)
                                Total Stok ({{ $selectedUnitObj->symbol }})
                            @else
                                Total Master Barang
                            @endif
                        </span>
                        
                        @if(!empty($unit_id) && $selectedUnitObj)
                            <h4 class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1">
                                {{ format_quantity($selectedUnitStock) }} <span class="text-xs font-bold text-gray-500">{{ $selectedUnitObj->symbol }}</span>
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Dari {{ number_format($selectedUnitItemCount) }} varian barang ({{ $selectedUnitObj->name }})
                            </p>
                        @else
                            <h4 class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1">
                                {{ number_format($totalItemsCount) }} <span class="text-xs font-normal text-gray-500">Item</span>
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                {{ number_format($safeStockCount) }} Stok Aman &bull; {{ number_format($criticalTotalCount) }} Warning
                            </p>
                        @endif
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>

                <!-- Breakdown Akumulasi per Satuan Utama (Memisahkan Kg, Pcs, Set, dll) -->
                @if(empty($unit_id))
                    <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-800/80">
                        <div class="flex items-center justify-between text-[11px] mb-1.5">
                            <span class="text-gray-400 dark:text-gray-500 font-medium">Stok per Satuan:</span>
                            <button type="button" @click="showUnitModal = true" class="text-brand-600 dark:text-brand-400 hover:underline font-bold text-[10px] cursor-pointer">
                                Rincian ({{ count($stockByUnits) }}) &rarr;
                            </button>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach($stockByUnits->take(3) as $uStock)
                                <button type="button" 
                                    @if($uStock->unit_id) wire:click="$set('unit_id', '{{ $uStock->unit_id }}')" @endif
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-gray-50 dark:bg-gray-800/80 hover:bg-brand-50 dark:hover:bg-brand-950/50 border border-gray-200 dark:border-gray-700 text-[11px] font-semibold text-gray-700 dark:text-gray-300 transition cursor-pointer"
                                    title="Klik untuk filter satuan {{ $uStock->unit }}">
                                    <span class="font-bold text-brand-600 dark:text-brand-400">{{ format_quantity($uStock->total_stock) }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $uStock->unit }}</span>
                                </button>
                            @endforeach
                            @if(count($stockByUnits) > 3)
                                <button type="button" @click="showUnitModal = true" 
                                    class="inline-flex items-center px-1.5 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-brand-600 text-[10px] font-semibold transition cursor-pointer">
                                    +{{ count($stockByUnits) - 3 }} lainnya
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Status / Filter Reset -->
            <div class="mt-2.5">
                @if(!empty($unit_id) && $selectedUnitObj)
                    <button type="button" wire:click="$set('unit_id', '')" class="text-[10px] font-bold text-brand-700 dark:text-brand-300 hover:text-rose-600 flex items-center gap-1 transition cursor-pointer">
                        <svg class="w-3 h-3 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Filter Satuan Aktif: {{ $selectedUnitObj->symbol }} (Klik untuk reset)</span>
                    </button>
                @else
                    <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 flex items-center gap-1">
                        <span>Katalog Lengkap Seluruh Satuan</span>
                    </div>
                @endif
            </div>

            <!-- POPUP MODAL: RINCIAN LENGKAP STOK PER SATUAN -->
            <template x-teleport="body">
                <div x-show="showUnitModal" x-cloak 
                    class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-5 bg-gray-950/80 backdrop-blur-sm overflow-y-auto"
                    @keydown.escape.window="showUnitModal = false"
                    @click.self="showUnitModal = false"
                    role="dialog" aria-modal="true">
                    
                    <div x-show="showUnitModal" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden my-auto p-5 space-y-4"
                        @click.stop>
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Rincian Stok Fisik per Satuan</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total akumulasi kuantitas fisik dipisahkan per satuan asli</p>
                            </div>
                            <button type="button" @click="showUnitModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="max-h-72 overflow-y-auto space-y-2 pr-1 divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($stockByUnits as $uStock)
                                <div class="pt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-10 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-brand-600 dark:text-brand-400 flex items-center justify-center text-xs font-black">
                                            {{ $uStock->unit }}
                                        </span>
                                        <div>
                                            <p class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $uStock->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $uStock->item_count }} varian barang</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-brand-600 dark:text-brand-400">
                                            {{ format_quantity($uStock->total_stock) }} <span class="text-xs font-semibold text-gray-500">{{ $uStock->unit }}</span>
                                        </p>
                                        @if($uStock->unit_id)
                                            <button type="button" 
                                                wire:click="$set('unit_id', '{{ $uStock->unit_id }}')" 
                                                @click="showUnitModal = false"
                                                class="text-[10px] text-brand-600 dark:text-brand-400 hover:underline font-bold cursor-pointer">
                                                Lihat Barang &rarr;
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                            <button type="button" @click="showUnitModal = false" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Card 4: Monitoring Cat & Shelf Life -->
        <div @if($catPaintingId) wire:click="$set('category_id', '{{ $category_id == $catPaintingId ? '' : $catPaintingId }}')" @endif
            class="relative overflow-hidden rounded-2xl border p-4.5 transition cursor-pointer shadow-xs {{ $category_id == $catPaintingId ? 'border-brand-500 bg-brand-50/90 dark:bg-brand-950/40 ring-2 ring-brand-500/20' : 'border-gray-200 bg-white hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-800' }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kategori Cat</span>
                    <h4 class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1">
                        {{ number_format($catPaintingCount) }} <span class="text-xs font-normal text-gray-500">Item</span>
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        Dilengkapi Manufacturing Date
                    </p>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014 4 4 4 0 014 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                </div>
            </div>
            @if($category_id == $catPaintingId)
                <div class="mt-2 text-[10px] font-bold text-brand-700 dark:text-brand-300 flex items-center gap-1">
                    <svg class="w-3 h-3 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Filter Kategori Aktif</span> (Klik untuk reset)
                </div>
            @endif
        </div>

        <!-- Card 5: Total Nilai Aset Stok Gudang (Valuasi Inventory - Khusus Web Admin & Finance) -->
        @if($canViewPrices)
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4.5 shadow-xs dark:border-gray-800 dark:bg-gray-900 transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Nilai Aset</span>
                        <h4 class="text-xl sm:text-2xl font-black font-mono tracking-tight text-emerald-700 dark:text-emerald-400 mt-1 tabular-nums truncate" title="Rp {{ number_format($totalAssetValue, 0, ',', '.') }}">
                            Rp {{ number_format($totalAssetValue, 0, ',', '.') }}
                        </h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            {{ number_format($itemsWithPriceCount) }} dari {{ number_format($totalItemsCount) }} item berharga
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-800/80">
                @if(!empty($search) || !empty($category_id) || !empty($unit_id) || !empty($stock_status))
                    <div class="text-[10px] text-emerald-800 dark:text-emerald-300 font-bold flex items-center justify-between">
                        <span>Aset Terfilter:</span>
                        <span class="font-mono tabular-nums">Rp {{ number_format($filteredAssetValue, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center gap-1 font-medium">
                        <span>Akumulasi stok &times; harga beli</span>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- PPIC Inventory Alert Smart Banner -->
    @if($criticalTotalCount > 0 && !empty($criticalItemsList))
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 text-rose-900 dark:text-rose-200 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-white font-black text-sm">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h5 class="text-xs font-extrabold uppercase tracking-wide">Peringatan Perencanaan PPIC</h5>
                    <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">
                        Terdapat <strong>{{ $criticalTotalCount }} item material</strong> yang berada di bawah atau sama dengan batas minimum stok (Reorder Point). Buatkan Purchase Request (FPBJ) agar fabrikasi tidak terhambat.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" wire:click="$set('stock_status', 'low')" class="px-3.5 py-2 rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-800 text-xs font-bold transition cursor-pointer">
                    Lihat {{ $criticalTotalCount }} Warning Stok
                </button>
                <button type="button" 
                    @click="$dispatch('trigger-purchase-request-bulk', {{ json_encode($criticalItemsList, JSON_HEX_APOS | JSON_HEX_QUOT) }})" 
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold transition shadow-xs cursor-pointer flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Cetak 1 Surat FPBJ (Semua {{ count($criticalItemsList) }} Warning Stok)</span>
                </button>
            </div>
        </div>
    @else
        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-200 text-xs shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white font-bold text-xs">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span class="font-semibold">Kondisi Persediaan Optimal: Seluruh stok material saat ini berada di atas ambang batas minimum reorder point.</span>
            </div>
            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-bold hidden sm:inline">PPIC Ready</span>
        </div>
    @endif


    <!-- Category Filter Chips -->
    @php
        $catCategories = $categories->filter(function($c) {
            $nameLower = strtolower($c->name);
            return str_contains($nameLower, 'cat') || str_contains($nameLower, 'thinner') || str_contains($nameLower, 'paint');
        });
        $nonCatCategories = $categories->reject(function($c) {
            $nameLower = strtolower($c->name);
            return str_contains($nameLower, 'cat') || str_contains($nameLower, 'thinner') || str_contains($nameLower, 'paint');
        });

        $activeCatItem = $catCategories->firstWhere('id', $category_id);
        $isCatActive = !empty($activeCatItem);
    @endphp
    <!-- Storage Location Switcher Tabs (STORAGE 1 vs STORAGE 2) -->
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mr-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Gudang:
        </span>
        <button type="button" wire:click="$set('storage_filter', '')" 
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1.5 {{ empty($storage_filter) ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
            <span>Semua Storage</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ empty($storage_filter) ? 'bg-brand-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">{{ $totalItemsCount }}</span>
        </button>
        <button type="button" wire:click="$set('storage_filter', 'STORAGE 1')" 
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1.5 {{ $storage_filter === 'STORAGE 1' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-800 hover:bg-slate-200 dark:bg-gray-800 dark:text-gray-300' }}"
            title="Gudang Utama: Menyimpan Consumable, Secondary Part, Cat, Gas, dan material umum lainnya">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            <span>STORAGE 1 (Umum &amp; Part)</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $storage_filter === 'STORAGE 1' ? 'bg-slate-800 text-white' : 'bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-gray-300' }}">{{ $storage1Count }}</span>
        </button>
        <button type="button" wire:click="$set('storage_filter', 'STORAGE 2')" 
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1.5 {{ $storage_filter === 'STORAGE 2' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-100 text-amber-900 hover:bg-amber-200 dark:bg-amber-950/60 dark:text-amber-300' }}"
            title="Khusus Bahan Baku Fabrikasi (Raw Material: Besi Plat, Pipa, Siku, Hollow, Round Bar)">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>STORAGE 2 (Raw Material)</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $storage_filter === 'STORAGE 2' ? 'bg-amber-700 text-white' : 'bg-amber-200 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200' }}">{{ $storage2Count }}</span>
        </button>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button type="button" wire:click="$set('category_id', '')" 
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer {{ empty($category_id) ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
            Semua Kategori
        </button>

        @foreach($nonCatCategories as $cat)
            <button type="button" wire:click="$set('category_id', '{{ $cat->id }}')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $category_id == $cat->id ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ $cat->name }}
            </button>
        @endforeach

        <!-- Dropdown Group Kategori Cat & Coating -->
        <div class="relative" x-data="{ openCatMenu: false }" @click.outside="openCatMenu = false">
            <button type="button" 
                @click="openCatMenu = !openCatMenu" 
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $isCatActive ? 'bg-brand-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                <span>{{ $isCatActive ? 'Cat: ' . $activeCatItem->name : 'Kategori Cat' }}</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openCatMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <!-- Dropdown Sub-menu -->
            <div x-show="openCatMenu" 
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                class="absolute left-0 mt-1.5 w-56 rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 py-2 z-50 overflow-hidden">
                
                <div class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 border-b border-gray-100 dark:border-gray-700/60 mb-1">
                    Pilih Sub-Kategori Cat:
                </div>

                @foreach($catCategories as $cat)
                    <button type="button" 
                        wire:click="$set('category_id', '{{ $cat->id }}')" 
                        @click="openCatMenu = false"
                        class="w-full text-left px-3.5 py-2 text-xs font-semibold flex items-center justify-between hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-gray-700 dark:hover:text-white transition cursor-pointer {{ $category_id == $cat->id ? 'bg-brand-50 text-brand-700 font-bold dark:bg-gray-700 dark:text-white' : 'text-gray-700 dark:text-gray-200' }}">
                        <span>{{ $cat->name }}</span>
                        @if($category_id == $cat->id)
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <button type="button" wire:click="$set('stock_status', '{{ $stock_status === 'low' ? '' : 'low' }}')" 
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $stock_status === 'low' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300' }}">
            <!-- <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Stok Menipis</span> -->
            <span>Stok Menipis</span>
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-4">
        
        <!-- Baris 1: Kotak Pencarian & Tombol Aksi Utama -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Input dengan Ruang Lapang -->
            <div class="relative flex-1 min-w-[280px]">
                <input type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari nama barang, kode material, atau spesifikasi..." 
                    class="w-full rounded-xl border border-gray-300 bg-gray-50/80 pl-10 pr-10 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/80 dark:text-white dark:placeholder:text-gray-500 dark:focus:bg-gray-900 transition-all">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                @if(!empty($search))
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition cursor-pointer" title="Hapus Pencarian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @endif
            </div>

            <!-- Tombol Aksi: Scan Barcode & Reset Filter -->
            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Button Buka Kamera Barcode Scanner -->
                <button type="button" @click="openScanner()" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-100 dark:bg-brand-950/50 dark:text-brand-300 dark:hover:bg-brand-900/60 border border-brand-200 dark:border-brand-800/80 text-xs sm:text-sm font-semibold transition shadow-2xs cursor-pointer active:scale-95" 
                    title="Pindai Barcode / QR Code Barang via Kamera">
                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Scan Barcode</span>
                </button>

                @if(!empty($search) || !empty($category_id) || !empty($unit_id) || !empty($stock_status) || !empty($storage_filter))
                    <button type="button" wire:click="resetFilters" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 dark:text-rose-400 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-900/60 transition cursor-pointer" 
                        title="Reset Semua Filter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Baris 2: Filter Data & Pengaturan Densitas Tampilan -->
        <div class="pt-3.5 border-t border-gray-100 dark:border-gray-800/80 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            
            <!-- 4 Dropdown Filter Data dalam Grid yang Rapi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 flex-1 max-w-4xl">
                <!-- Filter Storage Dropdown -->
                <div class="relative">
                    <select wire:model.live.debounce.150ms="storage_filter" class="w-full appearance-none rounded-xl border border-gray-300 bg-gray-50/80 pl-3.5 pr-8 py-2 text-xs sm:text-sm font-medium text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                        <option value="">Semua Storage</option>
                        <option value="STORAGE 1">STORAGE 1 (Umum &amp; Part)</option>
                        <option value="STORAGE 2">STORAGE 2 (Raw Material)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Filter Kategori Dropdown -->
                <div class="relative">
                    <select wire:model.live.debounce.150ms="category_id" class="w-full appearance-none rounded-xl border border-gray-300 bg-gray-50/80 pl-3.5 pr-8 py-2 text-xs sm:text-sm font-medium text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Filter Satuan Dropdown -->
                <div class="relative">
                    <select wire:model.live.debounce.150ms="unit_id" class="w-full appearance-none rounded-xl border border-gray-300 bg-gray-50/80 pl-3.5 pr-8 py-2 text-xs sm:text-sm font-medium text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                        <option value="">Semua Satuan</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->symbol }} ({{ $u->name }})</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Filter Status Stok -->
                <div class="relative">
                    <select wire:model.live.debounce.150ms="stock_status" class="w-full appearance-none rounded-xl border border-gray-300 bg-gray-50/80 pl-3.5 pr-8 py-2 text-xs sm:text-sm font-medium text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                        <option value="">Semua Status Stok</option>
                        <option value="safe">Stok Aman (> Min)</option>
                        <option value="low">Warning Stok (≤ Reorder)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Pengaturan Jumlah Baris per Halaman (PerPage) -->
            <div class="flex items-center justify-between sm:justify-end gap-2 text-xs text-gray-500 dark:text-gray-400 shrink-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-gray-100 dark:border-gray-800">
                <span class="whitespace-nowrap font-medium text-gray-600 dark:text-gray-400">Tampilkan:</span>
                <div class="relative">
                    <select wire:model.live.debounce.150ms="perPage" class="appearance-none rounded-xl border border-gray-300 bg-gray-50/80 pl-3 pr-7 py-2 text-xs sm:text-sm font-medium text-gray-700 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer transition">
                        <option value="15">15 / hal</option>
                        <option value="25">25 / hal</option>
                        <option value="50">50 / hal</option>
                        <option value="100">100 / hal</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Container with Loading Indicator -->
    <div class="relative rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-sm overflow-hidden">
        
        <!-- Livewire Loading Overlay -->
        <div wire:loading.flex class="absolute inset-0 z-20 flex items-center justify-center bg-white/60 dark:bg-gray-900/60 backdrop-blur-xs">
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 text-brand-600 font-bold text-xs">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Memuat Data Stok...
            </div>
        </div>

        @php
            $itemAuthUser = auth()->user();
            $canManageWarehouse = $itemAuthUser ? $itemAuthUser->canManageWarehouse() : false;
        @endphp

        <!-- MOBILE CARD VIEW (Active on screens < 768px) -->
        <div class="md:hidden space-y-3 p-3 sm:p-4 bg-gray-50/50 dark:bg-gray-900/40">
            @forelse($items as $item)
                @php
                    $effectiveMin = $item->getEffectiveMinStock($totalActiveTonnage);
                    $isLow = $item->stock <= $effectiveMin;
                    $isExpired = $item->isExpired();
                    $isExpiringSoon = $item->isNearExpiry(30);

                    $minVal = max(1, (int) $effectiveMin);
                    $targetVal = max($minVal * 2, (int) $item->stock);
                    $stockPct = $targetVal > 0 ? max(0, min(100, round(($item->stock / $targetVal) * 100))) : 0;

                    if ($item->stock <= 0) {
                        $barColor = 'bg-rose-600';
                    } elseif ($item->stock <= $effectiveMin) {
                        $barColor = 'bg-rose-500';
                    } elseif ($item->stock <= $effectiveMin * 1.5) {
                        $barColor = 'bg-amber-500';
                    } else {
                        $barColor = 'bg-emerald-600';
                    }

                    $itemJson = json_encode([
                        'id' => $item->id,
                        'code_item' => $item->code_item ?? '',
                        'name' => $item->name,
                        'brand' => $item->brand ?? '',
                        'is_offcut' => (bool) $item->isOffCut(),
                        'parent_item_id' => (string)($item->parent_item_id ?? ''),
                        'dimension' => $item->dimension ?? '',
                        'origin_project' => $item->origin_project ?? '',
                        'heat_number' => $item->heat_number ?? '',
                        'category_id' => (string)($item->category_id ?? ''),
                        'unit_id' => (string)($item->unit_id ?? ''),
                        'stock' => (float)$item->stock,
                        'unit' => $item->unit ?: 'Pcs',
                        'min_stock' => (float)$item->min_stock,
                        'requirement_per_ton' => $item->requirement_per_ton !== null ? (float)$item->requirement_per_ton : null,
                        'effective_min_stock' => $effectiveMin,
                        'location' => $item->location ?? '',
                        'storage' => $item->storage_location,
                        'unit_price' => $canViewPrices ? (float)($item->unit_price ?? 0) : 0,
                        'wholesale_unit' => $item->wholesale_unit ?? '',
                        'conversion_factor' => (float)($item->conversion_factor ?? 1),
                        'manufacturing_date' => $item->manufacturing_date ? \Carbon\Carbon::parse($item->manufacturing_date)->format('Y-m-d') : '',
                        'expiry_date' => $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : '',
                    ], JSON_UNESCAPED_UNICODE);
                @endphp
                <div wire:key="item-mobile-card-{{ $item->id }}" class="rounded-2xl border p-4 transition shadow-xs {{ $isLow || $isExpired ? 'border-rose-300 bg-rose-50/40 dark:border-rose-900/60 dark:bg-rose-950/20' : ($isExpiringSoon ? 'border-amber-300 bg-amber-50/40 dark:border-amber-900/60 dark:bg-amber-950/20' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900') }}">
                    <!-- Card Header: Checkbox + Name & Code + Category & Storage -->
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="pt-0.5 shrink-0">
                                <input type="checkbox" 
                                    class="item-checkbox rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer" 
                                    data-item="{{ $itemJson }}"
                                    @change="toggleSelectItem($el, $event)" 
                                    :checked="!!selectedMap[{{ $item->id }}]">
                            </div>
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-snug break-words">
                                        {{ $item->name }}
                                    </h4>
                                    @if($item->isOffCut())
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/70 dark:text-amber-300 dark:border-amber-800">
                                            OFF-CUT
                                        </span>
                                    @endif
                                </div>
                                @if($item->isOffCut() && !empty($item->dimension))
                                    <div class="text-[11px] text-amber-800 dark:text-amber-300 font-medium">
                                        Dimensi: <strong class="font-bold font-mono">{{ $item->dimension }}</strong>
                                        @if($item->parentItem)
                                            <span class="text-gray-500 text-[10px] block">Induk: {{ $item->parentItem->name }}</span>
                                        @endif
                                    </div>
                                @endif
                                @if(!empty($item->code_item))
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 font-mono text-[11px] font-extrabold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md border border-gray-200 dark:border-gray-700 tracking-wider shadow-2xs">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            {{ $item->code_item }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Category & Storage Pills -->
                        <div class="shrink-0 flex flex-col items-end gap-1">
                            @if(!empty($item->brand))
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800">
                                    {{ $item->brand }}
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                {{ $item->category->name ?? '-' }}
                            </span>
                            @if($item->storage_location === \App\Models\Item::STORAGE_2)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9.5px] font-black bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/70 dark:text-amber-300 dark:border-amber-800" title="{{ $item->display_location }}">
                                    STORAGE 2
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9.5px] font-black bg-slate-100 text-slate-800 border border-slate-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700" title="{{ $item->display_location }}">
                                    STORAGE 1
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Body: Stock Numbers & Progress Bar -->
                    <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-800/80 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400 font-medium">Stok Fisik:</span>
                            <div class="flex items-baseline gap-1">
                                <span class="font-mono text-base font-black {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">
                                    {{ format_quantity($item->stock) }}
                                </span>
                                <span class="text-[11px] font-bold text-gray-500 uppercase">{{ $item->unit ?: 'Pcs' }}</span>
                                @if($item->hasRequirementPerTon())
                                    <button type="button" 
                                        wire:click="loadMinStockBreakdown({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="loadMinStockBreakdown({{ $item->id }})"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/60 px-1.5 py-0.5 rounded border border-brand-200 dark:border-brand-800 cursor-pointer ml-1 disabled:opacity-50"
                                        title="Lihat Rincian Minimal Stok Project">
                                        <svg wire:loading.remove wire:target="loadMinStockBreakdown({{ $item->id }})" class="w-2.5 h-2.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg wire:loading wire:target="loadMinStockBreakdown({{ $item->id }})" class="w-2.5 h-2.5 animate-spin text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Min: {{ format_quantity($effectiveMin) }} (Auto)</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-amber-700 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-300 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800 ml-1" title="Belum diisi: Menggunakan fallback manual">
                                        Min: {{ format_quantity($effectiveMin) }} (Belum diisi)
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Progress Bar Indicator -->
                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $stockPct }}%;"></div>
                        </div>

                        @if(!empty($item->wholesale_unit) && $item->conversion_factor > 1)
                            <div class="flex items-center justify-between text-[11px] text-brand-600 dark:text-brand-400 font-medium">
                                <span>1 {{ $item->wholesale_unit }} = {{ $item->conversion_factor }} {{ $item->unit }}</span>
                                @if($item->stock > 0)
                                    @php
                                        $remStockMobile = fmod((float)$item->stock, (float)$item->conversion_factor);
                                    @endphp
                                    <span class="text-gray-500">≈ {{ floor($item->stock / $item->conversion_factor) }} {{ $item->wholesale_unit }}@if($remStockMobile > 0.0001) + {{ format_quantity($remStockMobile) }} {{ $item->unit }}@endif</span>
                                @endif
                            </div>
                        @endif

                        @if($item->isCat())
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                Tgl Pembuatan: <span class="font-medium text-gray-700 dark:text-gray-300">{{ format_date($item->manufacturing_date, 'Belum diisi') }}</span>
                            </div>
                        @endif

                        <!-- Harga Satuan & Nilai Aset Stok (Khusus Web Admin & Finance) -->
                        @if($canViewPrices)
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100 dark:border-gray-800/80 text-xs">
                            <div>
                                <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Harga Satuan</span>
                                <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200 tabular-nums">
                                    @if((float)$item->unit_price > 0)
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        @if($item->hasWholesaleConversion())
                                            <span class="block text-[10px] text-brand-600 dark:text-brand-400 font-sans font-medium">
                                                ≈ Rp {{ number_format($item->wholesale_price, 0, ',', '.') }}/{{ $item->wholesale_unit }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic font-normal text-[11px]">-</span>
                                    @endif
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Total Aset</span>
                                @php $itemAssetMobile = (float)$item->stock * (float)$item->unit_price; @endphp
                                <span class="font-mono text-xs font-black {{ $itemAssetMobile > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-400' }} tabular-nums">
                                    Rp {{ number_format($itemAssetMobile, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Card Footer: Status & Actions -->
                    <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between gap-2">
                        <div>
                            @if($isLow)
                                <button type="button" 
                                    data-item="{{ $itemJson }}"
                                    @click="reorderItem($el)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-rose-600 text-white hover:bg-rose-700 transition cursor-pointer shadow-xs active:scale-95"
                                    title="Klik untuk membuat Formulir Permintaan Pengadaan (FPBJ)">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Re-Order!</span>
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Stok Aman</span>
                                </span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        @if($canManageWarehouse)
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                    data-item="{{ $itemJson }}"
                                    @click="editItem($el)" 
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 dark:text-brand-300 dark:bg-brand-950/50 dark:hover:bg-brand-900/60 border border-brand-200/80 dark:border-brand-800 transition cursor-pointer shadow-2xs" 
                                    title="Edit Data Barang">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <a href="{{ route('items.barcode.print', $item->id) }}" target="_blank"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition cursor-pointer shadow-2xs" 
                                    title="Cetak Label Barcode & QR Code">
                                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <span>Barcode</span>
                                </a>

                                <button type="button" 
                                    data-name="{{ $item->name }}"
                                    @click="confirmDelete('Apakah Anda yakin ingin menghapus barang ' + $el.getAttribute('data-name') + '?', () => $wire.delete({{ $item->id }}))" 
                                    class="p-1.5 rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-100 dark:text-rose-300 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800 transition cursor-pointer shadow-2xs" 
                                    title="Hapus Barang">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                @php
                    $hasActiveFiltersMobile = !empty($search) || !empty($category_id) || !empty($unit_id) || !empty($stock_status);
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-gray-400 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mx-auto mb-3">
                        @if($hasActiveFiltersMobile)
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        @else
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        @endif
                    </div>
                    @if($hasActiveFiltersMobile)
                        <h4 class="font-bold text-xs text-gray-800 dark:text-gray-200">Tidak ada barang yang cocok</h4>
                        <p class="text-[11px] text-gray-400 mt-1">Sesuaikan kata kunci pencarian atau reset filter.</p>
                        <div class="mt-3">
                            <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Reset Filter</span>
                            </button>
                        </div>
                    @else
                        <h4 class="font-bold text-xs text-gray-800 dark:text-gray-200">Katalog Barang Belum Tersedia</h4>
                        <p class="text-[11px] text-gray-400 mt-1">Belum ada barang material yang terdaftar di sistem.</p>
                        @if($canManageWarehouse)
                            <div class="mt-3">
                                <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold rounded-xl bg-brand-600 text-white hover:bg-brand-700 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Tambah Barang</span>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            @endforelse
        </div>

        <!-- DESKTOP / TABLET DATA TABLE (Active on screens >= 768px) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-3 py-3.5 text-center w-10">
                            <div class="flex items-center justify-center">
                                <input type="checkbox" 
                                    id="select-all-items-cb"
                                    class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer"
                                    title="Pilih / Batalkan Semua di Halaman Ini"
                                    @change="toggleSelectAllOnPage($event)">
                            </div>
                        </th>
                        <th class="px-3 py-3.5 font-semibold cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('code_item')">
                            Kode Material
                            @if($sortField === 'code_item') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3.5 py-3.5 font-semibold cursor-pointer select-none" wire:click="sortBy('name')">
                            Nama Barang
                            @if($sortField === 'name') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-3.5 font-semibold cursor-pointer select-none text-center whitespace-nowrap" wire:click="sortBy('brand')">
                            Brand
                            @if($sortField === 'brand') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-3.5 font-semibold text-center whitespace-nowrap">Kategori</th>
                        <th class="px-3 py-3.5 font-semibold cursor-pointer select-none text-center whitespace-nowrap" wire:click="sortBy('stock')">
                            Stok Fisik
                            @if($sortField === 'stock') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-3.5 font-semibold text-center whitespace-nowrap">Min. Stok</th>
                        @if($canViewPrices)
                        <th class="px-3.5 py-3.5 font-semibold text-right cursor-pointer select-none whitespace-nowrap" wire:click="sortBy('unit_price')">
                            Nilai Aset &amp; Harga
                            @if($sortField === 'unit_price') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        @endif
                        <th class="px-3.5 py-3.5 font-semibold text-center select-none cursor-pointer whitespace-nowrap" wire:click="sortBy('updated_at')">
                            Status &amp; Update
                            @if($sortField === 'updated_at') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        @if($canManageWarehouse)
                            <th class="px-3 py-3.5 font-semibold text-center whitespace-nowrap">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($items as $item)
                        @php
                            $effectiveMin = $item->getEffectiveMinStock($totalActiveTonnage);
                            $isLow = $item->stock <= $effectiveMin;
                            $isExpired = $item->isExpired();
                            $isExpiringSoon = $item->isNearExpiry(30);

                            $itemJson = json_encode([
                                'id' => $item->id,
                                'code_item' => $item->code_item ?? '',
                                'name' => $item->name,
                                'brand' => $item->brand ?? '',
                                'is_offcut' => (bool) $item->isOffCut(),
                                'parent_item_id' => (string)($item->parent_item_id ?? ''),
                                'dimension' => $item->dimension ?? '',
                                'origin_project' => $item->origin_project ?? '',
                                'heat_number' => $item->heat_number ?? '',
                                'category_id' => (string)($item->category_id ?? ''),
                                'unit_id' => (string)($item->unit_id ?? ''),
                                'stock' => (float)$item->stock,
                                'unit' => $item->unit ?: 'Pcs',
                                'min_stock' => (float)$item->min_stock,
                                'requirement_per_ton' => $item->requirement_per_ton !== null ? (float)$item->requirement_per_ton : null,
                                'effective_min_stock' => $effectiveMin,
                                'location' => $item->location ?? '',
                                'storage' => $item->storage_location,
                                'unit_price' => $canViewPrices ? (float)($item->unit_price ?? 0) : 0,
                                'wholesale_unit' => $item->wholesale_unit ?? '',
                                'conversion_factor' => (float)($item->conversion_factor ?? 1),
                                'manufacturing_date' => $item->manufacturing_date ? \Carbon\Carbon::parse($item->manufacturing_date)->format('Y-m-d') : '',
                                'expiry_date' => $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : '',
                            ], JSON_UNESCAPED_UNICODE);
                        @endphp
                        <tr wire:key="item-row-{{ $item->id }}" class="hover:bg-gray-50/60 dark:hover:bg-gray-800/50 transition {{ $isLow || $isExpired ? 'bg-rose-50/30 dark:bg-rose-950/20' : ($isExpiringSoon ? 'bg-amber-50/30 dark:bg-amber-950/20' : '') }}">
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" 
                                    class="item-checkbox rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer" 
                                    data-item="{{ $itemJson }}"
                                    @change="toggleSelectItem($el, $event)" 
                                    :checked="!!selectedMap[{{ $item->id }}]">
                            </td>
                            <td class="whitespace-nowrap px-3 py-3">
                                @if(!empty($item->code_item))
                                    <span class="inline-flex items-center gap-1.5 font-mono text-xs font-bold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700 tracking-wider shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        {{ $item->code_item }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">-</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3 font-semibold text-gray-900 dark:text-white">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white leading-snug">{{ $item->name }}</span>
                                        @if($item->isOffCut())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/70 dark:text-amber-300 dark:border-amber-800" title="Sisa potongan raw material layak pakai">
                                                <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.758 7.758M12 12l2.879-2.879a3 3 0 10-4.242-4.242L7.758 10.758"/></svg>
                                                OFF-CUT
                                            </span>
                                        @endif
                                    </div>
                                    @if($item->isOffCut())
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-gray-600 dark:text-gray-300">
                                            @if(!empty($item->dimension))
                                                <span class="font-medium inline-flex items-center gap-1 text-amber-800 dark:text-amber-300">
                                                    <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                                    Dimensi: <strong class="font-bold font-mono">{{ $item->dimension }}</strong>
                                                </span>
                                            @endif
                                            @if($item->parentItem)
                                                <span class="text-gray-400">&bull;</span>
                                                <span class="text-gray-500 dark:text-gray-400">Induk: {{ $item->parentItem->name }}</span>
                                            @endif
                                            @if(!empty($item->origin_project))
                                                <span class="text-gray-400">&bull;</span>
                                                <span class="text-gray-500 dark:text-gray-400">Proyek: {{ $item->origin_project }}</span>
                                            @endif
                                            @if(!empty($item->heat_number))
                                                <span class="text-gray-400">&bull;</span>
                                                <span class="font-mono text-[10px] bg-gray-100 dark:bg-gray-800 px-1 rounded text-gray-700 dark:text-gray-300">Heat No: {{ $item->heat_number }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($item->isCat())
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 font-normal">
                                            Tgl Pembuatan: <span class="font-medium text-gray-700 dark:text-gray-300">{{ format_date($item->manufacturing_date, 'Belum diisi') }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($item->wholesale_unit) && $item->conversion_factor > 1)
                                        <div class="text-[11px] text-brand-600 dark:text-brand-400 font-medium">
                                            1 {{ $item->wholesale_unit }} = {{ $item->conversion_factor }} {{ $item->unit }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-center">
                                @if(!empty($item->brand))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800">
                                        {{ $item->brand }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700">
                                        {{ $item->category->name ?? '-' }}
                                    </span>
                                    @if($item->storage_location === \App\Models\Item::STORAGE_2)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/70 dark:text-amber-300 dark:border-amber-800" title="{{ $item->display_location }}">
                                            <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            STORAGE 2
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-100 text-slate-800 border border-slate-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700" title="{{ $item->display_location }}">
                                            <svg class="w-3 h-3 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                            STORAGE 1
                                        </span>
                                    @endif
                                    @if(!empty($item->location) && strcasecmp(trim($item->location), $item->storage_location) !== 0)
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                            {{ $item->location }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <!-- Kolom Terpisah: Stok Fisik -->
                            <td class="whitespace-nowrap px-3 py-3 font-bold text-gray-900 dark:text-white text-center">
                                <div class="space-y-1 flex flex-col items-center justify-center">
                                    <span class="text-xs font-mono font-bold {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-200' }}">
                                        {{ format_quantity($item->stock) }} <span class="text-[10px] font-bold text-gray-500 uppercase">{{ $item->unit ?: 'Pcs' }}</span>
                                    </span>

                                    @php
                                        $minVal = max(1, (int) $effectiveMin);
                                        $targetVal = max($minVal * 2, (int) $item->stock);
                                        $stockPct = $targetVal > 0 ? max(0, min(100, round(($item->stock / $targetVal) * 100))) : 0;

                                        if ($item->stock <= 0) {
                                            $barColor = 'bg-rose-600';
                                        } elseif ($item->stock <= $effectiveMin) {
                                            $barColor = 'bg-rose-500';
                                        } elseif ($item->stock <= $effectiveMin * 1.5) {
                                            $barColor = 'bg-amber-500';
                                        } else {
                                            $barColor = 'bg-emerald-600';
                                        }
                                    @endphp

                                    <!-- Stock Level Indicator Bar -->
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden" title="Stok Saat Ini: {{ format_quantity($item->stock) }} | Batas Minimum: {{ format_quantity($effectiveMin) }} {{ $item->unit ?: 'Pcs' }}">
                                        <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $stockPct }}%;"></div>
                                    </div>

                                    @if(!empty($item->wholesale_unit) && $item->conversion_factor > 1 && $item->stock > 0)
                                        @php
                                            $remStockDesktop = fmod((float)$item->stock, (float)$item->conversion_factor);
                                        @endphp
                                        <div class="text-[10px] text-gray-500 font-normal">
                                            ≈ {{ floor($item->stock / $item->conversion_factor) }} {{ $item->wholesale_unit }}
                                            @if($remStockDesktop > 0.0001)
                                                + {{ format_quantity($remStockDesktop) }} {{ $item->unit ?: 'Pcs' }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <!-- Kolom Terpisah: Min. Stok -->
                            <td class="whitespace-nowrap px-3 py-3 font-bold text-gray-900 dark:text-white text-center">
                                <div class="space-y-1 flex flex-col items-center justify-center">
                                    <span class="text-xs font-mono font-bold {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-200' }}">
                                        {{ format_quantity($effectiveMin) }} <span class="text-[10px] font-bold text-gray-500 uppercase">{{ $item->unit ?: 'Pcs' }}</span>
                                    </span>
                                    @if($item->hasRequirementPerTon())
                                        <button type="button" 
                                            wire:click="loadMinStockBreakdown({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="loadMinStockBreakdown({{ $item->id }})"
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-brand-50 text-brand-700 border border-brand-200 dark:bg-brand-950/60 dark:text-brand-300 dark:border-brand-800 hover:bg-brand-100 dark:hover:bg-brand-900/80 transition cursor-pointer shadow-2xs disabled:opacity-50"
                                            title="Klik untuk melihat rincian hitungan minimal stok per project">
                                            <svg wire:loading.remove wire:target="loadMinStockBreakdown({{ $item->id }})" class="w-2.5 h-2.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <svg wire:loading wire:target="loadMinStockBreakdown({{ $item->id }})" class="w-2.5 h-2.5 animate-spin text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            <span>Auto ({{ format_quantity($item->requirement_per_ton) }}/Ton)</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-amber-700 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-300 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800" title="Kebutuhan per ton belum diisi. Menggunakan nilai minimal stok manual sebagai fallback.">
                                            Manual: Belum diisi
                                        </span>
                                    @endif
                                </div>
                            </td>
                            @if($canViewPrices)
                            <td class="whitespace-nowrap px-3.5 py-3 text-right font-mono text-xs">
                                @php
                                    $unitPrice = (float) $item->unit_price;
                                    $totalAsset = (float) $item->stock * $unitPrice;
                                @endphp
                                <div class="space-y-0.5">
                                    @if($totalAsset > 0)
                                        <div class="font-black text-emerald-700 dark:text-emerald-400 tabular-nums text-sm">
                                            Rp {{ number_format($totalAsset, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="font-bold text-gray-400 dark:text-gray-500 tabular-nums">
                                            Rp 0
                                        </div>
                                    @endif

                                    <div class="text-[11px] font-sans">
                                        @if($unitPrice > 0)
                                            <span class="text-gray-500 dark:text-gray-400 font-medium">
                                                @ <span class="font-mono tabular-nums font-bold text-gray-700 dark:text-gray-300">Rp {{ number_format($unitPrice, 0, ',', '.') }}</span> / {{ $item->unit ?: 'Pcs' }}
                                            </span>
                                            @if($item->hasWholesaleConversion())
                                                <div class="text-[10px] text-brand-600 dark:text-brand-400 font-medium tracking-tight">
                                                    ≈ Rp {{ number_format($item->wholesale_price, 0, ',', '.') }} / {{ $item->wholesale_unit }} ({{ format_quantity($item->conversion_factor) }} {{ $item->unit ?: 'Pcs' }})
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 italic text-[10px]">Harga belum diset</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @endif
                            <td class="whitespace-nowrap px-3.5 py-3 text-center">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    @if($isExpired)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-950 dark:text-rose-300" title="Kadaluarsa: {{ format_date($item->expiry_date) }}">
                                            <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Expired! ({{ format_date($item->expiry_date) }})</span>
                                        </span>
                                    @elseif($isExpiringSoon)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950 dark:text-amber-300" title="Sisa {{ $item->days_until_expiry }} hari">
                                            <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Near Expiry ({{ $item->days_until_expiry }} Hari)</span>
                                        </span>
                                    @elseif($isLow)
                                        <button type="button" 
                                            data-item="{{ $itemJson }}"
                                            @click="reorderItem($el)" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-600 text-white hover:bg-rose-700 border border-rose-600 shadow-xs transition cursor-pointer"
                                            title="Klik untuk membuat Formulir Permintaan Pengadaan Barang & Jasa (FPBJ)">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Re-Order!</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Stok Aman</span>
                                        </span>
                                    @endif

                                    @if($item->updated_at)
                                        <div class="inline-flex items-center gap-1 text-[10.5px] text-gray-500 dark:text-gray-400 font-medium" title="Terakhir diperbarui: {{ \Carbon\Carbon::parse($item->updated_at)->format('d M Y H:i:s') }}">
                                            <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="font-mono font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($item->updated_at)->format(system_time_format()) }}</span>
                                            <span class="text-gray-400 dark:text-gray-500">({{ format_date($item->updated_at) }})</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            @if($canManageWarehouse)
                                <td class="whitespace-nowrap px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit Button (Pencil Icon) -->
                                        <button type="button" 
                                            data-item="{{ $itemJson }}"
                                            @click="editItem($el)" 
                                            class="p-2 rounded-xl text-brand-700 bg-brand-50 hover:bg-brand-100 dark:text-brand-300 dark:bg-brand-950/50 dark:hover:bg-brand-900/60 border border-brand-200/80 dark:border-brand-800 transition cursor-pointer shadow-2xs" 
                                            title="Edit Data Barang & Satuan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- Print Barcode Button -->
                                        <a href="{{ route('items.barcode.print', $item->id) }}" target="_blank"
                                            class="p-2 rounded-xl text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition cursor-pointer shadow-2xs" 
                                            title="Cetak Label Barcode & QR Code">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                            </svg>
                                        </a>

                                        <!-- Delete Button (Trash Icon) -->
                                        <button type="button" 
                                            data-name="{{ $item->name }}"
                                            @click="confirmDelete('Apakah Anda yakin ingin menghapus barang ' + $el.getAttribute('data-name') + '?', () => $wire.delete({{ $item->id }}))" 
                                            class="p-2 rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-100 dark:text-rose-300 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800 transition cursor-pointer shadow-2xs" 
                                            title="Hapus Barang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            @php
                                $totalCols = 8 + ($canViewPrices ? 1 : 0) + ($canManageWarehouse ? 1 : 0);
                                $hasActiveFilters = !empty($search) || !empty($category_id) || !empty($unit_id) || !empty($stock_status);
                            @endphp
                            <td colspan="{{ $totalCols }}" class="px-6 py-14 text-center">
                                <div class="max-w-md mx-auto flex flex-col items-center justify-center text-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-3">
                                        @if($hasActiveFilters)
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        @else
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    @if($hasActiveFilters)
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak ada barang yang cocok dengan filter</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kata kunci pencarian atau kombinasi filter kategori/satuan tidak menemukan barang.</p>
                                        <div class="mt-4">
                                            <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reset Filter Pencarian</span>
                                            </button>
                                        </div>
                                    @else
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Katalog Master Barang Belum Tersedia</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sistem belum memiliki data stok barang material gudang yang tersimpan.</p>
                                        @if($canManageWarehouse)
                                            <div class="mt-4">
                                                <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700 text-xs font-bold shadow-xs transition cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span>Tambah Barang Baru</span>
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

        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Floating Multi-Item Selection Action Bar (Image 1) -->
    <div wire:ignore wire:key="floating-multi-item-action-bar-wrapper">
        <template x-teleport="body">
            <div x-show="selectedCount > 0" 
                x-cloak 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-8"
                class="fixed bottom-6 right-4 sm:right-6 z-[999990] bg-gray-950/95 text-white px-4 sm:px-5 py-3.5 sm:py-4 rounded-2xl shadow-2xl flex flex-wrap sm:flex-nowrap items-center gap-3 sm:gap-4 border border-rose-500/50 backdrop-blur-md max-w-[calc(100vw-2rem)]">
                
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-white font-black text-sm" x-text="selectedCount"></span>
                    <div>
                        <div class="text-xs font-black text-white whitespace-nowrap"><span x-text="selectedCount"></span> Item Berhasil Dipilih</div>
                        <div class="text-[10px] text-gray-300 whitespace-nowrap">Siap dicetak ke 1 Surat FPBJ</div>
                    </div>
                </div>

                <div class="hidden sm:block h-6 w-px bg-gray-700 shrink-0"></div>

                <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                    <button type="button" 
                        @click="triggerBulkFPBJ()" 
                        class="px-4 sm:px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-lg transition cursor-pointer flex items-center gap-2 shrink-0">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Cetak FPBJ (<span x-text="selectedCount"></span> Item)</span>
                    </button>

                    <button type="button" 
                        @click="window.open('{{ route('items.barcode.bulk') }}?ids=' + Object.keys(selectedMap).join(','), '_blank')" 
                        class="px-4 sm:px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-black shadow-lg transition cursor-pointer flex items-center gap-2 shrink-0"
                        title="Cetak Label Barcode & QR Code untuk barang terpilih">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Cetak Barcode (<span x-text="selectedCount"></span>)</span>
                    </button>

                    <button type="button" 
                        @click="clearSelection()" 
                        class="px-3 py-2 text-xs font-semibold text-gray-400 hover:text-white transition cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Barcode & QR Code Camera Scanner Modal -->
    <div x-show="openScannerModal" x-cloak
        wire:key="barcode-camera-scanner-modal"
        class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-sm animate-fadeIn"
        @touchmove.prevent
        @wheel.prevent
        role="dialog" aria-modal="true">
        
        <div class="relative w-full max-w-sm sm:max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-800/90 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-brand-600 text-white shadow-xs">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">Pindai Barcode / QR Code</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Arahkan kamera ponsel ke kode barang</p>
                    </div>
                </div>
                <button type="button" @click="closeScanner()" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Viewport Container -->
            <div class="p-4 flex flex-col items-center justify-center bg-gray-950 relative min-h-[300px]">
                
                <!-- Loading State -->
                <div x-show="isScannerLoading" class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gray-950/90 z-20 text-white">
                    <svg class="w-8 h-8 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-xs font-semibold">Mengaktifkan Kamera...</span>
                </div>

                <!-- Scanner Error Message -->
                <div x-show="scannerError" class="p-4 text-center text-rose-300 text-xs space-y-2">
                    <svg class="w-8 h-8 mx-auto text-amber-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="font-bold">Akses Kamera Tidak Tersedia</p>
                    <p class="text-[11px] text-gray-300" x-text="scannerError"></p>
                    <p class="text-[10px] text-gray-400">Pastikan browser diberikan izin kamera pada smartphone Anda.</p>
                </div>

                <!-- Camera Target / Video Container -->
                <div wire:ignore id="barcode-scanner-viewport" class="w-full rounded-2xl overflow-hidden shadow-inner bg-black"></div>

                <!-- Detected Code Success Banner -->
                <div x-show="scannerSuccessCode" x-cloak
                    class="absolute inset-x-4 bottom-6 z-30 p-3 rounded-2xl bg-emerald-600 text-white text-center shadow-lg">
                    <div class="text-xs font-bold inline-flex items-center gap-1 justify-center"><svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Berhasil Terdeteksi!</div>
                    <div class="font-mono text-sm font-extrabold" x-text="scannerSuccessCode"></div>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div class="p-3.5 bg-gray-50 dark:bg-gray-800/90 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between gap-2">
                <button type="button" @click="switchScannerCamera()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Ganti Kamera</span>
                </button>

                <button type="button" @click="closeScanner()" class="px-4 py-1.5 rounded-xl bg-gray-200 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-300 transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- QUICK BARCODE ACTION MODAL (Material Masuk / Material Keluar after Scan) -->
    @if($showQuickActionModal)
        <div id="quick-barcode-modal"
            wire:key="quick-barcode-modal-container"
            x-data="{
                activeStep: '{{ $quickActionType ?: 'menu' }}',
                init() {
                    document.body.classList.add('overflow-hidden');
                },
                destroy() {
                    document.body.classList.remove('overflow-hidden');
                    document.documentElement.classList.remove('overflow-hidden');
                },
                closeQuickModal() {
                    document.body.classList.remove('overflow-hidden');
                    document.documentElement.classList.remove('overflow-hidden');
                    $wire.closeQuickActionModal();
                }
            }"
            @quick-action-step-changed.window="
                let val = $event.detail;
                if (Array.isArray(val)) val = val[0];
                else if (typeof val === 'object' && val !== null && val.step) val = val.step;
                activeStep = String(val || 'menu');
            "
            class="fixed inset-0 z-[999999] flex items-end sm:items-center justify-center p-0 sm:p-4"
            role="dialog" aria-modal="true">
            
            <!-- Dark Backdrop (Static backdrop, touch & wheel prevented to lock background scroll completely) -->
            <div class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm transition-opacity"
                @touchmove.prevent
                @wheel.prevent></div>

            <!-- Modal Dialog Container -->
            <div class="relative z-10 w-full max-w-lg bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden max-h-[92vh] flex flex-col animate-fadeIn"
                @click.stop>
                
                <!-- Mobile drag handle -->
                <div class="sm:hidden w-12 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full mx-auto my-2.5 shrink-0"></div>

                <!-- Modal Header -->
                <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-800/90 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-xs">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0a2 2 0 100-4 2 2 0 000 4zm-8 4v-1m4-1a2 2 0 100-4 2 2 0 000 4zm-4-8a2 2 0 100-4 2 2 0 000 4zm4-4a2 2 0 100-4 2 2 0 000 4zm8 0a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-gray-900 dark:text-white leading-tight">
                                Transaksi Cepat Barcode
                            </h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Input Material Masuk / Keluar via Smartphone
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeQuickModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer" title="Tutup Modal">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body (Scrollable with overscroll-contain) -->
                <div class="p-5 overflow-y-auto space-y-4 overscroll-contain" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
                    
                    <!-- NOT FOUND STATE -->
                    <div x-show="activeStep === 'not_found' || activeStep[0] === 'not_found'" class="text-center py-6 space-y-4">
                        <div class="w-16 h-16 mx-auto rounded-3xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center shadow-sm">
                            <svg class="w-8 h-8 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-base font-extrabold text-gray-900 dark:text-white">Barcode Tidak Terdaftar</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Kode <span class="font-mono font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded">{{ $scannedBarcodeText }}</span> belum ada pada katalog data barang gudang.
                            </p>
                        </div>
                        <div class="pt-2 flex items-center justify-center gap-2">
                            <button type="button" @click="closeQuickModal(); openScanner()" 
                                class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Pindai Ulang</span>
                            </button>
                            <button type="button" @click="closeQuickModal()" 
                                class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>

                    <!-- SCANNED ITEM IDENTIFIED -->
                    @if($scannedItem)
                        <!-- Item Identity Highlight Card (Always visible on menu, in, out) -->
                        <div x-show="activeStep !== 'success' && activeStep !== 'not_found' && activeStep[0] !== 'success' && activeStep[0] !== 'not_found'" class="p-4 rounded-2xl border border-brand-200 dark:border-brand-900/60 bg-brand-50/40 dark:bg-brand-950/20 space-y-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                        @if($scannedItem->code_item)
                                             <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-extrabold bg-white dark:bg-gray-800 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800 shadow-2xs">
                                                {{ $scannedItem->code_item }}
                                            </span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                            {{ $scannedItem->category->name ?? 'Material' }}
                                        </span>
                                    </div>
                                    <h4 class="text-base font-black text-gray-900 dark:text-white leading-snug">
                                        {{ $scannedItem->name }}
                                    </h4>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Sisa Stok Fisik</span>
                                    <span class="font-mono text-lg font-black {{ $scannedItem->stock <= $scannedItem->getEffectiveMinStock($totalActiveTonnage) ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        {{ format_quantity($scannedItem->stock) }}
                                    </span>
                                    <span class="text-xs font-bold text-gray-500 uppercase">{{ $scannedItem->unit ?: 'Pcs' }}</span>
                                </div>
                            </div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 flex items-center justify-between gap-2 pt-1 border-t border-brand-100 dark:border-brand-900/40">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Lokasi: <strong class="text-brand-700 dark:text-brand-300">{{ $scannedItem->display_location }}</strong>
                                </span>
                                @if($scannedItem->storage_location === \App\Models\Item::STORAGE_2)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300">
                                        STORAGE 2 (Raw Material)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-800 dark:bg-gray-800 dark:text-gray-300">
                                        STORAGE 1 (Umum)
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- STATE 1: MENU PILIHAN (Material Masuk / Keluar) -->
                        <div x-show="activeStep === 'menu' || activeStep[0] === 'menu'" class="space-y-3 pt-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                Pilih Transaksi untuk Barang Ini:
                            </label>

                            <!-- Button Material Masuk -->
                            <button type="button" @click="activeStep = 'in'; $wire.selectQuickAction('in')"
                                class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-emerald-500/40 bg-emerald-50/70 hover:bg-emerald-100/80 dark:bg-emerald-950/30 dark:hover:bg-emerald-950/60 dark:border-emerald-800/70 text-left transition shadow-xs group cursor-pointer active:scale-98">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md group-hover:scale-105 transition">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-emerald-950 dark:text-emerald-300">
                                            Material Masuk (Inbound)
                                        </h4>
                                        <p class="text-xs text-emerald-800/80 dark:text-emerald-400/80 mt-0.5">
                                            Penerimaan stok baru dari vendor / supplier
                                        </p>
                                    </div>
                                </div>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold text-sm shadow-2xs group-hover:translate-x-1 transition">
                                    →
                                </span>
                            </button>

                            <!-- Button Material Keluar -->
                            <button type="button" @click="activeStep = 'out'; $wire.selectQuickAction('out')"
                                class="w-full flex items-center justify-between p-4 rounded-2xl border-2 border-sky-500/40 bg-sky-50/70 hover:bg-sky-100/80 dark:bg-sky-950/30 dark:hover:bg-sky-950/60 dark:border-sky-800/70 text-left transition shadow-xs group cursor-pointer active:scale-98">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-md group-hover:scale-105 transition">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-sky-950 dark:text-sky-300">
                                            Material Keluar (Outbound)
                                        </h4>
                                        <p class="text-xs text-sky-800/80 dark:text-sky-400/80 mt-0.5">
                                            Pengeluaran stok untuk project / fabrikasi
                                        </p>
                                    </div>
                                </div>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white dark:bg-sky-900/60 text-sky-700 dark:text-sky-300 font-bold text-sm shadow-2xs group-hover:translate-x-1 transition">
                                    →
                                </span>
                            </button>

                            <!-- Subtle search filter shortcut -->
                            <div class="pt-2 text-center">
                                <button type="button" @click="closeQuickModal()" wire:click="filterScannedItemInTable" class="text-xs text-gray-500 hover:text-brand-600 dark:hover:text-brand-400 font-medium hover:underline cursor-pointer">
                                    <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>Hanya lihat barang ini di tabel stok</span>
                                </button>
                            </div>
                        </div>

                        <!-- STATE 2: FORM MATERIAL MASUK -->
                        <div x-show="activeStep === 'in' || activeStep[0] === 'in'" class="space-y-3.5 animate-fadeIn">
                            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                <button type="button" @click="activeStep = 'menu'; $wire.backToQuickMenu()" class="text-xs font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1 cursor-pointer">
                                    <span>← Kembali</span>
                                </button>
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg><span>Form Material Masuk</span>
                                </span>
                            </div>

                            @if($quickErrorMessage)
                                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>{{ $quickErrorMessage }}</span>
                                </div>
                            @endif

                            <!-- 1. Jumlah Material Masuk (Quantity Stepper) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Jumlah Material Masuk <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="$wire.quickInQty = Math.max(0.1, parseFloat($wire.quickInQty || 1) - 1)" 
                                        class="w-11 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-white font-black text-lg flex items-center justify-center transition cursor-pointer active:scale-95">
                                        -
                                    </button>
                                    <div class="relative flex-1">
                                        <input type="number" min="0.001" step="any" wire:model="quickInQty" required
                                            class="w-full text-center rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 py-2.5 text-base font-black text-gray-900 dark:text-white focus:border-emerald-500 focus:outline-none">
                                    </div>
                                    <button type="button" @click="$wire.quickInQty = parseFloat($wire.quickInQty || 0) + 1" 
                                        class="w-11 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-white font-black text-lg flex items-center justify-center transition cursor-pointer active:scale-95">
                                        +
                                    </button>
                                    <span class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">
                                        {{ $scannedItem->unit ?: 'Pcs' }}
                                    </span>
                                </div>
                                <!-- Quick Quantity Pills -->
                                <div class="flex items-center gap-1.5 mt-2">
                                    <span class="text-[10.5px] text-gray-400 font-bold">Cepat:</span>
                                    <button type="button" @click="$wire.quickInQty = 1" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">1</button>
                                    <button type="button" @click="$wire.quickInQty = 5" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">5</button>
                                    <button type="button" @click="$wire.quickInQty = 10" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">10</button>
                                    <button type="button" @click="$wire.quickInQty = 50" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">50</button>
                                    <button type="button" @click="$wire.quickInQty = 100" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">100</button>
                                </div>
                            </div>

                            <!-- 2. Supplier Input -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Supplier / Vendor <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" wire:model="quickInSupplier" placeholder="Contoh: PT. Sumber Baja Abadi / Toko Makmur" required
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs font-medium text-gray-900 dark:text-white focus:border-emerald-500 focus:outline-none">
                            </div>

                            <!-- 3. Tanggal Penerimaan & 4. Diterima Oleh (Default: Warehouse) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Tanggal Penerimaan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" wire:model="quickInDate" required
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        Diterima Oleh <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" wire:model="quickInReceivedBy" placeholder="Default: Warehouse" required
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-gray-900 dark:text-white focus:border-emerald-500">
                                </div>
                            </div>

                            <!-- Catatan (Opsional) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan (Opsional)</label>
                                <input type="text" wire:model="quickInNotes" placeholder="No PO / Keterangan tambahan..."
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs text-gray-900 dark:text-white focus:border-emerald-500">
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-2 flex items-center gap-2">
                                <button type="button" @click="activeStep = 'menu'; $wire.backToQuickMenu()" 
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="button" wire:click="submitQuickMaterialIn" wire:loading.attr="disabled"
                                    class="flex-1 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-98">
                                    <span wire:loading.remove wire:target="submitQuickMaterialIn" class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Simpan Material Masuk</span>
                                    <span wire:loading wire:target="submitQuickMaterialIn" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        Menyimpan...
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- STATE 3: FORM MATERIAL KELUAR -->
                        <div x-show="activeStep === 'out' || activeStep[0] === 'out'" class="space-y-3.5 animate-fadeIn">
                            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                <button type="button" @click="activeStep = 'menu'; $wire.backToQuickMenu()" class="text-xs font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1 cursor-pointer">
                                    <span>← Kembali</span>
                                </button>
                                <span class="inline-flex items-center gap-1.5 text-xs font-black text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-950/60 px-2.5 py-1 rounded-full border border-brand-200 dark:border-brand-800">
                                    <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg><span>Form Material Keluar</span>
                                </span>
                            </div>

                            @if($quickErrorMessage)
                                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>{{ $quickErrorMessage }}</span>
                                </div>
                            @endif

                            <!-- 1. Jumlah Keluar (Quantity Stepper with Max Stock indicator) -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Jumlah Material Keluar <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[11px] font-semibold text-gray-500">
                                        Maks: <strong class="text-sky-600 dark:text-sky-400">{{ format_quantity($scannedItem->stock) }} {{ $scannedItem->unit ?: 'Pcs' }}</strong>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="$wire.quickOutQty = Math.max(0.1, parseFloat($wire.quickOutQty || 1) - 1)" 
                                        class="w-11 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-white font-black text-lg flex items-center justify-center transition cursor-pointer active:scale-95">
                                        -
                                    </button>
                                    <div class="relative flex-1">
                                        <input type="number" min="0.001" step="any" max="{{ $scannedItem->stock }}" wire:model="quickOutQty" required
                                            class="w-full text-center rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 py-2.5 text-base font-black text-gray-900 dark:text-white focus:border-sky-500 focus:outline-none">
                                    </div>
                                    <button type="button" @click="$wire.quickOutQty = Math.min({{ (float)$scannedItem->stock }}, parseFloat($wire.quickOutQty || 0) + 1)" 
                                        class="w-11 h-11 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-white font-black text-lg flex items-center justify-center transition cursor-pointer active:scale-95">
                                        +
                                    </button>
                                    <span class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">
                                        {{ $scannedItem->unit ?: 'Pcs' }}
                                    </span>
                                </div>
                                <!-- Quick Quantity Pills -->
                                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                    <span class="text-[10.5px] text-gray-400 font-bold">Cepat:</span>
                                    <button type="button" @click="$wire.quickOutQty = 1" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">1</button>
                                    @if($scannedItem->stock >= 5)
                                        <button type="button" @click="$wire.quickOutQty = 5" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">5</button>
                                    @endif
                                    @if($scannedItem->stock >= 10)
                                        <button type="button" @click="$wire.quickOutQty = 10" class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 cursor-pointer">10</button>
                                    @endif
                                    @if($scannedItem->stock > 0)
                                        <button type="button" @click="$wire.quickOutQty = {{ $scannedItem->stock }}" class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-sky-100 hover:bg-sky-200 dark:bg-sky-950 text-sky-800 dark:text-sky-300 cursor-pointer">Semua ({{ $scannedItem->stock }})</button>
                                    @endif
                                </div>
                            </div>

                            <!-- 2. Tanggal Pengeluaran (Default: hari ketika di-scan) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Pengeluaran <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" wire:model="quickOutDate" required
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-bold text-gray-900 dark:text-white focus:border-sky-500">
                            </div>

                            <!-- 3. Nama Project -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Project <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="quickOutProjectId" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs font-bold text-gray-900 dark:text-white focus:border-sky-500 focus:outline-none cursor-pointer">
                                    <option value="">-- Pilih Project Terdaftar atau Ketik Manual di Bawah --</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                                    @endforeach
                                </select>
                                <input type="text" wire:model="quickOutProjectName" placeholder="Atau ketik nama project manual jika tidak ada di daftar..."
                                    class="w-full mt-1.5 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 px-3.5 py-2 text-xs font-medium text-gray-900 dark:text-white focus:border-sky-500">
                            </div>

                            <!-- 4. Nama PIC / Penerima -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    Nama PIC / Penerima <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" wire:model="quickOutPic" placeholder="Nama PIC / Penerima di lapangan..." required
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-xs font-medium text-gray-900 dark:text-white focus:border-sky-500">
                            </div>

                            <!-- Catatan (Opsional) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan (Opsional)</label>
                                <input type="text" wire:model="quickOutNotes" placeholder="Keperluan material / catatan..."
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs text-gray-900 dark:text-white focus:border-sky-500">
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-2 flex items-center gap-2">
                                <button type="button" @click="activeStep = 'menu'; $wire.backToQuickMenu()" 
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="button" wire:click="submitQuickMaterialOut" wire:loading.attr="disabled"
                                    class="flex-1 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-extrabold transition shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-98">
                                    <span wire:loading.remove wire:target="submitQuickMaterialOut" class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Simpan Material Keluar</span>
                                    <span wire:loading wire:target="submitQuickMaterialOut" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        Menyimpan...
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- STATE 4: LAYAR SUKSES -->
                        <div x-show="activeStep === 'success' || activeStep[0] === 'success'" class="text-center py-5 space-y-4 animate-fadeIn">
                            <div class="w-16 h-16 mx-auto rounded-3xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shadow-md">
                                <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="space-y-1.5">
                                <h4 class="text-lg font-black text-gray-900 dark:text-white">Transaksi Berhasil Dicatat!</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-300 max-w-sm mx-auto leading-relaxed">
                                    {{ $quickSuccessMessage }}
                                </p>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs max-w-sm mx-auto">
                                <span class="font-bold text-gray-500">Stok Terbaru:</span>
                                <span class="font-mono font-black text-base text-brand-600 dark:text-brand-400">
                                    {{ format_quantity($scannedItem->fresh()->stock) }} {{ $scannedItem->unit ?: 'Pcs' }}
                                </span>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-2">
                                <button type="button" @click="activeStep = 'menu'; closeQuickModal(); openScanner()"
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold transition shadow-sm cursor-pointer flex items-center justify-center gap-2 active:scale-95">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Pindai Barang Lain</span>
                                </button>
                                <button type="button" @click="activeStep = 'menu'; closeQuickModal()"
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                                    Selesai & Tutup
                                </button>
                            </div>
                        </div>

                    @endif

                </div>
            </div>
        </div>
    @endif
    <!-- ========================================================================= -->
    <!-- MODAL RINCIAN HITUNGAN MINIMAL STOK PER PROJECT (AUTOMATIC CALCULATION)   -->
    <!-- ========================================================================= -->
    <div x-data="{
        openBreakdownModal: false,
        breakdown: {
            item_id: null,
            item_name: '',
            code_item: '',
            unit: '',
            has_requirement: false,
            requirement_per_ton: null,
            total_active_tonnage: 0,
            total_min_stock: 0,
            is_fallback: true,
            fallback_min_stock: 0,
            projects: []
        },
        openBreakdown(detail) {
            let data = detail;
            if (detail && detail.data !== undefined) {
                data = detail.data;
            } else if (Array.isArray(detail) && detail.length > 0) {
                data = detail[0];
            }
            if (data && typeof data === 'object') {
                this.breakdown = data;
                this.openBreakdownModal = true;
            }
        },
        closeBreakdown() {
            this.openBreakdownModal = false;
        }
    }" 
    x-on:show-min-stock-breakdown.window="openBreakdown($event.detail)"
    @keydown.escape.window="closeBreakdown()">
        <template x-teleport="body">
            <div x-show="openBreakdownModal" 
                x-cloak 
                class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn"
                @click.self="closeBreakdown()">
                
                <div class="relative w-full max-w-2xl rounded-3xl bg-white p-5 sm:p-6 shadow-2xl border border-gray-200 dark:border-gray-800 dark:bg-gray-900 animate-scaleUp max-h-[90vh] flex flex-col">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 border border-brand-200 dark:border-brand-800">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <span>Rincian Minimal Stok Material</span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Perhitungan otomatis stok minimum berdasarkan tonase seluruh project aktif
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="closeBreakdown()" class="p-1 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-200 dark:hover:bg-gray-800 transition cursor-pointer">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable) -->
                    <div class="overflow-y-auto space-y-4 py-4 pr-1">
                        <!-- Material Profile Bar -->
                        <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="font-mono text-xs font-bold text-brand-600 dark:text-brand-400 block" x-text="breakdown.code_item || '-'"></span>
                                <h4 class="text-sm font-black text-gray-900 dark:text-white" x-text="breakdown.item_name"></h4>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Status Perhitungan</span>
                                <template x-if="breakdown.has_requirement">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Otomatis Berbasis Project
                                    </span>
                                </template>
                                <template x-if="!breakdown.has_requirement">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800">
                                        Manual (Cadangan / Fallback)
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Calculation Formula Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Kebutuhan per Ton</span>
                                <div class="mt-1 flex items-baseline gap-1">
                                    <span class="font-mono text-lg font-black text-brand-600 dark:text-brand-400" x-text="breakdown.has_requirement ? breakdown.requirement_per_ton : '-'"></span>
                                    <span class="text-xs text-gray-500 font-semibold" x-text="breakdown.has_requirement ? (breakdown.unit + ' / Ton') : 'Belum diisi'"></span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Total Tonase Aktif</span>
                                <div class="mt-1 flex items-baseline gap-1">
                                    <span class="font-mono text-lg font-black text-gray-900 dark:text-white" x-text="breakdown.total_active_tonnage"></span>
                                    <span class="text-xs text-gray-500 font-semibold">Ton</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl border border-brand-200 dark:border-brand-800 bg-brand-50/40 dark:bg-brand-950/30">
                                <span class="text-[10px] uppercase font-bold text-brand-700 dark:text-brand-400 block">Minimal Stok Hasil</span>
                                <div class="mt-1 flex items-baseline gap-1">
                                    <span class="font-mono text-xl font-black text-brand-700 dark:text-brand-300" x-text="breakdown.total_min_stock"></span>
                                    <span class="text-xs text-brand-600 font-bold uppercase" x-text="breakdown.unit"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback Notice If Not Configured -->
                        <template x-if="!breakdown.has_requirement">
                            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200 space-y-1">
                                <div class="font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Material ini belum diatur kebutuhan per ton-nya</span>
                                </div>
                                <p class="text-[11px] leading-relaxed text-amber-700 dark:text-amber-300">
                                    Sistem saat ini menggunakan nilai stok minimum statis (manual) sebesar <strong class="font-mono font-bold" x-text="breakdown.fallback_min_stock + ' ' + breakdown.unit"></strong> sebagai cadangan. Untuk mengaktifkan hitungan otomatis, buka menu <strong>Edit</strong> barang ini dan isi field <strong>Kebutuhan per Ton</strong>.
                                </p>
                            </div>
                        </template>

                        <!-- Projects Table -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Daftar Project Aktif & Kontribusi Kebutuhan
                                </h5>
                                <span class="text-[11px] text-gray-400 font-medium" x-text="breakdown.projects.length + ' Project Aktif'"></span>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold uppercase">
                                        <tr>
                                            <th class="px-3 py-2 text-center w-10 border-r border-gray-200 dark:border-gray-700">NO</th>
                                            <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700">NAMA PROJECT</th>
                                            <th class="px-3 py-2 text-center w-24 border-r border-gray-200 dark:border-gray-700">STATUS</th>
                                            <th class="px-3 py-2 text-right w-28 border-r border-gray-200 dark:border-gray-700">TONASE</th>
                                            <th class="px-3 py-2 text-right w-36">KEBUTUHAN</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <template x-for="(proj, idx) in breakdown.projects" :key="proj.id">
                                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                                <td class="px-3 py-2 text-center font-bold text-gray-400 border-r border-gray-100 dark:border-gray-800" x-text="idx + 1"></td>
                                                <td class="px-3 py-2 border-r border-gray-100 dark:border-gray-800">
                                                    <div class="font-bold text-gray-900 dark:text-white" x-text="proj.name"></div>
                                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                                        <span class="font-mono" x-text="proj.code || '-'"></span>
                                                        <template x-if="proj.location">
                                                            <span class="text-indigo-600 dark:text-indigo-400" x-text="'• ' + proj.location"></span>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2 text-center border-r border-gray-100 dark:border-gray-800">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300" x-text="proj.status"></span>
                                                </td>
                                                <td class="px-3 py-2 text-right font-mono font-bold text-gray-800 dark:text-gray-200 border-r border-gray-100 dark:border-gray-800" x-text="proj.tonnage + ' Ton'"></td>
                                                <td class="px-3 py-2 text-right font-mono font-black text-brand-600 dark:text-brand-400">
                                                    <span x-text="breakdown.has_requirement ? (proj.requirement_qty + ' ' + breakdown.unit) : '-'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="breakdown.projects.length === 0">
                                            <tr>
                                                <td colspan="5" class="px-3 py-6 text-center text-gray-400 italic">
                                                    Tidak ada project aktif saat ini.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-800/80 font-bold border-t border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <td colspan="3" class="px-3 py-2.5 text-right uppercase text-[11px] text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                                                Total Akumulasi:
                                            </td>
                                            <td class="px-3 py-2.5 text-right font-mono text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700" x-text="breakdown.total_active_tonnage + ' Ton'"></td>
                                            <td class="px-3 py-2.5 text-right font-mono font-black text-brand-700 dark:text-brand-300" x-text="breakdown.total_min_stock + ' ' + breakdown.unit"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-end shrink-0">
                        <button type="button" @click="closeBreakdown()" class="px-5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

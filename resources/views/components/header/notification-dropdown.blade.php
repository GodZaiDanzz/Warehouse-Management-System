{{-- Real WMS Notification Dropdown Component --}}
@php
    $notificationData = \Illuminate\Support\Facades\Cache::remember('wms_header_notifications_data', 60, function () {
        $activeTonnage = \App\Models\Project::getTotalActiveTonnage();
        $criticalItems = \App\Models\Item::whereRaw('stock <= COALESCE(requirement_per_ton * ?, min_stock)', [$activeTonnage])
            ->with('category:id,name')
            ->select(['id', 'name', 'stock', 'unit', 'min_stock', 'requirement_per_ton', 'category_id'])
            ->orderBy('stock', 'asc')
            ->take(8)
            ->get();

        $criticalCount = \App\Models\Item::whereRaw('stock <= COALESCE(requirement_per_ton * ?, min_stock)', [$activeTonnage])->count();

        $recentOuts = \App\Models\MaterialOut::with('item:id,name')
            ->select(['id', 'reference_number', 'date', 'item_id', 'quantity', 'unit', 'project'])
            ->latest('date')
            ->latest('id')
            ->take(5)
            ->get();

        $recentIns = \App\Models\MaterialIn::with('item:id,name')
            ->select(['id', 'reference_number', 'date', 'item_id', 'quantity', 'unit', 'supplier'])
            ->latest('date')
            ->latest('id')
            ->take(5)
            ->get();

        return [
            'criticalItems' => $criticalItems,
            'criticalCount' => $criticalCount,
            'recentOuts' => $recentOuts,
            'recentIns' => $recentIns,
        ];
    });

    $criticalItems = $notificationData['criticalItems'];
    $criticalCount = $notificationData['criticalCount'];
    $recentOuts = $notificationData['recentOuts'];
    $recentIns = $notificationData['recentIns'];
    $totalBadgeCount = $criticalCount;
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    activeTab: 'critical',
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- Notification Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-gray-900 h-11 w-11 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white cursor-pointer"
        @click="toggleDropdown()"
        type="button"
        title="Notifikasi & Peringatan Stok"
    >
        <!-- Dynamic Notification Badge -->
        @if ($totalBadgeCount > 0)
            <span class="absolute -top-0.5 -right-0.5 z-10 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-black text-white shadow-xs ring-2 ring-white dark:ring-gray-900">
                {{ $totalBadgeCount > 99 ? '99+' : $totalBadgeCount }}
            </span>
        @endif

        <!-- Bell Icon -->
        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
        </svg>
    </button>

    <!-- Dropdown Modal / Popup -->
    <div
        x-show="dropdownOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute -right-2 mt-2 flex h-[520px] w-[360px] sm:w-[410px] flex-col rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-gray-800 dark:bg-gray-900 z-99999"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h5 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifikasi & Peringatan
                </h5>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">WMS PT Mudalaya Energy Indonesia</p>
            </div>

            <button @click="closeDropdown()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer" type="button">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Segmented Tab Navigation -->
        <div class="grid grid-cols-3 gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl mb-3 text-[11px] font-bold">
            <button
                type="button"
                @click="activeTab = 'critical'"
                :class="activeTab === 'critical' ? 'bg-white text-rose-600 dark:bg-gray-900 dark:text-rose-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                class="py-1.5 px-2 rounded-lg text-center transition cursor-pointer flex items-center justify-center gap-1"
            >
                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Warning</span>
                @if ($criticalCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 text-[10px] font-black">
                        {{ $criticalCount }}
                    </span>
                @endif
            </button>

            <button
                type="button"
                @click="activeTab = 'out'"
                :class="activeTab === 'out' ? 'bg-white text-brand-600 dark:bg-gray-900 dark:text-brand-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                class="py-1.5 px-2 rounded-lg text-center transition cursor-pointer flex items-center justify-center gap-1"
            >
                <svg class="w-3.5 h-3.5 text-brand-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
            </button>

            <button
                type="button"
                @click="activeTab = 'in'"
                :class="activeTab === 'in' ? 'bg-white text-emerald-600 dark:bg-gray-900 dark:text-emerald-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                class="py-1.5 px-2 rounded-lg text-center transition cursor-pointer flex items-center justify-center gap-1"
            >
                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Masuk
            </button>
        </div>

        <!-- List Content Section -->
        <div class="flex-1 overflow-y-auto custom-scrollbar space-y-2 pr-1">
            <!-- TAB 1: STOK KRITIS -->
            <div x-show="activeTab === 'critical'" class="space-y-2">
                @if ($criticalItems->isEmpty())
                    <div class="text-center py-10 px-4">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h6 class="text-xs font-bold text-gray-800 dark:text-white">Stok Gudang Aman!</h6>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Tidak ada item yang berada di bawah batas stok minimum saat ini.</p>
                    </div>
                @else
                    <div class="px-1 py-1 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                        Perlu Segera Di-Reorder (PPIC / Gudang):
                    </div>
                    @foreach ($criticalItems as $crit)
                        <a
                            href="{{ route('items.index', ['search' => $crit->name]) }}"
                            @click="closeDropdown()"
                            class="flex items-start gap-3 p-2.5 rounded-xl border border-rose-100 bg-rose-50/40 hover:bg-rose-50 dark:border-rose-950/60 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 transition block"
                        >
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <h6 class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $crit->name }}</h6>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase {{ $crit->stock <= 0 ? 'bg-rose-600 text-white' : 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300' }}">
                                        {{ $crit->stock <= 0 ? 'HABIS' : 'MENIPIS' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                    <span>{{ $crit->category->name ?? 'Barang' }}</span>
                                    <span>Sisa: <strong class="text-rose-600 dark:text-rose-400 font-mono font-black">{{ format_quantity($crit->stock) }} {{ $crit->unit }}</strong> (Min: {{ format_quantity($crit->effective_min_stock) }})</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- TAB 2: MATERIAL KELUAR -->
            <div x-show="activeTab === 'out'" class="space-y-2" style="display: none;">
                @if ($recentOuts->isEmpty())
                    <div class="text-center py-10 px-4 text-xs text-gray-400">
                        Belum ada riwayat material keluar.
                    </div>
                @else
                    <div class="px-1 py-1 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                        Pengeluaran Material Terbaru:
                    </div>
                    @foreach ($recentOuts as $out)
                        <a
                            href="{{ route('material-out.index', ['search' => $out->reference_number]) }}"
                            @click="closeDropdown()"
                            class="flex items-start gap-3 p-2.5 rounded-xl border border-gray-100 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:bg-gray-800/40 transition block"
                        >
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <h6 class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $out->item->name ?? 'Barang' }}</h6>
                                    <span class="font-mono text-xs font-black text-rose-600 dark:text-rose-400">
                                        -{{ format_quantity($out->quantity) }} {{ $out->unit }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="truncate flex items-center gap-1">
                                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $out->project }}
                                    </span>
                                    <span class="shrink-0 text-[10px] text-gray-400">{{ format_date($out->date, '') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- TAB 3: MATERIAL MASUK -->
            <div x-show="activeTab === 'in'" class="space-y-2" style="display: none;">
                @if ($recentIns->isEmpty())
                    <div class="text-center py-10 px-4 text-xs text-gray-400">
                        Belum ada riwayat material masuk.
                    </div>
                @else
                    <div class="px-1 py-1 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                        Penerimaan Material Terbaru:
                    </div>
                    @foreach ($recentIns as $in)
                        <a
                            href="{{ route('material-in.index', ['search' => $in->reference_number]) }}"
                            @click="closeDropdown()"
                            class="flex items-start gap-3 p-2.5 rounded-xl border border-gray-100 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:bg-gray-800/40 transition block"
                        >
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <h6 class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $in->item->name ?? 'Barang' }}</h6>
                                    <span class="font-mono text-xs font-black text-emerald-600 dark:text-emerald-400">
                                        +{{ format_quantity($in->quantity) }} {{ $in->unit }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="truncate">{{ $in->supplier }}</span>
                                    <span class="shrink-0 text-[10px] text-gray-400">{{ format_date($in->date, '') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="pt-3 mt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs">
            <a
                href="{{ route('items.index') }}"
                @click="closeDropdown()"
                class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 font-bold flex items-center gap-1"
            >
                Lihat Seluruh Stok Barang
            </a>
            @if (auth()->check() && auth()->user()->isAdmin())
                <a
                    href="{{ route('backup.index') }}"
                    @click="closeDropdown()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1"
                    title="Backup Database"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    Backup DB
                </a>
            @endif
        </div>
    </div>
</div>

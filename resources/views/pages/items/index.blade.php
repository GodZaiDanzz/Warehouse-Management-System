@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 space-y-6" x-data="{ 
        openImportModal: false, 
        openCreateModal: false,
        openEditModal: false,
        openPrModal: false,
        editItem: { id: null, code_item: '', name: '', brand: '', category_id: '', unit_id: '', stock: 0, unit: 'PCS', min_stock: 0, requirement_per_ton: null, unit_price: 0, storage: 'STORAGE 1', location: '', wholesale_unit: '', conversion_factor: 1, manufacturing_date: '', expiry_date: '', is_offcut: false, parent_item_id: null, dimension: '', origin_project: '', heat_number: '' },
        prData: {
            document_no: '095/FPBJ/MGI/MEI/IX/2026',
            required_date: '',
            notes_global: '',
            warehouse_user: '{{ auth()->user()->name ?? "Rafi Idzhad Rasyidin" }}',
            scm_manager: 'Nur Bakti',
            ops_director: 'Dedi Herdiyana',
            items: []
        }
    }"
    @trigger-edit-item.window="
        const detail = $event.detail || {};
        let initialStorage = detail.storage || 'STORAGE 1';
        let initialLoc = detail.location || '';
        if (!detail.storage) {
            initialStorage = (detail.category_id == 194 || (detail.code_item && (detail.code_item.startsWith('RAW') || detail.code_item.startsWith('OFF'))) || detail.is_offcut) ? 'STORAGE 2' : 'STORAGE 1';
        }
        editItem = Object.assign({}, detail, {
            storage: initialStorage,
            location: initialLoc,
            requirement_per_ton: (detail && detail.requirement_per_ton !== null && detail.requirement_per_ton !== undefined && detail.requirement_per_ton !== '') ? parseFloat(detail.requirement_per_ton) : null,
            conversion_factor: (detail && parseFloat(detail.conversion_factor) > 0) ? parseFloat(detail.conversion_factor) : 1,
            is_offcut: !!detail.is_offcut,
            parent_item_id: detail.parent_item_id || null,
            dimension: detail.dimension || '',
            origin_project: detail.origin_project || '',
            heat_number: detail.heat_number || ''
        });
        openEditModal = true;
    "
    @trigger-purchase-request.window="
        const item = $event.detail;
        prData.document_no = '095/FPBJ/MGI/MEI/IX/' + new Date().getFullYear();
        prData.items = [{
            id: item.id,
            code_item: item.code_item || item.code || '',
            name: item.name,
            stock: item.stock,
            unit: item.unit || 'Pcs',
            min_stock: item.min_stock,
            request_qty: Math.max(1, (item.min_stock * 2) - item.stock),
            notes: 'Digunakan untuk semua project'
        }];
        const reqDate = new Date();
        reqDate.setDate(reqDate.getDate() + 3);
        prData.required_date = reqDate.toISOString().split('T')[0];
        openPrModal = true;
    "
    @trigger-purchase-request-bulk.window="
        const items = $event.detail;
        if (!items || items.length === 0) return;
        prData.document_no = '095/FPBJ/MGI/MEI/IX/' + new Date().getFullYear();
        prData.items = items.map(i => ({
            id: i.id,
            code_item: i.code_item || i.code || '',
            name: i.name,
            stock: i.stock,
            unit: i.unit || 'Pcs',
            min_stock: i.min_stock,
            request_qty: i.request_qty || Math.max(1, (i.min_stock * 2) - i.stock),
            notes: i.notes || 'Digunakan untuk semua project'
        }));
        const reqDate = new Date();
        reqDate.setDate(reqDate.getDate() + 3);
        prData.required_date = reqDate.toISOString().split('T')[0];
        openPrModal = true;
    ">
    <!-- Page Header -->
    @php
        $currentUser = auth()->user();
        $canManage = $currentUser && $currentUser->canManageWarehouse();
        $canViewPrices = $currentUser && $currentUser->canViewPrices();
    @endphp
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Stok Barang Gudang</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Sistem Manajemen Stok Gudang - PT Mudalaya Energy Indonesia</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2.5">
            @if($canManage)
                <!-- Button Tambah Barang Baru (Primary Action) -->
                <button @click="openCreateModal = true" type="button" class="btn-add-theme inline-flex items-center justify-center gap-2 rounded-xl border border-brand-600 bg-brand-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-brand-700 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Barang</span>
                </button>

                <!-- Button Buka Modal Import Excel (Secondary Action) -->
                <button @click="openImportModal = true" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 transition hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Excel</span>
                </button>
            @else
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 text-xs font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                    Akses {{ $currentUser && $currentUser->isPpic() ? 'PPIC' : ($currentUser && $currentUser->isQc() ? 'QC' : ($currentUser && $currentUser->isFinance() ? 'Finance' : 'Pengguna')) }}: Mode Baca (Hanya Melihat Ketersediaan Stok)
                </div>
            @endif

            <!-- Button Export Excel with Category Filter Options -->
            <div class="relative" x-data="{ openExportMenu: false }" @click.away="openExportMenu = false">
                <button type="button" @click="openExportMenu = !openExportMenu" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-2.5 text-xs font-bold text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 shadow-2xs transition cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Excel</span>
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 transition-transform duration-200" :class="{ 'rotate-180': openExportMenu }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="openExportMenu" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                    class="absolute right-0 mt-2 w-64 rounded-2xl bg-white dark:bg-gray-900 p-2 shadow-xl border border-gray-200 dark:border-gray-800 z-50 text-xs">
                    
                    <div class="px-2.5 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Pilih Kategori Ekspor
                    </div>

                    <a href="{{ route('items.export') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition font-semibold">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Semua Kategori (Full)</span>
                        </div>
                    </a>

                    <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                    <a href="{{ route('items.export', ['category' => 'consumable']) }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <span>Consumable</span>
                    </a>

                    <a href="{{ route('items.export', ['category' => 'secondary']) }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <span>Secondary Part</span>
                    </a>

                    <a href="{{ route('items.export', ['category' => 'gas']) }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <span>Gas</span>
                    </a>

                    <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                    <!-- Cat Umbrella Options -->
                    <a href="{{ route('items.export', ['category' => 'cat']) }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-xl text-brand-700 dark:text-brand-300 bg-brand-50/50 dark:bg-brand-950/30 hover:bg-brand-100/70 dark:hover:bg-brand-900/50 transition font-bold">
                        <div class="flex items-center gap-1.5">
                            <span>Cat (Semua Varian Cat)</span>
                        </div>
                    </a>

                    <div class="pl-3.5 space-y-0.5 mt-0.5 border-l-2 border-brand-200 dark:border-brand-800/60 ml-2">
                        <a href="{{ route('items.export', ['category' => 'Cat - Primer']) }}" class="block px-2 py-1 rounded-lg text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-gray-800 text-[11px] transition">
                            Cat - Primer
                        </a>
                        <a href="{{ route('items.export', ['category' => 'Cat - Second']) }}" class="block px-2 py-1 rounded-lg text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-gray-800 text-[11px] transition">
                            Cat - Second
                        </a>
                        <a href="{{ route('items.export', ['category' => 'Cat - Top Coat']) }}" class="block px-2 py-1 rounded-lg text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-gray-800 text-[11px] transition">
                            Cat - Top Coat
                        </a>
                        <a href="{{ route('items.export', ['category' => 'Cat - Thinner']) }}" class="block px-2 py-1 rounded-lg text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-gray-50 dark:hover:bg-gray-800 text-[11px] transition">
                            Cat - Thinner
                        </a>
                    </div>

                    <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                    <div class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Lokasi Gudang
                    </div>

                    <a href="{{ route('items.export', ['storage' => 'STORAGE 1']) }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            <span>STORAGE 1 (Consumable, Part, Cat, Gas)</span>
                        </div>
                    </a>

                    <a href="{{ route('items.export', ['storage' => 'STORAGE 2']) }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>STORAGE 2 (Raw Material)</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Component -->
    <x-alert />

    @if($canManage)
        <!-- MODAL POPUP: IMPORT DATA BARANG EXCEL (TELEPORT TO BODY) -->
        <template x-teleport="body">
            <div x-show="openImportModal" x-cloak 
                class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-5 bg-gray-950/80 backdrop-blur-sm overflow-y-auto animate-fadeIn"
                style="z-index: 999999;"
                @keydown.escape.window="openImportModal = false"
                @click.self="openImportModal = false"
                role="dialog" aria-modal="true">

                <!-- Modal Dialog Box Container -->
                <div x-show="openImportModal" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    class="relative w-full max-w-xl max-h-[90vh] flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-800 overflow-hidden my-auto"
                    @click.stop>
                    
                    @livewire('item-import-component')
                </div>
            </div>
        </template>
    @endif

    <!-- Livewire Server-Side Data Table Component -->
    @livewire('item-table-component')

    @if($canManage)
    <!-- MODAL: TAMBAH BARANG BARU (BULK MULTI-INPUT & SINGLE TAB) -->
    <template x-teleport="body">
        <div x-show="openCreateModal" x-cloak 
            class="fixed inset-0 z-[999999] flex items-center justify-center p-2 sm:p-4 md:p-6 bg-gray-950/80 backdrop-blur-sm overflow-y-auto animate-fadeIn"
            style="z-index: 999999;"
            @keydown.escape.window="openCreateModal = false"
            @click.self="openCreateModal = false"
            role="dialog" aria-modal="true">

            <!-- Modal Dialog Container -->
            <div x-show="openCreateModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative w-full max-w-[96vw] 2xl:max-w-7xl max-h-[92vh] flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden my-auto"
                x-data="{
                    activeTab: 'bulk', // 'bulk' atau 'single'
                    submitting: false,
                    bulkCategory: '',
                    bulkUnit: 'PCS',
                    bulkMinStock: 5,
                    singleStorage: 'STORAGE 1',
                    singleLocation: '',
                    isOffcut: false,
                    singleCategoryChanged(e) {
                        const text = e.target.options[e.target.selectedIndex]?.text?.toLowerCase() || '';
                        if (text.includes('off cut') || text.includes('off-cut')) {
                            this.singleStorage = 'STORAGE 2';
                            this.isOffcut = true;
                        } else if (text.includes('raw')) {
                            this.singleStorage = 'STORAGE 2';
                            this.isOffcut = false;
                        } else {
                            this.singleStorage = 'STORAGE 1';
                            this.isOffcut = false;
                        }
                    },
                    rows: [
                        { code_item: '', category_id: '', name: '', brand: '', stock: 0, unit: 'PCS', min_stock: 5, unit_price: 0 },
                        { code_item: '', category_id: '', name: '', brand: '', stock: 0, unit: 'PCS', min_stock: 5, unit_price: 0 },
                        { code_item: '', category_id: '', name: '', brand: '', stock: 0, unit: 'PCS', min_stock: 5, unit_price: 0 },
                        { code_item: '', category_id: '', name: '', brand: '', stock: 0, unit: 'PCS', min_stock: 5, unit_price: 0 },
                        { code_item: '', category_id: '', name: '', brand: '', stock: 0, unit: 'PCS', min_stock: 5, unit_price: 0 },
                    ],
                    addRow() {
                        this.rows.push({ 
                            code_item: '',
                            category_id: this.bulkCategory || '', 
                            name: '', 
                            brand: '',
                            stock: 0, 
                            unit: this.bulkUnit || 'PCS', 
                            min_stock: this.bulkMinStock || 5,
                            unit_price: 0
                        });
                        this.$nextTick(() => {
                            if (this.$refs.tableContainer) {
                                this.$refs.tableContainer.scrollTop = this.$refs.tableContainer.scrollHeight;
                            }
                        });
                    },
                    addRows(count) {
                        for (let i = 0; i < count; i++) {
                            this.rows.push({ 
                                code_item: '',
                                category_id: this.bulkCategory || '', 
                                name: '', 
                                brand: '',
                                stock: 0, 
                                unit: this.bulkUnit || 'PCS', 
                                min_stock: this.bulkMinStock || 5,
                                unit_price: 0
                            });
                        }
                        this.$nextTick(() => {
                            if (this.$refs.tableContainer) {
                                this.$refs.tableContainer.scrollTop = this.$refs.tableContainer.scrollHeight;
                            }
                        });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        } else {
                            this.rows[0] = { code_item: '', category_id: '', name: '', brand: '', stock: 0, unit: this.bulkUnit || 'PCS', min_stock: 5, unit_price: 0 };
                        }
                    },
                    clearEmptyRows() {
                        const filled = this.rows.filter(r => r.name && r.name.trim() !== '');
                        this.rows = filled.length > 0 ? filled : [
                            { code_item: '', category_id: '', name: '', brand: '', stock: 0, unit: this.bulkUnit || 'PCS', min_stock: 5, unit_price: 0 }
                        ];
                    },
                    applyCategoryToAll() {
                        if (!this.bulkCategory) return;
                        this.rows.forEach(r => r.category_id = this.bulkCategory);
                    },
                    applyUnitToAll() {
                        if (!this.bulkUnit) return;
                        const targetUnit = this.bulkUnit.trim().toUpperCase();
                        this.rows.forEach(r => r.unit = targetUnit);
                    },
                    get filledCount() {
                        return this.rows.filter(r => r.name && r.name.trim() !== '').length;
                    }
                }">
                <form action="{{ route('items.store') }}" method="POST" @submit="submitting = true" class="flex flex-col h-full overflow-hidden">
                    @csrf
                    <!-- Modal Header -->
                    <div class="px-5 py-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white leading-tight">Tambah Data Barang Gudang</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Input langsung banyak baris data (format tabel cepat) atau gunakan form tunggal</p>
                            </div>
                        </div>

                        <!-- Mode Switcher Tabs & Close -->
                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl border border-gray-200/80 dark:border-gray-700/80">
                                <button type="button" @click="activeTab = 'bulk'" 
                                    :class="activeTab === 'bulk' ? 'bg-white dark:bg-gray-700 text-brand-700 dark:text-brand-300 shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 font-medium hover:text-gray-900'"
                                    class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5">
                                    <span>Input Banyak (Tabel)</span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300 font-bold" x-text="rows.length"></span>
                                </button>
                                <button type="button" @click="activeTab = 'single'" 
                                    :class="activeTab === 'single' ? 'bg-white dark:bg-gray-700 text-brand-700 dark:text-brand-300 shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 font-medium hover:text-gray-900'"
                                    class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                    Input 1 Barang
                                </button>
                            </div>

                            <button type="button" @click="openCreateModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer" title="Tutup (Esc)">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body (Scrollable) -->
                    <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 bg-white dark:bg-gray-900">
                        <!-- TAB 1: BULK MULTI-ROW GRID -->
                        <div x-show="activeTab === 'bulk'" class="space-y-3">
                            <!-- Quick Action Toolbar -->
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
                                <!-- Left: Row Adders -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white dark:bg-gray-700 px-3 py-1.5 text-xs font-bold text-gray-800 dark:text-white border border-gray-200 dark:border-gray-600 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                                        <span x-text="rows.length + ' Baris Aktif'"></span>
                                    </span>
                                    <div class="inline-flex rounded-lg shadow-2xs border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 p-0.5">
                                        <button type="button" @click="addRow()" class="px-2.5 py-1 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition cursor-pointer">
                                            + 1 Baris
                                        </button>
                                        <button type="button" @click="addRows(5)" class="px-2.5 py-1 text-xs font-bold text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-900/40 rounded-md transition cursor-pointer border-l border-gray-200 dark:border-gray-600">
                                            + 5 Baris
                                        </button>
                                        <button type="button" @click="addRows(10)" class="px-3 py-1 text-xs font-extrabold text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-900/30 hover:bg-brand-100 rounded-md transition cursor-pointer border-l border-brand-200 dark:border-brand-800">
                                            + 10 Baris Sekaligus
                                        </button>
                                    </div>
                                    <button type="button" @click="clearEmptyRows()" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition cursor-pointer" title="Hapus semua baris yang belum diisi">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Bersihkan Baris Kosong
                                    </button>
                                </div>

                                <!-- Right: Mass Set Helper -->
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="font-bold text-gray-600 dark:text-gray-400 shrink-0">Setel Cepat Semua Baris:</span>
                                    <div class="flex items-center gap-1.5">
                                        <select x-model="bulkCategory" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2.5 py-1 text-xs text-gray-800 dark:text-white outline-none focus:border-brand-500">
                                            <option value="">Pilih Kategori...</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" @click="applyCategoryToAll()" class="px-2.5 py-1 text-xs font-bold text-brand-700 dark:text-brand-300 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/40 dark:hover:bg-brand-900/60 rounded-lg border border-brand-200 dark:border-brand-700 transition cursor-pointer" title="Terapkan kategori ke seluruh baris">
                                            Set Kategori
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <select x-model="bulkUnit" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2.5 py-1 text-xs font-semibold text-gray-800 dark:text-white outline-none focus:border-brand-500 cursor-pointer">
                                            <option value="">Pilih Satuan...</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->symbol }}">{{ $u->symbol }} ({{ $u->name }})</option>
                                            @endforeach
                                        </select>
                                        <button type="button" @click="applyUnitToAll()" class="px-2.5 py-1 text-xs font-bold text-brand-700 dark:text-brand-300 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/40 dark:hover:bg-brand-900/60 rounded-lg border border-brand-200 dark:border-brand-700 transition cursor-pointer" title="Terapkan satuan ke seluruh baris">
                                            Set Satuan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Matrix Table Container -->
                            <div x-ref="tableContainer" class="max-h-[50vh] overflow-y-auto overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xs custom-scrollbar">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="sticky top-0 z-20 text-white font-bold tracking-wider" style="background-color: var(--theme-table-header-bg, #275538)">
                                        <tr>
                                            <th class="px-3 py-3 w-10 text-center">#</th>
                                            <th class="px-3 py-3 w-24 text-center">Kode</th>
                                            <th class="px-3 py-3 w-44">Kategori <span class="text-rose-300">*</span></th>
                                            <th class="px-3 py-3 w-32">Brand</th>
                                            <th class="px-3 py-3 w-72 max-w-sm">Nama Barang Lengkap <span class="text-rose-300">*</span></th>
                                            <th class="px-3 py-3 w-20 text-center">Stok Awal</th>
                                            <th class="px-3 py-3 w-32 min-w-[120px] text-center">Satuan</th>
                                            <th class="px-3 py-3 w-20 text-center">Stok Min</th>
                                            @if($canViewPrices)
                                            <th class="px-3 py-3 w-32 text-right">Harga Beli (Rp)</th>
                                            @endif
                                            <th class="px-2 py-3 w-12 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                        <template x-for="(row, index) in rows" :key="index">
                                            <tr class="hover:bg-brand-50/20 dark:hover:bg-brand-950/20 even:bg-gray-50/40 dark:even:bg-gray-800/30 transition">
                                                <td class="px-3 py-2 text-center font-bold text-gray-500 dark:text-gray-400" x-text="index + 1"></td>
                                                <td class="px-2 py-2 text-center w-24">
                                                    <input type="text" 
                                                        :name="'items[' + index + '][code_item]'" 
                                                        x-model="row.code_item" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        placeholder="Auto" 
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1.5 text-center font-mono text-xs uppercase text-gray-900 dark:text-white outline-none focus:border-brand-500">
                                                </td>
                                                <td class="px-2 py-2 w-44">
                                                    <select 
                                                        :name="'items[' + index + '][category_id]'" 
                                                        x-model="row.category_id" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1.5 text-xs text-gray-900 dark:text-white outline-none focus:border-brand-500 cursor-pointer">
                                                        <option value="">Pilih Kategori</option>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-2 py-2 w-32">
                                                    <input type="text" 
                                                        :name="'items[' + index + '][brand]'" 
                                                        x-model="row.brand" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        placeholder="Brand..." 
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1.5 text-xs text-gray-900 dark:text-white outline-none focus:border-brand-500">
                                                </td>
                                                <td class="px-2 py-2 w-72 max-w-sm">
                                                    <input type="text" 
                                                        :name="'items[' + index + '][name]'" 
                                                        x-model="row.name" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        placeholder="Nama barang..." 
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                                </td>
                                                <td class="px-2 py-2 text-center w-20">
                                                    <input type="number" 
                                                        :name="'items[' + index + '][stock]'" 
                                                        x-model.number="row.stock" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        min="0" 
                                                        step="any"
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1.5 text-center font-bold text-xs text-gray-900 dark:text-white outline-none focus:border-brand-500">
                                                </td>
                                                <td class="px-2 py-2 text-center w-32 min-w-[120px]">
                                                    <select 
                                                        :name="'items[' + index + '][unit]'" 
                                                        x-model="row.unit" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1.5 text-center text-xs text-gray-900 dark:text-white outline-none focus:border-brand-500 font-bold cursor-pointer">
                                                        @foreach($units as $u)
                                                            <option value="{{ $u->symbol }}">{{ $u->symbol }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-2 py-2 text-center w-20">
                                                    <input type="number" 
                                                        :name="'items[' + index + '][min_stock]'" 
                                                        x-model.number="row.min_stock" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        min="0" 
                                                        step="any"
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1.5 text-center font-bold text-xs text-gray-900 dark:text-white outline-none focus:border-brand-500">
                                                </td>
                                                @if($canViewPrices)
                                                <td class="px-2 py-2 text-right w-32">
                                                    <input type="number" 
                                                        :name="'items[' + index + '][unit_price]'" 
                                                        x-model.number="row.unit_price" 
                                                        :disabled="activeTab !== 'bulk'"
                                                        min="0" 
                                                        step="any"
                                                        placeholder="0" 
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2 py-1.5 text-right font-mono text-xs text-gray-900 dark:text-white outline-none focus:border-brand-500">
                                                </td>
                                                @endif
                                                <td class="px-2 py-2 text-center w-12">
                                                    <button type="button" @click="removeRow(index)" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus Baris Ini">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400 px-1 pt-1">
                                <span><strong>Tips:</strong> Tekan tombol <strong>Tab</strong> di keyboard untuk berpindah kolom & baris dengan cepat. Baris kosong akan otomatis dilewati saat disimpan.</span>
                                <button type="button" @click="addRow()" class="font-bold text-brand-600 dark:text-brand-400 hover:underline cursor-pointer self-end">
                                    + Tambah Baris Baru
                                </button>
                            </div>
                        </div>

                        <!-- TAB 2: SINGLE FORM INPUT -->
                        <div x-show="activeTab === 'single'" class="space-y-4 max-w-xl mx-auto py-2">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Kode Material <span class="text-xs font-normal text-gray-400">(Opsional - Otomatis dibuat jika dikosongkan)</span>
                                </label>
                                <input type="text" name="code_item" :disabled="activeTab !== 'single'" placeholder="Contoh: CSM-001 (Kosongkan untuk otomatis)" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-mono uppercase text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Kategori Barang <span class="text-rose-500">*</span></label>
                                <select name="category_id" :disabled="activeTab !== 'single'" @change="singleCategoryChanged($event)" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Lokasi Penyimpanan Gudang (Storage 1 / Storage 2) & Rak Detail -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Gudang Penyimpanan <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="relative flex items-center justify-center p-2 rounded-lg border cursor-pointer text-xs font-bold transition"
                                            :class="singleStorage === 'STORAGE 1' ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 ring-1 ring-brand-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50'">
                                            <input type="radio" name="storage" value="STORAGE 1" x-model="singleStorage" :disabled="activeTab !== 'single'" class="sr-only">
                                            <span>STORAGE 1</span>
                                        </label>
                                        <label class="relative flex items-center justify-center p-2 rounded-lg border cursor-pointer text-xs font-bold transition"
                                            :class="singleStorage === 'STORAGE 2' ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50'">
                                            <input type="radio" name="storage" value="STORAGE 2" x-model="singleStorage" :disabled="activeTab !== 'single'" class="sr-only">
                                            <span>STORAGE 2</span>
                                        </label>
                                    </div>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                        STORAGE 2 khusus Raw Material, STORAGE 1 untuk Consumable, Gas, Part, Cat, dll.
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Detail Lokasi / Rak <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="location" x-model="singleLocation" :disabled="activeTab !== 'single'" placeholder="Contoh: Rak A-02, Baris 3" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-brand-500">
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                        Keterangan nomor rak atau zona dalam gudang.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nama Barang <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" :disabled="activeTab !== 'single'" placeholder="Gate Valve 3 Inch Class 150" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Brand / Merk Cat <span class="text-xs font-normal text-gray-400">(Khusus Cat, contoh: Novalux, International, Jotun)</span>
                                </label>
                                <input type="text" name="brand" :disabled="activeTab !== 'single'" placeholder="Contoh: Novalux, International, Jotun..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                            </div>

                            <!-- Off-Cut Remnant Details (Single Form) -->
                            <div x-show="isOffcut" x-cloak class="rounded-xl p-3.5 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800/80 space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-amber-200 text-amber-900 font-extrabold uppercase tracking-wider">OFF-CUT REMNANT</span>
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-300">Spesifikasi Potongan Sisa Material</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Dimensi Sisa <span class="text-rose-500">*</span></label>
                                        <input type="text" name="dimension" :disabled="activeTab !== 'single' || !isOffcut" placeholder="Contoh: P: 2.4m, Tebal: 12mm" class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-gray-900 dark:text-white focus:border-amber-500">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Heat No / Mill Cert</label>
                                        <input type="text" name="heat_number" :disabled="activeTab !== 'single' || !isOffcut" placeholder="Contoh: HT-88291A" class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-mono text-gray-900 dark:text-white focus:border-amber-500">
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Asal Proyek Pemotongan</label>
                                    <input type="text" name="origin_project" :disabled="activeTab !== 'single' || !isOffcut" placeholder="Contoh: Tangki Pertamina RU VI" class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-amber-500">
                                </div>
                                <input type="hidden" name="is_offcut" :value="isOffcut ? '1' : '0'" :disabled="activeTab !== 'single'">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Stok Awal <span class="text-rose-500">*</span></label>
                                    <input type="number" name="stock" :disabled="activeTab !== 'single'" value="0" min="0" step="any" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Satuan Terkecil <span class="text-rose-500">*</span></label>
                                    <select name="unit_id" :disabled="activeTab !== 'single'" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                                        <option value="">Pilih Satuan</option>
                                        @foreach($units as $u)
                                            <option value="{{ $u->id }}" {{ strtoupper($u->symbol) === 'PCS' ? 'selected' : '' }}>{{ $u->symbol }} ({{ $u->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Pengaturan Minimal Stok (Otomatis & Fallback) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-brand-50/40 dark:bg-brand-950/20 border border-brand-200 dark:border-brand-900/40 rounded-xl">
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Kebutuhan per Ton
                                        <span class="text-xs font-normal text-gray-400">(Auto Min. Stok)</span>
                                    </label>
                                    <input type="number" name="requirement_per_ton" :disabled="activeTab !== 'single'" min="0" step="any" placeholder="Contoh: 2 (atau kosongkan)" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 font-mono">
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Jika diisi, minimal stok otomatis dihitung dari tonase project aktif.</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Stok Min. Cadangan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number" name="min_stock" :disabled="activeTab !== 'single'" value="20" min="0" step="any" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 font-mono">
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Fallback jika kebutuhan per ton belum diisi.</p>
                                </div>
                            </div>

                            <div class="space-y-2 rounded-xl p-3 bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700"
                                x-data="{
                                    showCalc: false,
                                    packPrice: '',
                                    packQty: '',
                                    packUnit: 'Pail',
                                    applyCalc() {
                                        const price = parseFloat(this.packPrice);
                                        const qty = parseFloat(this.packQty);
                                        if (!isNaN(price) && !isNaN(qty) && qty > 0) {
                                            const perUnit = Math.round((price / qty) * 100) / 100;
                                            $refs.singleUnitPriceInput.value = perUnit;
                                            const wu = document.querySelector('input[name=wholesale_unit]');
                                            const cf = document.querySelector('input[name=conversion_factor]');
                                            if (wu && !wu.value && this.packUnit) wu.value = this.packUnit;
                                            if (cf && (!cf.value || cf.value == '1')) cf.value = qty;
                                            this.showCalc = false;
                                        }
                                    }
                                }">
                                @if($canViewPrices)
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Harga Satuan Beli / Unit Cost (Rp)
                                    </label>
                                    <button type="button" @click="showCalc = !showCalc" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:underline cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span x-text="showCalc ? 'Tutup Kalkulator' : 'Kalkulator Beli Kemasan'"></span>
                                    </button>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-gray-500">Rp</span>
                                    <input type="number" 
                                        x-ref="singleUnitPriceInput"
                                        name="unit_price" 
                                        :disabled="activeTab !== 'single'" 
                                        min="0" 
                                        step="any" 
                                        placeholder="0 (Harga per satuan terkecil)" 
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 pl-10 pr-3 py-2 text-sm font-mono text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Disimpan sebagai harga per 1 satuan terkecil (misal per KG/Ltr/Pcs) agar valuasi aset persediaan akurat.
                                </p>

                                <!-- Expandable Package Pricing Calculator -->
                                <div x-show="showCalc" x-cloak class="pt-2 border-t border-gray-200 dark:border-gray-700/80 space-y-2.5">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-brand-700 dark:text-brand-300">
                                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Kalkulator Konversi Beli Kemasan / Grosir</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-medium">Harga Faktur Kemasan (Rp)</label>
                                            <input type="number" x-model.number="packPrice" placeholder="Contoh: 200000" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs font-mono text-gray-900 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-medium">Satuan Kemasan</label>
                                            <input type="text" x-model="packUnit" placeholder="Pail, Sak, Dus" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-medium">Isi / Bobot Kemasan</label>
                                            <input type="number" x-model.number="packQty" min="0.001" step="any" placeholder="Contoh: 5 (untuk 5 KG)" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs font-mono text-gray-900 dark:text-white">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 pt-1">
                                        <div class="text-[11px] text-gray-600 dark:text-gray-300">
                                            <template x-if="packPrice > 0 && packQty > 0">
                                                <span>
                                                    Hasil: <strong class="text-emerald-600 dark:text-emerald-400 font-mono">Rp <span x-text="Math.round((packPrice / packQty) * 100) / 100"></span></strong> / satuan terkecil.
                                                </span>
                                            </template>
                                            <template x-if="!packPrice || !packQty">
                                                <span class="text-gray-400 italic">Ketik harga kemasan dan bobot di atas.</span>
                                            </template>
                                        </div>
                                        <button type="button" @click="applyCalc()" :disabled="!packPrice || !packQty || packQty <= 0" class="px-3 py-1 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white font-bold text-xs transition cursor-pointer">
                                            Terapkan ke Harga
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Khusus Cat / Painting: Tanggal Pembuatan (Manufacturing Date) -->
                            <div class="rounded-xl p-3 bg-brand-50/50 dark:bg-brand-950/20 border border-brand-200 dark:border-brand-800/60 space-y-2"
                                x-data="{
                                    fp: null,
                                    initPicker() {
                                        if (typeof flatpickr === 'undefined') {
                                            setTimeout(() => this.initPicker(), 100);
                                            return;
                                        }
                                        if (this.fp) return;
                                        this.fp = flatpickr(this.$refs.createMfgDateInput, {
                                            dateFormat: 'Y-m-d',
                                            altInput: true,
                                            altFormat: 'd/m/Y',
                                            allowInput: true,
                                            monthSelectorType: 'dropdown',
                                            altInputClass: 'w-full rounded-xl border border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer shadow-2xs',
                                            onChange: (dates, dateStr) => {
                                                $refs.createMfgDateInput.value = dateStr;
                                            }
                                        });
                                    }
                                }"
                                x-init="$nextTick(() => initPicker())">
                                <label class="block text-xs font-bold text-brand-900 dark:text-brand-300 flex items-center justify-between cursor-pointer" @click.prevent="fp && fp.open()">
                                    <span>Tanggal Pembuatan (Khusus Kategori Cat Wajib Diisi)</span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 dark:text-brand-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Pilih Tanggal (d/m/Y)</span>
                                    </span>
                                </label>
                                <div class="relative cursor-pointer">
                                    <input type="text" 
                                        x-ref="createMfgDateInput"
                                        name="manufacturing_date" 
                                        :disabled="activeTab !== 'single'" 
                                        placeholder="Pilih Tanggal / Bulan / Tahun..." 
                                        class="w-full rounded-xl border border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer">
                                </div>
                                <p class="text-[11px] text-brand-700 dark:text-brand-300">Mencatat masa produksi/batch cat untuk pemantauan masa simpan.</p>
                            </div>

                            <!-- Tanggal Kadaluarsa (Expiry Date) -->
                            <div class="rounded-xl p-3 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 space-y-2"
                                x-data="{
                                    fpExp: null,
                                    initPicker() {
                                        if (typeof flatpickr === 'undefined') {
                                            setTimeout(() => this.initPicker(), 100);
                                            return;
                                        }
                                        if (this.fpExp) return;
                                        this.fpExp = flatpickr(this.$refs.createExpDateInput, {
                                            dateFormat: 'Y-m-d',
                                            altInput: true,
                                            altFormat: 'd/m/Y',
                                            allowInput: true,
                                            monthSelectorType: 'dropdown',
                                            altInputClass: 'w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-amber-500 cursor-pointer shadow-2xs',
                                            onChange: (dates, dateStr) => {
                                                $refs.createExpDateInput.value = dateStr;
                                            }
                                        });
                                    }
                                }"
                                x-init="$nextTick(() => initPicker())">
                                <label class="block text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center justify-between cursor-pointer" @click.prevent="fpExp && fpExp.open()">
                                    <span>Tanggal Kadaluarsa (Expiry Date - Cat / Kimia)</span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Pilih Tanggal (d/m/Y)</span>
                                    </span>
                                </label>
                                <div class="relative cursor-pointer">
                                    <input type="text" 
                                        x-ref="createExpDateInput"
                                        name="expiry_date" 
                                        :disabled="activeTab !== 'single'" 
                                        placeholder="Pilih Tanggal Kadaluarsa..." 
                                        class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-amber-500 cursor-pointer">
                                </div>
                                <p class="text-[11px] text-amber-700 dark:text-amber-300">Peringatan otomatis akan aktif jika sisa masa simpan mendekati atau melewati tanggal ini.</p>
                            </div>

                            <!-- Konversi Satuan (Unit of Measure) -->
                            <div class="rounded-xl p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-2">
                                <label class="block text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    Fitur Konversi Satuan Pembelian Grosir (Opsional)
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] text-gray-500">Satuan Grosir (Pembelian)</label>
                                        <input type="text" name="wholesale_unit" :disabled="activeTab !== 'single'" placeholder="Contoh: Box, Dus, Lusin, Pail" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500">Isi per Satuan Grosir</label>
                                        <input type="number" name="conversion_factor" :disabled="activeTab !== 'single'" min="1" placeholder="Contoh: 50 (isi 50 pcs)" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Saat penginputan barang masuk/keluar, tim gudang bisa langsung menginput dalam satuan Box atau Pcs.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 dark:bg-gray-800/90 px-5 py-3.5 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200 dark:border-gray-800 shrink-0">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <span x-show="activeTab === 'bulk'">
                                Terisi: <strong class="text-brand-600 dark:text-brand-400" x-text="filledCount"></strong> dari <span x-text="rows.length"></span> baris data.
                            </span>
                            <span x-show="activeTab === 'single'">Mode Form Tunggal (1 Barang)</span>
                        </div>
                        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                            <button type="button" @click="openCreateModal = false" class="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" :disabled="submitting" class="btn-save-theme inline-flex justify-center items-center gap-2 rounded-xl bg-brand-600 px-6 py-2 text-xs font-bold text-white shadow-md hover:bg-brand-700 disabled:opacity-75 transition cursor-pointer">
                                <template x-if="submitting"><x-spinner /></template>
                                <span x-show="!submitting" x-text="activeTab === 'bulk' ? (filledCount > 0 ? 'Simpan ' + filledCount + ' Barang Sekaligus' : 'Simpan Semua Barang') : 'Simpan Barang'"></span>
                                <span x-show="submitting">Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
    <!-- MODAL EDIT BARANG & SATUAN -->
    <template x-teleport="body">
        <div x-show="openEditModal" x-cloak 
            class="fixed inset-0 z-[999999] flex items-center justify-center p-2 sm:p-4 bg-gray-950/80 backdrop-blur-sm overflow-y-auto animate-fadeIn"
            style="z-index: 999999;"
            @click.self="openEditModal = false"
            @keydown.escape.window="openEditModal = false"
            role="dialog" aria-modal="true">

            <!-- Modal Content -->
            <div x-show="openEditModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative w-full max-w-xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden my-auto"
                x-data="{ submittingEdit: false }">

                <form :action="'/items/' + editItem.id" method="POST" @submit="submittingEdit = true">
                    @csrf
                    @method('PUT')

                    <!-- Header -->
                    <div class="px-5 py-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white leading-tight">Edit Data & Satuan Barang</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Ubah nama, kategori, stok, atau satuan barang</p>
                            </div>
                        </div>
                        <button type="button" @click="openEditModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto bg-white dark:bg-gray-900">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Kode Material</label>
                            <input type="text" name="code_item" x-model="editItem.code_item" placeholder="Contoh: CSM-001" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-mono uppercase text-gray-900 dark:text-white focus:border-brand-500">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Kategori Barang <span class="text-rose-500">*</span></label>
                            <select name="category_id" x-model="editItem.category_id" @change="
                                const text = $event.target.options[$event.target.selectedIndex]?.text?.toLowerCase() || '';
                                if (text.includes('off cut') || text.includes('off-cut')) {
                                    editItem.storage = 'STORAGE 2';
                                    editItem.is_offcut = true;
                                } else if (text.includes('raw')) {
                                    editItem.storage = 'STORAGE 2';
                                } else {
                                    editItem.storage = 'STORAGE 1';
                                }
                            " required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lokasi Penyimpanan Gudang (Storage 1 / Storage 2) & Rak Detail -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Gudang Penyimpanan <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="relative flex items-center justify-center p-2 rounded-lg border cursor-pointer text-xs font-bold transition"
                                        :class="editItem.storage === 'STORAGE 2' ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50' : 'border-brand-500 bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 ring-1 ring-brand-500'">
                                        <input type="radio" name="storage" value="STORAGE 1" x-model="editItem.storage" class="sr-only">
                                        <span>STORAGE 1</span>
                                    </label>
                                    <label class="relative flex items-center justify-center p-2 rounded-lg border cursor-pointer text-xs font-bold transition"
                                        :class="editItem.storage === 'STORAGE 2' ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50'">
                                        <input type="radio" name="storage" value="STORAGE 2" x-model="editItem.storage" class="sr-only">
                                        <span>STORAGE 2</span>
                                    </label>
                                </div>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    STORAGE 2 khusus Raw Material, STORAGE 1 untuk material lainnya.
                                </p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Detail Lokasi / Rak <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                                </label>
                                <input type="text" name="location" x-model="editItem.location" placeholder="Contoh: Rak A-02, Baris 3" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-brand-500">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Keterangan nomor rak atau zona dalam gudang.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editItem.name" required placeholder="Nama barang..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Brand / Merk Cat <span class="text-xs font-normal text-gray-400">(Khusus Cat, contoh: Novalux, International, Jotun)</span>
                            </label>
                            <input type="text" name="brand" x-model="editItem.brand" placeholder="Contoh: Novalux, International, Jotun..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>

                        <!-- Off-Cut Remnant Details (Edit Modal) -->
                        <div x-show="editItem.is_offcut" x-cloak class="rounded-xl p-3.5 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-amber-200 text-amber-900 font-extrabold uppercase tracking-wider">OFF-CUT REMNANT</span>
                                    <span class="text-xs font-bold text-amber-900 dark:text-amber-300">Spesifikasi Potongan Sisa Material</span>
                                </div>
                                <template x-if="editItem.parent_item && editItem.parent_item.name">
                                    <span class="text-[11px] text-amber-700 dark:text-amber-400 font-medium truncate max-w-xs" :title="'Material Induk: ' + editItem.parent_item.name">
                                        Induk: <strong x-text="editItem.parent_item.name"></strong>
                                    </span>
                                </template>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Dimensi Sisa <span class="text-rose-500">*</span></label>
                                    <input type="text" name="dimension" x-model="editItem.dimension" placeholder="Contoh: P: 2.4m, Tebal: 12mm" class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-gray-900 dark:text-white focus:border-amber-500">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Heat No / Mill Cert</label>
                                    <input type="text" name="heat_number" x-model="editItem.heat_number" placeholder="Contoh: HT-88291A" class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-mono text-gray-900 dark:text-white focus:border-amber-500">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Asal Proyek Pemotongan</label>
                                <input type="text" name="origin_project" x-model="editItem.origin_project" placeholder="Contoh: Tangki Pertamina RU VI" class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-amber-500">
                            </div>
                            <input type="hidden" name="is_offcut" :value="editItem.is_offcut ? '1' : '0'">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Jumlah Stok <span class="text-rose-500">*</span></label>
                                <input type="number" name="stock" x-model.number="editItem.stock" required min="0" step="any" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 font-bold">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Satuan Terkecil <span class="text-rose-500">*</span></label>
                                <select name="unit_id" x-model="editItem.unit_id" required class="w-full rounded-xl border border-brand-300 dark:border-brand-700 bg-brand-50/30 dark:bg-gray-800 px-3 py-2 text-sm font-bold text-brand-700 dark:text-brand-400 focus:border-brand-500">
                                    <option value="">Pilih Satuan</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->symbol }} ({{ $u->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Pengaturan Minimal Stok (Otomatis & Fallback) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-brand-50/40 dark:bg-brand-950/20 border border-brand-200 dark:border-brand-900/40 rounded-xl">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Kebutuhan per Ton
                                    <span class="text-xs font-normal text-gray-400">(Auto Min. Stok)</span>
                                </label>
                                <input type="number" name="requirement_per_ton" x-model.number="editItem.requirement_per_ton" min="0" step="any" placeholder="Belum diisi (kosong)" class="w-full rounded-xl border border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-brand-700 dark:text-brand-400 focus:border-brand-500 font-mono">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Jika diisi, minimal stok otomatis dihitung dari tonase project aktif.</p>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Stok Min. Cadangan <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="min_stock" x-model.number="editItem.min_stock" required min="0" step="any" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 font-mono">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Fallback jika kebutuhan per ton belum diisi.</p>
                            </div>
                        </div>

                        @if($canViewPrices)
                        <div class="space-y-2 rounded-xl p-3 bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700"
                            x-data="{
                                showCalc: false,
                                packPrice: '',
                                packQty: '',
                                packUnit: '',
                                initValues() {
                                    if (editItem.conversion_factor > 1) {
                                        this.packQty = editItem.conversion_factor;
                                        this.packUnit = editItem.wholesale_unit || 'Pail';
                                        if (editItem.unit_price > 0) {
                                            this.packPrice = Math.round(editItem.unit_price * editItem.conversion_factor);
                                        }
                                    }
                                },
                                applyCalc() {
                                    const price = parseFloat(this.packPrice);
                                    const qty = parseFloat(this.packQty);
                                    if (!isNaN(price) && !isNaN(qty) && qty > 0) {
                                        editItem.unit_price = Math.round((price / qty) * 100) / 100;
                                        if (this.packUnit) editItem.wholesale_unit = this.packUnit;
                                        editItem.conversion_factor = qty;
                                        this.showCalc = false;
                                    }
                                }
                            }"
                            x-effect="if (openEditModal) initValues()">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Harga Satuan Beli / Unit Cost (Rp)
                                </label>
                                <button type="button" @click="showCalc = !showCalc" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:underline cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span x-text="showCalc ? 'Tutup Kalkulator' : 'Kalkulator Beli Kemasan'"></span>
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-gray-500">Rp</span>
                                <input type="number" name="unit_price" x-model.number="editItem.unit_price" min="0" step="any" placeholder="0 (Harga per satuan terkecil)" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 pl-10 pr-3 py-2 text-sm font-mono text-gray-900 dark:text-white focus:border-brand-500">
                            </div>

                            <template x-if="editItem.conversion_factor > 1 && editItem.unit_price > 0">
                                <div class="text-[11px] text-brand-600 dark:text-brand-400 font-medium">
                                    Ekuivalen Kemasan: ≈ <strong class="font-mono">Rp <span x-text="Math.round(editItem.unit_price * editItem.conversion_factor).toLocaleString('id-ID')"></span></strong> per <span x-text="editItem.wholesale_unit || 'Kemasan'"></span> (<span x-text="editItem.conversion_factor"></span> <span x-text="editItem.unit"></span>).
                                </div>
                            </template>

                            <!-- Expandable Package Pricing Calculator -->
                            <div x-show="showCalc" x-cloak class="pt-2 border-t border-gray-200 dark:border-gray-700/80 space-y-2.5">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-brand-700 dark:text-brand-300">
                                    <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Kalkulator Konversi Beli Kemasan / Grosir</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <div>
                                        <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-medium">Harga Faktur Kemasan (Rp)</label>
                                        <input type="number" x-model.number="packPrice" placeholder="Contoh: 200000" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs font-mono text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-medium">Satuan Kemasan</label>
                                        <input type="text" x-model="packUnit" placeholder="Pail, Sak, Dus" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-medium">Isi / Bobot Kemasan</label>
                                        <input type="number" x-model.number="packQty" min="0.001" step="any" placeholder="Contoh: 5" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs font-mono text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <div class="text-[11px] text-gray-600 dark:text-gray-300">
                                        <template x-if="packPrice > 0 && packQty > 0">
                                            <span>
                                                Hasil: <strong class="text-emerald-600 dark:text-emerald-400 font-mono">Rp <span x-text="Math.round((packPrice / packQty) * 100) / 100"></span></strong> / <span x-text="editItem.unit"></span>.
                                            </span>
                                        </template>
                                    </div>
                                    <button type="button" @click="applyCalc()" :disabled="!packPrice || !packQty || packQty <= 0" class="px-3 py-1 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white font-bold text-xs transition cursor-pointer">
                                        Terapkan ke Harga
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Khusus Cat: Manufacturing Date -->
                        <div class="rounded-xl p-3 bg-brand-50/50 dark:bg-brand-950/20 border border-brand-200 dark:border-brand-800/60 space-y-2"
                            x-data="{
                                fp: null,
                                initPicker() {
                                    if (typeof flatpickr === 'undefined') {
                                        setTimeout(() => this.initPicker(), 100);
                                        return;
                                    }
                                    if (this.fp) return;
                                    this.fp = flatpickr(this.$refs.editMfgDateInput, {
                                        dateFormat: 'Y-m-d',
                                        altInput: true,
                                        altFormat: 'd/m/Y',
                                        allowInput: true,
                                        monthSelectorType: 'dropdown',
                                        altInputClass: 'w-full rounded-xl border border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer shadow-2xs',
                                        defaultDate: editItem.manufacturing_date || null,
                                        onChange: (dates, dateStr) => {
                                            editItem.manufacturing_date = dateStr;
                                        }
                                    });
                                }
                            }"
                            x-init="$nextTick(() => initPicker())"
                            x-effect="if (fp && openEditModal && (editItem.manufacturing_date || '') !== (fp.input ? fp.input.value : '')) { fp.setDate(editItem.manufacturing_date || '', false); }">
                            <label class="block text-xs font-bold text-brand-900 dark:text-brand-300 flex items-center justify-between cursor-pointer" @click.prevent="fp && fp.open()">
                                <span>Tanggal Pembuatan (Khusus Kategori Cat)</span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 dark:text-brand-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Pilih Tanggal (d/m/Y)</span>
                                </span>
                            </label>
                            <div class="relative cursor-pointer">
                                <input type="text" 
                                    x-ref="editMfgDateInput"
                                    name="manufacturing_date" 
                                    x-model="editItem.manufacturing_date" 
                                    placeholder="Pilih Tanggal / Bulan / Tahun..." 
                                    class="w-full rounded-xl border border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer"
                                    readonly>
                            </div>
                        </div>

                        <!-- Tanggal Kadaluarsa (Expiry Date) -->
                        <div class="rounded-xl p-3 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 space-y-2"
                            x-data="{
                                fpExp: null,
                                initPicker() {
                                    if (typeof flatpickr === 'undefined') {
                                        setTimeout(() => this.initPicker(), 100);
                                        return;
                                    }
                                    if (this.fpExp) return;
                                    this.fpExp = flatpickr(this.$refs.editExpDateInput, {
                                        dateFormat: 'Y-m-d',
                                        altInput: true,
                                        altFormat: 'd/m/Y',
                                        allowInput: true,
                                        monthSelectorType: 'dropdown',
                                        altInputClass: 'w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-amber-500 cursor-pointer shadow-2xs',
                                        defaultDate: editItem.expiry_date || null,
                                        onChange: (dates, dateStr) => {
                                            editItem.expiry_date = dateStr;
                                        }
                                    });
                                }
                            }"
                            x-init="$nextTick(() => initPicker())"
                            x-effect="if (fpExp && openEditModal && (editItem.expiry_date || '') !== (fpExp.input ? fpExp.input.value : '')) { fpExp.setDate(editItem.expiry_date || '', false); }">
                            <label class="block text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center justify-between cursor-pointer" @click.prevent="fpExp && fpExp.open()">
                                <span>Tanggal Kadaluarsa (Expiry Date - Cat / Kimia)</span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Pilih Tanggal (d/m/Y)</span>
                                </span>
                            </label>
                            <div class="relative cursor-pointer">
                                <input type="text" 
                                    x-ref="editExpDateInput"
                                    name="expiry_date" 
                                    x-model="editItem.expiry_date" 
                                    placeholder="Pilih Tanggal Kadaluarsa..." 
                                    class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-amber-500 cursor-pointer"
                                    readonly>
                            </div>
                        </div>

                        <!-- Konversi Satuan Grosir -->
                        <div class="rounded-xl p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-2">
                            <label class="block text-xs font-bold text-gray-900 dark:text-white">
                                Satuan Pembelian Grosir (Opsional)
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] text-gray-500">Satuan Grosir</label>
                                    <input type="text" name="wholesale_unit" x-model="editItem.wholesale_unit" placeholder="Box, Pail, Dus" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-500">Isi per Satuan Grosir</label>
                                    <input type="number" name="conversion_factor" x-model.number="editItem.conversion_factor" min="1" placeholder="Contoh: 50" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 dark:bg-gray-800/90 px-5 py-3.5 flex items-center justify-end gap-2.5 border-t border-gray-200 dark:border-gray-800">
                        <button type="button" @click="openEditModal = false" class="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="submittingEdit" class="btn-save-theme inline-flex justify-center items-center gap-2 rounded-xl bg-brand-600 px-6 py-2 text-xs font-bold text-white shadow-md hover:bg-brand-700 disabled:opacity-75 transition cursor-pointer">
                            <template x-if="submittingEdit"><x-spinner /></template>
                            <span x-show="!submittingEdit">Simpan Perubahan</span>
                            <span x-show="submittingEdit">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL PURCHASE REQUEST / FPBJ (100% EXCEL TEMPLATE COMPATIBLE) -->
    <template x-teleport="body">
        <div x-show="openPrModal" x-cloak 
            class="fixed inset-0 z-[999999] flex items-center justify-center p-2 sm:p-4 bg-gray-950/80 backdrop-blur-sm overflow-y-auto animate-fadeIn"
            style="z-index: 999999;"
            @click.self="openPrModal = false"
            @keydown.escape.window="openPrModal = false"
            role="dialog" aria-modal="true">

            <!-- Modal Content -->
            <div x-show="openPrModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden my-auto"
                x-data="{ submittingPr: false }">

                <form action="{{ route('items.purchase-request.export') }}" method="POST" target="_blank" @submit="submittingPr = true; setTimeout(() => { submittingPr = false; openPrModal = false; }, 1200)">
                    @csrf

                    <!-- Header -->
                    <div class="px-5 py-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-800 bg-rose-50/70 dark:bg-rose-950/40">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-white shrink-0 shadow-xs">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white leading-tight">Formulir Permintaan Pengadaan Barang & Jasa (FPBJ)</h3>
                                <p class="text-xs text-rose-700 dark:text-rose-300">Pengadaan Material Re-Order Point - PT Mudalaya Energy Indonesia</p>
                            </div>
                        </div>
                        <button type="button" @click="openPrModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto bg-white dark:bg-gray-900">
                        
                        <!-- Top Metadata: Doc No & Date -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nomor Dokumen FPBJ <span class="text-rose-500">*</span></label>
                                <input type="text" name="document_no" x-model="prData.document_no" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-mono font-bold text-gray-900 dark:text-white focus:border-brand-500">
                            </div>

                            <div class="space-y-1" x-data="{
                                fpPr: null,
                                initPrPicker() {
                                    if (typeof flatpickr === 'undefined') {
                                        setTimeout(() => this.initPrPicker(), 100);
                                        return;
                                    }
                                    if (this.fpPr) return;
                                    this.fpPr = flatpickr(this.$refs.prReqDateInput, {
                                        dateFormat: 'Y-m-d',
                                        altInput: true,
                                        altFormat: 'd/m/Y',
                                        allowInput: true,
                                        monthSelectorType: 'dropdown',
                                        altInputClass: 'w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer shadow-2xs',
                                        defaultDate: prData.required_date || null,
                                        onChange: (dates, dateStr) => {
                                            prData.required_date = dateStr;
                                        }
                                    });
                                }
                            }"
                            x-init="$nextTick(() => initPrPicker())"
                            x-effect="if (fpPr && openPrModal && (prData.required_date || '') !== (fpPr.input ? fpPr.input.value : '')) { fpPr.setDate(prData.required_date || '', false); }">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between cursor-pointer" @click.prevent="fpPr && fpPr.open()">
                                    <span>Tanggal Diperlukan <span class="text-rose-500">*</span></span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 dark:text-brand-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Pilih Tanggal</span>
                                    </span>
                                </label>
                                <input type="text" 
                                    x-ref="prReqDateInput"
                                    name="required_date" 
                                    x-model="prData.required_date" 
                                    required
                                    placeholder="Pilih Tanggal..." 
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer"
                                    readonly>
                            </div>
                        </div>

                        <!-- Data Item Re-Order Cards -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Daftar Material Permintaan Pengadaan:</label>
                            
                            <template x-for="(item, idx) in prData.items" :key="idx">
                                <div class="p-4 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/30 dark:bg-rose-950/20 space-y-3">
                                    <input type="hidden" :name="'items[' + idx + '][id]'" :value="item.id">
                                    <input type="hidden" :name="'items[' + idx + '][code_item]'" :value="item.code_item || ''">
                                    <input type="hidden" :name="'items[' + idx + '][name]'" :value="item.name">
                                    <input type="hidden" :name="'items[' + idx + '][stock]'" :value="item.stock">
                                    <input type="hidden" :name="'items[' + idx + '][min_stock]'" :value="item.min_stock">

                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span x-show="item.code_item" x-text="item.code_item" class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300"></span>
                                                <h4 class="text-sm font-black text-gray-900 dark:text-white" x-text="item.name"></h4>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                                <span>Data Stock: <strong class="text-rose-600 font-bold" x-text="item.stock + ' ' + item.unit"></strong></span>
                                                <span>•</span>
                                                <span>Minimum Stock: <strong class="text-gray-800 dark:text-gray-200 font-bold" x-text="item.min_stock + ' ' + item.unit"></strong></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" 
                                                x-show="prData.items.length > 1" 
                                                @click="prData.items.splice(idx, 1)" 
                                                class="px-2 py-1 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-700 dark:bg-rose-900/40 dark:hover:bg-rose-900/80 dark:text-rose-300 text-[11px] font-bold transition cursor-pointer flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                                <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Warning Stok</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-rose-200/60 dark:border-rose-900/40">
                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                                                Jumlah Permintaan (Qty) <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <input type="number" 
                                                    :name="'items[' + idx + '][request_qty]'" 
                                                    x-model.number="item.request_qty" 
                                                    min="0.001" 
                                                    step="any"
                                                    required 
                                                    class="w-full rounded-xl border border-rose-300 dark:border-rose-800 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-extrabold text-rose-600 dark:text-rose-400 focus:border-brand-500 shadow-2xs">
                                                
                                                <input type="text" 
                                                    :name="'items[' + idx + '][unit]'" 
                                                    x-model="item.unit" 
                                                    required 
                                                    class="w-24 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-3 py-2 text-xs font-bold text-center text-gray-800 dark:text-white">
                                            </div>
                                        </div>

                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">Keterangan Item</label>
                                            <input type="text" 
                                                :name="'items[' + idx + '][notes]'" 
                                                x-model="item.notes" 
                                                placeholder="Contoh: Digunakan untuk semua project" 
                                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-brand-500">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Data Penandatangan & Persetujuan Form -->
                        <div class="rounded-xl p-3.5 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-3">
                            <label class="block text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                Data Penandatangan Dokumentasi FPBJ:
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400">Pemohon (Warehouse)</label>
                                    <input type="text" name="warehouse_user" x-model="prData.warehouse_user" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400">Manager Produksi / SCM</label>
                                    <input type="text" name="scm_manager" x-model="prData.scm_manager" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400">Direktur Operational</label>
                                    <input type="text" name="ops_director" x-model="prData.ops_director" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Tambahan Global -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Catatan Tambahan Formulir (Keterangan Footer)</label>
                            <textarea name="notes_global" x-model="prData.notes_global" rows="2" placeholder="Catatan opsional untuk pengadaan ini..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-brand-500"></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 dark:bg-gray-800/90 px-5 py-3.5 flex items-center justify-between border-t border-gray-200 dark:border-gray-800">
                        <span class="text-xs text-gray-500">Format Excel: <strong>100% Identik Gambar 2</strong></span>
                        <div class="flex items-center gap-2.5">
                            <button type="button" @click="openPrModal = false" class="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" :disabled="submittingPr" class="btn-save-theme inline-flex justify-center items-center gap-2 rounded-xl bg-brand-600 px-6 py-2 text-xs font-bold text-white shadow-md hover:bg-brand-700 disabled:opacity-75 transition cursor-pointer">
                                <template x-if="submittingPr"><x-spinner /></template>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Cetak / Download Excel FPBJ</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif
</div>
@endsection

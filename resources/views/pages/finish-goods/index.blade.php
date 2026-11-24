@extends('layouts.app')

@section('content')
<div x-data="{
    openCreateModal: false,
    openEditModal: false,
    openDeliveryModal: false,
    openTraceModal: false,
    traceItem: null,
    editData: {
        id: '',
        project_id: '',
        item_name: '',
        item_code: '',
        quantity: 1,
        unit: 'PCS',
        production_date: '{{ date('Y-m-d') }}',
        qc_status: 'Pending',
        status: 'In Storage',
        delivery_date: '',
        delivery_order_number: '',
        material_cost: '',
        manpower_cost: '',
        notes: ''
    },
    deliveryData: {
        id: '',
        item_name: '',
        delivery_date: '{{ date('Y-m-d') }}',
        delivery_order_number: '',
        notes: ''
    },
    setEdit(item) {
        this.editData = {
            id: item.id,
            project_id: item.project_id,
            item_name: item.item_name,
            item_code: item.item_code || '',
            quantity: item.quantity,
            unit: item.unit,
            production_date: item.production_date ? item.production_date.substring(0, 10) : '',
            qc_status: item.qc_status,
            status: item.status,
            delivery_date: item.delivery_date ? item.delivery_date.substring(0, 10) : '',
            delivery_order_number: item.delivery_order_number || '',
            material_cost: item.material_cost || '',
            manpower_cost: item.manpower_cost || '',
            notes: item.notes || ''
        };
        this.openEditModal = true;
    },
    setDelivery(item) {
        this.deliveryData = {
            id: item.id,
            item_name: item.item_name,
            delivery_date: '{{ date('Y-m-d') }}',
            delivery_order_number: item.delivery_order_number || '',
            notes: ''
        };
        this.openDeliveryModal = true;
    },
    setTrace(item) {
        this.traceItem = item;
        this.openTraceModal = true;
    },
    openWipModalState: false,
    wipData: {
        project_id: @js(request('project_id') ?? ''),
        wip_stage: @js($stageKey ?? 'Cutting'),
        date: @js(date('Y-m-d')),
        item_id: '',
        quantity: 1,
        unit: 'PCS',
        manpower_hours: 0,
        manpower_rate_per_hour: 25000,
        pic: @js(auth()->user()->name ?? 'Operator Fabrikasi'),
        notes: ''
    },
    openWipModal(stage) {
        this.wipData.wip_stage = stage;
        this.wipData.project_id = @js(request('project_id') ?? '');
        this.openWipModalState = true;
    }
}" class="p-4 sm:p-6 space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            @if($currentTab === 'finish_goods')
                <h1 class="text-2xl font-black text-gray-900 dark:text-white">Barang Jadi & WIP (Work In Process)</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Pencatatan produk jadi hasil fabrikasi per project hingga pengiriman ke klien.</p>
            @else
                <h1 class="text-2xl font-black text-gray-900 dark:text-white">WIP (Work In Process) - Tahap {{ $stageKey }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Pencatatan dan pemantauan material keluar serta upah tenaga kerja pada proses <strong>{{ $stageKey }}</strong>.</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('finish-goods.export', request()->query()) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 shadow-xs transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>

            @if(auth()->user() && auth()->user()->canManageWarehouse())
                @if($currentTab === 'finish_goods')
                    <button type="button" @click="openCreateModal = true" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 shadow-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Catat Barang Jadi Baru
                    </button>
                @else
                    <button type="button" @click="openWipModal('{{ $stageKey }}')" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700 shadow-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Input WIP ({{ $stageKey }})</span>
                    </button>
                @endif
            @endif
        </div>
    </div>

    <!-- TABS NAVIGASI 5 TAHAP PROSES FABRIKASI & WIP -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-gray-200 dark:border-gray-800 print:hidden">
        <!-- Tab 0: Produk Jadi -->
        <a href="{{ route('finish-goods.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'finish_goods'])) }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer {{ $currentTab === 'finish_goods' ? 'bg-brand-600 text-white shadow-brand-500/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Produk Jadi (Finish Goods)</span>
            <span class="rounded-full {{ $currentTab === 'finish_goods' ? 'bg-white/20 text-white' : 'bg-brand-100 text-brand-800 dark:bg-brand-950/60 dark:text-brand-300' }} px-2 py-0.5 text-[10px] font-black font-mono">
                {{ $wipCounts['finish_goods'] ?? 0 }}
            </span>
        </a>

        <!-- Tab 1: Cutting -->
        <a href="{{ route('finish-goods.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'cutting'])) }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer {{ $currentTab === 'cutting' ? 'bg-amber-600 text-white shadow-amber-500/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.828 2.828a2 2 0 11-2.828-2.828L8.99 11.293m0 0L4.707 7.001a2 2 0 112.828-2.828l4.283 4.283m0 0L19 19"/></svg>
            <span>Tahap 1: Cutting & Profiling</span>
            <span class="rounded-full {{ $currentTab === 'cutting' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }} px-2 py-0.5 text-[10px] font-black font-mono">
                {{ $wipCounts['cutting'] ?? 0 }}
            </span>
        </a>

        <!-- Tab 2: Vendor Eksternal -->
        <a href="{{ route('finish-goods.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'vendor'])) }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer {{ $currentTab === 'vendor' ? 'bg-blue-600 text-white shadow-blue-500/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Tahap 2: Vendor Eksternal (Subkon)</span>
            <span class="rounded-full {{ $currentTab === 'vendor' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }} px-2 py-0.5 text-[10px] font-black font-mono">
                {{ $wipCounts['vendor'] ?? 0 }}
            </span>
        </a>

        <!-- Tab 3: Welding -->
        <a href="{{ route('finish-goods.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'welding'])) }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer {{ $currentTab === 'welding' ? 'bg-rose-600 text-white shadow-rose-500/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Tahap 3: Welding & Assembling</span>
            <span class="rounded-full {{ $currentTab === 'welding' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }} px-2 py-0.5 text-[10px] font-black font-mono">
                {{ $wipCounts['welding'] ?? 0 }}
            </span>
        </a>

        <!-- Tab 4: Painting -->
        <a href="{{ route('finish-goods.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'painting'])) }}"
           class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-xs cursor-pointer {{ $currentTab === 'painting' ? 'bg-purple-600 text-white shadow-purple-500/20' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4 4 4 0 014-4 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
            <span>Tahap 4: Painting & Finishing</span>
            <span class="rounded-full {{ $currentTab === 'painting' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' }} px-2 py-0.5 text-[10px] font-black font-mono">
                {{ $wipCounts['painting'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Stats Cards -->
    @if($currentTab === 'finish_goods')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
        <!-- Total Barang Jadi -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Produk Jadi</span>
                    <h3 class="mt-2 text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($stats['total_items'] ?? 0) }} Item</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Akumulasi kuantitas: <span class="font-bold text-gray-700 dark:text-gray-300 font-mono">{{ number_format($stats['total_quantity'] ?? 0, 1) }} Unit</span></p>
        </div>

        <!-- In Storage -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Tersimpan di Gudang</span>
                    <h3 class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ number_format($stats['in_storage_count'] ?? 0) }} Item</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Siap kirim / menunggu jadwal handover</p>
        </div>

        <!-- Delivered -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Terkirim ke Klien</span>
                    <h3 class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($stats['delivered_count'] ?? 0) }} Item</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Sudah memiliki nomor Surat Jalan / DO</p>
        </div>

        <!-- QC Passed -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Lolos Inspeksi QC</span>
                    <h3 class="mt-2 text-2xl font-black text-blue-600 dark:text-blue-400 font-mono">{{ number_format($stats['qc_passed_count'] ?? 0) }} Item</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Memenuhi spesifikasi standar kualitas</p>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
        <!-- Total Catatan WIP -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Catatan Material WIP</span>
            <h3 class="mt-2 text-2xl font-black text-brand-600 dark:text-brand-400 font-mono">{{ number_format($stageStats['total_items'] ?? 0) }} Catatan</h3>
            <p class="mt-3 text-xs text-gray-400">Tahap: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $stageKey }}</span></p>
        </div>

        <!-- Total Kuantitas Fisik -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Total Kuantitas Fisik</span>
            <h3 class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ number_format($stageStats['total_qty'] ?? 0, 1) }} Unit</h3>
            <p class="mt-3 text-xs text-gray-400">Total volume material terserap</p>
        </div>

        <!-- Total Jam Kerja Manpower -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Total Jam Manpower</span>
            <h3 class="mt-2 text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">{{ number_format($stageStats['total_manpower_hours'] ?? 0, 1) }} Jam</h3>
            <p class="mt-3 text-xs text-gray-400">Total jam kerja operator fabrikasi</p>
        </div>

        <!-- Total Upah Tenaga Kerja -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total Biaya Manpower</span>
            <h3 class="mt-2 text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($stageStats['total_manpower_cost'] ?? 0, 0, ',', '.') }}</h3>
            <p class="mt-3 text-xs text-gray-400">Akumulasi upah tenaga kerja WIP</p>
        </div>
    </div>
    @endif

    <!-- Filter Bar -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4.5 dark:border-gray-800 dark:bg-gray-900 shadow-xs print:hidden">
        <form method="GET" action="{{ route('finish-goods.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="hidden" name="tab" value="{{ $currentTab }}">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Cari Barang / Tag / Surat Jalan</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama produk, no tag, project, no PO..." class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-3.5 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Project Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Filter Project</label>
                <select name="project_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">-- Semua Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} {{ $p->po_number ? '(PO: ' . $p->po_number . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Storage / Delivered -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Status Pengiriman</label>
                <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">-- Semua Status --</option>
                    <option value="In Storage" {{ request('status') === 'In Storage' ? 'selected' : '' }}>Tersimpan di Gudang</option>
                    <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Terkirim ke Klien</option>
                </select>
            </div>

            <!-- Status QC -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Status QC</label>
                <select name="qc_status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">-- Semua Status QC --</option>
                    <option value="Passed" {{ request('qc_status') === 'Passed' ? 'selected' : '' }}>Lolos (Passed)</option>
                    <option value="Pending" {{ request('qc_status') === 'Pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                    <option value="Rejected" {{ request('qc_status') === 'Rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-brand-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                <a href="{{ route('finish-goods.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition" title="Reset Filter">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>
    </div>

    @if($currentTab === 'finish_goods')
    <!-- Finish Goods Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="theme-table-header text-white font-extrabold uppercase border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">NO</th>
                        <th class="px-4 py-3.5">NAMA PROJECT & NO PO</th>
                        <th class="px-4 py-3.5">PRODUK JADI / TAG NO</th>
                        <th class="px-4 py-3.5 text-center">JUMLAH</th>
                        <th class="px-4 py-3.5 text-right">TOTAL HPP</th>
                        <th class="px-4 py-3.5">TGL PRODUKSI</th>
                        <th class="px-4 py-3.5 text-center">STATUS QC</th>
                        <th class="px-4 py-3.5 text-center">STATUS PENGIRIMAN</th>
                        <th class="px-4 py-3.5 text-center w-28">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($finishGoods as $index => $fg)
                        <tr class="hover:bg-gray-50/80 transition dark:hover:bg-gray-800/50">
                            <!-- NO -->
                            <td class="px-4 py-3.5 text-center font-semibold text-gray-500 dark:text-gray-400">
                                {{ $finishGoods->firstItem() + $index }}
                            </td>

                            <!-- PROJECT & NO PO -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col">
                                    <a href="{{ route('projects.show', $fg->project_id) }}" class="font-bold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition">
                                        {{ $fg->project->name ?? '-' }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        @if($fg->project && $fg->project->po_number)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                                                PO: {{ $fg->project->po_number }}
                                            </span>
                                        @endif
                                        @if($fg->project && $fg->project->client)
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                                                Klien: {{ $fg->project->client }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- PRODUK JADI -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $fg->item_name }}</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if($fg->item_code)
                                            <span class="text-[10px] font-mono font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.2 rounded">
                                                Tag: {{ $fg->item_code }}
                                            </span>
                                        @endif
                                        @if($fg->notes)
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 italic truncate max-w-xs" title="{{ $fg->notes }}">
                                                "{{ $fg->notes }}"
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- JUMLAH -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="font-black text-gray-900 dark:text-white text-sm font-mono">
                                    {{ (float)$fg->quantity == (int)$fg->quantity ? number_format($fg->quantity, 0) : number_format($fg->quantity, 2) }}
                                </span>
                                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 ml-0.5">
                                    {{ $fg->unit }}
                                </span>
                            </td>

                            <!-- TOTAL HPP -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                @if($fg->total_hpp > 0)
                                    <div class="font-extrabold text-gray-900 dark:text-white font-mono text-xs">
                                        {{ format_currency($fg->total_hpp) }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                        @if($fg->quantity > 1)
                                            <span>@ {{ format_currency($fg->hpp_per_unit) }}/{{ $fg->unit }}</span>
                                        @else
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Tervalidasi</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- TGL PRODUKSI -->
                            <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                {{ $fg->production_date ? format_date($fg->production_date) : '-' }}
                            </td>

                            <!-- STATUS QC -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($fg->qc_status === 'Passed')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Lolos QC
                                    </span>
                                @elseif($fg->qc_status === 'Rejected')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[10px] font-extrabold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Ditolak QC
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-extrabold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Menunggu QC
                                    </span>
                                @endif
                            </td>

                            <!-- STATUS PENGIRIMAN -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($fg->status === 'Delivered')
                                    <div class="flex flex-col items-center">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-extrabold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Terkirim
                                        </span>
                                        @if($fg->delivery_order_number)
                                            <span class="text-[10px] font-mono text-gray-600 dark:text-gray-400 mt-0.5">
                                                DO: {{ $fg->delivery_order_number }}
                                            </span>
                                        @endif
                                        @if($fg->delivery_date)
                                            <span class="text-[9.5px] text-gray-400">
                                                {{ format_date($fg->delivery_date) }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-extrabold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        Di Gudang
                                    </span>
                                @endif
                            </td>

                            <!-- AKSI -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="setTrace({{ json_encode($fg) }})" class="rounded-lg p-1.5 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/40 transition cursor-pointer" title="Lacak Alur WIP & Rincian Material/Manpower (Traceability)">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </button>

                                    @if($fg->status !== 'Delivered')
                                        <button type="button" @click="setDelivery({{ json_encode($fg) }})" class="rounded-lg p-1.5 text-gray-500 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-blue-950/40 dark:hover:text-blue-300 transition cursor-pointer" title="Catat Pengiriman / Delivery Order">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                        </button>
                                    @endif

                                    <button type="button" @click="setEdit({{ json_encode($fg) }})" class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-brand-400 transition cursor-pointer" title="Edit Data Barang Jadi">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    @if(auth()->user() && auth()->user()->canManageWarehouse())
                                    <form method="POST" action="{{ route('finish-goods.destroy', $fg->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data barang jadi ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-gray-500 hover:bg-rose-50 hover:text-rose-600 dark:text-gray-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 transition cursor-pointer" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Belum Ada Data Barang Jadi (Finish Goods)</span>
                                    <p class="text-xs text-gray-400 max-w-sm">Data produk jadi hasil fabrikasi project akan tampil di sini setelah dicatat.</p>
                                    @if(auth()->user() && auth()->user()->canManageWarehouse())
                                        <button type="button" @click="openCreateModal = true" class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-brand-700 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Catat Barang Jadi Baru
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($finishGoods->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $finishGoods->links() }}
            </div>
        @endif
    </div>

        @else
    <!-- WIP Process Materials & Manpower Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50 dark:bg-gray-800/30">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>Daftar Penggunaan Material & Manpower: {{ $stageKey }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        {{ $stageKey }}
                    </span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Setiap material yang keluar di bawah otomatis memotong stok gudang dan terakumulasi ke HPP barang jadi.</p>
            </div>
            @if(auth()->user() && auth()->user()->canManageWarehouse())
                <button type="button" @click="openWipModal('{{ $stageKey }}')" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-amber-700 shadow-xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Input WIP {{ $stageKey }}</span>
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="theme-table-header text-white font-extrabold uppercase border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">NO</th>
                        <th class="px-4 py-3.5">PROJECT & NO PO</th>
                        <th class="px-4 py-3.5">TANGGAL</th>
                        <th class="px-4 py-3.5">KODE MATERIAL</th>
                        <th class="px-4 py-3.5">NAMA BARANG</th>
                        <th class="px-4 py-3.5 text-center">KUANTITAS FISIK</th>
                        <th class="px-4 py-3.5 text-center">MANPOWER (JAM)</th>
                        <th class="px-4 py-3.5 text-right">BIAYA MANPOWER</th>
                        <th class="px-4 py-3.5">PIC PELAKSANA</th>
                        <th class="px-4 py-3.5">CATATAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($wipRecords as $index => $mo)
                        <tr class="hover:bg-gray-50/80 transition dark:hover:bg-gray-800/50">
                            <!-- NO -->
                            <td class="px-4 py-3.5 text-center font-semibold text-gray-500 dark:text-gray-400">
                                {{ $wipRecords->firstItem() + $index }}
                            </td>

                            <!-- PROJECT & NO PO -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $mo->projectRelation->name ?? $mo->project }}</span>
                                    @if($mo->projectRelation && $mo->projectRelation->po_number)
                                        <span class="font-mono text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                                            PO: {{ $mo->projectRelation->po_number }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- TANGGAL -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-gray-800 dark:text-gray-200">{{ format_date($mo->date) }}</div>
                                <span class="font-mono text-[10px] text-gray-400">{{ $mo->reference_number }}</span>
                            </td>

                            <!-- KODE MATERIAL -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                    {{ $mo->item->code_item ?? '-' }}
                                </span>
                            </td>

                            <!-- NAMA BARANG -->
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $mo->item->name ?? '-' }}</span>
                                <span class="text-[10px] text-gray-400">{{ $mo->item->category->name ?? 'Material' }}</span>
                            </td>

                            <!-- KUANTITAS FISIK -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-mono font-bold text-xs">
                                    -{{ format_quantity($mo->quantity) }} {{ $mo->unit }}
                                </span>
                            </td>

                            <!-- MANPOWER (JAM) -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($mo->manpower_hours > 0)
                                    <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 rounded-md border border-rose-200 dark:border-rose-800">
                                        {{ (float)$mo->manpower_hours }} Jam
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs italic">-</span>
                                @endif
                            </td>

                            <!-- BIAYA MANPOWER -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                @if($mo->manpower_cost > 0)
                                    <span class="font-mono font-bold text-xs text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format($mo->manpower_cost, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- PIC -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $mo->pic ?: '-' }}</span>
                            </td>

                            <!-- CATATAN -->
                            <td class="px-4 py-3.5">
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $mo->notes ?: '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="h-12 w-12 rounded-full bg-amber-50 dark:bg-amber-950/60 flex items-center justify-center text-amber-500">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Belum Ada Pengeluaran Material untuk Tahap {{ $stageKey }}</span>
                                    <p class="text-xs text-gray-400 max-w-sm">Klik tombol di bawah untuk mencatat pemakaian material dan upah tenaga kerja pada proses {{ $stageKey }}.</p>
                                    @if(auth()->user() && auth()->user()->canManageWarehouse())
                                        <button type="button" @click="openWipModal('{{ $stageKey }}')" class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-amber-700 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>+ Input WIP {{ $stageKey }}</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wipRecords->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $wipRecords->links() }}
            </div>
        @endif
    </div>
    @endif
    
    <!-- Modal Catat Barang Jadi Baru -->
    <template x-teleport="body">
        <div x-show="openCreateModal" x-cloak @click.self="openCreateModal = false" @keydown.escape.window="openCreateModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/80 dark:text-brand-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Catat Barang Jadi Baru</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pencatatan produk atau komponen hasil fabrikasi project</p>
                        </div>
                    </div>
                    <button type="button" @click="openCreateModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('finish-goods.store') }}" class="space-y-4">
                    @csrf

                    <!-- Project Selection (Searchable Combobox) -->
                    <div class="relative" x-data="{
                        open: false,
                        search: '',
                        selectedId: '',
                        selectedLabel: '',
                        projects: [
                            @foreach($projects as $p)
                            {
                                id: '{{ $p->id }}',
                                name: {{ json_encode($p->name) }},
                                po: {{ json_encode($p->po_number ?? '') }},
                                client: {{ json_encode($p->client ?? '') }}
                            },
                            @endforeach
                        ],
                        get filtered() {
                            if (!this.search.trim()) return this.projects;
                            const q = this.search.toLowerCase();
                            return this.projects.filter(p => 
                                p.name.toLowerCase().includes(q) || 
                                (p.po && p.po.toLowerCase().includes(q)) || 
                                (p.client && p.client.toLowerCase().includes(q))
                            );
                        },
                        select(p) {
                            this.selectedId = p.id;
                            this.selectedLabel = p.name + (p.po ? ' (PO: ' + p.po + ')' : '');
                            this.open = false;
                            this.search = '';
                        },
                        clear() {
                            this.selectedId = '';
                            this.selectedLabel = '';
                            this.open = false;
                            this.search = '';
                        }
                    }" @click.outside="open = false">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Project / Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        
                        <input type="text" name="project_id" :value="selectedId" required class="sr-only opacity-0 w-0 h-0 absolute -z-10" tabindex="-1">

                        <div class="relative">
                            <button type="button" 
                                    @click="open = !open; if(open) $nextTick(() => $refs.projectSearchInput.focus())"
                                    class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition text-left shadow-2xs">
                                <span class="truncate pr-2 font-medium" :class="selectedId ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'" x-text="selectedLabel || '-- Cari & Pilih Project Fabrikasi --'"></span>
                                <div class="flex items-center gap-1.5 shrink-0 text-gray-400">
                                    <template x-if="selectedId">
                                        <span @click.stop="clear()" class="p-0.5 hover:text-rose-500 transition cursor-pointer" title="Reset Pilihan Project">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </span>
                                    </template>
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            <!-- Dropdown Popover -->
                            <div x-show="open" 
                                 x-cloak 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 right-0 top-full z-30 mt-1 rounded-2xl border border-gray-200 bg-white p-2.5 shadow-2xl dark:border-gray-700 dark:bg-gray-800 space-y-2">
                                
                                <!-- Filter Input -->
                                <div class="relative">
                                    <input x-ref="projectSearchInput" 
                                           type="text" 
                                           x-model="search" 
                                           placeholder="Ketik nama project, nomor PO, atau nama klien..." 
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-8 pr-7 py-1.5 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Filtered List -->
                                <div class="max-h-52 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                    <template x-for="p in filtered" :key="p.id">
                                        <div @click="select(p)" 
                                             class="flex flex-col gap-0.5 rounded-xl px-3 py-2 cursor-pointer transition text-left"
                                             :class="selectedId == p.id ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/70 dark:text-brand-300 font-bold' : 'hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-800 dark:text-gray-200'">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs truncate" :class="selectedId == p.id ? 'font-bold' : 'font-semibold'" x-text="p.name"></span>
                                                <template x-if="selectedId == p.id">
                                                    <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400">
                                                <template x-if="p.po">
                                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-bold text-gray-700 dark:text-gray-300" x-text="'PO: ' + p.po"></span>
                                                </template>
                                                <template x-if="p.client">
                                                    <span class="truncate" x-text="'Klien: ' + p.client"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="filtered.length === 0" class="py-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                        Tidak ditemukan project dengan kata kunci "<span class="font-bold text-gray-600 dark:text-gray-300" x-text="search"></span>"
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Item Name -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Produk / Komponen <span class="text-rose-500">*</span></label>
                            <input type="text" name="item_name" required placeholder="Contoh: Spool Pipa 4 Inch Sch 40, Skid Base..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <!-- Item Code / Tag No -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Tag / Spool ID / Seri <span class="text-xs font-normal text-gray-400">(Opsional)</span></label>
                            <input type="text" name="item_code" placeholder="Contoh: SP-001-A, DWG-MEI-2026-08..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Quantity & Unit -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jumlah Hasil (Qty) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="quantity" value="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="unit" list="finish_good_unit_list" value="PCS" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <datalist id="finish_good_unit_list">
                                <option value="PCS"></option>
                                <option value="UNIT"></option>
                                <option value="SET"></option>
                                <option value="BATANG"></option>
                                <option value="LEMBAR"></option>
                                <option value="ROLL"></option>
                                @foreach($units ?? [] as $u)
                                    <option value="{{ $u->symbol }}">{{ $u->symbol }} ({{ $u->name }})</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <!-- Production Date & QC Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Tanggal Selesai Produksi (Interactive Flatpickr Calendar) -->
                        <div class="space-y-1"
                             x-data="{
                                 fp: null,
                                 init() {
                                     const initPicker = () => {
                                         if (typeof flatpickr === 'undefined') {
                                             setTimeout(initPicker, 50);
                                             return;
                                         }
                                         if (this.fp) return;
                                         this.fp = flatpickr(this.$refs.prodDateInput, {
                                             dateFormat: 'Y-m-d',
                                             altInput: true,
                                             altFormat: 'd/m/Y',
                                             allowInput: false,
                                             clickOpens: true,
                                             defaultDate: '{{ date('Y-m-d') }}',
                                             altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 pl-3.5 pr-10 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer select-none transition shadow-2xs',
                                             onChange: (selectedDates, dateStr) => {
                                                 this.$refs.prodDateInput.value = dateStr;
                                             }
                                         });
                                     };
                                     this.$nextTick(() => initPicker());
                                 },
                                 openPicker() {
                                     if (this.fp) this.fp.open();
                                 }
                             }">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 cursor-pointer" @click="openPicker()">
                                Tanggal Selesai Produksi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative cursor-pointer" @click="openPicker()">
                                <input type="text" 
                                       x-ref="prodDateInput" 
                                       name="production_date" 
                                       value="{{ date('Y-m-d') }}" 
                                       required 
                                       class="hidden">
                                <button type="button" @click.stop="openPicker()" class="absolute right-3 top-2.5 text-gray-400 hover:text-brand-600 transition cursor-pointer" title="Buka Kalender">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Inspeksi QC <span class="text-rose-500">*</span></label>
                            <select name="qc_status" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="Pending">Pending (Dalam Pemeriksaan QC)</option>
                                <option value="Passed" selected>Passed (Lolos Uji QC)</option>
                                <option value="Rejected">Rejected (Gagal QC / Ditolak)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Storage / Delivery Status -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Penyimpanan / Pengiriman <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                            <option value="In Storage" selected>In Storage (Tersimpan di Gudang / Siap Kirim)</option>
                            <option value="Delivered">Delivered (Telah Dikirim ke Klien)</option>
                        </select>
                    </div>

                    <!-- Bagian Perhitungan HPP & Akumulasi WIP (Finance) -->
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3.5 text-xs dark:border-indigo-900/40 dark:bg-indigo-950/20 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-indigo-950 dark:text-indigo-200 flex items-center gap-1.5 text-xs">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Perhitungan HPP & Akumulasi WIP (Finance)</span>
                            </span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="auto_calculate_wip" value="1" checked class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300">Tarik Otomatis dari Material Keluar Proyek</span>
                            </label>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            Centang untuk menjumlahkan otomatis seluruh biaya material part & manpower yang telah dikeluarkan untuk proyek ini ke HPP produk jadi.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Estimasi Biaya Material Part (Rp)</label>
                                <input type="number" step="any" min="0" name="material_cost" placeholder="0 (Kosongkan jika otomatis)" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Estimasi Biaya Manpower (Rp)</label>
                                <input type="number" step="any" min="0" name="manpower_cost" placeholder="0 (Kosongkan jika otomatis)" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan / Spesifikasi Tambahan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan dimensi, nomor heat, area penempatan di workshop/gudang..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openCreateModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="btn-save-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white hover:bg-brand-700 focus:outline-none shadow-sm transition cursor-pointer">
                            Simpan Barang Jadi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Edit Barang Jadi -->
    <template x-teleport="body">
        <div x-show="openEditModal" x-cloak @click.self="openEditModal = false" @keydown.escape.window="openEditModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/80 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Edit Data Barang Jadi</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Perbarui rincian produk fabrikasi dan status pengiriman</p>
                        </div>
                    </div>
                    <button type="button" @click="openEditModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'/finish-goods/' + editData.id" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Project Selection (Searchable Combobox) -->
                    <div class="relative" x-data="{
                        open: false,
                        search: '',
                        projects: [
                            @foreach($projects as $p)
                            {
                                id: '{{ $p->id }}',
                                name: {{ json_encode($p->name) }},
                                po: {{ json_encode($p->po_number ?? '') }},
                                client: {{ json_encode($p->client ?? '') }}
                            },
                            @endforeach
                        ],
                        get selectedLabel() {
                            const found = this.projects.find(p => p.id == editData.project_id);
                            if (!found) return '';
                            return found.name + (found.po ? ' (PO: ' + found.po + ')' : '');
                        },
                        get filtered() {
                            if (!this.search.trim()) return this.projects;
                            const q = this.search.toLowerCase();
                            return this.projects.filter(p => 
                                p.name.toLowerCase().includes(q) || 
                                (p.po && p.po.toLowerCase().includes(q)) || 
                                (p.client && p.client.toLowerCase().includes(q))
                            );
                        },
                        select(p) {
                            editData.project_id = p.id;
                            this.open = false;
                            this.search = '';
                        }
                    }" @click.outside="open = false">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Project / Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        
                        <input type="text" name="project_id" :value="editData.project_id" required class="sr-only opacity-0 w-0 h-0 absolute -z-10" tabindex="-1">

                        <div class="relative">
                            <button type="button" 
                                    @click="open = !open; if(open) $nextTick(() => $refs.editProjectSearchInput.focus())"
                                    class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer transition text-left shadow-2xs">
                                <span class="truncate pr-2 font-medium" :class="editData.project_id ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'" x-text="selectedLabel || '-- Cari & Pilih Project Fabrikasi --'"></span>
                                <div class="flex items-center gap-1.5 shrink-0 text-gray-400">
                                    <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>

                            <!-- Dropdown Popover -->
                            <div x-show="open" 
                                 x-cloak 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 right-0 top-full z-30 mt-1 rounded-2xl border border-gray-200 bg-white p-2.5 shadow-2xl dark:border-gray-700 dark:bg-gray-800 space-y-2">
                                
                                <!-- Filter Input -->
                                <div class="relative">
                                    <input x-ref="editProjectSearchInput" 
                                           type="text" 
                                           x-model="search" 
                                           placeholder="Ketik nama project, nomor PO, atau nama klien..." 
                                           class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-8 pr-7 py-1.5 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Filtered List -->
                                <div class="max-h-52 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                                    <template x-for="p in filtered" :key="p.id">
                                        <div @click="select(p)" 
                                             class="flex flex-col gap-0.5 rounded-xl px-3 py-2 cursor-pointer transition text-left"
                                             :class="editData.project_id == p.id ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/70 dark:text-brand-300 font-bold' : 'hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-800 dark:text-gray-200'">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs truncate" :class="editData.project_id == p.id ? 'font-bold' : 'font-semibold'" x-text="p.name"></span>
                                                <template x-if="editData.project_id == p.id">
                                                    <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400">
                                                <template x-if="p.po">
                                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-bold text-gray-700 dark:text-gray-300" x-text="'PO: ' + p.po"></span>
                                                </template>
                                                <template x-if="p.client">
                                                    <span class="truncate" x-text="'Klien: ' + p.client"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="filtered.length === 0" class="py-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                        Tidak ditemukan project dengan kata kunci "<span class="font-bold text-gray-600 dark:text-gray-300" x-text="search"></span>"
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Item Name -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Produk / Komponen <span class="text-rose-500">*</span></label>
                            <input type="text" name="item_name" x-model="editData.item_name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <!-- Item Code / Tag No -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Tag / Spool ID / Seri</label>
                            <input type="text" name="item_code" x-model="editData.item_code" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Quantity & Unit -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Jumlah Hasil (Qty) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="quantity" x-model="editData.quantity" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="unit" x-model="editData.unit" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Production Date & QC Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Tanggal Selesai Produksi (Interactive Flatpickr Calendar) -->
                        <div class="space-y-1"
                             x-data="{
                                 fp: null,
                                 init() {
                                     const initPicker = () => {
                                         if (typeof flatpickr === 'undefined') {
                                             setTimeout(initPicker, 50);
                                             return;
                                         }
                                         if (this.fp) return;
                                         this.fp = flatpickr(this.$refs.editProdDateInput, {
                                             dateFormat: 'Y-m-d',
                                             altInput: true,
                                             altFormat: 'd/m/Y',
                                             allowInput: false,
                                             clickOpens: true,
                                             altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 pl-3.5 pr-10 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer select-none transition shadow-2xs',
                                             onChange: (selectedDates, dateStr) => {
                                                 editData.production_date = dateStr;
                                                 this.$refs.editProdDateInput.value = dateStr;
                                             }
                                         });
                                         if (editData.production_date) {
                                             this.fp.setDate(editData.production_date);
                                         }
                                     };
                                     this.$nextTick(() => initPicker());
                                     this.$watch('editData.production_date', (val) => {
                                         if (this.fp && val) {
                                             this.fp.setDate(val);
                                         }
                                     });
                                 },
                                 openPicker() {
                                     if (this.fp) this.fp.open();
                                 }
                             }">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 cursor-pointer" @click="openPicker()">
                                Tanggal Selesai Produksi
                            </label>
                            <div class="relative cursor-pointer" @click="openPicker()">
                                <input type="text" 
                                       x-ref="editProdDateInput" 
                                       name="production_date" 
                                       :value="editData.production_date" 
                                       class="hidden">
                                <button type="button" @click.stop="openPicker()" class="absolute right-3 top-2.5 text-gray-400 hover:text-brand-600 transition cursor-pointer" title="Buka Kalender">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status QC <span class="text-rose-500">*</span></label>
                            <select name="qc_status" x-model="editData.qc_status" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="Pending">Pending (Dalam Pemeriksaan QC)</option>
                                <option value="Passed">Passed (Lolos QC)</option>
                                <option value="Rejected">Rejected (Ditolak)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Storage / Delivery Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status Pengiriman <span class="text-rose-500">*</span></label>
                            <select name="status" x-model="editData.status" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                                <option value="In Storage">In Storage (Di Gudang)</option>
                                <option value="Delivered">Delivered (Terkirim)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Surat Jalan / DO</label>
                            <input type="text" name="delivery_order_number" x-model="editData.delivery_order_number" placeholder="Nomor DO..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- HPP Breakdown (Finance) -->
                    @if(auth()->user() && auth()->user()->canViewPrices())
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-800/40 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Penyesuaian Biaya HPP (Finance)
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 mb-1">Biaya Material (Rp)</label>
                                <input type="number" step="0.01" min="0" name="material_cost" x-model="editData.material_cost" placeholder="0" class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 mb-1">Biaya Manpower (Rp)</label>
                                <input type="number" step="0.01" min="0" name="manpower_cost" x-model="editData.manpower_cost" placeholder="0" class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan / Keterangan</label>
                        <textarea name="notes" x-model="editData.notes" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openEditModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="btn-save-theme inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-xs font-bold text-white hover:bg-brand-700 focus:outline-none shadow-sm transition cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Catat Pengiriman (DO / Delivery) -->
    <template x-teleport="body">
        <div x-show="openDeliveryModal" x-cloak @click.self="openDeliveryModal = false" @keydown.escape.window="openDeliveryModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-950/80 dark:text-sky-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Catat Pengiriman Produk</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Update status serah terima dan nomor Surat Jalan</p>
                        </div>
                    </div>
                    <button type="button" @click="openDeliveryModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'/finish-goods/' + deliveryData.id + '/delivery'" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 p-3 border border-gray-200 dark:border-gray-700">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Produk Jadi:</span>
                        <p class="text-sm font-black text-gray-900 dark:text-white uppercase mt-0.5" x-text="deliveryData.item_name"></p>
                    </div>

                    <!-- Delivery Date (Interactive Flatpickr Calendar) -->
                    <div class="space-y-1"
                         x-data="{
                             fp: null,
                             init() {
                                 const initPicker = () => {
                                     if (typeof flatpickr === 'undefined') {
                                         setTimeout(initPicker, 50);
                                         return;
                                     }
                                     if (this.fp) return;
                                     this.fp = flatpickr(this.$refs.deliveryDateInput, {
                                         dateFormat: 'Y-m-d',
                                         altInput: true,
                                         altFormat: 'd/m/Y',
                                         allowInput: false,
                                         clickOpens: true,
                                         altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 pl-3.5 pr-10 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-sky-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer select-none transition shadow-2xs',
                                         onChange: (selectedDates, dateStr) => {
                                             deliveryData.delivery_date = dateStr;
                                             this.$refs.deliveryDateInput.value = dateStr;
                                         }
                                     });
                                     if (deliveryData.delivery_date) {
                                         this.fp.setDate(deliveryData.delivery_date);
                                     }
                                 };
                                 this.$nextTick(() => initPicker());
                                 this.$watch('deliveryData.delivery_date', (val) => {
                                     if (this.fp && val) {
                                         this.fp.setDate(val);
                                     }
                                 });
                             },
                             openPicker() {
                                 if (this.fp) this.fp.open();
                             }
                         }">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 cursor-pointer" @click="openPicker()">
                            Tanggal Pengiriman <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative cursor-pointer" @click="openPicker()">
                            <input type="text" 
                                   x-ref="deliveryDateInput" 
                                   name="delivery_date" 
                                   :value="deliveryData.delivery_date" 
                                   required 
                                   class="hidden">
                            <button type="button" @click.stop="openPicker()" class="absolute right-3 top-2.5 text-gray-400 hover:text-sky-600 transition cursor-pointer" title="Buka Kalender">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Delivery Order Number -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Surat Jalan / DO <span class="text-rose-500">*</span></label>
                        <input type="text" name="delivery_order_number" x-model="deliveryData.delivery_order_number" required placeholder="Contoh: SJ-2026/MEI/045..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono uppercase text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Pengiriman / Ekspedisi</label>
                        <textarea name="notes" x-model="deliveryData.notes" rows="2" placeholder="Nama ekspedisi, supir, nomor polisi kendaraan, lokasi site..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-medium text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openDeliveryModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-5 py-2 text-xs font-bold text-white hover:bg-sky-700 focus:outline-none shadow-sm transition cursor-pointer">
                            Konfirmasi Pengiriman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Traceability Alur WIP & Rincian HPP -->
    <template x-teleport="body">
        <div x-show="openTraceModal" x-cloak @click.self="openTraceModal = false" @keydown.escape.window="openTraceModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-4xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-5 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto" x-show="traceItem">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3.5 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>Traceability Alur Produksi & Biaya WIP</span>
                                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 dark:text-indigo-400 px-2 py-0.5 rounded-full border border-indigo-200 dark:border-indigo-800">
                                    Finish Good Audit
                                </span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rincian pemakaian material dan jam kerja manpower yang membentuk barang jadi ini.</p>
                        </div>
                    </div>
                    <button type="button" @click="openTraceModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Product & Project Info Card -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Nama Barang Jadi</span>
                        <p class="font-extrabold text-gray-900 dark:text-white mt-0.5 text-sm" x-text="traceItem?.item_name || '-'"></p>
                        <span class="font-mono text-gray-500 dark:text-gray-400 text-[11px]" x-text="'Tag: ' + (traceItem?.item_code || '-')"></span>
                    </div>
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Project / Klien</span>
                        <p class="font-extrabold text-indigo-700 dark:text-indigo-300 mt-0.5 text-sm truncate" x-text="traceItem?.project?.name || '-'"></p>
                        <span class="font-mono text-gray-500 dark:text-gray-400 text-[11px]" x-text="'PO: ' + (traceItem?.project?.po_number || '-')"></span>
                    </div>
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Hasil Produksi</span>
                        <p class="font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5 text-sm font-mono" x-text="(Number(traceItem?.quantity || 0).toLocaleString('id-ID')) + ' ' + (traceItem?.unit || 'PCS')"></p>
                        <span class="text-gray-500 dark:text-gray-400 text-[11px]" x-text="'Tgl: ' + (traceItem?.production_date ? traceItem.production_date.substring(0, 10) : '-')"></span>
                    </div>
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-3 border border-gray-100 dark:border-gray-800">
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Status QC & Gudang</span>
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="traceItem?.qc_status === 'Passed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                  x-text="'QC: ' + (traceItem?.qc_status || 'Pending')"></span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="traceItem?.status === 'Delivered' ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800' : 'bg-gray-100 text-gray-700 border border-gray-200'"
                                  x-text="traceItem?.status || 'In Storage'"></span>
                        </div>
                    </div>
                </div>

                <!-- Financial HPP Breakdown Card -->
                @if(auth()->user() && auth()->user()->canViewPrices())
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 dark:border-indigo-900/50 dark:bg-indigo-950/20">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Total Biaya Material</span>
                            <p class="font-mono text-base font-extrabold text-gray-900 dark:text-white mt-0.5" x-text="'Rp ' + Number(traceItem?.material_cost || 0).toLocaleString('id-ID')"></p>
                            <span class="text-[10px] text-gray-400">Akumulasi bahan baku & consumable</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Total Upah Manpower</span>
                            <p class="font-mono text-base font-extrabold text-gray-900 dark:text-white mt-0.5" x-text="'Rp ' + Number(traceItem?.manpower_cost || 0).toLocaleString('id-ID')"></p>
                            <span class="text-[10px] text-gray-400">Upah langsung operator & teknisi</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-indigo-700 dark:text-indigo-300">Total HPP Barang Jadi</span>
                            <p class="font-mono text-lg font-black text-indigo-700 dark:text-indigo-400 mt-0.5" x-text="'Rp ' + Number(traceItem?.total_hpp || 0).toLocaleString('id-ID')"></p>
                            <span class="text-[10px] text-indigo-500 dark:text-indigo-400">Material + Manpower</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-300">HPP per Satuan Unit</span>
                            <p class="font-mono text-lg font-black text-emerald-700 dark:text-emerald-400 mt-0.5" x-text="'Rp ' + Number(traceItem?.hpp_per_unit || 0).toLocaleString('id-ID')"></p>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400" x-text="'Per 1 ' + (traceItem?.unit || 'PCS')"></span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Linked Material Outs & WIP Details Table -->
                <div class="space-y-2.5" x-data="{ stageFilter: 'all' }">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-2.5">
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-2">
                                <span>Material & Manpower Terpakai (WIP Breakdown)</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800"
                                      x-text="((traceItem?.material_outs || []).filter(m => stageFilter === 'all' || m.wip_stage === stageFilter).length) + ' Item Terfilter'"></span>
                            </h4>
                            <p class="text-[11px] text-gray-400">Pilih tahapan untuk men-trace material spesifik:</p>
                        </div>

                        <!-- Filter Tabs Tahapan -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" @click="stageFilter = 'all'"
                                    :class="stageFilter === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer">
                                Semua Tahap
                            </button>
                            <button type="button" @click="stageFilter = 'Cutting'"
                                    :class="stageFilter === 'Cutting' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 dark:bg-amber-950/60 dark:text-amber-300'"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                Tahap 1: Cutting
                            </button>
                            <button type="button" @click="stageFilter = 'Welding'"
                                    :class="stageFilter === 'Welding' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300'"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                Tahap 3: Welding
                            </button>
                            <button type="button" @click="stageFilter = 'Painting'"
                                    :class="stageFilter === 'Painting' ? 'bg-sky-600 text-white shadow-xs' : 'bg-sky-50 text-sky-800 hover:bg-sky-100 dark:bg-sky-950/60 dark:text-sky-300'"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                Tahap 3: Painting
                            </button>
                            <button type="button" @click="stageFilter = 'Packing'"
                                    :class="stageFilter === 'Packing' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300'"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Tahap 4: Packing
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-72 overflow-y-auto custom-scrollbar rounded-xl border border-gray-100 dark:border-gray-800">
                        <table class="w-full text-left text-xs">
                            <thead class="theme-table-header font-bold uppercase text-white sticky top-0 z-10 text-[11px]">
                                <tr>
                                    <th class="px-3 py-2.5 text-center w-10">NO</th>
                                    <th class="px-3 py-2.5">TANGGAL</th>
                                    <th class="px-3 py-2.5">TAHAP WIP</th>
                                    <th class="px-3 py-2.5">MATERIAL / BARANG</th>
                                    <th class="px-3 py-2.5 text-right">QTY KELUAR</th>
                                    @if(auth()->user() && auth()->user()->canViewPrices())
                                    <th class="px-3 py-2.5 text-right">BIAYA MATERIAL</th>
                                    <th class="px-3 py-2.5 text-right">MAN-HOURS & UPAH</th>
                                    @endif
                                    <th class="px-3 py-2.5">PIC / OPERATOR</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                                <template x-for="(mo, idx) in (traceItem?.material_outs || [])" :key="mo.id">
                                    <tr x-show="stageFilter === 'all' || mo.wip_stage === stageFilter" class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                                        <td class="px-3 py-2.5 text-center text-gray-400 font-mono" x-text="idx + 1"></td>
                                        <td class="px-3 py-2.5 font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap" x-text="mo.date ? mo.date.substring(0, 10) : '-'"></td>
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                  :class="{
                                                      'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800': mo.wip_stage === 'Cutting',
                                                      'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800': mo.wip_stage === 'Welding',
                                                      'bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800': mo.wip_stage === 'Painting',
                                                      'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800': mo.wip_stage === 'Packing',
                                                      'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400': !mo.wip_stage
                                                  }"
                                                  x-text="mo.wip_stage || 'Umum'"></span>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <span class="font-bold text-gray-900 dark:text-white block" x-text="mo.item?.name || mo.notes || 'Material'"></span>
                                            <span class="font-mono text-[10px] text-gray-400" x-text="mo.item?.code || '-'"></span>
                                        </td>
                                        <td class="px-3 py-2.5 text-right font-mono font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap"
                                            x-text="(Number(mo.quantity).toLocaleString('id-ID')) + ' ' + (mo.unit || 'PCS')"></td>
                                        @if(auth()->user() && auth()->user()->canViewPrices())
                                        <td class="px-3 py-2.5 text-right font-mono text-gray-800 dark:text-gray-200 whitespace-nowrap"
                                            x-text="'Rp ' + Number(Number(mo.quantity) * Number(mo.item?.unit_price || 0)).toLocaleString('id-ID')"></td>
                                        <td class="px-3 py-2.5 text-right font-mono text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                            <span class="font-bold" x-text="(mo.manpower_hours || 0) + ' Jam'"></span>
                                            <span class="text-[10px] text-gray-400 block" x-text="'(Rp ' + Number(mo.manpower_cost || 0).toLocaleString('id-ID') + ')'"></span>
                                        </td>
                                        @endif
                                        <td class="px-3 py-2.5 text-gray-600 dark:text-gray-300 whitespace-nowrap" x-text="mo.pic || '-'"></td>
                                    </tr>
                                </template>
                                <tr x-show="!traceItem?.material_outs || traceItem?.material_outs?.length === 0">
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center space-y-1">
                                            <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="text-xs font-semibold">Tidak ada transaksi material keluar yang tertaut langsung ke barang jadi ini.</span>
                                            <span class="text-[11px] text-gray-400">Data ini dibuat sebelum fitur penautan otomatis aktif atau nominal HPP diisi secara manual.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                    <a :href="'/projects/' + traceItem?.project_id"
                       class="inline-flex items-center gap-1.5 font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition">
                        <span>Buka Detail Project di Modul Project</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <button type="button" @click="openTraceModal = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- MODAL INPUT MATERIAL & MANPOWER WIP PER PROSES -->
    <template x-teleport="body">
        <div x-show="openWipModalState" x-cloak @click.self="openWipModalState = false" @keydown.escape.window="openWipModalState = false"
             class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn">
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 dark:border dark:border-gray-800 space-y-4 max-h-[92vh] overflow-y-auto custom-scrollbar my-auto">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/80 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">
                                Catat WIP: <span x-text="'Tahap ' + wipData.wip_stage"></span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Alokasikan material dari stok gudang dan catat upah jam kerja manpower.</p>
                        </div>
                    </div>
                    <button type="button" @click="openWipModalState = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('finish-goods.store-wip') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="wip_stage" :value="wipData.wip_stage">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Project -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pilih Project <span class="text-rose-500">*</span></label>
                            <select name="project_id" x-model="wipData.project_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">-- Pilih Project Tujuan --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} {{ $p->po_number ? '(PO: ' . $p->po_number . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Pengeluaran (Interactive Flatpickr Calendar) -->
                        <div class="space-y-1"
                             x-data="{
                                 fp: null,
                                 init() {
                                     const initPicker = () => {
                                         if (typeof flatpickr === 'undefined') {
                                             setTimeout(initPicker, 50);
                                             return;
                                         }
                                         if (this.fp) return;
                                         this.fp = flatpickr(this.$refs.wipDateInput, {
                                             dateFormat: 'Y-m-d',
                                             altInput: true,
                                             altFormat: 'd/m/Y',
                                             allowInput: false,
                                             clickOpens: true,
                                             defaultDate: wipData.date || '{{ date('Y-m-d') }}',
                                             altInputClass: 'w-full rounded-xl border border-gray-200 bg-gray-50 pl-3.5 pr-10 py-2 text-xs font-semibold text-gray-800 outline-none focus:border-amber-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer select-none transition shadow-2xs',
                                             onChange: (selectedDates, dateStr) => {
                                                 wipData.date = dateStr;
                                                 this.$refs.wipDateInput.value = dateStr;
                                             }
                                         });
                                     };
                                     this.$nextTick(() => initPicker());
                                     this.$watch('wipData.date', val => {
                                         if (this.fp && val && val !== this.fp.input.value) {
                                             this.fp.setDate(val, false);
                                         }
                                     });
                                 },
                                 openPicker() {
                                     if (this.fp) this.fp.open();
                                 },
                                 destroy() {
                                     if (this.fp) { this.fp.destroy(); this.fp = null; }
                                 }
                             }">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 cursor-pointer" @click="openPicker()">
                                Tanggal Pengeluaran <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative cursor-pointer" @click="openPicker()">
                                <input type="text" 
                                       x-ref="wipDateInput" 
                                       name="date" 
                                       :value="wipData.date" 
                                       required 
                                       class="hidden">
                                <button type="button" @click.stop="openPicker()" class="absolute right-3 top-2.5 text-gray-400 hover:text-amber-600 transition cursor-pointer" title="Buka Kalender">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Material Picker -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Material dari Stok Gudang <span class="text-rose-500">*</span></label>
                        <select name="item_id" x-model="wipData.item_id" @change="
                            const itm = @js($itemsList).find(i => String(i.id) === String($event.target.value));
                            if (itm) { wipData.unit = itm.unit; }
                        " required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">-- Pilih Material Gudang --</option>
                            @foreach($itemsList as $itm)
                                <option value="{{ $itm->id }}">
                                    {{ $itm->code_item ? '[' . $itm->code_item . '] ' : '' }}{{ $itm->name }} (Stok: {{ format_quantity($itm->stock) }} {{ $itm->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Kuantitas -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kuantitas Terpakai <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" min="0.0001" name="quantity" x-model="wipData.quantity" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                        </div>

                        <!-- Satuan -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <select name="unit" x-model="wipData.unit" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @foreach($units as $u)
                                    <option value="{{ $u->symbol }}">{{ $u->symbol }} ({{ $u->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tenaga Kerja / Manpower -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3.5 dark:border-amber-800/60 dark:bg-amber-950/30 space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-200">Perhitungan Upah Tenaga Kerja (Manpower WIP)</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Jam Kerja Tenaga Kerja (Hours)</label>
                                <input type="number" step="0.1" min="0" name="manpower_hours" x-model.number="wipData.manpower_hours" placeholder="0" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Tarif Upah per Jam (Rp / Jam)</label>
                                <input type="number" step="1000" min="0" name="manpower_rate_per_hour" x-model.number="wipData.manpower_rate_per_hour" placeholder="25000" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-amber-200/60 dark:border-amber-800/60">
                            <span class="text-gray-600 dark:text-gray-400">Total Biaya Tenaga Kerja:</span>
                            <span class="font-extrabold text-amber-700 dark:text-amber-300 font-mono"
                                  x-text="'Rp ' + ((wipData.manpower_hours || 0) * (wipData.manpower_rate_per_hour || 0)).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <!-- PIC & Catatan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">PIC Pelaksana / Mandor <span class="text-rose-500">*</span></label>
                            <input type="text" name="pic" x-model="wipData.pic" required placeholder="Nama operator / PIC..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Proses WIP</label>
                            <input type="text" name="notes" x-model="wipData.notes" placeholder="Contoh: Potong plat shell tangki..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800 outline-none focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="openWipModalState = false" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="rounded-xl bg-amber-600 px-5 py-2 text-xs font-bold text-white hover:bg-amber-700 shadow-xs cursor-pointer">
                            Simpan WIP & Potong Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

@endsection

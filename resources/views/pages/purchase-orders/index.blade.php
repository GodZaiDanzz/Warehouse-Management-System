@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path><rect x="9" y="3" width="6" height="4" rx="1"></rect><path d="M9 12h6"></path><path d="M9 16h6"></path></svg>
                </span>
                Purchase Order (PO)
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Manajemen pengadaan barang & pemesanan material untuk proyek atau stok gudang - PT Mudalaya Energy Indonesia
            </p>
        </div>
    </div>

    <!-- Livewire Purchase Order Component -->
    @livewire('purchase-order-component')
</div>
@endsection


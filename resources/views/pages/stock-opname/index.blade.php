@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                <span class="p-2 rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </span>
                Stock Opname Harian & Rolling Stock
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Pencatatan otomatis stok awal, input fisik sore penutupan, hitung pemakaian otomatis, dan bergulir ke hari esok secara berulang - PT Mudalaya Energy Indonesia.
            </p>
        </div>
    </div>
    @livewire('stock-opname-component')
</div>
@endsection

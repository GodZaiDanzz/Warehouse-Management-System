@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1" aria-label="Breadcrumb">
                <span>Sistem</span>
                <span>/</span>
                <span class="font-medium text-gray-600 dark:text-gray-300">Debugging</span>
                <span>/</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">Reset Data</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Data Sistem</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pemeliharaan dan inisialisasi ulang data operasional gudang PT Mudalaya Energy Indonesia</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('backup.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition shadow-xs">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span>Backup Database Dahulu</span>
            </a>
        </div>
    </div>

    <!-- Reset Data Livewire Component -->
    @livewire('reset-data-component')
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengeluaran Material (Material Keluar)</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Pencatatan material keluar gudang berdasarkan request PIC - PT Mudalaya Energy Indonesia</p>
    </div>

    <!-- Livewire Material Out Component -->
    @livewire('material-out-component')
</div>
@endsection

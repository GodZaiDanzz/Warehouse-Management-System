@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Penerimaan Material (Material Masuk)</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Pencatatan material masuk gudang dari vendor & supplier - PT Mudalaya Energy Indonesia</p>
    </div>

    <!-- Livewire Material In Component -->
    @livewire('material-in-component')
</div>
@endsection

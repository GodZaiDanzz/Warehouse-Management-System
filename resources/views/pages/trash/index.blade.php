@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Global Alert Notifications -->
    <x-alert />

    <!-- Livewire Trash Component -->
    @livewire('trash-component')
</div>
@endsection

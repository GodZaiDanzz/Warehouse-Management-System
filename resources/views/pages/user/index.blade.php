@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6" x-data="{ activeTab: 'profile' }">
    <!-- Page Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Akun & Profil Pengguna</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola informasi data diri administrator & keamanan kata sandi akun WMS</p>
        </div>
    </div>

    <!-- Alert Component -->
    <x-alert />

    <!-- Grid Container -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: User Summary Card -->
        <div class="lg:col-span-4 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm text-center space-y-4">
                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-brand-600 text-white font-bold text-3xl shadow-md">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $user->email }}</p>
                    @if($user->isPpic())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 mt-3 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            PPIC
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 mt-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                            Administrator Gudang
                        </span>
                    @endif
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 text-left text-xs space-y-3 text-gray-600 dark:text-gray-300">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Status Akun</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Aktif</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Hak Akses</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $user->getRoleDescription() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Tabbed Settings Forms -->
        <div class="lg:col-span-8 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-sm overflow-hidden">
                <!-- Navigation Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'border-brand-600 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-900 font-bold' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="flex items-center gap-2 px-6 py-4 text-sm border-b-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Edit Profile
                    </button>
                    <button @click="activeTab = 'password'" :class="activeTab === 'password' ? 'border-brand-600 text-brand-600 dark:text-brand-400 bg-white dark:bg-gray-900 font-bold' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="flex items-center gap-2 px-6 py-4 text-sm border-b-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Keamanan & Ganti Password
                    </button>
                </div>

                <!-- Tab 1: Edit Profile -->
                <div x-show="activeTab === 'profile'" class="p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Informasi Diri Administrator</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Perbarui nama pengguna dan alamat email akun Anda</p>
                    </div>

                    <form action="{{ route('user.update-profile') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700 transition cursor-pointer">
                                Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Change Password -->
                <div x-show="activeTab === 'password'" class="p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Ganti Kata Sandi (Password)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pastikan akun Anda menggunakan kata sandi yang kuat untuk keamanan sistem</p>
                    </div>

                    <form action="{{ route('user.update-password') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Password Saat Ini <span class="text-rose-500">*</span></label>
                            <input type="password" name="current_password" required placeholder="••••••••" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Password Baru <span class="text-rose-500">*</span></label>
                                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                                <input type="password" name="password_confirmation" required placeholder="Ulangi password baru" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-rose-700 transition cursor-pointer">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

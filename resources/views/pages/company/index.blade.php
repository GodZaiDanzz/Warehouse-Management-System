@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Company Profile</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pengaturan Identitas Perusahaan, Dual Logo & Nama Perusahaan (2 Nama & 2 Logo)</p>
        </div>
    </div>

    <!-- Alert Component -->
    <x-alert />

    <!-- Profile Edit Card Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ 
        logoPreview: '{{ $profile->logo_path ? asset($profile->logo_path) : '' }}',
        secondaryLogoPreview: '{{ $profile->secondary_logo_path ? asset($profile->secondary_logo_path) : '' }}',
        companyName1: '{{ addslashes($profile->name ?? 'PT MUDALAYA ENERGY INDONESIA') }}',
        companyName2: '{{ addslashes($profile->secondary_name ?? '') }}',
        companyTagline: '{{ addslashes($profile->tagline ?? '') }}',
        sidebarTitle: '{{ addslashes($profile->sidebar_title ?? 'WAREHOUSE') }}', 
        sidebarSubtitle: '{{ addslashes($profile->sidebar_subtitle ?? 'Management System') }}', 
        removeLogo: false,
        removeSecondaryLogo: false,
        submitting: false 
    }">
        
        <!-- Left Column: Current Logo & Brand Preview Card -->
        <div class="lg:col-span-4 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm text-center space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Preview Logo & Identitas</h3>
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                        Dual Identity
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 text-left">Tampilan logo dan identitas perusahaan yang digunakan pada Topbar, Dokumen Surat Jalan, dan Sistem.</p>
                
                <!-- Dual Logo Box -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 p-5 rounded-2xl bg-gray-50 dark:bg-gray-800/80 border border-dashed border-gray-300 dark:border-gray-700 min-h-[160px]">
                    <!-- Logo 1 Box -->
                    <div class="flex flex-col items-center justify-center p-2 flex-1 max-w-[150px]">
                        <span class="text-[10px] font-extrabold uppercase text-gray-400 mb-1.5 tracking-wider">Logo 1 (Utama)</span>
                        <template x-if="logoPreview && !removeLogo">
                            <div class="flex items-center justify-center h-20 w-20">
                                <img :src="logoPreview" alt="Logo 1" class="max-h-20 max-w-20 object-contain rounded-lg shadow-2xs">
                            </div>
                        </template>
                        <template x-if="!logoPreview || removeLogo">
                            <div class="flex flex-col items-center justify-center gap-1 py-2">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-brand-600 text-white font-black text-xl shadow-xs">
                                    {{ strtoupper(substr($profile->name ?? 'ME', 0, 2)) }}
                                </div>
                                <span class="text-[10px] font-semibold text-gray-400">Belum Ada</span>
                            </div>
                        </template>
                    </div>

                    <!-- Divider -->
                    <div class="hidden sm:block h-16 w-px bg-gray-300 dark:bg-gray-700"></div>
                    <div class="sm:hidden w-full h-px bg-gray-300 dark:bg-gray-700"></div>

                    <!-- Logo 2 Box -->
                    <div class="flex flex-col items-center justify-center p-2 flex-1 max-w-[150px]">
                        <span class="text-[10px] font-extrabold uppercase text-gray-400 mb-1.5 tracking-wider">Logo 2 (Kedua)</span>
                        <template x-if="secondaryLogoPreview && !removeSecondaryLogo">
                            <div class="flex items-center justify-center h-20 w-20">
                                <img :src="secondaryLogoPreview" alt="Logo 2" class="max-h-20 max-w-20 object-contain rounded-lg shadow-2xs">
                            </div>
                        </template>
                        <template x-if="!secondaryLogoPreview || removeSecondaryLogo">
                            <div class="flex flex-col items-center justify-center gap-1 py-2">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-black text-xl shadow-xs">
                                    <span x-text="companyName2 ? companyName2.substring(0, 2).toUpperCase() : '2'"></span>
                                </div>
                                <span class="text-[10px] font-semibold text-gray-400">Belum Ada</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Company Names Display -->
                <div class="pt-2 text-left space-y-2">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-brand-100 text-brand-800 dark:bg-brand-900/60 dark:text-brand-300">PT 1</span>
                            <h4 class="text-sm font-extrabold text-gray-900 dark:text-white truncate" x-text="companyName1 || 'PT Mudalaya Energy Indonesia'">{{ $profile->name }}</h4>
                        </div>
                    </div>
                    <template x-if="companyName2">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-brand-100 text-brand-800 dark:bg-brand-900/60 dark:text-brand-300">PT 2</span>
                                <h4 class="text-sm font-extrabold text-gray-900 dark:text-white truncate" x-text="companyName2">{{ $profile->secondary_name }}</h4>
                            </div>
                        </div>
                    </template>
                    <template x-if="companyTagline && companyTagline !== companyName2">
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider pt-1" x-text="companyTagline">{{ $profile->tagline }}</p>
                    </template>
                </div>

                <div class="text-left text-xs space-y-2 pt-4 border-t border-gray-100 dark:border-gray-800 text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="truncate">{{ $profile->email ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $profile->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <span class="truncate">{{ $profile->website ?? '-' }}</span>
                    </div>
                </div>

                <!-- Preview Topbar Header -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 text-left space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">Preview Header Topbar:</span>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-center gap-3">
                        <div class="flex items-center gap-2 shrink-0">
                            <template x-if="logoPreview && !removeLogo">
                                <img :src="logoPreview" class="h-8 w-8 sm:h-9 sm:w-9 object-contain" />
                            </template>
                            <template x-if="secondaryLogoPreview && !removeSecondaryLogo">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400 font-light text-sm">|</span>
                                    <img :src="secondaryLogoPreview" class="h-8 w-8 sm:h-9 sm:w-9 object-contain" />
                                </div>
                            </template>
                        </div>
                        <div class="flex flex-col text-left truncate justify-center">
                            <span class="font-extrabold text-xs sm:text-sm text-gray-900 dark:text-white leading-tight truncate" x-text="companyName1 || 'PT MUDALAYA ENERGY INDONESIA'"></span>
                            <span class="font-extrabold text-xs sm:text-sm text-gray-900 dark:text-white leading-tight truncate" x-text="companyName2"></span>
                            <span class="text-[9px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate mt-0.5" x-text="companyTagline"></span>
                        </div>
                    </div>
                </div>

                <!-- Preview Sidebar Header -->
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 text-left space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">Preview Header Sidebar:</span>
                    <div class="p-3 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white shadow-xs">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex flex-col truncate">
                            <span class="font-black text-sm text-gray-900 dark:text-white tracking-[0.14em] uppercase leading-tight truncate" x-text="sidebarTitle || 'WAREHOUSE'">
                                {{ $profile->sidebar_title ?? 'WAREHOUSE' }}
                            </span>
                            <span class="text-[9.5px] font-bold text-gray-500 dark:text-gray-400 tracking-[0.05em] uppercase truncate" x-text="sidebarSubtitle || 'Management System'">
                                {{ $profile->sidebar_subtitle ?? 'Management System' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Form Settings -->
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm space-y-6">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Identitas & Dual Logo Perusahaan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kelola 2 nama perusahaan dan unggah 2 logo resmi untuk keperluan sistem & dokumen</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-200 dark:border-brand-800">
                        <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>2 Nama & 2 Logo</span>
                    </span>
                </div>

                <form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit="submitting = true">
                    @csrf
                    @method('PUT')

                    <!-- Hidden fields for remove logo action -->
                    <input type="hidden" name="remove_logo" :value="removeLogo ? 1 : 0">
                    <input type="hidden" name="remove_secondary_logo" :value="removeSecondaryLogo ? 1 : 0">

                    <!-- SECTION: UPLOAD 2 LOGO -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-extrabold text-gray-900 dark:text-white">
                                Unggah Berkas Logo Perusahaan
                            </label>
                            <span class="text-[11px] text-gray-400">Format: PNG, JPG, SVG, WEBP (Maks. 5MB)</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Logo 1 (Utama) -->
                            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/40 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand-600 text-white font-extrabold text-xs">1</span>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Logo 1 (Perusahaan Utama)</span>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-100 text-brand-800 dark:bg-brand-900/60 dark:text-brand-300">Utama</span>
                                </div>

                                <!-- Preview Thumbnail -->
                                <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 min-h-[72px]">
                                    <template x-if="logoPreview && !removeLogo">
                                        <div class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-3">
                                                <img :src="logoPreview" alt="Logo 1" class="h-12 w-auto max-w-[120px] object-contain rounded-md" />
                                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Logo Terpasang</span>
                                                </span>
                                            </div>
                                            <button type="button" @click="removeLogo = true" class="text-xs text-rose-500 hover:text-rose-700 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus Logo 1">
                                                Hapus
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!logoPreview || removeLogo">
                                        <div class="flex items-center justify-between w-full">
                                            <span class="text-xs text-gray-400 italic">Belum ada Logo 1</span>
                                            <template x-if="removeLogo">
                                                <button type="button" @click="removeLogo = false" class="text-xs text-brand-600 font-bold hover:underline cursor-pointer">
                                                    Batalkan Hapus
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1">Pilih File Logo 1 Baru:</label>
                                    <input type="file" name="logo" accept="image/*" 
                                        @change="const file = $event.target.files[0]; if (file) { logoPreview = URL.createObjectURL(file); removeLogo = false; }"
                                        class="block w-full text-xs text-gray-900 dark:text-gray-100 
                                        file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 
                                        file:text-xs file:font-bold file:bg-brand-600 file:text-white 
                                        hover:file:bg-brand-700 dark:file:bg-brand-600 dark:file:text-white 
                                        border border-gray-300 dark:border-gray-700 rounded-xl cursor-pointer 
                                        bg-white dark:bg-gray-800 focus:outline-none">
                                </div>
                                <p class="text-[10.5px] text-gray-400">Contoh: Logo PT. Mudalaya Energy Indonesia</p>
                            </div>

                            <!-- Logo 2 (Kedua / Partner) -->
                            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/40 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand-600 text-white font-extrabold text-xs">2</span>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Logo 2 (Perusahaan Kedua)</span>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-100 text-brand-800 dark:bg-brand-900/60 dark:text-brand-300">Kedua / Partner</span>
                                </div>

                                <!-- Preview Thumbnail -->
                                <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 min-h-[72px]">
                                    <template x-if="secondaryLogoPreview && !removeSecondaryLogo">
                                        <div class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-3">
                                                <img :src="secondaryLogoPreview" alt="Logo 2" class="h-12 w-auto max-w-[120px] object-contain rounded-md" />
                                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Logo Terpasang</span>
                                                </span>
                                            </div>
                                            <button type="button" @click="removeSecondaryLogo = true" class="text-xs text-rose-500 hover:text-rose-700 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus Logo 2">
                                                Hapus
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!secondaryLogoPreview || removeSecondaryLogo">
                                        <div class="flex items-center justify-between w-full">
                                            <span class="text-xs text-gray-400 italic">Belum ada Logo 2</span>
                                            <template x-if="removeSecondaryLogo">
                                                <button type="button" @click="removeSecondaryLogo = false" class="text-xs text-brand-600 font-bold hover:underline cursor-pointer">
                                                    Batalkan Hapus
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1">Pilih File Logo 2 Baru:</label>
                                    <input type="file" name="secondary_logo" accept="image/*" 
                                        @change="const file2 = $event.target.files[0]; if (file2) { secondaryLogoPreview = URL.createObjectURL(file2); removeSecondaryLogo = false; }"
                                        class="block w-full text-xs text-gray-900 dark:text-gray-100 
                                        file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 
                                        file:text-xs file:font-bold file:bg-brand-600 file:text-white 
                                        hover:file:bg-brand-700 dark:file:bg-brand-600 dark:file:text-white 
                                        border border-gray-300 dark:border-gray-700 rounded-xl cursor-pointer 
                                        bg-white dark:bg-gray-800 focus:outline-none">
                                </div>
                                <p class="text-[10.5px] text-gray-400">Contoh: Logo PT. Mutiara Global Industry</p>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION: 2 NAMA PERUSAHAAN -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Identitas Nama Perusahaan</h4>
                            <span class="text-[10.5px] text-gray-400">Dukungan 2 Entitas PT</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Nama Perusahaan 1 (Utama) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" x-model="companyName1" value="{{ old('name', $profile->name) }}" required 
                                    placeholder="Contoh: PT. MUDALAYA ENERGY INDONESIA"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                <p class="text-[10.5px] text-gray-400">Nama entitas perusahaan utama</p>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Nama Perusahaan 2 (Kedua / Partner / Afiliasi)
                                </label>
                                <input type="text" name="secondary_name" x-model="companyName2" value="{{ old('secondary_name', $profile->secondary_name) }}" 
                                    placeholder="Contoh: PT. MUTIARA GLOBAL INDUSTRY"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                <p class="text-[10.5px] text-gray-400">Nama entitas perusahaan kedua</p>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Slogan / Tagline Perusahaan</label>
                            <input type="text" name="tagline" x-model="companyTagline" value="{{ old('tagline', $profile->tagline) }}" 
                                placeholder="Contoh: Energy, EPC & General Contractor"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- SECTION: KONTAK & ALAMAT -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Informasi Kontak & Lokasi Kantor</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Email Kantor</label>
                                <input type="email" name="email" value="{{ old('email', $profile->email) }}" 
                                    placeholder="contoh: info@mudalayaenergy.co.id"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nomor Telepon Kantor</label>
                                <input type="text" name="phone" value="{{ old('phone', $profile->phone) }}" 
                                    placeholder="contoh: +62 21 555-0199"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Website Perusahaan</label>
                            <input type="text" name="website" value="{{ old('website', $profile->website) }}" 
                                placeholder="contoh: www.mudalayaenergy.co.id"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Alamat Lengkap Perusahaan</label>
                            <textarea name="address" rows="2" placeholder="Alamat kantor / workshop perusahaan..." 
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">{{ old('address', $profile->address) }}</textarea>
                        </div>
                    </div>

                    <!-- Konfigurasi Header Sidebar -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Teks Header Sidebar Aplikasi</h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Atur teks judul dan sub-judul yang tampil di bagian atas sidebar menu</p>
                            </div>
                            <span class="text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 px-2.5 py-1 rounded-lg border border-brand-200 dark:border-brand-800">
                                Sidebar Brand
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Judul Utama Sidebar (Atas)</label>
                                <input type="text" name="sidebar_title" x-model="sidebarTitle" value="{{ old('sidebar_title', $profile->sidebar_title ?? 'WAREHOUSE') }}" placeholder="Contoh: WAREHOUSE"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none uppercase font-black tracking-wider">
                                <p class="text-[11px] text-gray-400">Default: <code class="font-bold text-gray-600 dark:text-gray-300">WAREHOUSE</code></p>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Sub-Judul Sidebar (Bawah)</label>
                                <input type="text" name="sidebar_subtitle" x-model="sidebarSubtitle" value="{{ old('sidebar_subtitle', $profile->sidebar_subtitle ?? 'Management System') }}" placeholder="Contoh: Management System"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                <p class="text-[11px] text-gray-400">Default: <code class="font-bold text-gray-600 dark:text-gray-300">Management System</code></p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                        <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition cursor-pointer disabled:opacity-75 btn-save-theme">
                            <template x-if="submitting">
                                <x-spinner />
                            </template>
                            <template x-if="!submitting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <span x-text="submitting ? 'Memproses & Menyimpan...' : 'Simpan Semua Perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

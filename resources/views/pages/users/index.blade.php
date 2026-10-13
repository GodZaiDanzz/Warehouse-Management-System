@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6 space-y-6" x-data="{ 
        openCreateModal: false, 
        openEditModal: false, 
        openDeleteModal: false,
        editUser: { id: null, name: '', email: '', role: 'admin' },
        deleteUserData: { id: null, name: '' }
    }">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Pengguna (User Management)</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pengelolaan akun administrator & petuga pergudangan PT Mudalaya Energy Indonesia</p>
        </div>
        
        <div>
            <button @click="openCreateModal = true" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-600 bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-sm cursor-pointer btn-add-theme">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pengguna Baru
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    <x-alert />

    <!-- Filter & Search Bar -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <form method="GET" action="{{ route('users.index') }}" class="flex gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email pengguna..." class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
            </div>
            <button type="submit" class="btn-save-theme rounded-xl bg-brand-600 hover:bg-brand-700 px-5 py-2.5 text-sm font-semibold text-white transition cursor-pointer shadow-xs">Cari</button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="theme-table-header text-xs uppercase text-white font-bold border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4 text-center">Hak Akses</th>
                        <th class="px-6 py-4 text-center">Tanggal Terdaftar</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($users as $usr)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-white font-bold text-xs shadow-xs">
                                    {{ strtoupper(substr($usr->name, 0, 2)) }}
                                </div>
                                <span>{{ $usr->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-gray-600 dark:text-gray-300">
                                {{ $usr->email }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($usr->role === 'ppic')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                        PPIC (View Only)
                                    </span>
                                @elseif($usr->role === 'qc')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Quality Control (QC)
                                    </span>
                                @elseif($usr->role === 'warehouse')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Gudang (Warehouse)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-800 dark:bg-brand-950/50 dark:text-brand-300 border border-brand-200 dark:border-brand-800/60 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                        Web Administrator
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-gray-500">
                                {{ format_date($usr->created_at) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Edit Button -->
                                    <button @click="
                                        editUser = { id: {{ $usr->id }}, name: '{{ addslashes($usr->name) }}', email: '{{ addslashes($usr->email) }}', role: '{{ $usr->role ?? 'admin' }}' };
                                        openEditModal = true;
                                    " type="button" class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/40 transition" title="Edit User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <form id="delete-user-form-{{ $usr->id }}" action="{{ route('users.destroy', $usr->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button @click="
                                        confirmDelete('Apakah Anda yakin ingin menghapus akun pengguna {{ addslashes($usr->name) }}?', () => {
                                            document.getElementById('delete-user-form-{{ $usr->id }}').submit();
                                        });
                                    " type="button" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400">Belum ada pengguna terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: TAMBAH PENGGUNA -->
    <template x-teleport="body">
        <div x-show="openCreateModal" x-cloak @click.self="openCreateModal = false" @keydown.escape.window="openCreateModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn" style="z-index: 999999;" aria-modal="true">
            <div x-show="openCreateModal" x-transition class="relative z-10 inline-block w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 my-8">
                <form action="{{ route('users.store') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Akun Pengguna Baru</h3>
                            <button type="button" @click="openCreateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-amber-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Staff PPIC" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Email Login <span class="text-amber-500">*</span></label>
                            <input type="email" name="email" required placeholder="ppic@mudalaya.com" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Hak Akses (Role) <span class="text-amber-500">*</span></label>
                            <select name="role" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer">
                                <option value="admin">Web Administrator (Full Access Seluruh Data Website, Sistem, Debugging & Profil)</option>
                                <option value="warehouse">Gudang / Warehouse (Kelola Data Stok & Transaksi Gudang)</option>
                                <option value="ppic">PPIC (View Only - Ketersediaan Stok, Riwayat & Laporan)</option>
                                <option value="qc">Quality Control (Inspeksi & Approval Penerimaan Barang)</option>
                            </select>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Web Administrator dapat mengelola semua data website. Role Gudang dibatasi dari menu Pengaturan System, Debugging, dan Company Profile.</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Password <span class="text-amber-500">*</span></label>
                            <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/80 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-200 dark:border-gray-800">
                        <button type="submit" :disabled="submitting" class="inline-flex justify-center items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white shadow-md hover:bg-brand-700 disabled:opacity-75 btn-save-theme cursor-pointer">
                            <template x-if="submitting"><x-spinner /></template>
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan User'"></span>
                        </button>
                        <button type="button" @click="openCreateModal = false" class="inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 cursor-pointer">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL 2: EDIT PENGGUNA -->
    <template x-teleport="body">
        <div x-show="openEditModal" x-cloak @click.self="openEditModal = false" @keydown.escape.window="openEditModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn" style="z-index: 999999;" aria-modal="true">
            <div x-show="openEditModal" x-transition class="relative z-10 inline-block w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 my-8">
                <form :action="'/users/' + editUser.id" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Data Pengguna</h3>
                            <button type="button" @click="openEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editUser.name" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Email Login</label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Hak Akses (Role)</label>
                            <select name="role" x-model="editUser.role" required class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 cursor-pointer">
                                <option value="admin">Web Administrator (Full Access Seluruh Data Website, Sistem, Debugging & Profil)</option>
                                <option value="warehouse">Gudang / Warehouse (Kelola Data Stok & Transaksi Gudang)</option>
                                <option value="ppic">PPIC (View Only - Ketersediaan Stok, Riwayat & Laporan)</option>
                                <option value="qc">Quality Control (Inspeksi & Approval Penerimaan Barang)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Password Baru (Opsional)</label>
                            <input type="password" name="password" placeholder="Kosongkan jika tidak diganti" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500">
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/80 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-200 dark:border-gray-800">
                        <button type="submit" :disabled="submitting" class="inline-flex justify-center items-center gap-2 rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white shadow-md hover:bg-brand-700 disabled:opacity-75 btn-save-theme cursor-pointer">
                            <template x-if="submitting"><x-spinner /></template>
                            <span x-text="submitting ? 'Memperbarui...' : 'Update User'"></span>
                        </button>
                        <button type="button" @click="openEditModal = false" class="inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 cursor-pointer">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection

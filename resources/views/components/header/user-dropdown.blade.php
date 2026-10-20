@php
    use App\Models\User;
    use App\Models\CompanyProfile;
    
    $currentUser = auth()->user();
    if (!$currentUser) {
        $currentUser = (object)[
            'name' => 'Tamu',
            'email' => '',
        ];
    }
    $company = CompanyProfile::getProfile();
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    openSupportModal: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- User Button -->
    <button
        class="flex items-center text-gray-700 dark:text-gray-300 hover:text-gray-900 focus:outline-none cursor-pointer"
        @click.prevent="toggleDropdown()"
        type="button"
    >
        <span class="mr-0 sm:mr-3 overflow-hidden rounded-full h-9 w-9 sm:h-10 sm:w-10 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
            @if($company->logo_path && file_exists(public_path($company->logo_path)))
                <img src="{{ $company->getFaviconUrl() ?? asset($company->logo_path) }}" alt="User" class="h-full w-full object-contain p-1" />
            @else
                <span class="w-full h-full bg-brand-600 text-white flex items-center justify-center">
                    {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                </span>
            @endif
        </span>

        <span class="hidden sm:block mr-1 font-bold text-sm text-gray-900 dark:text-white max-w-[120px] truncate">{{ strtok($currentUser->name, ' ') }}</span>

        <!-- Chevron Icon -->
        <svg
            class="hidden sm:block w-4 h-4 transition-transform duration-200 text-gray-500"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-3 flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-800 dark:bg-gray-900 z-50"
    >
        <!-- User Info -->
        <div class="pb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between gap-2">
                <span class="block font-bold text-sm text-gray-900 dark:text-white truncate">{{ $currentUser->name }}</span>
                @if(isset($currentUser->role) && $currentUser->role === 'ppic')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-700 shrink-0">PPIC</span>
                @elseif(isset($currentUser->role) && $currentUser->role === 'warehouse')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-700 shrink-0">Gudang</span>
                @elseif(isset($currentUser->role) && $currentUser->role === 'qc')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-700 shrink-0">QC</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-800 dark:bg-brand-950/60 dark:text-brand-300 border border-brand-200 shrink-0">Web Admin</span>
                @endif
            </div>
            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $currentUser->email }}</span>
        </div>

        <!-- Quick Switch User (Hanya untuk Web Administrator) -->
        @if(auth()->check() && auth()->user()->isWebAdmin())
        <div class="py-2 border-b border-gray-100 dark:border-gray-800">
            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5 px-1">Ganti Akun:</span>
            <div class="flex flex-col gap-1">
                @foreach(User::all() as $u)
                    <a href="{{ route('switch-user', $u->id) }}" 
                        class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ isset($currentUser->id) && $currentUser->id === $u->id ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 font-bold border border-brand-200 dark:border-brand-800' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                        <span class="truncate max-w-[130px]">{{ $u->name }}</span>
                        <span class="text-[10px] font-mono uppercase font-bold text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            {{ $u->role === 'ppic' ? 'PPIC' : ($u->role === 'warehouse' ? 'Gudang' : ($u->role === 'qc' ? 'QC' : 'Web Admin')) }}
                            @if(isset($currentUser->id) && $currentUser->id === $u->id)
                                <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Menu Items -->
        <ul class="flex flex-col gap-1 py-2 border-b border-gray-100 dark:border-gray-800">
            <!-- Edit Profile -->
            <li>
                <a href="{{ route('user.index') }}" @click="closeDropdown()"
                    class="flex items-center gap-3 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Edit Profile
                </a>
            </li>


            <!-- Support -->
            <li>
                <button type="button" @click="closeDropdown(); openSupportModal = true"
                    class="w-full flex items-center gap-3 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition text-left cursor-pointer">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Support & Bantuan
                </button>
            </li>
        </ul>

        <!-- Sign Out -->
        <a href="{{ route('logout') }}" @click="closeDropdown()"
            class="flex items-center gap-3 px-3 py-2.5 mt-1 text-xs font-bold text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Sign Out
        </a>
    </div>

    <!-- Modal Support Bantuan -->
    <template x-teleport="body">
        <div x-show="openSupportModal" x-cloak @click.self="openSupportModal = false" @keydown.escape.window="openSupportModal = false" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4 bg-gray-950/80 backdrop-blur-xs overflow-y-auto animate-fadeIn" aria-modal="true">
            <div x-show="openSupportModal" x-transition class="relative z-10 inline-block w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white text-left shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-900 p-6 my-auto max-h-[92vh] overflow-y-auto custom-scrollbar space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Bantuan & Dukungan WMS</h3>
                    </div>
                    <button type="button" @click="openSupportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="text-xs space-y-3 text-gray-600 dark:text-gray-300">
                    <p class="font-medium">Sistem Manajemen Stok Gudang dikembangkan untuk operasional <strong>{{ $company->name }}</strong>.</p>
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 space-y-2 border border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between"><span class="font-bold">Versi Aplikasi:</span> <span>WMS v1.0.0 (Laravel 10)</span></div>
                        <div class="flex justify-between"><span class="font-bold">Email Support:</span> <span>{{ $company->email ?? 'support@mudalaya.com' }}</span></div>
                        <div class="flex justify-between"><span class="font-bold">Telepon Support:</span> <span>{{ $company->phone ?? '+62 21 555-0199' }}</span></div>
                    </div>
                </div>
                <div class="pt-2 flex justify-end">
                    <button type="button" @click="openSupportModal = false" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 cursor-pointer">Tutup</button>
                </div>
            </div>
        </div>
    </template>
</div>

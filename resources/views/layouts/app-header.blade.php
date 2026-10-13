<header
    class="sticky top-0 flex w-full bg-white border-gray-200 z-99999 dark:border-gray-800 dark:bg-gray-900 border-b"
    x-data="{
        isApplicationMenuOpen: false,
        toggleApplicationMenu() {
            this.isApplicationMenuOpen = !this.isApplicationMenuOpen;
        }
    }">
    @php
        $headerCompany = \App\Models\CompanyProfile::getProfile();
    @endphp
    <div class="flex items-center justify-between w-full px-3 py-2.5 sm:px-6">
        <!-- LEFT: Desktop & Mobile Sidebar Toggle Buttons -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Desktop Sidebar Toggle Button (visible on xl and up) -->
            <button
                class="hidden xl:flex items-center justify-center w-10 h-10 text-gray-500 border border-gray-200 rounded-lg dark:border-gray-800 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded }"
                @click="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                        fill="currentColor"></path>
                </svg>
            </button>

            <!-- Mobile Menu Toggle Button (visible below xl) -->
            <button
                type="button"
                class="flex xl:hidden items-center justify-center w-10 h-10 text-gray-700 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-xs hover:bg-gray-50 dark:hover:bg-gray-700/80 active:scale-95 transition cursor-pointer shrink-0"
                :class="{ 'bg-gray-100 dark:bg-gray-700 ring-2 ring-brand-500/20': $store.sidebar && $store.sidebar.isMobileOpen }"
                @click="$store.sidebar.toggleMobileOpen()"
                aria-label="Toggle Mobile Menu"
                title="Buka Menu Sidebar">
                <!-- High-Visibility 3-Bar Hamburger Icon -->
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- CENTER: COMPANY PROFILE (ATAS TENGAH - STATIC DISPLAY / NOT CLICKABLE) -->
        <div class="flex items-center justify-center min-w-0 px-1 sm:px-2">
            <div class="flex items-center gap-2 sm:gap-3.5 select-none cursor-default text-center">
                <!-- Logos Container -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    @if($headerCompany->hasPrimaryLogo())
                        <img src="{{ asset($headerCompany->logo_path) }}?v={{ filemtime(public_path($headerCompany->logo_path)) }}" alt="Logo 1" class="h-8 w-8 sm:h-10 sm:w-10 object-contain flex-shrink-0" />
                    @else
                        <div class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white font-extrabold text-xs sm:text-sm shadow-xs">
                            {{ strtoupper(substr($headerCompany->name, 0, 2)) }}
                        </div>
                    @endif

                    @if($headerCompany->hasSecondaryLogo())
                        <span class="text-gray-300 dark:text-gray-700 font-light text-base select-none">|</span>
                        <img src="{{ asset($headerCompany->secondary_logo_path) }}?v={{ filemtime(public_path($headerCompany->secondary_logo_path)) }}" alt="Logo 2" class="h-8 w-8 sm:h-10 sm:w-10 object-contain flex-shrink-0" />
                    @endif
                </div>

                <!-- Company Names -->
                <div class="flex flex-col text-left truncate justify-center">
                    <span class="font-extrabold text-xs sm:text-sm text-gray-900 dark:text-white leading-tight truncate max-w-[120px] 2xsm:max-w-[150px] sm:max-w-[280px] md:max-w-[420px]">
                        {{ $headerCompany->name }}
                    </span>
                    @if($headerCompany->secondary_name)
                        <span class="hidden sm:inline-block font-extrabold text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-tight truncate max-w-[120px] 2xsm:max-w-[150px] sm:max-w-[280px] md:max-w-[420px]">
                            {{ $headerCompany->secondary_name }}
                        </span>
                    @endif
                    @if($headerCompany->tagline && $headerCompany->tagline !== $headerCompany->secondary_name)
                        <span class="hidden md:inline-block text-[9.5px] sm:text-[10px] font-bold text-gray-500 dark:text-gray-400 tracking-wider truncate max-w-[150px] sm:max-w-[280px] uppercase mt-0.5">
                            {{ $headerCompany->tagline }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT: Notification & User Dropdown -->
        <div class="flex items-center gap-2 2xsm:gap-3 shrink-0">
            <!-- Notification Dropdown -->
            <x-header.notification-dropdown />

            <!-- User Dropdown -->
            <x-header.user-dropdown />
        </div>
    </div>
</header>

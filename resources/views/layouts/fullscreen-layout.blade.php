<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }}</title>

    <!-- Google Fonts Outfit (Preconnect & Non-blocking) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @php
        $globalCompany = \App\Models\CompanyProfile::getProfile();
        $globalFaviconUrl = $globalCompany->getFaviconUrl();
    @endphp

    @if($globalFaviconUrl)
        <!-- Favicon Presisi Tanpa Margin Putih -->
        <link rel="icon" type="image/png" href="{{ $globalFaviconUrl }}">
        <link rel="shortcut icon" href="{{ $globalFaviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $globalFaviconUrl }}">
    @endif

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    try {
                        localStorage.setItem('theme', 'light');
                    } catch (e) {}
                    this.theme = 'light';
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    // Dark mode dinonaktifkan: wajib selalu menggunakan light mode
                    this.theme = 'light';
                    try {
                        localStorage.setItem('theme', 'light');
                    } catch (e) {}
                    this.updateTheme();
                },
                updateTheme() {
                    document.documentElement.classList.remove('dark');
                    if (document.body) {
                        document.body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            let isExpandedInitial = window.innerWidth >= 1280;
            try {
                const saved = localStorage.getItem('sidebar_expanded');
                if (saved !== null) {
                    isExpandedInitial = window.innerWidth >= 1280 ? (saved === 'true') : false;
                } else if (window.innerWidth < 1280) {
                    isExpandedInitial = false;
                }
            } catch (e) {}

            Alpine.store('sidebar', {
                // Initialize based on screen size & saved preference
                isExpanded: isExpandedInitial,
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                    try {
                        localStorage.setItem('sidebar_expanded', this.isExpanded ? 'true' : 'false');
                    } catch (e) {}
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    this.isHovered = false;
                }
            });
        });
    </script>

    <!-- Wajib Light Mode: Hapus dark mode dan bersihkan storage -->
    <script>
        (function() {
            try {
                localStorage.setItem('theme', 'light');
            } catch (e) {}
            document.documentElement.classList.remove('dark');
            const removeDark = () => {
                document.documentElement.classList.remove('dark');
                if (document.body) {
                    document.body.classList.remove('dark', 'bg-gray-900');
                }
            };
            removeDark();
            document.addEventListener('DOMContentLoaded', removeDark);
        })();
    </script>
</head>

<body x-data="{ 'loaded': true}" x-init="
    if (typeof $store !== 'undefined' && $store.sidebar) {
        if (window.innerWidth < 1280) {
            $store.sidebar.isExpanded = false;
        } else {
            let saved = null;
            try {
                saved = localStorage.getItem('sidebar_expanded');
            } catch (e) {}
            $store.sidebar.isExpanded = saved !== null ? (saved === 'true') : true;
        }
    }
">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    @yield('content')

    @livewireScripts
</body>

@stack('scripts')

</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }}</title>

    <!-- Google Fonts Outfit (Preconnect & High-Performance Asynchronous Loading) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap">
    </noscript>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @php
        $globalCompany = \App\Models\CompanyProfile::getProfile();
        $globalThemeColor = $globalCompany->theme_color ?? '#275538';
        $globalBtnAddColor = $globalCompany->btn_add_color ?? $globalThemeColor;
        $globalBtnSaveColor = $globalCompany->btn_save_color ?? $globalThemeColor;
        $globalBtnOtherColor = $globalCompany->btn_other_color ?? $globalThemeColor;
        $globalFaviconUrl = $globalCompany->getFaviconUrl();
    @endphp

    <meta name="theme-color" content="{{ $globalThemeColor }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    @if($globalFaviconUrl)
        <!-- Favicon Presisi Tanpa Margin Putih -->
        <link rel="icon" type="image/png" href="{{ $globalFaviconUrl }}">
        <link rel="shortcut icon" href="{{ $globalFaviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $globalFaviconUrl }}">
    @endif
    <!-- Dynamic Website Theme Style -->
    <style>
        :root {
            --theme-table-header-bg: {{ $globalThemeColor }};
            --theme-btn-add-bg: {{ $globalBtnAddColor }};
            --theme-btn-save-bg: {{ $globalBtnSaveColor }};
            --theme-btn-other-bg: {{ $globalBtnOtherColor }};
            --theme-accent-amber: #F59E0B;
            --theme-accent-amber-hover: #D97706;
        }
        /* Mobile hardware acceleration & responsive performance */
        .content-visibility-auto {
            content-visibility: auto;
            contain-intrinsic-size: 0 450px;
        }
        @media (max-width: 768px) {
            .mobile-hw-accel {
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
            }
        }
        [x-cloak] {
            display: none !important;
        }
        .swal2-container {
            z-index: 99999999 !important;
        }
        .theme-table-header,
        table thead,
        table thead tr,
        table thead th,
        table thead tr.theme-table-header,
        table thead.theme-table-header {
            background-color: var(--theme-table-header-bg) !important;
            color: #ffffff !important;
        }
        .theme-table-header th,
        table thead th,
        table thead tr th,
        table thead tr.theme-table-header th,
        table thead.theme-table-header th {
            background-color: var(--theme-table-header-bg) !important;
            color: #ffffff !important;
        }

        /* Dynamic Custom Button Theme Styles */
        .btn-add-theme {
            background-color: var(--theme-btn-add-bg) !important;
            border-color: var(--theme-btn-add-bg) !important;
            color: #ffffff !important;
            transition: all 0.2s ease-in-out;
        }
        .btn-add-theme:hover {
            filter: brightness(0.9);
            opacity: 0.95;
        }
        .btn-save-theme {
            background-color: var(--theme-btn-save-bg) !important;
            border-color: var(--theme-btn-save-bg) !important;
            color: #ffffff !important;
            transition: all 0.2s ease-in-out;
        }
        .btn-save-theme:hover {
            filter: brightness(0.9);
            opacity: 0.95;
        }
        .btn-other-theme {
            background-color: var(--theme-btn-other-bg) !important;
            border-color: var(--theme-btn-other-bg) !important;
            color: #ffffff !important;
            transition: all 0.2s ease-in-out;
        }
        .btn-other-theme:hover {
            filter: brightness(0.9);
            opacity: 0.95;
        }
        .btn-accent-amber:hover {
            background-color: var(--theme-accent-amber-hover) !important;
            border-color: var(--theme-accent-amber-hover) !important;
        }

        /* Flatpickr Calendar High Z-Index for Modals & Overlay Dialogs */
        .flatpickr-calendar {
            z-index: 99999999 !important;
        }

        /* ApexCharts Tooltip High-Contrast White Text Fix */
        .apexcharts-tooltip {
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #ffffff !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
        }
        .apexcharts-tooltip-title {
            background: #1e293b !important;
            border-bottom: 1px solid #334155 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        .apexcharts-tooltip-text,
        .apexcharts-tooltip-text-y-value,
        .apexcharts-tooltip-text-y-label,
        .apexcharts-tooltip-text-goals-value,
        .apexcharts-tooltip-text-z-value,
        .apexcharts-tooltip-series-group span,
        .apexcharts-tooltip * {
            color: #ffffff !important;
            font-weight: 600 !important;
        }
    </style>

    <!-- Theme & Sidebar Stores -->
    <script>
        (function() {
            function getInitialSidebarExpanded() {
                if (window.innerWidth < 1280) return false;
                try {
                    const saved = localStorage.getItem('sidebar_expanded');
                    if (saved !== null) {
                        return saved === 'true';
                    }
                } catch (e) {}
                return true;
            }

            function createSidebarStore(Alpine) {
                if (!Alpine) return;
                if (!Alpine.store('sidebar')) {
                    Alpine.store('sidebar', {
                        isExpanded: getInitialSidebarExpanded(),
                        isMobileOpen: false,
                        isHovered: false,

                        toggleExpanded() {
                            this.isExpanded = !this.isExpanded;
                            this.isMobileOpen = false;
                            try {
                                localStorage.setItem('sidebar_expanded', this.isExpanded ? 'true' : 'false');
                            } catch (e) {}
                        },
                        toggleMobileOpen() {
                            this.isMobileOpen = !this.isMobileOpen;
                        },
                        setMobileOpen(val) {
                            this.isMobileOpen = val;
                        },
                        setHovered(val) {
                            this.isHovered = false;
                        }
                    });
                }
                if (!Alpine.store('theme')) {
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
                }
            }

            document.addEventListener('alpine:init', () => {
                if (window.Alpine) createSidebarStore(window.Alpine);
            });
            document.addEventListener('livewire:init', () => {
                if (window.Alpine) createSidebarStore(window.Alpine);
            });
            if (window.Alpine) {
                createSidebarStore(window.Alpine);
            }
        })();
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

<body
    x-data="{ 'loaded': true}"
    x-init="
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
        const checkMobile = () => {
            if (typeof $store !== 'undefined' && $store.sidebar) {
                if (window.innerWidth < 1280) {
                    $store.sidebar.setMobileOpen(false);
                    $store.sidebar.isExpanded = false;
                } else {
                    $store.sidebar.isMobileOpen = false;
                    let saved = null;
                    try {
                        saved = localStorage.getItem('sidebar_expanded');
                    } catch (e) {}
                    $store.sidebar.isExpanded = saved !== null ? (saved === 'true') : true;
                }
            }
        };
        window.addEventListener('resize', checkMobile);
    ">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 min-w-0 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[272px]': (typeof $store !== 'undefined' && $store.sidebar) ? $store.sidebar.isExpanded : true,
                'xl:ml-[85px]': (typeof $store !== 'undefined' && $store.sidebar) ? !$store.sidebar.isExpanded : false,
                'ml-0': (typeof $store !== 'undefined' && $store.sidebar) ? $store.sidebar.isMobileOpen : false
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto w-full max-w-7xl md:p-6 min-w-0 overflow-x-hidden">
                <x-alert />
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </div>

    </div>

    @livewireScripts
</body>

@stack('scripts')

</html>

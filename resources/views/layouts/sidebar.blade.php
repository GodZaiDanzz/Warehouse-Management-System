
@php
    use App\Helpers\MenuHelper;
    use App\Models\CompanyProfile;
    $menuGroups = MenuHelper::getMenuGroups();

    // Get current path
    $currentPath = request()->path();
    $companyProfile = CompanyProfile::getProfile();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 px-4.5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200"
    x-data="{
        openSubmenus: {},
        init() {
            // Auto-open Dashboard menu on page load
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        // Check if any submenu item matches current path
                        @foreach ($item['subItems'] as $subItem)
                            if (currentPath === '{{ ltrim($subItem['path'], '/') }}' ||
                                window.location.pathname === '{{ $subItem['path'] }}') {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                            } @endforeach
            @endif
            @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];

            // Close all other submenus when opening a new one
            if (newState) {
                this.openSubmenus = {};
            }

            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            return window.location.pathname === path || '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }"
    :class="{
        'w-[272px]': $store.sidebar ? ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen) : true,
        'w-[85px]': $store.sidebar ? (!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) : false,
        'translate-x-0': $store.sidebar ? $store.sidebar.isMobileOpen : false,
        '-translate-x-full xl:translate-x-0': $store.sidebar ? !$store.sidebar.isMobileOpen : true
    }">
    <!-- Logo & System Brand Section -->
    <div class="pt-5 pb-4.5 flex items-center justify-between border-b border-gray-100 dark:border-gray-800/80 mb-3"
        :class="($store.sidebar && !$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ?
        'xl:justify-center' :
        'justify-between'">
        <a href="/" class="flex items-center gap-3 overflow-hidden group">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white shadow-xs group-hover:scale-105 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            
            <div x-show="!$store.sidebar || ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)" class="flex flex-col truncate">
                <span class="font-black text-sm text-gray-900 dark:text-white tracking-[0.14em] uppercase leading-tight truncate">
                    {{ $companyProfile->sidebar_title ?? 'WAREHOUSE' }}
                </span>
                <span class="text-[9.5px] font-bold text-gray-500 dark:text-gray-400 tracking-[0.05em] uppercase truncate">
                    {{ $companyProfile->sidebar_subtitle ?? 'Management System' }}
                </span>
            </div>
        </a>

        <!-- Mobile Close Button (visible below xl when mobile drawer is open) -->
        <button
            type="button"
            x-show="$store.sidebar && $store.sidebar.isMobileOpen"
            @click="$store.sidebar.setMobileOpen(false)"
            class="flex xl:hidden items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer"
            aria-label="Tutup Menu"
            title="Tutup Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav class="mb-6">
            <div class="flex flex-col gap-3.5">
                @foreach ($menuGroups as $groupIndex => $menuGroup)
                    <div>
                        <!-- Menu Group Title -->
                        @if(!empty($menuGroup['title']))
                            <h2 class="mb-2.5 text-[11px] uppercase flex leading-[18px] text-gray-400 dark:text-gray-500 font-bold tracking-wider"
                                :class="($store.sidebar && !$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen) ?
                                'lg:justify-center' : 'justify-start'">
                                <template
                                    x-if="!$store.sidebar || ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">
                                    <span>{{ $menuGroup['title'] }}</span>
                                </template>
                                <template x-if="$store.sidebar && !$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                      <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                                    </svg>
                                </template>
                            </h2>
                        @endif

                        <!-- Menu Items -->
                        <ul class="flex flex-col gap-1">
                            @foreach ($menuGroup['items'] as $itemIndex => $item)
                                <li>
                                    @if (isset($item['subItems']))
                                        <!-- Menu Item with Submenu -->
                                        <button @click="toggleSubmenu({{ $groupIndex }}, {{ $itemIndex }})"
                                            class="menu-item group w-full"
                                            :title="(!$store.sidebar || !$store.sidebar.isExpanded) ? '{{ $item['name'] }}' : ''"
                                            :class="[
                                                isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) ?
                                                'menu-item-active' : 'menu-item-inactive',
                                                (!$store.sidebar || (!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen)) ?
                                                'xl:justify-center' : 'xl:justify-start'
                                            ]">

                                            <!-- Icon -->
                                            <span class="shrink-0 flex items-center justify-center [&>svg]:w-[22px] [&>svg]:h-[22px]"
                                                :class="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) ?
                                                    'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <!-- Text -->
                                            <span
                                                x-show="$store.sidebar && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)"
                                                class="menu-item-text flex items-center gap-2.5 text-sm truncate font-medium">
                                                {{ $item['name'] }}
                                            </span>

                                            <!-- Chevron Down Icon -->
                                            <svg x-show="$store.sidebar && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)"
                                                class="ml-auto w-4.5 h-4.5 transition-transform duration-200"
                                                :class="{
                                                    'rotate-180 text-brand-500': isSubmenuOpen({{ $groupIndex }},
                                                        {{ $itemIndex }})
                                                }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <!-- Submenu -->
                                        <div x-show="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) && $store.sidebar && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)">
                                            <ul class="mt-1.5 space-y-1 ml-8">
                                                @foreach ($item['subItems'] as $subItem)
                                                    <li>
                                                        <a href="{{ $subItem['path'] }}" class="menu-dropdown-item"
                                                            :class="isActive('{{ $subItem['path'] }}') ?
                                                                'menu-dropdown-item-active' :
                                                                'menu-dropdown-item-inactive'">
                                                            {{ $subItem['name'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <!-- Simple Menu Item -->
                                        <a href="{{ $item['path'] }}" class="menu-item group"
                                            :title="(!$store.sidebar || !$store.sidebar.isExpanded) ? '{{ $item['name'] }}' : ''"
                                            :class="[
                                                isActive('{{ $item['path'] }}') ? 'menu-item-active' :
                                                'menu-item-inactive',
                                                (!$store.sidebar || (!$store.sidebar.isExpanded && !$store.sidebar.isMobileOpen)) ?
                                                'xl:justify-center' :
                                                'justify-start'
                                            ]">

                                            <!-- Icon -->
                                            <span class="shrink-0 flex items-center justify-center [&>svg]:w-[22px] [&>svg]:h-[22px]"
                                                :class="isActive('{{ $item['path'] }}') ? 'menu-item-icon-active' :
                                                    'menu-item-icon-inactive'">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <!-- Text -->
                                            <span
                                                x-show="$store.sidebar && ($store.sidebar.isExpanded || $store.sidebar.isMobileOpen)"
                                                class="menu-item-text flex items-center gap-2.5 text-sm truncate font-medium">
                                                {{ $item['name'] }}
                                            </span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>

    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    class="fixed z-50 h-screen w-full bg-gray-900/50"></div>

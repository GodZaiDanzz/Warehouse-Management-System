<div
    x-data
    x-show="$store.sidebar && $store.sidebar.isMobileOpen"
    x-cloak
    @click="$store.sidebar.setMobileOpen(false)"
    class="fixed inset-0 z-40 bg-gray-900/50 xl:hidden"
></div>

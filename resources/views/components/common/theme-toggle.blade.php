<button
    x-data="{ theme: 'light' }"
    x-init="
        document.documentElement.classList.remove('dark');
        try { localStorage.setItem('theme', 'light'); } catch (e) {}
    "
    class="hidden"
>
</button>

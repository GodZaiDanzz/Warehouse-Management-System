<div
  id="app-preloader"
  x-show="loaded"
  x-init="setTimeout(() => loaded = false, 80); window.addEventListener('DOMContentLoaded', () => loaded = false)"
  x-transition.opacity.duration.200ms
  class="fixed left-0 top-0 z-[99999] flex h-screen w-screen flex-col items-center justify-center bg-white/95 backdrop-blur-xs transition-opacity duration-200 pointer-events-none will-change-opacity"
>
  <div class="relative flex items-center justify-center">
      <div class="h-12 w-12 animate-spin rounded-full border-3 border-solid border-brand-600 border-t-transparent shadow-md"></div>
  </div>
  <p class="mt-3 text-xs font-bold text-gray-700 tracking-wider">Loading...</p>
</div>

<script>
  (function() {
    function hidePreloader() {
      var p = document.getElementById('app-preloader');
      if (p) {
        p.style.opacity = '0';
        p.style.pointerEvents = 'none';
        setTimeout(function() {
          if (p && p.parentNode) {
            p.remove();
          }
        }, 150);
      }
    }
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
      setTimeout(hidePreloader, 50);
    } else {
      document.addEventListener('DOMContentLoaded', hidePreloader);
      window.addEventListener('load', hidePreloader);
      setTimeout(hidePreloader, 150);
    }
  })();
</script>

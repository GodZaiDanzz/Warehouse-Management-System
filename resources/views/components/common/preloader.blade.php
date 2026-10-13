<div
  id="app-preloader"
  x-show="loaded"
  x-init="setTimeout(() => loaded = false, 200); window.addEventListener('DOMContentLoaded', () => loaded = false)"
  x-transition.opacity.duration.300ms
  class="fixed left-0 top-0 z-[99999] flex h-screen w-screen flex-col items-center justify-center bg-white/95 backdrop-blur-xs transition-opacity duration-300"
>
  <div class="relative flex items-center justify-center">
      <div class="h-14 w-14 animate-spin rounded-full border-4 border-solid border-brand-600 border-t-transparent shadow-md"></div>
  </div>
  <p class="mt-3 text-xs font-bold text-gray-700 tracking-wider animate-pulse">Loading...</p>
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
            p.style.display = 'none';
          }
        }, 200);
      }
    }
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
      setTimeout(hidePreloader, 100);
    } else {
      document.addEventListener('DOMContentLoaded', hidePreloader);
      window.addEventListener('load', hidePreloader);
      setTimeout(hidePreloader, 300);
    }
  })();
</script>

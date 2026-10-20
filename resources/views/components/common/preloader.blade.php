<!-- High-performance top-level progress bar (Zero render-blocking, zero FCP penalty) -->
<div
  id="app-preloader"
  class="fixed top-0 left-0 right-0 z-[99999] h-[2.5px] bg-brand-600 dark:bg-brand-400 transition-all duration-300 pointer-events-none shadow-xs"
  style="width: 0%; opacity: 0;"
></div>

<script>
  (function() {
    var p = document.getElementById('app-preloader');
    if (p) {
      p.style.opacity = '1';
      p.style.width = '70%';
      function removePreloader() {
        if (!p) return;
        p.style.width = '100%';
        setTimeout(function() {
          p.style.opacity = '0';
          setTimeout(function() {
            if (p && p.parentNode) p.remove();
          }, 200);
        }, 60);
      }
      if (document.readyState === 'complete') {
        removePreloader();
      } else {
        window.addEventListener('load', removePreloader, { once: true });
        setTimeout(removePreloader, 400);
      }
    }
  })();
</script>

<script>
// Scroll to section on hash
if (window.location.hash === '#best-sellers') {
  var el = document.getElementById('best-sellers');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
}

// Animated stat counters — fires once when the stats bar enters the viewport
(function () {
  var animated = false;
  function runCounters() {
    if (animated) return;
    animated = true;
    document.querySelectorAll('.count-up').forEach(function (el) {
      var target   = parseInt(el.dataset.target, 10);
      var duration = 1600;
      var start    = performance.now();
      function step(now) {
        var elapsed  = now - start;
        var progress = Math.min(elapsed / duration, 1);
        var eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        el.textContent = Math.floor(eased * target).toLocaleString('en-IN');
        if (progress < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    });
  }

  var statsBar = document.querySelector('.stats-v2');
  if (statsBar && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries, obs) {
      if (entries[0].isIntersecting) { runCounters(); obs.disconnect(); }
    }, { threshold: 0.3 }).observe(statsBar);
  } else if (statsBar) {
    runCounters();
  }
})();
</script>

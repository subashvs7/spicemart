<script>
// Auto-scroll to order summary on load
window.addEventListener('load', function() {
  var el = document.getElementById('orderSummary');
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
</script>

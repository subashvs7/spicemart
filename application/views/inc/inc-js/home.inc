<script>
// Auto-scroll to best sellers on hash
if (window.location.hash === '#best-sellers') {
  var el = document.getElementById('best-sellers');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
}
</script>

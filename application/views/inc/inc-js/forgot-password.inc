<script>
// Auto-focus first input on page load
(function() {
  var first = document.querySelector('form input:not([type=hidden])');
  if (first) first.focus();
})();
</script>

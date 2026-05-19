<script>
// Auto-dismiss success alert after 4 seconds
(function() {
  var alert = document.querySelector('.alert-success');
  if (alert) setTimeout(function() {
    alert.style.transition = 'opacity .5s';
    alert.style.opacity = '0';
    setTimeout(function() { alert.remove(); }, 500);
  }, 4000);
})();
</script>

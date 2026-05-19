<script>
// Password match validation
(function() {
  var form = document.querySelector('form');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    var pw  = form.querySelector('[name=password]');
    var cpw = form.querySelector('[name=confirm_password]');
    if (pw && cpw && pw.value !== cpw.value) {
      e.preventDefault();
      alert('Passwords do not match.');
      cpw.focus();
    }
  });
})();
</script>

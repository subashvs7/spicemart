<script>
(function() {
  var paymentCards = document.querySelectorAll('.payment-option');
  paymentCards.forEach(function(card) {
    card.addEventListener('click', function() {
      paymentCards.forEach(function(c) { c.classList.remove('border-saffron', 'shadow'); });
      card.classList.add('border-saffron', 'shadow');
      var radio = card.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });
  // Highlight default selected on load
  document.querySelectorAll('input[name="payment_method"]').forEach(function(r) {
    if (r.checked) {
      var parent = r.closest('.payment-option');
      if (parent) parent.classList.add('border-saffron', 'shadow');
    }
  });
})();
</script>

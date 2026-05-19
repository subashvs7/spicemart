<script>
// Wishlist toggle and add-to-cart are handled by main.js
// Update wishlist badge count after toggle
document.addEventListener('wishlist:updated', function(e) {
  var badge = document.getElementById('wishlistBadge');
  if (!badge) return;
  var count = e.detail && e.detail.count !== undefined ? e.detail.count : 0;
  badge.textContent = count;
  badge.style.display = count > 0 ? '' : 'none';
});
</script>

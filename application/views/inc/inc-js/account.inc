<script>
(function() {
  // Activate Bootstrap 5 tab from PHP $tab variable
  var activeTab = <?php echo json_encode(isset($tab) ? $tab : 'profile'); ?>;
  var tabEl = document.querySelector('#accountTabs [data-bs-target="#tab-' + activeTab + '"]');
  if (tabEl) {
    var bsTab = new bootstrap.Tab(tabEl);
    bsTab.show();
  }
})();
</script>

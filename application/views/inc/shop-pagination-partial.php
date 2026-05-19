<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($total_pages > 1): ?>
<nav class="mt-5 d-flex justify-content-center" aria-label="Product pages">
  <ul class="pagination">
    <li class="page-item <?php echo $page_num <= 1 ? 'disabled' : ''; ?>">
      <a class="page-link ajax-page" href="#" data-page="<?php echo $page_num - 1; ?>">
        <i class="bi bi-chevron-left"></i>
      </a>
    </li>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <?php if ($i === 1 || $i === $total_pages || abs($i - $page_num) <= 2): ?>
        <li class="page-item <?php echo $i === $page_num ? 'active' : ''; ?>">
          <a class="page-link ajax-page" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php elseif (abs($i - $page_num) === 3): ?>
        <li class="page-item disabled"><span class="page-link">…</span></li>
      <?php endif; ?>
    <?php endfor; ?>
    <li class="page-item <?php echo $page_num >= $total_pages ? 'disabled' : ''; ?>">
      <a class="page-link ajax-page" href="#" data-page="<?php echo $page_num + 1; ?>">
        <i class="bi bi-chevron-right"></i>
      </a>
    </li>
  </ul>
</nav>
<?php endif; ?>

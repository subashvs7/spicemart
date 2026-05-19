<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-5">
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo site_url('home'); ?>">Home</a></li>
      <li class="breadcrumb-item active"><?php echo htmlspecialchars($page['title']); ?></li>
    </ol>
  </nav>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <h1 class="mb-4" style="font-family:'Playfair Display',serif"><?php echo htmlspecialchars($page['title']); ?></h1>
      <div class="bg-white rounded-xl shadow-soft p-4 cms-content">
        <?php echo $page['content']; ?>
      </div>
    </div>
  </div>
</div>

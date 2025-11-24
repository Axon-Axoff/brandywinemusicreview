<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main>
  <h2><?= $page_title ?? '' ?></h2>
  <?php if (!empty($welcome_message)): ?>
    <p><?= $welcome_message ?></p>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/footer_template.php'; ?>
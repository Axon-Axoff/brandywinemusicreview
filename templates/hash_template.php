<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container my-4">
<form action="?page=hash" method="post">
  <input type="text" name="pwdHash">
  <button type="submit">Hash</button>
</form>
<h3 class="text-light">Hashed Password</h3>
<h5 class="text-light"><?= !empty($hashed) ? $hashed : '' ?></h5>
</main>

<?php require __DIR__ . '/footer_template.php'; ?>

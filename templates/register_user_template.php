<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>
<?php if ($app_env == 'production'): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <script>
    function onSubmit(token) {
      document.getElementById("register").submit();
    }
  </script>
<?php endif; ?>

<main class="container my-4">
  <div class="text-center">
    <h2 class="mb-4 text-light">Register</h2>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php elseif (!empty($success_message)): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
  <?php endif; ?>

  <form method="post" id="register" action="?page=register" class="card p-4 shadow-sm muted-light-blue" style="max-width: 720px;margin-left:auto;margin-right:auto;">
    <div class="mb-3">
      <label for="user_name" class="form-label">Username</label>
      <input type="text" id="user_name" name="user_name" class="form-control" maxlength="75" required>
    </div>

    <div class="mb-3">
      <label for="full_name" class="form-label">Full Name</label>
      <input type="text" id="full_name" name="full_name" class="form-control" maxlength="100" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="text" id="email" name="email" class="form-control" maxlength="100" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" id="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="confirm_password" class="form-label">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
    </div>
    <?php if ($app_env == 'production'): ?>
      <button type="submit" class="btn btn-primary w-100 g-recaptcha" data-sitekey="<?= $recaptcha_key ?>" data-callback="onSubmit">Register</button>
    <?php elseif ($app_env == 'development'): ?>
      <button type="submit" class="btn btn-primary w-100">Register</button>
    <?php endif; ?>
  </form>
</main>

<?php require __DIR__ . '/footer_template.php'; ?>
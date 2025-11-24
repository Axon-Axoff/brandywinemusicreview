<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container my-4">
  <div class="text-center">
    <h2 class="mb-4 text-light">Login</h2>
  </div>
  <?php if (!empty($login_error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($login_error) ?></div>
  <?php endif; ?>

  <form method="post" action="?page=login" class="d-flex card p-4 shadow-sm muted-light-blue" style="max-width: 720px;margin-left:auto;margin-right:auto;">
    <div class="mb-3">
      <label for="user_name" class="form-label">Username</label>
      <input type="text" id="user_name" name="user_name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" id="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary body-bg-color w-100">Login</button>

    <!-- Register link -->
    <p class="mt-3 mb-0 text-center">
      Not a member? <a href="?page=register" class="fw-bold">Register here</a>
    </p>
  </form>

</main>

<?php require __DIR__ . '/footer_template.php'; ?>
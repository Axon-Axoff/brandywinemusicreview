<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<div class="container my-2">
  <?= renderPageTitle('My Profile'); ?>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
  <?php endif; ?>
  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-4 text-center">
      <img id="previewPic"
        src="<?= htmlspecialchars($user_data['profile_pic'] ?? '/uploads/profiles/generic.png') ?>"
        alt="Profile Picture"
        class="img-thumbnail mb-3"
        width="300" height="300">
    </div>
    <div class="col-md-8">
      <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm muted-light-blue">

        <div class="mb-3">
          <label for="profile_pic" class="form-label">Profile Picture</label>
          <input type="file" id="profile_pic" name="profile_pic"
            class="form-control" accept=".jpg,.jpeg,.png,.webp">
        </div>

        <div class="mb-3">
          <label for="full_name" class="form-label">Full Name</label>
          <input type="text" id="full_name" name="full_name" class="form-control"
            value="<?= htmlspecialchars($user_data['full_name']); ?>" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="text" id="email" name="email" class="form-control"
            value="<?= htmlspecialchars($user_data['email']); ?>" required>
        </div>

        <div class="mb-3">
          <label for="bio" class="form-label">Short Bio</label>
          <textarea id="bio" name="bio" class="form-control" rows="4" maxlength="500"><?= htmlspecialchars($user_data['bio']); ?></textarea>
        </div>

        <div class="mb-3">
          <label for="link)1_title" class="form-label">Website, Bandcamp, etc. Link Name</label>
          <input type="text" id="link_1_title" name="link_1_title" class="form-control"
            value="<?= htmlspecialchars($user_data['link_1_title']); ?>"
            placeholder="My Website">
        </div>

        <div class="mb-3">
          <label for="link_1_url" class="form-label">Link URL</label>
          <input type="text" id="link_1_url" name="link_1_url" class="form-control"
            value="<?= htmlspecialchars($user_data['link_1_url']); ?>"
            placeholder="https://website.com">
        </div>

        <div class="mb-3">
          <label for="link_2_title" class="form-label">Second Link Name</label>
          <input type="text" id="link_2_title" name="link_2_title" class="form-control"
            value="<?= htmlspecialchars($user_data['link_2_title']); ?>"
            placeholder="My Other Intersting Link">
        </div>

        <div class="mb-3">
          <label for="link_2_url" class="form-label">Second Link URL</label>
          <input type="text" id="link_2_url" name="link_2_url" class="form-control"
            value="<?= htmlspecialchars($user_data['link_2_url']); ?>"
            placeholder="https://otherwebsite.com">
        </div>

        <div class="mb-3">
          <label for="new_password" class="form-label">New Password</label>
          <input type="password" id="new_password" name="new_password" class="form-control">
        </div>

        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary body-bg-color">Update Profile</button>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('profile_pic').addEventListener('change', function(e) {
    const [file] = e.target.files;
    if (file) {
      document.getElementById('previewPic').src = URL.createObjectURL(file);
    }
  });
</script>

<?php require __DIR__ . '/footer_template.php'; ?>
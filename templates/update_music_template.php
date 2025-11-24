<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container my-4">
  <div class="text-center">
    <h2 class="mb-4 text-light">Edit Music</h2>
  </div>

  <form method="post" enctype="multipart/form-data"
    action="?page=update_music&id=<?= (int)$music_item['music_id'] ?>&type=<?= htmlspecialchars($type) ?>"
    class="d-flex card p-4 shadow-sm muted-light-blue"
    style="max-width:720px;margin:auto;">

    <!-- Title -->
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" id="title" name="title" class="form-control"
        maxlength="75" value="<?= htmlspecialchars($music_item['title']) ?>" required>
    </div>

    <!-- Artist -->
    <div class="mb-3">
      <label for="artist" class="form-label">Artist</label>
      <input type="text" id="artist" name="artist" class="form-control"
        maxlength="75" value="<?= htmlspecialchars($music_item['artist']) ?>" required>
    </div>

    <!-- Album (if song) -->
    <?php if ($type === 'song'): ?>
      <div class="mb-3">
        <label for="album" class="form-label">Album</label>
        <input type="text" id="album" name="album" class="form-control"
          maxlength="75" value="<?= htmlspecialchars($music_item['album']) ?>">
      </div>
    <?php endif; ?>

    <!-- Image -->
    <div class="mb-3 text-center">
      <label for="image" class="form-label d-block">Cover Art</label>
      <div id="uploadBox" class="d-flex align-items-center justify-content-center border"
        style="width:300px; height:300px; margin:0 auto; cursor:pointer; background:#222; overflow:hidden; border-radius:12px;">
        <img id="imagePreview"
          src="<?= htmlspecialchars($music_item['image_path'] ?: '/images/upload-icon.png') ?>"
          alt="Cover" style="max-width:100%; max-height:100%; object-fit:cover;">
      </div>
      <input type="file" id="image" name="image" class="d-none" accept=".jpg,.jpeg,.png,.webp">
    </div>

    <!-- Genre -->
    <div class="mb-3">
      <label for="genre" class="form-label">Genre</label>
      <select id="genre" name="genre" class="form-select" required>
        <?php foreach ($valid_genres as $genre): ?>
          <option value="<?= $genre ?>" <?= ($music_item['genre'] === $genre) ? 'selected' : '' ?>>
            <?= $genre ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Release Date -->
    <div class="mb-3">
      <label for="release_date" class="form-label">Release Date</label>
      <input type="date" id="release_date" name="release_date" class="form-control"
        value="<?= htmlspecialchars($music_item['release_date']) ?>" required>
    </div>

    <button type="submit" class="btn btn-warning fw-bold">Save Changes</button>
  </form>

  <?php if (!empty($comments) && ($_SESSION['admin'] ?? 'N') === 'Y'): ?>
    <h4 class="mt-5 text-light">Manage Comments</h4>
    <ul class="list-group">
      <?php foreach ($comments as $c): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= htmlspecialchars($c['comment']) ?> — <em><?= htmlspecialchars($c['full_name']) ?></em></span>
          <form method="post" action="?page=update_music&id=<?= (int)$music_item['music_id'] ?>&type=<?= htmlspecialchars($type) ?>" onsubmit="return confirm('Delete this comment?');">
            <input type="hidden" name="action" value="delete_comment">
            <input type="hidden" name="comment_id" value="<?= (int)$c['comment_id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- Delete button (admin or creator only) -->
  <?php if (($_SESSION['admin'] ?? 'N') === 'Y' || $music_item['creator_id'] == $_SESSION['user_id']): ?>
    <form method="post"
      action="?page=update_music&id=<?= (int)$music_item['music_id'] ?>&type=<?= htmlspecialchars($type) ?>"
      class="mt-4 text-center"
      onsubmit="return confirm('⚠️ WARNING: This will permanently delete the <?= htmlspecialchars($type) ?> and ALL related ratings/comments. Are you sure?');">
      <input type="hidden" name="action" value="delete_music">
      <button type="submit" class="btn btn-danger">
        Delete This <?= ucfirst($type) ?>
      </button>
    </form>
  <?php endif; ?>

</main>

<script>
  // image preview
  const uploadBox = document.getElementById('uploadBox');
  const imageInput = document.getElementById('image');
  const imagePreview = document.getElementById('imagePreview');
  uploadBox.addEventListener('click', () => imageInput.click());
  imageInput.addEventListener('change', (e) => {
    const [file] = e.target.files;
    if (file) {
      imagePreview.src = URL.createObjectURL(file);
      imagePreview.style.objectFit = "cover";
    }
  });
</script>

<?php require __DIR__ . '/footer_template.php'; ?>
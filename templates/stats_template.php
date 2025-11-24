<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container my-2">
  <?= renderPageTitle('Statistics'); ?>

  <!-- ALBUMS ROW -->
  <div class="row g-4 mb-5">
    <!-- Top Albums (Weighted) -->
    <div class="col-12 col-lg-4">
      <h3 class="text-light">Top Albums (Weighted)</h3>
      <ul class="list-unstyled">
        <?php foreach ($top_albums as $a): ?>
          <li class="mb-2">
            <a href="?page=music&id=<?= (int)$a['music_id'] ?>&type=album"
              class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 text-decoration-none text-dark">
              <div class="d-flex align-items-center">
                <img src="<?= htmlspecialchars($a['image_path']) ?>" alt=""
                  style="width:60px;height:60px;object-fit:cover;border-radius:6px;" class="me-2">
                <div>
                  <strong><?= htmlspecialchars(trim(($a['title_prefix'] ?? '') . ' ' . $a['title'])) ?></strong>
                  <div style="color:#444;">
                    <?= htmlspecialchars(trim(($a['artist_prefix'] ?? '') . ' ' . $a['artist'])) ?>
                  </div>
                </div>
              </div>
              <div class="fw-bold burnt-orange-text" style="font-size:1.5rem;">
                <?= number_format($a['weighted_rating'], 2) ?>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Top Album Genres -->
    <div class="col-12 col-lg-4">
      <h3 class="text-light">Top Album Genres</h3>
      <ul class="list-unstyled">
        <?php foreach ($top_album_genres as $g): ?>
          <li class="mb-2">
            <a href="?page=music_list&type=album&filter=genre&value=<?= urlencode($g['genre']) ?>"
              class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 text-decoration-none">
              <h5 class="mb-0 text-dark"><?= htmlspecialchars($g['genre']) ?></h5>
              <div class="fw-bold burnt-orange-text" style="font-size:1.5rem;"><?= (int)$g['total'] ?></div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Most Rated Albums -->
    <div class="col-12 col-lg-4">
      <h3 class="text-light">Most Rated Albums</h3>
      <ul class="list-unstyled">
        <?php foreach ($most_rated_albums as $a): ?>
          <li class="mb-2">
            <a href="?page=music&id=<?= (int)$a['music_id'] ?>&type=album"
              class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 text-decoration-none text-dark">
              <div class="d-flex align-items-center">
                <img src="<?= htmlspecialchars($a['image_path']) ?>" alt=""
                  style="width:60px;height:60px;object-fit:cover;border-radius:6px;" class="me-2">
                <div>
                  <strong><?= htmlspecialchars(trim(($a['title_prefix'] ?? '') . ' ' . $a['title'])) ?></strong>
                  <div style="color:#444;">
                    <?= htmlspecialchars(trim(($a['artist_prefix'] ?? '') . ' ' . $a['artist'])) ?>
                  </div>
                </div>
              </div>
              <div class="fw-bold burnt-orange-text" style="font-size:1.5rem;">
                <?= (int)$a['total_raters'] ?>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- SONGS ROW -->
  <div class="row g-4 mb-5">
    <!-- Top Songs (Weighted) -->
    <div class="col-12 col-lg-4">
      <h3 class="text-light">Top Songs (Weighted)</h3>
      <ul class="list-unstyled">
        <?php foreach ($top_songs as $s): ?>
          <li class="mb-2">
            <a href="?page=music&id=<?= (int)$s['music_id'] ?>&type=song"
              class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 text-decoration-none text-dark">
              <div class="d-flex align-items-center">
                <img src="<?= htmlspecialchars($s['image_path']) ?>" alt=""
                  style="width:60px;height:60px;object-fit:cover;border-radius:6px;" class="me-2">
                <div>
                  <strong><?= htmlspecialchars(trim(($s['title_prefix'] ?? '') . ' ' . $s['title'])) ?></strong>
                  <div style="color:#444;">
                    <?= htmlspecialchars(trim(($s['artist_prefix'] ?? '') . ' ' . $s['artist'])) ?>
                  </div>
                </div>
              </div>
              <div class="fw-bold burnt-orange-text" style="font-size:1.5rem;">
                <?= number_format($s['weighted_rating'], 2) ?>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Top Song Genres -->
    <div class="col-12 col-lg-4">
      <h3 class="text-light">Top Song Genres</h3>
      <ul class="list-unstyled">
        <?php foreach ($top_song_genres as $g): ?>
          <li class="mb-2">
            <a href="?page=music_list&type=song&filter=genre&value=<?= urlencode($g['genre']) ?>"
              class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 text-decoration-none">
              <h5 class="mb-0 text-dark"><?= htmlspecialchars($g['genre']) ?></h5>
              <div class="fw-bold burnt-orange-text" style="font-size:1.5rem;"><?= (int)$g['total'] ?></div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Most Rated Songs -->
    <div class="col-12 col-lg-4">
      <h3 class="text-light">Most Rated Songs</h3>
      <ul class="list-unstyled">
        <?php foreach ($most_rated_songs as $s): ?>
          <li class="mb-2">
            <a href="?page=music&id=<?= (int)$s['music_id'] ?>&type=song"
              class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 text-decoration-none text-dark">
              <div class="d-flex align-items-center">
                <img src="<?= htmlspecialchars($s['image_path']) ?>" alt=""
                  style="width:60px;height:60px;object-fit:cover;border-radius:6px;" class="me-2">
                <div>
                  <strong><?= htmlspecialchars(trim(($s['title_prefix'] ?? '') . ' ' . $s['title'])) ?></strong>
                  <div style="color:#444;">
                    <?= htmlspecialchars(trim(($s['artist_prefix'] ?? '') . ' ' . $s['artist'])) ?>
                  </div>
                </div>
              </div>
              <div class="fw-bold burnt-orange-text" style="font-size:1.5rem;">
                <?= (int)$s['total_raters'] ?>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- BY YEARS -->
  <div class="row g-4">
    <!-- Albums by Year -->
    <div class="col-12 col-lg-6">
      <h3 class="text-light">Albums by Year Range</h3>
      <div class="d-flex justify-content-between text-light fw-bold mb-2">
        <span>Years</span><span>Albums</span><span>Avg Rating</span>
      </div>
      <?php foreach ($albums_by_year as $row): ?>
        <a href="?page=music_list&type=album&filter=year_range&value=<?= urlencode($row['range']) ?>"
          class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 mb-2 text-decoration-none text-dark">
          <span class="fw-bold text-dark"><?= $row['range'] ?></span>
          <span class="fw-bold burnt-orange-text"><?= $row['total'] ?></span>
          <span class="fw-bold burnt-orange-text"><?= number_format($row['avg_rating'], 2) ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Songs by Year -->
    <div class="col-12 col-lg-6">
      <h3 class="text-light">Songs by Year Range</h3>
      <div class="d-flex justify-content-between text-light fw-bold mb-2">
        <span>Years</span><span>Songs</span><span>Avg Rating</span>
      </div>
      <?php foreach ($songs_by_year as $row): ?>
        <a href="?page=music_list&type=song&filter=year_range&value=<?= urlencode($row['range']) ?>"
          class="d-flex justify-content-between align-items-center comment-bg-hover rounded p-2 mb-2 text-decoration-none text-dark">
          <span class="fw-bold text-dark"><?= $row['range'] ?></span>
          <span class="fw-bold burnt-orange-text"><?= $row['total'] ?></span>
          <span class="fw-bold burnt-orange-text"><?= number_format($row['avg_rating'], 2) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require __DIR__ . '/footer_template.php'; ?>
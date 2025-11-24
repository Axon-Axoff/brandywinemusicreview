<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<div class="container my-2">
  <?= renderPageTitle('Member Details'); ?>
  <div class="muted-light-blue p-3" style="border-radius:12px;">
    <div class="border border-2 border-secondary p-3 position-relative" style="border-radius:10px;background-color:#dddddd">

      <!-- Message Button -->
      <a href="?page=messages&to=<?= (int)$member['user_id'] ?>&tab=compose"
        class="btn btn-sm button-green-outline position-absolute top-0 end-0 m-3">
        Message
      </a>

      <!-- Section 1: Profile -->
      <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-lg-row align-items-center align-items-lg-start">
          <img src="<?= htmlspecialchars($member['profile_pic'] ?: '/uploads/profiles/generic.png') ?>"
            alt="<?= htmlspecialchars($member['user_name']) ?>"
            class="rounded me-lg-3"
            style="width:300px; height:300px; object-fit:cover; border-radius:8px;">

          <div class="ms-lg-4 mt-2 mt-lg-0 text-center text-lg-start w-100">
            <h3 class="text-dark"><?= htmlspecialchars($member['user_name']) ?></h3>
            <h5 style="color:#333333;"><?= htmlspecialchars($member['full_name']) ?></h5>
            <div class="text-muted mb-2">
              Joined <?= date("F jS Y", strtotime($member['created_at'])) ?>
            </div>
            <div class="muted-light-blue p-2 rounded w-100" style="min-height:186px;">
              <h4 class="text-dark">Bio</h4>
              <div style="color:#444444;">
                <?= nl2br(htmlspecialchars($member['bio'] ?? 'No bio available.')) ?>
                <?php if (!empty($member['link_1_title']) || !empty($member['link_2_title'])): ?>
                  <strong class="d-block text-dark mt-5">Check out my links below:</strong>
                  <?php if (!empty($member['link_1_title'])): ?>
                    <a href="<?= $member['link_1_url'] ?>" target="_blank" rel="noopener noreferrer nofollow" class="d-block album-title-link">
                      <strong>
                        <small>[ <?= $member['link_1_title'] ?> ]</small>
                      </strong>
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($member['link_2_title'])): ?>
                    <a href="<?= $member['link_2_url'] ?>" target="_blank" rel="noopener noreferrer nofollow" class="d-block album-title-link">
                      <strong>
                        <small>[ <?= $member['link_2_title'] ?> ]</small>
                      </strong>
                    </a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="row g-4">
        <!-- Section 3: Latest Albums -->
        <div class="col-12 col-lg-4 px-2">
          <div class="body-bg-color p-2 rounded">
            <h4 class="text-light">Latest Albums</h4>
            <?php if (!empty($latest_albums)): ?>
              <ul class="list-unstyled">
                <?php foreach ($latest_albums as $album): ?>
                  <li class="mb-2">
                    <a href="?page=music&id=<?= (int)$album['music_id'] ?>&type=album"
                      class="d-flex align-items-center comment-bg-hover rounded p-2 text-decoration-none text-dark">
                      <?php if (!empty($album['image_path'])): ?>
                        <img src="<?= htmlspecialchars($album['image_path']) ?>"
                          alt="<?= htmlspecialchars($album['title']) ?>"
                          style="width:60px; height:60px; object-fit:cover; border-radius:6px;"
                          class="me-2">
                      <?php endif; ?>
                      <div>
                        <strong><?= htmlspecialchars(trim(($album['title_prefix'] ?? '') . ' ' . $album['title'])) ?></strong>
                        <?php if (!empty($album['artist'])): ?>
                          <div style="color:#444444;">
                            <?= htmlspecialchars(trim(($album['artist_prefix'] ?? '') . ' ' . $album['artist'])) ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="text-light fst-italic">No albums added</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Section 4: Latest Songs -->
        <div class="col-12 col-lg-4 px-2">
          <div class="body-bg-color p-2 rounded">
            <h4 class="text-light">Latest Songs</h4>
            <?php if (!empty($latest_songs)): ?>
              <ul class="list-unstyled">
                <?php foreach ($latest_songs as $song): ?>
                  <li class="mb-2">
                    <a href="?page=music&id=<?= (int)$song['music_id'] ?>&type=song"
                      class="d-flex align-items-center comment-bg-hover rounded p-2 text-decoration-none text-dark">
                      <?php if (!empty($song['image_path'])): ?>
                        <img src="<?= htmlspecialchars($song['image_path']) ?>"
                          alt="<?= htmlspecialchars($song['title']) ?>"
                          style="width:60px; height:60px; object-fit:cover; border-radius:6px;"
                          class="me-2">
                      <?php endif; ?>
                      <div>
                        <strong><?= htmlspecialchars(trim(($song['title_prefix'] ?? '') . ' ' . $song['title'])) ?></strong>
                        <?php if (!empty($song['artist'])): ?>
                          <div style="color:#444444;">
                            <?= htmlspecialchars(trim(($song['artist_prefix'] ?? '') . ' ' . $song['artist'])) ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="text-light fst-italic">No songs added</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Section 5: Latest Ratings -->
        <div class="col-12 col-lg-4 px-2">
          <div class="body-bg-color p-2 rounded">
            <h4 class="text-light">Latest Ratings</h4>
            <?php if (!empty($latest_ratings)): ?>
              <ul class="list-unstyled">
                <?php foreach ($latest_ratings as $rating): ?>
                  <li class="mb-2">
                    <a href="?page=music&id=<?= (int)$rating['music_id'] ?>&type=<?= htmlspecialchars($rating['type']) ?>"
                      class="d-flex align-items-center comment-bg-hover rounded p-2 text-decoration-none text-dark">
                      <?php if (!empty($rating['image_path'])): ?>
                        <img src="<?= htmlspecialchars($rating['image_path']) ?>"
                          alt="<?= htmlspecialchars($rating['title']) ?>"
                          style="width:60px; height:60px; object-fit:cover; border-radius:6px;"
                          class="me-2">
                      <?php endif; ?>
                      <div class="flex-grow-1">
                        <strong><?= htmlspecialchars(trim(($rating['title_prefix'] ?? '') . ' ' . $rating['title'])) ?></strong>
                        <?php if (!empty($rating['artist'])): ?>
                          <div style="color:#444444;">
                            <?= htmlspecialchars(trim(($rating['artist_prefix'] ?? '') . ' ' . $rating['artist'])) ?>
                          </div>
                        <?php endif; ?>
                      </div>
                      <div class="ms-2 fw-bold burnt-orange-text" style="color:#cc5500; font-size:1.25rem;">
                        <?= (int)$rating['rating'] ?>
                      </div>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="text-light fst-italic">No ratings</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="d-flex w-100 justify-content-center mt-3">
        <button class="btn btn-primary body-bg-color"
          onclick="safeBack('<?php echo htmlspecialchars($_SESSION['last_page'] ?? '/'); ?>')">
          ← Back
        </button>
      </div>
    </div>
  </div>

</div>

<?php require __DIR__ . '/footer_template.php'; ?>
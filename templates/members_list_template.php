<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container">
  <div class="w-100 pt-2"><?php renderPageTitle('Members'); ?></div>

  <div class="muted-light-blue mt-3 p-2" style="border-radius:8px;">
    <div class="mx-3 py-2">
      <div class="row g-3">
        <?php foreach ($members as $m): ?>
          <div class="col-12">
            <div class="row align-items-center bg-white rounded p-2" style="border: solid 2px #888888;">
          
              <!-- Profile + Info -->
              <div class="col-12 col-lg-4 mb-2 mb-sm-0 text-center text-lg-start">
                <a href="?page=member&id=<?= (int)$m['user_id'] ?>" class="d-block text-decoration-none text-dark p-2 h-100 member-box">
                  <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center justify-content-lg-start">
                    <img src="<?= htmlspecialchars($m['profile_pic'] ?: '/uploads/profiles/generic.png') ?>"
                         alt="<?= htmlspecialchars($m['user_name']) ?>"
                         class="rounded-circle me-lg-3 mb-2 mb-lg-0"
                         style="width:60px; height:60px; object-fit:cover;">
                    <div>
                      <div class="fw-bold"><?= htmlspecialchars($m['user_name']) ?></div>
                      <div><?= htmlspecialchars($m['full_name']) ?></div>
                      <div class="text-muted small">
                        Ratings: <strong><?= (int)$m['albums_rated'] ?> albums · <?= (int)$m['songs_rated'] ?> songs</strong>
                      </div>
                      <div class="text-muted small">
                        Joined <?= date("F jS Y", strtotime($m['created_at'])) ?>
                      </div>
                    </div>
                  </div>
                </a>
              </div>

              <!-- Latest Activity -->
              <div class="col-12 col-lg-4 text-center text-lg-start">
                <?php if (!empty($m['latest_activity'])): 
                  $la = $m['latest_activity']; ?>
                  <a href="?page=music&id=<?= (int)$la['target_id'] ?>&type=<?= htmlspecialchars($la['target_type']) ?>" 
                     class="text-decoration-none text-dark d-block p-2 latest-activity-box" style="min-height:106px;">
                    <div class="fw-bold" style="color:#884433;">
                      Latest Activity: <?= ucfirst($la['action_type']) ?> <?= ucfirst($la['target_type']) ?>
                    </div>
                    <?php if (!empty($la['target_id'])): 
                      $stmt = $db_connection->prepare("
                          SELECT title, title_prefix, artist, artist_prefix
                          FROM music
                          WHERE music_id = :id
                          LIMIT 1
                      ");
                      $stmt->execute(['id' => $la['target_id']]);
                      $musicRow = $stmt->fetch(PDO::FETCH_ASSOC);
                      if ($musicRow): ?>
                        <div>
                          <small class="text-dark"><strong>
                          <?= htmlspecialchars(trim(($musicRow['title_prefix'] ?? '') . ' ' . $musicRow['title'])) ?>
                          <?php if (!empty($musicRow['artist'])): ?>
                            by <?= htmlspecialchars(trim(($musicRow['artist_prefix'] ?? '') . ' ' . $musicRow['artist'])) ?>
                          <?php endif; ?>
                          </strong></small>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>
                    <div class="small text-muted">on <?= date("F jS Y", strtotime($la['activity_time'])) ?></div>
                  </a>
                <?php else: ?>
                  <div class="text-muted fst-italic">No activity yet</div>
                <?php endif; ?>
              </div>

              <!-- Message Button -->
              <div class="col-12 col-lg-4 text-center text-lg-end pt-2 pt-lg-0">
                <a href="?page=messages&to=<?= (int)$m['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                  Message
                </a>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/footer_template.php'; ?>

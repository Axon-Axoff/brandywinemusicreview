<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>
<?php $page_title = ucfirst($type) . " Details"; ?>
<main class="container">
  <div class="w-100 pt-2"><?php renderPageTitle($page_title) ?></div>
  <div class="my-4 d-flex justify-content-center">
    <div class="card shadow-lg p-2 p-sm-4 w-100 <?= $type == 'album' ? 'muted-light-blue' : 'muted-light-red' ?> position-relative" style="max-width:720px;">

      <?php if (
        isLoggedIn() &&
        ($music_item['creator_id'] == $_SESSION['user_id'] || ($_SESSION['admin'] ?? 'N') === 'Y')
      ): ?>
        <a href="?page=update_music&id=<?= (int)$music_item['music_id'] ?>&type=<?= htmlspecialchars($type) ?>"
          class="btn btn-warning position-absolute top-0 start-0 m-2 fw-bold"
          style="border:2px solid #ffcc00; color:black;">
          ✎ Edit
        </a>
      <?php endif; ?>

      <p class="text-center text-muted mb-4 mb-sm-1" style="font-size:0.9rem;">
        <strong><?= ucfirst($type) ?></strong>
      </p>

      <div class="album-info-box">
        <h2 class="text-center mb-3">
          <?= htmlspecialchars(trim(($music_item['title_prefix'] ?? '') . ' ' . $music_item['title'])) ?>
        </h2>

        <?php if (!empty($music_item['image_path'])): ?>
          <div class="text-center mb-3">
            <img src="<?= htmlspecialchars($music_item['image_path']) ?>"
              alt="<?= htmlspecialchars($music_item['title']) ?>"
              class="img-fluid rounded shadow-sm" style="max-height:300px;">
          </div>
        <?php endif; ?>
        <?php if ($type == 'song'): ?>
          <p class="text-center text-muted mb-1">
            <?= htmlspecialchars(trim(($music_item['album_prefix'] ?? '') . ' ' . $music_item['album'])) ?>
          </p>
        <?php endif; ?>
        <p class="text-center text-muted mb-1">
          <?= htmlspecialchars(trim(($music_item['artist_prefix'] ?? '') . ' ' . $music_item['artist'])) ?>
        </p>
        <p class="text-center text-muted mb-3">
          <strong>Genre:</strong> <?= htmlspecialchars($music_item['genre']) ?> |
          <strong>Released:</strong> <?= substr($music_item['release_date'], 0, 4) ?>
        </p>

        <p class="card-text text-center mb-0">
          <small class="text-muted">
            Added by
            <a href="?page=music_list&type=<?= $type ?>&filter=search&value=<?= urlencode($music_item['creator_name']) ?>"
              class="album-meta-link">
              <?= htmlspecialchars($music_item['creator_name']) ?>
            </a>
            on <?= date("F jS Y", strtotime($music_item['date_added'])) ?>
          </small>
        </p>

        <?php if ($initial_comment): ?>
          <div class="alert alert-warning p-3 mb-4">
            <p class="mb-1"><?= nl2br(htmlspecialchars($initial_comment['comment'])) ?></p>
            <small class="text-muted">
              — <?= htmlspecialchars($initial_comment['full_name']) ?>
              on <?= date("F j, Y", strtotime($initial_comment['date_added'])) ?>
            </small>
          </div>
        <?php endif; ?>
      </div>

      <!-- Ratings Summary -->
      <div class="row text-center mb-4">
        <div class="col-6">
          <small class="fw-semibold text-muted"><?= htmlspecialchars($music_item['total_ratings'] ?? 0) ?> Ratings</small>
          <div class="fw-bold burnt-orange-text" style="font-size:2.8rem;">
            <?= htmlspecialchars(number_format($music_item['rating'] ?? 0, 2)) ?>
          </div>
          <small class="text-muted">Ranking:</small>
          <span style="font-size:1.8rem;"><?= $music_item['rating_ranking'] ?? '-' ?></span>
        </div>
        <div class="col-6">
          <small class="fw-semibold text-muted">Weighted Rating</small>
          <div class="fw-bold burnt-orange-text" style="font-size:2.8rem;">
            <?= htmlspecialchars(number_format($music_item['weighted_rating'] ?? 0, 2)) ?>
          </div>
          <small class="text-muted">Ranking:</small>
          <span style="font-size:1.8rem;"><?= $music_item['weighted_ranking'] ?? '-' ?></span>
        </div>
      </div>

      <div class="listen-box">
        <div class="d-flex justify-content-center mb-3">
          <button class="btn btn-primary body-bg-color p-2" style="width:60%;"
            data-bs-toggle="modal"
            data-bs-target="#musicModal"
            data-music="<?= htmlspecialchars($music_item['link'] ?? '') ?>">
            Listen
          </button>
        </div>

        <div class="d-flex text-center">
          <small class="text-light">If you have spotify just click the three dots on the player and play on spotify for full song.</small>
        </div>

        <?php if (isLoggedIn()): ?>
          <form method="post" id="linkUpdateForm" action="?page=music&id=<?= (int)$music_item['music_id'] ?>&type=<?= $type ?>" class="mt-5">
            <div class="mb-2">
              <small class="text-light">Spotify Or Youtube Link (https)<br>
                Bandcamp Embed (iframe)</small>
              <input type="text" id="link" name="link"
                value="<?= htmlspecialchars($music_item['link'] ?? '') ?>"
                class="form-control text-muted w-100 w-sm-75 mx-auto"
                placeholder="Paste Link Or Embed Here">
              <div id="linkError" class="text-danger mt-1 small d-none"></div>
            </div>
            <button type="submit"
              class="d-flex btn btn-dark text-black w-100 w-sm-75 mx-auto burnt-orange-bg justify-content-center update-link-button">
              Update
            </button>
          </form>
        <?php endif; ?>
      </div>

      <!-- Raters -->
      <h5 class="my-2 text-center">Rated By</h5>
      <?php if (!empty($raters)): ?>
        <ul class="list-group mb-4">
          <?php foreach ($raters as $r): ?>
            <li class="list-group-item d-flex justify-content-between">
              <span><?= htmlspecialchars($r['full_name']) ?></span>
              <span class="fw-bold burnt-orange-text"><?= $r['rating'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-muted">No ratings yet.</p>
      <?php endif; ?>

      <!-- Rating Form -->
      <?php if (isLoggedIn()): ?>
        <div class="card mt-4">
          <div class="card-body rounded" style="background-color:#aaaaaa;">
            <h5><?= $user_rating ? "Update Your Rating" : "Add a Rating" ?></h5>
            <form method="post" action="?page=music&id=<?= (int)$music_item['music_id'] ?>&type=<?= $type ?>">
              <div class="mb-3">
                <select name="rating" class="form-select" required>
                  <?php
                  $rating_labels = [
                    1 => "1 - Terrible",
                    2 => "2 - Yawn-What?",
                    3 => "3 - Not Too Shabby",
                    4 => "4 - Really Good",
                    5 => "5 - All Time Great"
                  ];
                  ?>
                  <option value="">-- Select --</option>
                  <?php foreach ($rating_labels as $i => $label): ?>
                    <option value="<?= $i ?>" <?= ($user_rating == $i) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($label) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary body-bg-color">Save Rating</button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- Comments -->
      <h5 class="mt-5 mb-3">Comments</h5>
      <?php if (!empty($comments)): ?>
        <div class="list-group mb-4">
          <?php foreach ($comments as $c): ?>
            <div class="list-group-item">
              <p class="mb-1"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
              <small class="text-muted">
                — <?= htmlspecialchars($c['full_name']) ?> on <?= date("F j, Y", strtotime($c['date_added'])) ?>
              </small>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-muted">No comments yet.</p>
      <?php endif; ?>

      <?php if (isLoggedIn()): ?>
        <form method="post" action="?page=music&id=<?= (int)$music_item['music_id'] ?>&type=<?= $type ?>">
          <input type="hidden" name="action" value="add_comment">
          <div class="mb-3">
            <label for="comment" class="form-label">Add a Comment</label>
            <textarea id="comment" name="comment" class="form-control" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn button-green">Post Comment</button>
        </form>
      <?php endif; ?>
      <div class="d-flex w-100 justify-content-center">
        <button class="btn btn-primary body-bg-color mt-5"
          onclick="safeBack('<?php echo htmlspecialchars($_SESSION['last_page'] ?? '/'); ?>')">
          ← Back
        </button>
      </div>
    </div>
  </div>
</main>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("linkUpdateForm");
    if (!form) return;

    const linkInput = document.getElementById("link");
    const errorDiv = document.getElementById("linkError");

    form.addEventListener("submit", (e) => {
      const val = linkInput.value.trim();

      if (val.includes("bandcamp.com")) {
        // Must be full iframe embed
        if (!val.startsWith("<iframe")) {
          e.preventDefault();
          errorDiv.textContent = "Bandcamp links must be pasted as the full iframe embed code. On Bandcamp select 'share / embed', then select 'embed this song / album', select large style, copy entire html code.";
          errorDiv.classList.remove("d-none");
          return;
        }
      }

      // Clear error if valid
      errorDiv.textContent = "";
      errorDiv.classList.add("d-none");
    });
  });
</script>

<?php require __DIR__ . '/player_modal.php'; ?>
<?php require __DIR__ . '/footer_template.php'; ?>
<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<?php $page_title = ucfirst($type) . "s"; ?>

<main class="container my-2 mb-1">
  <?php renderPageTitle($page_title); ?>
  <div class="row">
    <div class="col-12 col-lg-4 col-xxl-3">
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center">
        <div class="fw-bold text-white small">Search <?= $page_title ?>:</div>
      </div>
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center mb-2">

        <?php
        $filter_labels = [
          'rating'          => 'Rating',
          'weighted_rating' => 'Rank',
          'title'           => 'Title',
          'artist'          => 'Artist',
          'genre'           => 'Genre',
          'release_date'    => 'Release Date',
          'year_range'      => 'Year Range',
          'date_added'      => 'Date Added',
          'creator_name'    => 'Added By',
          'total_ratings'   => 'Total Ratings',
          'search'          => 'Search'
        ];

        if ($total_items === 0) {
          $count_text = "No Results";
        } elseif ($total_items === 1) {
          $count_text = "1 " . ucfirst($type);
        } else {
          $count_text = $total_items . " " . ucfirst($type) . "s";
        }
        $arrow = ($dir === 'asc') ? '&#9650;' : '&#9660;';
        $sort_label = $filter_labels[$sort] ?? ucfirst($sort);
        ?>

        <!-- Search -->
        <form method="get" action="" class="d-flex me-2 mb-1">
          <input type="hidden" name="page" value="music_list">
          <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
          <input type="hidden" name="filter" value="search">
          <input type="text" name="value"
            class="form-control me-2"
            style="height:30px; min-width:120px; font-size:0.85rem; padding:2px 6px;"
            placeholder="Search..."
            value="<?= htmlspecialchars($_GET['value'] ?? '') ?>">
          <button type="submit" class="btn btn-sm btn-primary"
            style="height:30px; line-height:1; padding:0 8px;">
            Go
          </button>
        </form>

        <a href="?page=add">
          <img src="/images/add-music.webp" alt="Add Music" title="Add Album or Song" style="opacity:0.7;" />
        </a>
      </div>
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center">
        <div class="fw-bold text-white small">Filters:</div>
      </div>
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center mb-2">
        <!-- Genre Filter -->
        <button class="btn btn-outline-light me-1" style="height: 30px !important; font-size:0.9rem; padding: 0em 0.5em; border-radius: 8px;" data-bs-toggle="offcanvas" data-bs-target="#genresOffcanvas">
          Genres
        </button>
        <!-- Year Range Filter -->
        <button class="btn btn-outline-light me-1" style="height: 30px !important; font-size:0.9rem; padding: 0em 0.5em; border-radius: 8px;" data-bs-toggle="offcanvas" data-bs-target="#yearsOffcanvas">
          Years
        </button>
        <!-- Sort By -->
        <button class="btn btn-outline-light me-1" style="height: 30px !important; font-size:0.9rem; padding: 0em 0.5em; border-radius: 8px;" data-bs-toggle="offcanvas" data-bs-target="#sortOffcanvas">
          Sort
        </button>
        <!-- ASC/DESC Toggle -->
        <button id="sortDirBtn" class="btn btn-primary" style="height: 30px !important; font-size:0.9rem; padding: 0em 0.5em; border-radius: 8px;">
          <?= htmlspecialchars($sort_label) ?> <?= $arrow ?>
        </button>
      </div>
    </div>

    <div class="col-12 col-lg-8 col-xxl-9">
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center">
        <div class="fw-bold text-white small">Viewing:</div>
      </div>
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center mb-2">

        <?php
        $activeBadges = [];

        // legacy filter/value
        if ($filter && $value) {
          $filter_label = $filter_labels[$filter] ?? ucfirst($filter);
          $activeBadges[] = ['label' => $filter_label, 'filter' => $filter, 'value' => $value];
        }

        // multi-genre (each genre is its own badge)
        if (!empty($genres)) {
          foreach ($genres as $g) {
            $activeBadges[] = ['label' => 'Genre', 'filter' => 'genres[]', 'value' => $g];
          }
        }

        // year range
        if ($year_range) {
          $activeBadges[] = ['label' => 'Years', 'filter' => 'year_range', 'value' => $year_range];
        }

        // custom year range
        if ($year_start && $year_end) {
          $activeBadges[] = ['label' => 'Years', 'filter' => 'custom_range', 'value' => "{$year_start}-{$year_end}"];
        }
        ?>
        <div class="d-flex flex-wrap align-items-center mb-1" style="gap: 0.25rem;">
          <?php if (!empty($activeBadges)): ?>

            <span class="badge align-middle"
              style="background-color:#a7d7a2; color:#222; font-size:0.9rem; padding:0.5em">
              <?= htmlspecialchars($count_text) ?>
            </span>

            <?php foreach ($activeBadges as $b): ?>
              <span class="badge position-relative"
                style="background-color:#e6a96c; color:#222; font-size:0.9rem; padding:0.5em; cursor:pointer;"
                onclick="removeFilter('<?= $b['filter'] ?>','<?= htmlspecialchars($b['value'], ENT_QUOTES) ?>')">
                <?= htmlspecialchars($b['value']) ?>&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="body-text-color fw-bolder"
                  style="font-size: 17px; position:absolute; top:0px; right:0px; padding-right:6px;">x</span>
              </span>
            <?php endforeach; ?>

            <a href="?page=music_list&type=<?= htmlspecialchars($type) ?>"
              class="btn btn-outline-light align-middle"
              style="font-size:0.9rem; padding:0.5em; line-height:1;">
              Clear All
            </a>

          <?php else: ?>
            <span class="badge"
              style="background-color:#a7d7a2; color:#222; font-size:0.9rem; padding:0.5em;">
              <?= htmlspecialchars($count_text) ?>
            </span>

          <?php endif; ?>
        </div>
      </div>

      <!-- Genres Offcanvas -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="genresOffcanvas"
        style="top:80px; width:275px; background-color:#333;">
        <div class="offcanvas-header">
          <h6 class="text-light mb-0">Select Genres (up to 3)</h6>
        </div>
        <div class="offcanvas-body p-2 custom-scroll">
          <form id="genresForm" onsubmit="return false;">
            <?php foreach ($valid_genres as $genre): ?>
              <div class="form-check">
                <input type="checkbox" class="form-check-input genre-check"
                  value="<?= htmlspecialchars($genre) ?>"
                  id="genre_<?= htmlspecialchars($genre) ?>">
                <label for="genre_<?= htmlspecialchars($genre) ?>"
                  class="form-check-label text-light"><?= htmlspecialchars($genre) ?></label>
              </div>
            <?php endforeach; ?>
          </form>
          <!-- Floating Go button -->
          <button type="button" id="genresGoBtn"
            class="btn btn-primary position-fixed"
            style="left:220px; top:50%; transform:translateY(-50%);">
            Go
          </button>
        </div>
      </div>

      <!-- Year Range Offcanvas -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="yearsOffcanvas"
        style="top:80px; width:275px; background-color:#333;">
        <div class="offcanvas-header">
          <h6 class="text-light mb-0">Select Year Range</h6>
        </div>
        <div class="offcanvas-body p-2">
          <form id="yearsForm">
            <!-- Custom Range -->
            <div class="d-flex gap-2 mb-2">
              <input type="number" class="form-control form-control-sm"
                id="yearStart" placeholder="Start" min="1900" max="2100"
                value="<?= htmlspecialchars($year_start ?? '') ?>">
              <input type="number" class="form-control form-control-sm"
                id="yearEnd" placeholder="End" min="1900" max="2100"
                value="<?= htmlspecialchars($year_end ?? '') ?>">
              <button type="button" id="yearGoBtn" class="btn btn-primary btn-sm">Go</button>
            </div>

            <!-- Predefined Ranges (checkbox style, single-select enforced in JS) -->
            <div class="form-check">
              <input type="checkbox" name="year_range" value="" id="year_all"
                class="form-check-input rounded-check"
                <?= empty($year_range) ? 'checked' : '' ?>>
              <label for="year_all" class="form-check-label text-light">All Years</label>
            </div>
            <div class="form-check">
              <input type="checkbox" name="year_range" value="1900-1940" id="year_1900_1940"
                class="form-check-input rounded-check"
                <?= $year_range === '1900-1940' ? 'checked' : '' ?>>
              <label for="year_1900_1940" class="form-check-label text-light">1900–1940</label>
            </div>
            <?php for ($y = 1945; $y <= 2015; $y += 5): ?>
              <div class="form-check">
                <input type="checkbox" name="year_range" value="<?= $y ?>-<?= $y + 10 ?>"
                  id="year_<?= $y ?>" class="form-check-input rounded-check"
                  <?= $year_range === "$y-" . ($y + 10) ? 'checked' : '' ?>>
                <label for="year_<?= $y ?>" class="form-check-label text-light"><?= $y ?>–<?= $y + 10 ?></label>
              </div>
            <?php endfor; ?>
          </form>
        </div>
      </div>

      <!-- Sort By Offcanvas -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="sortOffcanvas"
        style="top:80px; width:275px; background-color:#333;">
        <div class="offcanvas-header">
          <h6 class="text-light mb-0">Sort By</h6>
        </div>
        <div class="offcanvas-body p-2">
          <form id="sortForm">
            <?php foreach ($filter_labels as $key => $label): ?>
              <?php if (!in_array($key, ['year_range', 'search'])): ?>
                <div class="form-check">
                  <input type="checkbox" name="sortRadio" value="<?= $key ?>"
                    id="sort_<?= $key ?>" class="form-check-input rounded-check"
                    <?= $sort === $key ? 'checked' : '' ?>>
                  <label for="sort_<?= $key ?>" class="form-check-label text-light"><?= $label ?></label>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </form>
        </div>
      </div>


      <!-- Top Pagination -->
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center">

        <div class="fw-bold text-white small">Pages:</div>
      </div>
      <div class="d-flex justify-content-center justify-content-lg-start align-items-center">
        <?= renderPagination($page, $total_pages, $filter, $value, $sort, $dir, $total_items, $per_page, $type, $genres, $year_range, $year_start, $year_end); ?>
      </div>
    </div>
  </div>


  <?php if (!empty($music_list)): ?>
    <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3 g-4 mb-2">
      <?php foreach ($music_list as $music_item): ?>
        <div class="col">
          <div class="card h-100 shadow-lg" style="background-color: #ddddff;border-width:2px;">
            <?php if (!empty($music_item['image_path'])): ?>
              <a href="?page=music&id=<?= (int)$music_item['music_id'] ?>&type=<?= $type ?>">
                <img src="<?= htmlspecialchars($music_item['image_path']) ?>"
                  class="card-img-top"
                  alt="<?= htmlspecialchars($music_item['title']) ?>"
                  style="max-height: 160px; object-fit: cover;">
              </a>
            <?php endif; ?>

            <div class="card-body">
              <div class="d-flex flex-column align-items-center">
                <h5 class="card-title mb-1">
                  <a href="?page=music&id=<?= (int)$music_item['music_id'] ?>&type=<?= $type ?>"
                    class="album-title-link">
                    <?= htmlspecialchars(($music_item['title_prefix'] ?? '') . ' ' . $music_item['title']) ?>
                  </a>
                </h5>
                <h6 class="card-subtitle text-muted mb-1">
                  <a href="?page=music_list&type=<?= $type ?>&filter=search&value=<?= urlencode($music_item['artist']) ?>"
                    class="album-meta-link">
                    <?= htmlspecialchars(($music_item['artist_prefix'] ?? '') . ' ' . $music_item['artist']) ?>
                  </a>
                </h6>
              </div>

              <!-- Genre + Released -->
              <div class="d-flex justify-content-center">
                <small><strong>Genre:</strong>
                  <a href="?page=music_list&type=<?= $type ?>&filter=search&value=<?= urlencode($music_item['genre']) ?>"
                    class="album-meta-link">
                    <?= htmlspecialchars($music_item['genre']) ?>
                  </a>
                </small>
                <strong><small>&nbsp;<span style="position:relative; top:-4px;">|</span>&nbsp;</small></strong>
                <small><strong>Released:</strong>
                  <a href="?page=music_list&type=<?= $type ?>&filter=search&value=<?= substr($music_item['release_date'], 0, 4) ?>"
                    class="album-meta-link">
                    <?= substr($music_item['release_date'], 0, 4) ?>
                  </a>
                </small>
              </div>

              <!-- Stats -->
              <div class="row text-center mt-2 mb-2">
                <div class="col mb-3">
                  <div class="fw-bold burnt-orange-text" style="font-size:2.4rem;margin-bottom:-12px;">
                    <?= htmlspecialchars($music_item['rating']) ?>
                  </div>
                  <small><strong>Rating</strong></small>
                </div>
                <div class="col mb-3">
                  <div class="fw-bold burnt-orange-text" style="font-size:2.4rem;margin-bottom:-12px;">
                    <?= htmlspecialchars($music_item['weighted_rating']) ?>
                  </div>
                  <small><strong>Weighted</strong></small>
                </div>
                <div class="col mb-3">
                  <div class="fw-bold burnt-orange-text" style="font-size:2.4rem;margin-bottom:-12px;">
                    <?= htmlspecialchars($music_item['total_ratings']) ?>
                  </div>
                  <small><strong>Ratings</strong></small>
                </div>
              </div>

              <div class="d-flex justify-content-center mb-2">
                <button type="button" class="btn btn-primary body-bg-color p-2" style="width:60%;"
                  data-bs-toggle="modal"
                  data-bs-target="#musicModal"
                  data-music="<?= htmlspecialchars($music_item['link'] ?? '') ?>">
                  Listen
                </button>
              </div>

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

              <?php if (!empty($music_item['comment_count'])): ?>
                <div class="text-center mt-2">
                  <span class="badge comment-badge">
                    <?= $music_item['comment_count'] == 1 ? '1 Comment' : $music_item['comment_count'] . " Comments" ?>
                  </span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No <?= ucfirst($type) ?>s found.</div>
  <?php endif; ?>

  <!-- Bottom Pagination -->
  <div class="text-center">
    <span class="fw-bold text-white small">Pages:</span>
  </div>
  <?= renderPagination($page, $total_pages, $filter, $value, $sort, $dir, $total_items, $per_page, $type, $genres, $year_range, $year_start, $year_end); ?>
  <script>
    function removeFilter(filter, value) {
      const url = new URL(window.location.href);

      if (filter === "genres[]") {
        const all = url.searchParams.getAll("genres[]");
        url.searchParams.delete("genres[]");
        all.filter(g => g !== value).forEach(g => url.searchParams.append("genres[]", g));
      } else if (filter === "year_range") {
        url.searchParams.delete("year_range");
      } else if (filter === "custom_range") {
        url.searchParams.delete("year_start");
        url.searchParams.delete("year_end");
      } else if (filter === "search") {
        url.searchParams.delete("filter");
        url.searchParams.delete("value");
      } else {
        url.searchParams.delete(filter);
      }

      window.location.href = url.toString();
    }
  </script>

</main>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // --- Genres ---
    let pendingGenres = [];
    const genreChecks = document.querySelectorAll(".genre-check");

    genreChecks.forEach(chk => {
      chk.addEventListener("change", () => {
        const checked = [...genreChecks].filter(c => c.checked).map(c => c.value);

        if (checked.length > 3) {
          chk.checked = false;
          alert("You can only select up to 3 genres.");
          return;
        }
        pendingGenres = checked; // Store until Go is clicked
      });
    });

    // Go button for genres
    const genresGoBtn = document.getElementById("genresGoBtn");
    if (genresGoBtn) {
      genresGoBtn.addEventListener("click", () => {
        const url = new URL(window.location.href);
        url.searchParams.delete("genres[]");
        pendingGenres.forEach(g => url.searchParams.append("genres[]", g));
        window.location.href = url.toString();
      });
    }

    // --- Year Range predefined (auto-submit like checkboxes) ---
    document.querySelectorAll("input[name='year_range']").forEach(radio => {
      radio.addEventListener("change", e => {
        const val = e.target.value;
        const url = new URL(window.location.href);
        url.searchParams.delete("year_range");
        url.searchParams.delete("year_start");
        url.searchParams.delete("year_end");
        if (val) url.searchParams.set("year_range", val);
        window.location.href = url.toString();
      });
    });

    // --- Year Range custom with Go ---
    const yearGoBtn = document.getElementById("yearGoBtn");
    if (yearGoBtn) {
      yearGoBtn.addEventListener("click", () => {
        const yearStart = document.getElementById("yearStart").value;
        const yearEnd = document.getElementById("yearEnd").value;
        if (yearStart && yearEnd) {
          const url = new URL(window.location.href);
          url.searchParams.set("year_start", yearStart);
          url.searchParams.set("year_end", yearEnd);
          url.searchParams.delete("year_range");
          window.location.href = url.toString();
        } else {
          alert("Please enter both start and end years.");
        }
      });
    }

    // --- Sort By (auto-submit) ---
    const sortRadios = document.querySelectorAll("input[name='sortRadio']");
    sortRadios.forEach(radio => {
      radio.addEventListener("change", () => {
        const url = new URL(window.location.href);
        url.searchParams.set("sort", radio.value);
        window.location.href = url.toString();
      });
    });

    // --- ASC/DESC toggle ---
    const sortBtn = document.getElementById("sortDirBtn");
    if (sortBtn) {
      sortBtn.addEventListener("click", () => {
        const url = new URL(window.location.href);
        const current = url.searchParams.get("dir") || "desc";
        const next = current === "asc" ? "desc" : "asc";
        url.searchParams.set("dir", next);
        window.location.href = url.toString();
      });
    }
  });
</script>



<?php require __DIR__ . '/player_modal.php'; ?>
<?php require __DIR__ . '/footer_template.php'; ?>
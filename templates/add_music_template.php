<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container my-2">
  <?= renderPageTitle('Add Music'); ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php elseif (!empty($success_message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
  <?php endif; ?>

  <!-- Default soft-gray wrapper -->
  <form id="addMusicForm" method="post" enctype="multipart/form-data"
    class="d-flex card p-4 shadow-sm soft-gray"
    style="max-width:720px;margin-left:auto;margin-right:auto;">

    <!-- Type selection -->
    <div class="d-flex mb-3 justify-content-center align-items-center gap-3">
      <label class="form-label mb-0"><strong><small>What are you adding?</small></strong></label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="type" id="typeAlbum" value="album" required>
        <label class="form-check-label" for="typeAlbum">Album</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="type" id="typeSong" value="song">
        <label class="form-check-label" for="typeSong">Song</label>
      </div>
    </div>

    <div class="px-3 px-sm-5">
      <!-- Title -->
      <div class="mb-3 position-relative">
        <label for="title" class="form-label"><span id="titleLabel">Title</span></label>
        <input type="text" id="title" name="title"
          class="form-control" maxlength="75"
          placeholder="Choose album or song above"
          disabled required>
        <div id="titleSuggestions" class="list-group position-absolute w-100" style="z-index:1000;"></div>
      </div>

      <!-- Artist -->
      <div class="mb-3 position-relative">
        <label for="artist" class="form-label">Artist</label>
        <input type="text" id="artist" name="artist" class="form-control" maxlength="75" required>
        <div id="artistSuggestions" class="list-group position-absolute w-100" style="z-index:1000;"></div>
      </div>

      <!-- Album field (for songs only) -->
      <div class="mb-3 d-none" id="albumField">
        <label for="album" class="form-label">Album (if adding a Song)</label>
        <input type="text" id="album" name="album" class="form-control" maxlength="75">
      </div>

      <!-- Image upload -->
      <div class="mb-3 text-center">
        <label for="image" class="form-label d-block">Cover Art</label>
        <div id="uploadBox" class="d-flex align-items-center justify-content-center border"
          style="width:300px; height:300px; margin:0 auto; cursor:pointer; background:#222; overflow:hidden; border-radius:12px;">
          <img id="imagePreview" src="/images/upload-icon.png" alt="Upload" style="max-width:100%; max-height:100%;">
        </div>
        <input type="file" id="image" name="image" class="d-none" accept=".jpg,.jpeg,.png,.webp" required>
      </div>

      <!-- Genre -->
      <div class="mb-3">
        <label for="genre" class="form-label">Genre</label>
        <select id="genre" name="genre" class="form-select" required>
          <option value="">-- Select Genre --</option>
          <?php foreach ($valid_genres as $genre): ?>
            <option value="<?= $genre ?>"><?= $genre ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Release Date -->
      <div class="mb-3">
        <label for="release_date" class="form-label">Release Date</label>
        <input type="date" id="release_date" name="release_date" class="form-control" required>
      </div>

      <!-- Initial Rating -->
      <div class="mb-3">
        <label for="rating" class="form-label">Initial Rating</label>
        <select id="rating" name="rating" class="form-select" required>
          <option value="">-- Select --</option>
          <option value="1">1 - Terrible</option>
          <option value="2">2 - Yawn-What?</option>
          <option value="3">3 - Not Too Shabby</option>
          <option value="4">4 - Really Good</option>
          <option value="5">5 - All Time Great</option>
        </select>
      </div>

      <!-- Initial Comment -->
      <div class="mb-3">
        <label for="comment" class="form-label">
          Initial description. Why add this <span id="musicTypeLabel">album/song</span>?
        </label>
        <textarea id="comment" name="comment" class="form-control" rows="3" required></textarea>
      </div>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary body-bg-color w-100">Add</button>
  </form>
</main>

<script>
  const existingMusic = <?= $existing_json ?? '[]' ?>;

  // --- Type switching ---
  const uploadBox = document.getElementById('uploadBox');
  const imageInput = document.getElementById('image');
  const imagePreview = document.getElementById('imagePreview');
  const formWrapper = document.getElementById('addMusicForm');
  const titleInput = document.getElementById('title');
  const titleLabel = document.getElementById('titleLabel');
  const musicTypeLabel = document.getElementById('musicTypeLabel');

  document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
      const albumField = document.getElementById('albumField');
      titleInput.disabled = false;

      if (this.value === 'song') {
        albumField.classList.remove('d-none');
        musicTypeLabel.textContent = 'song';
        titleLabel.textContent = 'Song Title';
        titleInput.placeholder = "Enter song title";
        formWrapper.classList.remove('soft-gray', 'muted-light-blue');
        formWrapper.classList.add('muted-light-red');
      } else {
        albumField.classList.add('d-none');
        musicTypeLabel.textContent = 'album';
        titleLabel.textContent = 'Album Title';
        titleInput.placeholder = "Enter album title";
        formWrapper.classList.remove('soft-gray', 'muted-light-red');
        formWrapper.classList.add('muted-light-blue');
      }
    });
  });

  uploadBox.addEventListener('click', () => imageInput.click());
  imageInput.addEventListener('change', (e) => {
    const [file] = e.target.files;
    if (file) {
      imagePreview.src = URL.createObjectURL(file);
      imagePreview.style.objectFit = "cover";
    }
  });

  function createSuggestionItem(m) {
    const item = document.createElement('a');
    item.className = 'list-group-item list-group-item-action small d-flex align-items-center';
    item.href = `?page=music&id=${m.music_id}&type=${m.type}`;
    item.target = "_blank"; // optional: open in new tab

    const img = document.createElement('img');
    img.src = m.image_path || '/images/generic.png';
    img.alt = (m.title_prefix + " " + m.title).trim();
    img.style.width = "48px";
    img.style.height = "48px";
    img.style.objectFit = "cover";
    img.classList.add("rounded", "me-2");

    const title = (m.title_prefix + " " + m.title).trim();
    const artist = (m.artist_prefix + " " + m.artist).trim();

    const textWrapper = document.createElement('div');
    textWrapper.innerHTML = `
    <div><strong>${title}</strong> by ${artist}</div>
    <div class="text-muted">Already exists (${m.type})</div>
  `;

    item.appendChild(img);
    item.appendChild(textWrapper);
    return item;
  }

  function showSuggestions(inputEl, suggestionsEl, query, type) {
    suggestionsEl.innerHTML = "";
    if (query.length < 4) return;

    const matches = existingMusic.filter(m => {
      const field = type === 'title' ?
        (m.title_prefix + " " + m.title).trim().toLowerCase() :
        (m.artist_prefix + " " + m.artist).trim().toLowerCase();
      return field.includes(query.toLowerCase());
    }).slice(0, 5);

    matches.forEach(m => {
      suggestionsEl.appendChild(createSuggestionItem(m));
    });
  }

  const titleSuggestions = document.getElementById('titleSuggestions');
  const artistSuggestions = document.getElementById('artistSuggestions');
  const artistInput = document.getElementById('artist');


  titleInput.addEventListener('input', () => showSuggestions(titleInput, titleSuggestions, titleInput.value, 'title'));
  artistInput.addEventListener('input', () => showSuggestions(artistInput, artistSuggestions, artistInput.value, 'artist'));

  document.addEventListener('click', (e) => {
    if (!titleSuggestions.contains(e.target) && e.target !== titleInput) {
      titleSuggestions.innerHTML = "";
    }
    if (!artistSuggestions.contains(e.target) && e.target !== artistInput) {
      artistSuggestions.innerHTML = "";
    }
  });
</script>

<?php require __DIR__ . '/footer_template.php'; ?>
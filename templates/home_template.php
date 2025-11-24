<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container my-4">

  <!-- Intro -->
  <div class="mb-2 text-center text-light">
    <div class="d-flex flex-column align-items-center text-center w-100">
      <h2>Welcome to</h2>

      <div class="d-flex align-items-center justify-content-center mb-3">
        <img src="/images/glass-logo.png"
          class="me-2 me-sm-3"
          style="height:60px; object-fit:contain;">
        <h2 class="mb-0">Brandywine Music Review</h2>
      </div>

    </div>
    <p>A place where you can sit back with a fine goblet of brandywine and rate and review your favorite and most loathed music of these glorious times.</p>
    <p>For more info about how it all works hit the learn more button below.</p>
  </div>

  <!-- About Sections (hidden by default unless ?page=about) -->
  <div id="aboutSections" class="<?= (($_GET['page'] ?? '') === 'about') ? '' : 'd-none' ?>">
    <div class="d-flex flex-column flex-lg-row muted-light-blue text-dark p-2 p-sm-3 mb-2 align-items-center align-items-lg-start" style="border-radius:8px;">

      <!-- Image -->
      <div class="p-sm-2 text-center" style="max-width:380px;">
        <img src="/images/consummate-reviewer.png"
          class="rounded img-fluid mx-auto d-block shrink-on-small"
          alt=""
          style="max-width:380px;" />
      </div>

      <!-- Text -->
      <div class="p-3 text-center text-lg-start" style="max-width:800px;">
        <h5>Signing up and looking around</h5>
        <p>
          Before you sign up, have a look around. I am not trying to create a huge encyclopedic music database here.
          This is really just a place for some like minded music fans to share some music and their thoughts on it.
          Members can add music as a song or as a full album, rate and comment on it and see what other users have to say.
          The idea is to add a song or album if you think it is really great or really bad (for fun), or more than anything
          if you think it could be something new for other members to hear.
        </p>
        <p>
          If you feel like you have something you would like to rate, comment on, or add, go to the login on the upper right
          and on the login screen select register to create a new account. Brandywine Music Review welcomes all, sign on up
          and share your thoughts.
        </p>
      </div>

    </div>

    <div class="d-flex flex-column-reverse flex-lg-row muted-light-blue text-dark p-2 p-sm-3 mb-2 align-items-center align-items-lg-start" style="border-radius:8px;">

      <!-- Text block -->
      <div class="p-2 text-center text-lg-start">
        <h5>Rating, commenting, and adding</h5>
        <p>
          The easiest thing to get started with is rating or commenting on an album or song.
          Simply select Albums or Songs on top and you will see a sortable searchable list of
          songs/albums already in the database. If any of them interest you click on them to get
          the main song/album page and there will be a spot to rate it on a 1-5 scale (5 being the best),
          or make a comment at the bottom, anything you feel like saying about that particular song/album.
          Simple as that.
        </p>
        <p>
          To add and album or song click on the little add music icon (the + inside the circle inside
          the square) and start by choosing album or song. The best way to be accurate is too look up the
          song/album online (allmusic, wikipedia, etc.) to get the spellings right and the album art,
          release date, etc. There is also a place to put in a listen link which accepts youtube and
          spotify urls. I reccomend spotify if you have it even though they only play 1 minute previews
          for each song the links are more stable (youtube links seem to go bad more often). So you just
          fill in all the info, rate and comment and add. Simple as that.
        </p>
      </div>

      <!-- Image block -->
      <div class="text-center p-sm-2 ms-lg-3">
        <img src="/images/brandywine-reviewer.png"
          class="rounded img-fluid mx-auto d-block shrink-on-small"
          alt=""
          style="max-width:380px;">
      </div>
    </div>

    <div class="muted-light-blue text-dark p-4 mb-4" style="border-radius:8px;">
      <h5>Get to know the community</h5>
      <p>Check the members tab and see what others are up to. Comment on other members albums/songs, rate other members albums/songs, see how other members
        rate your albums/songs. See who has similar or vastly different music taste. If you have a question or comment that is directed more towards a member
        than an album/song use the messaging system (messages under profile pic and username) and hit the send message tab
        and select the member you want to send a message to. Simple as that.
      </p>
    </div>
  </div>

  <div class="mb-5 text-center text-light">
    <button id="toggleAbout" class="btn btn-primary body-bg-color">
      <?= (($_GET['page'] ?? '') === 'about') ? 'Hide About' : 'Learn More' ?>
    </button>
  </div>

  <!-- Recent Content -->
  <div class="row">
    <!-- Recent Albums -->
    <div class="col-12 col-lg-4 mb-4">
      <h4 class="text-light mb-3">Recent Albums</h4>
      <ul class="list-unstyled muted-light-blue p-2" style="border-radius:8px;">
        <?php foreach ($recent_albums as $album): ?>
          <li class="mb-2">
            <a href="?page=music&id=<?= (int)$album['music_id'] ?>&type=album"
              class="d-flex align-items-center w-100 p-2 rounded text-decoration-none text-dark comment-bg-hover">

              <img src="<?= htmlspecialchars($album['image_path'] ?: '/uploads/profiles/generic.png') ?>"
                alt="Album Art"
                class="me-2"
                style="width:60px;height:60px;object-fit:cover;">

              <div>
                <div class="fw-bold">
                  <?= htmlspecialchars($album['title']) ?>
                </div>
                <div class="text-muted small">
                  <?= htmlspecialchars($album['artist']) ?> (<?= substr($album['release_date'], 0, 4) ?>)
                </div>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Recent Songs -->
    <div class="col-12 col-lg-4 mb-4">
      <h4 class="text-light mb-3">Recent Songs</h4>
      <ul class="list-unstyled muted-light-red p-2" style="border-radius:8px;">
        <?php foreach ($recent_songs as $song): ?>
          <li class="mb-2">
            <a href="?page=music&id=<?= (int)$song['music_id'] ?>&type=song"
              class="d-flex align-items-center w-100 p-2 rounded text-decoration-none text-dark comment-bg-hover">

              <img src="<?= htmlspecialchars($song['image_path'] ?: '/uploads/profiles/generic.png') ?>"
                alt="Album Art"
                class="me-2"
                style="width:60px;height:60px;object-fit:cover;">

              <div>
                <div class="fw-bold">
                  <?= htmlspecialchars($song['title']) ?>
                </div>
                <div class="text-muted small">
                  <?= htmlspecialchars($song['artist']) ?> (<?= substr($song['release_date'], 0, 4) ?>)
                </div>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Recent Comments -->
    <div class="col-12 col-lg-4 mb-4">
      <h4 class="text-light mb-3">Recent Comments</h4>
      <ul class="list-unstyled muted-light-blue p-2" style="border-radius:8px;">
        <?php foreach ($recent_comments as $c): ?>
          <li class="mb-2">
            <a href="?page=music&id=<?= (int)$c['target_id'] ?>&type=<?= htmlspecialchars($c['target_type']) ?>"
              class="text-decoration-none text-dark d-block p-2 rounded comment-bg-hover" style="border: solid 2px #444444;">
              <small><?= htmlspecialchars(mb_strimwidth($c['comment'], 0, 100, '...')) ?></small>
              <div class="text-muted small">
                — <?= htmlspecialchars($c['full_name']) ?> on <?= date("M j, Y", strtotime($c['date_added'])) ?>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</main>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('toggleAbout');
    const aboutSections = document.getElementById('aboutSections');

    if (toggleBtn) {
      toggleBtn.addEventListener('click', function() {
        if (aboutSections.classList.contains('d-none')) {
          aboutSections.classList.remove('d-none');
          toggleBtn.textContent = 'Hide About';
          history.replaceState(null, '', '?page=about'); // update URL
        } else {
          aboutSections.classList.add('d-none');
          toggleBtn.textContent = 'Learn More';
          history.replaceState(null, '', '?page=home'); // update URL
        }
      });
    }
  });
</script>

<?php require __DIR__ . '/footer_template.php'; ?>
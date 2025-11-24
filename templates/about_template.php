<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container-fluid my-4">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">
      <div class="card shadow-lg p-5 muted-light-blue">
        <h2 class="mb-4 text-center">About <?= htmlspecialchars($site_title) ?></h2>
        <p>
          <?= htmlspecialchars($site_title) ?> is a community-driven music review database. 
          It brings together albums, songs, and comments contributed by members, offering a growing 
          archive of ratings and reviews. Whether you’re here to discover new music, share your 
          favorites, or connect with other music fans, this site is built for you.
        </p>
        <p>
          You can browse albums and songs, leave ratings and comments, and keep track of your own 
          profile. Our mission is to preserve the shared experience of music listening and to create 
          a place where thoughtful discussion and discovery thrive.
        </p>
        <p>
          This project is still growing — new features are being added regularly. Stay tuned as we 
          continue to build and improve <?= htmlspecialchars($site_title) ?>!
        </p>
        <div class="text-center mt-4">
          <a href="?page=music_list&type=album" class="btn btn-primary me-2">Browse Albums</a>
          <a href="?page=music_list&type=song" class="btn btn-secondary">Browse Songs</a>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/footer_template.php'; ?>

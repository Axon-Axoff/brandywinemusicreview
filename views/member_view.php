  <?php

$page_title = "Member";

  if (!isLoggedIn()) {
      redirect('?page=login');
  }

  $member_id = (int)($_GET['id'] ?? 0);
  if ($member_id <= 0) {
      echo '<div class="alert alert-danger">Invalid member ID.</div>';
      require __DIR__ . '/footer_template.php';
      exit;
  }

  // Fetch member
  $stmt = $db_connection->prepare("
      SELECT user_id, user_name, full_name, profile_pic, created_at, bio, link_1_title, link_1_url, link_2_title, link_2_url 
      FROM users
      WHERE user_id = :id
      LIMIT 1
  ");
  $stmt->execute(['id' => $member_id]);
  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$member) {
      echo '<div class="alert alert-danger">Member not found.</div>';
      require __DIR__ . '/footer_template.php';
      exit;
  }

  // Latest albums
  $stmt = $db_connection->prepare("
      SELECT music_id, title, title_prefix, artist, artist_prefix, image_path, date_added
      FROM music
      WHERE creator_id = :id AND type = 'album'
      ORDER BY date_added DESC
      LIMIT 10
  ");
  $stmt->execute(['id' => $member_id]);
  $latest_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Latest songs
  $stmt = $db_connection->prepare("
      SELECT music_id, title, title_prefix, artist, artist_prefix, image_path, date_added
      FROM music
      WHERE creator_id = :id AND type = 'song'
      ORDER BY date_added DESC
      LIMIT 10
  ");
  $stmt->execute(['id' => $member_id]);
  $latest_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Latest ratings
  $stmt = $db_connection->prepare("
      SELECT r.rating_id, r.rating, r.date_added,
             m.music_id, m.type, m.title, m.title_prefix, m.artist, m.artist_prefix, m.image_path
      FROM ratings r
      JOIN music m ON r.target_id = m.music_id AND r.target_type = m.type
      WHERE r.rater_id = :id
      ORDER BY r.date_added DESC
      LIMIT 10
  ");
  $stmt->execute(['id' => $member_id]);
  $latest_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

  require __DIR__ . '/../templates/member_template.php';
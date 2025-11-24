<?php
if (!isLoggedIn()) {
  redirect('?page=login');
}

$page_title = "Add Music";
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type   = $_POST['type'] ?? '';
  $title  = trim($_POST['title'] ?? '');
  $artist = trim($_POST['artist'] ?? '');
  $album_for_song = trim($_POST['album'] ?? '');
  $genre  = $_POST['genre'] ?? '';
  $release_date = $_POST['release_date'] ?? null;
  $rating = (int)($_POST['rating'] ?? 0);
  $comment = trim($_POST['comment'] ?? '');

  $title_prefix = "";
  if (stripos($title, "The ") === 0) {
    $title_prefix = "The";
    $title = trim(substr($title, 4));
  }
  $artist_prefix = "";
  if (stripos($artist, "The ") === 0) {
    $artist_prefix = "The";
    $artist = trim(substr($artist, 4));
  }

  // Validation
  if (empty($title)) $errors[] = "Title is required.";
  if (empty($artist)) $errors[] = "Artist is required.";
  if (empty($genre)) $errors[] = "Genre is required.";
  if (empty($release_date)) $errors[] = "Release date is required.";
  if ($rating < 1 || $rating > 5) $errors[] = "A rating is required.";
  if (empty($comment)) $errors[] = "An initial comment is required.";
  if (empty($_FILES['image']['tmp_name'])) $errors[] = "Image is required.";
  if ($type !== 'album' && $type !== 'song') $errors[] = "Invalid type.";

  // Duplicate check
  $stmt = $db_connection->prepare("
        SELECT COUNT(*) FROM music
        WHERE type = :type
          AND LOWER(title) = LOWER(:title)
          AND LOWER(artist) = LOWER(:artist)
    ");
  $stmt->execute([
    'type'   => $type,
    'title'  => $title,
    'artist' => $artist
  ]);
  if ($stmt->fetchColumn() > 0) {
    $errors[] = "This $type already exists in the database.";
  }

  if (empty($errors)) {
    // Insert new row in unified table
    $stmt = $db_connection->prepare("
            INSERT INTO music 
                (type, title, title_prefix, artist, artist_prefix, album, genre, release_date, creator_id, creator_name, date_added) 
            VALUES 
                (:type, :title, :title_prefix, :artist, :artist_prefix, :album, :genre, :release_date, :creator_id, :creator_name, NOW())
        ");
    $stmt->execute([
      'type'          => $type,
      'title'         => $title,
      'title_prefix'  => $title_prefix,
      'artist'        => $artist,
      'artist_prefix' => $artist_prefix,
      'album'         => ($type === 'song' ? $album_for_song : null),
      'genre'         => $genre,
      'release_date'  => $release_date,
      'creator_id'    => $_SESSION['user_id'],
      'creator_name'  => $_SESSION['full_name']
    ]);

    $new_id = $db_connection->lastInsertId();
    $filename = $type . "_" . $new_id . ".jpg";

    // Handle image upload
    $file = $_FILES['image'];
    $folder = '/images/' . date('Y-m') . '/';
    $full_folder = __DIR__ . '/..' . $folder;
    if (!is_dir($full_folder)) mkdir($full_folder, 0775, true);

    $target = $full_folder . $filename;
    $src_img = match ($file['type']) {
      'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
      'image/png'  => imagecreatefrompng($file['tmp_name']),
      'image/webp' => imagecreatefromwebp($file['tmp_name']),
      default      => null
    };

    if ($src_img) {
      $dst_img = imagecreatetruecolor(300, 300);
      $src_w = imagesx($src_img);
      $src_h = imagesy($src_img);
      imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, 300, 300, $src_w, $src_h);
      imagejpeg($dst_img, $target, 90);
      imagedestroy($src_img);
      imagedestroy($dst_img);

      $image_path = $folder . $filename;
      $stmt = $db_connection->prepare("UPDATE music SET image_path = :path WHERE music_id = :id");
      $stmt->execute(['path' => $image_path, 'id' => $new_id]);
    }

    // Insert rating
    $stmt = $db_connection->prepare("
            INSERT INTO ratings (rater_id, target_type, target_id, rating, date_added)
            VALUES (:rater_id, :target_type, :target_id, :rating, NOW())
        ");
    $stmt->execute([
      'rater_id'    => $_SESSION['user_id'],
      'target_type' => $type,
      'target_id'   => $new_id,
      'rating'      => $rating
    ]);

    // If user not flagged as rater yet, update + recalc
    $check = $db_connection->prepare("SELECT rater_flag FROM users WHERE user_id = :id");
    $check->execute(['id' => $_SESSION['user_id']]);
    $flag = (int)$check->fetchColumn();
    if ($flag === 0) {
      $db_connection->prepare("UPDATE users SET rater_flag = 1 WHERE user_id = :id")
        ->execute(['id' => $_SESSION['user_id']]);
      calculateByAddRater($db_connection); // updates weights globally
    }

    calculateByAddRating($db_connection, $type, $new_id);

    // Insert initial comment
    $stmt = $db_connection->prepare("
            INSERT INTO comments (target_type, target_id, commenter_id, comment, date_added, is_initial)
            VALUES (:target_type, :target_id, :commenter_id, :comment, NOW(), 1)
        ");
    $stmt->execute([
      'target_type'   => $type,
      'target_id'     => $new_id,
      'commenter_id'  => $_SESSION['user_id'],
      'comment'       => $comment
    ]);

    // Redirect to the new music page
    redirect("?page=music&id={$new_id}&type={$type}");
  }
}

// Load all existing music title/artist/type into JSON for suggestion dropdowns
$stmt = $db_connection->query("
    SELECT music_id, type, title, title_prefix, artist, artist_prefix, image_path
    FROM music
");
$existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
$existing_json = json_encode($existing, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

require __DIR__ . '/../templates/add_music_template.php';

<?php
if (!isLoggedIn()) redirect('?page=login');

$type     = $_GET['type'] ?? 'album';
$music_id = (int)($_GET['id'] ?? 0);

// Fetch item
$stmt = $db_connection->prepare("SELECT * FROM music WHERE music_id = :id AND type = :type");
$stmt->execute(['id' => $music_id, 'type' => $type]);
$music_item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$music_item) {
    redirect("?page=music_list&type=$type");
}

// Check permissions (only creator or admin can edit)
if ($music_item['creator_id'] != $_SESSION['user_id'] && ($_SESSION['admin'] ?? 'N') !== 'Y') {
    redirect("?page=music&id=$music_id&type=$type");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_music') {
    $music_id = (int)($_GET['id'] ?? 0);
    $type     = $_GET['type'] ?? 'album';

    // Fetch music item (to check permissions + get image path)
    $stmt = $db_connection->prepare("SELECT creator_id, image_path FROM music WHERE music_id = :id AND type = :type");
    $stmt->execute(['id' => $music_id, 'type' => $type]);
    $music_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$music_item) {
        redirect("?page=music_list&type=$type&error=" . urlencode(ucfirst($type) . " not found."));
    }

    // Permissions: only admin or creator can delete
    if (($_SESSION['admin'] ?? 'N') === 'Y' || $music_item['creator_id'] == $_SESSION['user_id']) {
        // Delete ratings
        $stmt = $db_connection->prepare("DELETE FROM ratings WHERE target_type = :type AND target_id = :id");
        $stmt->execute(['type' => $type, 'id' => $music_id]);

        // Delete comments
        $stmt = $db_connection->prepare("DELETE FROM comments WHERE target_type = :type AND target_id = :id");
        $stmt->execute(['type' => $type, 'id' => $music_id]);

        // Delete cover image if exists
        if (!empty($music_item['image_path'])) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $music_item['image_path'];
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        // Delete the music item itself
        $stmt = $db_connection->prepare("DELETE FROM music WHERE music_id = :id AND type = :type");
        $stmt->execute(['id' => $music_id, 'type' => $type]);

        redirect("?page=music_list&type=$type&success=" . urlencode(ucfirst($type) . " deleted successfully."));
    } else {
        redirect("?page=music_list&type=$type&error=" . urlencode("Permission denied."));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comment') {
    $comment_id = (int)($_POST['comment_id'] ?? 0);
    if ($comment_id) {
        $stmt = $db_connection->prepare("DELETE FROM comments WHERE comment_id = :id");
        $stmt->execute(['id' => $comment_id]);
    }
    redirect("?page=music&id=$music_id&type=$type");
}

// --- Handle POST update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']);
    $artist       = trim($_POST['artist']);
    $album        = $type === 'song' ? trim($_POST['album'] ?? '') : null;
    $genre        = trim($_POST['genre']);
    $release_date = $_POST['release_date'];

    $image_path = $music_item['image_path']; // keep old unless new uploaded
    if (!empty($_FILES['image']['tmp_name'])) {
        $fileTmp  = $_FILES['image']['tmp_name'];
        $fileType = mime_content_type($fileTmp);
        
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($fileType, $allowed)) {
            // Create GD source
            switch ($fileType) {
                case 'image/jpeg': $src = imagecreatefromjpeg($fileTmp); break;
                case 'image/png':  $src = imagecreatefrompng($fileTmp); break;
                case 'image/webp': $src = imagecreatefromwebp($fileTmp); break;
            }
          
            if ($src) {
                $width  = imagesx($src);
                $height = imagesy($src);
            
                // Destination 300x300 canvas
                $dst = imagecreatetruecolor(300, 300);
                $white = imagecolorallocate($dst, 255, 255, 255);
                imagefilledrectangle($dst, 0, 0, 300, 300, $white);
            
                $scale = min(300 / $width, 300 / $height);
                $newW = (int)($width * $scale);
                $newH = (int)($height * $scale);
                $x = (int)((300 - $newW) / 2);
                $y = (int)((300 - $newH) / 2);
            
                imagecopyresampled($dst, $src, $x, $y, 0, 0, $newW, $newH, $width, $height);
            
                // --- Determine file path ---
                if (!empty($music_item['image_path'])) {
                    // reuse existing path
                    $savePath = $_SERVER['DOCUMENT_ROOT'] . $music_item['image_path'];
                } else {
                    // generate new path
                    $folder = "/images/" . date("Y-m");
                    $fullDir = $_SERVER['DOCUMENT_ROOT'] . $folder;
                    if (!is_dir($fullDir)) {
                        mkdir($fullDir, 0777, true);
                    }
                    $fileName = $type . "_" . $music_id . ".jpg"; // album_12.jpg or song_34.jpg
                    $savePath = $fullDir . "/" . $fileName;
                    $dbPath   = $folder . "/" . $fileName;
                  
                    // update DB only if new file path
                    $stmt = $db_connection->prepare("
                        UPDATE music SET image_path = :path WHERE music_id = :id
                    ");
                    $stmt->execute([
                        'path' => $dbPath,
                        'id'   => $music_id
                    ]);
                }
              
                // Save JPG (overwrite or new)
                imagejpeg($dst, $savePath, 90);
              
                imagedestroy($dst);
                imagedestroy($src);
            }
        }
    }


    // Update DB
    $stmt = $db_connection->prepare("
        UPDATE music
        SET title = :title,
            artist = :artist,
            album = :album,
            genre = :genre,
            release_date = :release_date,
            image_path = :image_path
        WHERE music_id = :id AND type = :type
    ");
    $stmt->execute([
        'title'        => $title,
        'artist'       => $artist,
        'album'        => $album,
        'genre'        => $genre,
        'release_date' => $release_date,
        'image_path'   => $image_path,
        'id'           => $music_id,
        'type'         => $type
    ]);

    redirect("?page=music&id=$music_id&type=$type&success=" . urlencode("Music updated!"));
}

// Fetch comments (admin only)
$comments = [];
if (($_SESSION['admin'] ?? 'N') === 'Y') {
    $stmt = $db_connection->prepare("
        SELECT c.*, u.full_name
        FROM comments c
        JOIN users u ON c.commenter_id = u.user_id
        WHERE c.target_type = :type AND c.target_id = :id
        ORDER BY c.date_added ASC
    ");
    $stmt->execute(['type' => $type, 'id' => $music_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require __DIR__ . '/../templates/update_music_template.php';

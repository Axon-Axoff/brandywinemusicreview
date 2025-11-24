<?php
$page_title = "Home";

// Recent Albums
$stmt = $db_connection->prepare("
    SELECT music_id, title, artist, release_date, image_path
    FROM music
    WHERE type = 'album'
    ORDER BY date_added DESC
    LIMIT 10
");
$stmt->execute();
$recent_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Songs
$stmt = $db_connection->prepare("
    SELECT music_id, title, artist, release_date, image_path
    FROM music
    WHERE type = 'song'
    ORDER BY date_added DESC
    LIMIT 10
");
$stmt->execute();
$recent_songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Comments (joined to users + music for context)
$stmt = $db_connection->prepare("
    SELECT c.comment, c.date_added, c.target_type, c.target_id, u.full_name, m.title, m.type
    FROM comments c
    JOIN users u ON c.commenter_id = u.user_id
    JOIN music m ON c.target_id = m.music_id AND c.target_type = m.type
    ORDER BY c.date_added DESC
    LIMIT 10
");
$stmt->execute();
$recent_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../templates/home_template.php';

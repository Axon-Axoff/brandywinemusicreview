<?php
if (!isLoggedIn()) {
    redirect('?page=login');
}

$type     = $_GET['type'] ?? 'album';
$music_id = (int)($_GET['id'] ?? 0);

// Fetch item
$stmt = $db_connection->prepare("SELECT * FROM music WHERE music_id = :id AND type = :type");
$stmt->execute(['id' => $music_id, 'type' => $type]);
$music_item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$music_item) {
    redirect("?page=music_list&type=$type");
}

// --- Handle rating form submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating_value = (int)$_POST['rating'];

    if ($rating_value >= 1 && $rating_value <= 5) {
        $stmt = $db_connection->prepare("
            INSERT INTO ratings (rater_id, target_type, target_id, rating, date_added)
            VALUES (:rater_id, :target_type, :music_id, :rating, NOW())
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), date_added = NOW()
        ");
        $stmt->execute([
            'rater_id'    => $_SESSION['user_id'],
            'target_type' => $type,
            'music_id'    => $music_id,
            'rating'      => $rating_value
        ]);

        // mark as rater
        $db_connection->prepare("UPDATE users SET rater_flag=1 WHERE user_id=:id AND rater_flag=0")
            ->execute(['id' => $_SESSION['user_id']]);

        calculateByAddRating($db_connection, $type, $music_id);

        // Send system message to creator
        $stmt = $db_connection->prepare("SELECT creator_id, title, title_prefix FROM music WHERE music_id = :id");
        $stmt->execute(['id' => $music_id]);
        $music = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($music && $music['creator_id'] != $_SESSION['user_id']) {
            $title   = trim(($music['title_prefix'] ?? '') . ' ' . $music['title']);
            $subject = $_SESSION['full_name'] . " rated " . $title;
            $body    = "Rating: " . $rating_value . "\n\n" .
                       "View here: <a href='" . urlBase() . "?page=music&id={$music_id}&type={$type}'>" .
                       htmlspecialchars($title) . "</a>";
            sendSystemMessage($db_connection, (int)$music['creator_id'], $subject, $body);
        }
    }

    redirect("?page=music&id=$music_id&type=$type");
}

// --- Handle new comment submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_comment') {
    $comment_text = trim($_POST['comment'] ?? '');
    if (!empty($comment_text)) {
        $stmt = $db_connection->prepare("
            INSERT INTO comments (commenter_id, target_type, target_id, comment, date_added, is_initial)
            VALUES (:uid, :type, :tid, :comment, NOW(), 0)
        ");
        $stmt->execute([
            'uid'     => $_SESSION['user_id'],
            'type'    => $type,
            'tid'     => $music_id,
            'comment' => $comment_text
        ]);
    }

    // Send system message to creator
    $stmt = $db_connection->prepare("SELECT creator_id, title, title_prefix FROM music WHERE music_id = :id");
    $stmt->execute(['id' => $music_id]);
    $music = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($music && $music['creator_id'] != $_SESSION['user_id']) {
        $title    = trim(($music['title_prefix'] ?? '') . ' ' . $music['title']);
        $subject  = $_SESSION['full_name'] . " commented on " . $title;
        $excerpt  = mb_substr($comment_text, 0, 100) . (mb_strlen($comment_text) > 100 ? "…" : "");
        $body     = htmlspecialchars($excerpt) . "\n\n" .
                    "View here: <a href='" . urlBase() . "?page=music&id={$music_id}&type={$type}'>" .
                    htmlspecialchars($title) . "</a>";
        sendSystemMessage($db_connection, (int)$music['creator_id'], $subject, $body);
    }

    redirect("?page=music&id=$music_id&type=$type");
}

// --- Handle link update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link'])) {
    $link = normalizeMusicLink($_POST['link']);
    if (!empty($link)) {
        $stmt = $db_connection->prepare("UPDATE music SET link = :link WHERE music_id = :id");
        $stmt->execute([
            'link' => $link,
            'id'   => $music_id
        ]);
    }

    redirect("?page=music&id=$music_id&type=$type");
}

// Fetch raters
$stmt = $db_connection->prepare("
    SELECT r.rating, u.full_name
    FROM ratings r
    JOIN users u ON r.rater_id = u.user_id
    WHERE r.target_type = :type AND r.target_id = :id
    ORDER BY r.date_added DESC
    LIMIT 10
");
$stmt->execute(['id' => $music_id, 'type' => $type]);
$raters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user’s rating
$stmt = $db_connection->prepare("
    SELECT rating FROM ratings 
    WHERE target_type=:type AND target_id=:id AND rater_id=:uid
");
$stmt->execute(['type' => $type, 'id' => $music_id, 'uid' => $_SESSION['user_id']]);
$user_rating = $stmt->fetchColumn();

// Initial comment
$stmt = $db_connection->prepare("
    SELECT c.*, u.full_name
    FROM comments c
    JOIN users u ON c.commenter_id = u.user_id
    WHERE c.target_type = :type AND c.target_id = :id AND c.is_initial = 1
    ORDER BY c.date_added ASC LIMIT 1
");
$stmt->execute(['id' => $music_id, 'type' => $type]);
$initial_comment = $stmt->fetch(PDO::FETCH_ASSOC);

// Other comments
$stmt = $db_connection->prepare("
    SELECT c.*, u.full_name
    FROM comments c
    JOIN users u ON c.commenter_id = u.user_id
    WHERE c.target_type = :type AND c.target_id = :id AND c.is_initial = 0
    ORDER BY c.date_added ASC
");
$stmt->execute(['id' => $music_id, 'type' => $type]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../templates/music_template.php';

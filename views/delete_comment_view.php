<?php
if (!isLoggedIn() || ($_SESSION['admin'] ?? 'N') !== 'Y') {
    redirect('?page=login');
}

$comment_id = (int)($_GET['id'] ?? 0);
if ($comment_id) {
    $stmt = $db_connection->prepare("DELETE FROM comments WHERE comment_id = :id");
    $stmt->execute(['id' => $comment_id]);
}

redirect($_SERVER['HTTP_REFERER'] ?? '?page=music_list');

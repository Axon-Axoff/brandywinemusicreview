<?php
if (!isLoggedIn()) {
    redirect('?page=login');
}

$page_title = "Messages";
$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// --- Handle actions (save, delete, reply, compose) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $message_id = (int) ($_POST['message_id'] ?? 0);

    if ($action === 'toggle_read' && $message_id) {
        $stmt = $db_connection->prepare("
            UPDATE messages
            SET is_read = IF(is_read=1,0,1)
            WHERE message_id = :id AND receiver_id = :uid
        ");
        $stmt->execute(['id' => $message_id, 'uid' => $user_id]);
    }

    if ($action === 'delete' && $message_id) {
        $stmt = $db_connection->prepare("
            DELETE FROM messages
            WHERE message_id = :id
              AND (receiver_id = :uid OR sender_id = :uid)
        ");
        $stmt->execute(['id' => $message_id, 'uid' => $user_id]);
    }

    if ($action === 'mark_all_read') {
        $stmt = $db_connection->prepare("
            UPDATE messages
            SET is_read = 1
            WHERE receiver_id = :uid
        ");
        $stmt->execute(['uid' => $user_id]);
    }

    if ($action === 'save' && $message_id) {
        $stmt = $db_connection->prepare("
            UPDATE messages
            SET is_saved = IF(is_saved=1,0,1)
            WHERE message_id = :id
              AND receiver_id = :uid
        ");
        $stmt->execute(['id' => $message_id, 'uid' => $user_id]);
    }

    if ($action === 'reply' && !empty($_POST['body']) && !empty($_POST['parent_id'])) {
        $parent_id = (int) $_POST['parent_id'];

        // Find who to send reply to
        $stmt = $db_connection->prepare("
            SELECT sender_id, receiver_id
            FROM messages
            WHERE message_id = :parent_id
        ");
        $stmt->execute(['parent_id' => $parent_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($parent) {
            $receiver_id = ($parent['sender_id'] == $user_id)
                ? $parent['receiver_id']
                : $parent['sender_id'];

            $stmt = $db_connection->prepare("
                INSERT INTO messages (sender_id, receiver_id, subject, body, parent_id, created_at)
                VALUES (:sender_id, :receiver_id, 'Re:', :body, :parent_id, NOW())
            ");
            $stmt->execute([
                'sender_id'   => $user_id,
                'receiver_id' => $receiver_id,
                'body'        => sanitizeInput($_POST['body']),
                'parent_id'   => $parent_id
            ]);
            redirect('?page=messages&success=' . urlencode("Reply sent!") . '&tab=inbox');
        }
    }

    if ($action === 'compose' && !empty($_POST['receiver_id']) && !empty($_POST['subject']) && !empty($_POST['body'])) {
        $stmt = $db_connection->prepare("
            INSERT INTO messages (sender_id, receiver_id, subject, body, created_at)
            VALUES (:sender_id, :receiver_id, :subject, :body, NOW())
        ");
        $stmt->execute([
            'sender_id'   => $user_id,
            'receiver_id' => (int) $_POST['receiver_id'],
            'subject'     => sanitizeInput($_POST['subject']),
            'body'        => sanitizeInput($_POST['body'])
        ]);
        redirect('?page=messages&success=' . urlencode("Message sent!") . '&tab=sent');
    }

    // Mark entire thread as read
    if ($action === 'mark_thread_read' && !empty($_POST['thread_root_id'])) {
        $root_id = (int) $_POST['thread_root_id'];
    
        // Collect all IDs in this thread
        $ids = [];
        $queue = [$root_id];
        $childStmt = $db_connection->prepare("SELECT message_id FROM messages WHERE parent_id = :pid");
    
        while ($queue) {
            $pid = array_shift($queue);
            $ids[] = $pid;
            $childStmt->execute(['pid' => $pid]);
            $children = $childStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($children as $cid) {
                $queue[] = (int)$cid;
            }
        }
      
        if ($ids) {
            $inClause = implode(',', array_map('intval', $ids));
            $db_connection->exec("
                UPDATE messages
                SET is_read = 1
                WHERE message_id IN ($inClause)
                  AND receiver_id = {$user_id}
            ");
        }
        redirect('?page=messages&tab=inbox');
    }

    // Mark thread as partially unread (root + last only)
    if ($action === 'mark_thread_unread_partial' && !empty($_POST['thread_root_id'])) {
        $root_id = (int) $_POST['thread_root_id'];
    
        // Get last descendant by created_at
        $stmt = $db_connection->prepare("
            SELECT message_id
            FROM messages
            WHERE parent_id = :pid OR message_id = :pid
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['pid' => $root_id]);
        $last_id = (int) $stmt->fetchColumn();
    
        $idsToUnread = [$root_id, $last_id];
    
        $inClause = implode(',', array_map('intval', $idsToUnread));
        $db_connection->exec("
            UPDATE messages
            SET is_read = 0
            WHERE message_id IN ($inClause)
              AND receiver_id = {$user_id}
        ");
    
        redirect('?page=messages&tab=inbox');
    }

    // Default redirect back
    redirect('?page=messages');
}

// --- Pick up success message after redirect ---
if (!empty($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}

// --- Determine active tab ---
if (isset($_GET['to']) && is_numeric($_GET['to'])) {
    // If coming from the Members "Message" button → go straight to compose
    $active_tab = 'compose';
} else {
    $active_tab = $_GET['tab'] ?? 'inbox';
}


list($inbox_threads, $sent_only) = getInboxThreads($db_connection, $user_id);

// --- Compose dropdown ---
$stmt = $db_connection->query("SELECT user_id, full_name FROM users ORDER BY full_name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../templates/messages_template.php';

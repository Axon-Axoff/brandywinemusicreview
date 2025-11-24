<?php


// --- Build conversation threads ---
function buildMessageTree(array $messages, $parent_id = null): array
{
  $branch = [];
  foreach ($messages as $msg) {
    if ($msg['parent_id'] == $parent_id) {
      $children = buildMessageTree($messages, $msg['message_id']);
      if ($children) {
        $msg['children'] = $children;
      }
      $branch[] = $msg;
    }
  }
  return $branch;
}


function calculateByAddRater(PDO $db): void
{
  // Total raters
  $stmt = $db->query("SELECT COUNT(*) FROM users WHERE rater_flag = 1");
  $total_raters = (int)$stmt->fetchColumn();
  if ($total_raters <= 0) return;

  // Update all music items with ratings
  $stmt = $db->query("SELECT music_id, type, rating, total_ratings FROM music WHERE total_ratings > 0");
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $music) {
    $weight = $music['total_ratings'] / $total_raters;
    $weighted_rating = round(($music['rating'] * $weight) + $music['rating'], 2);

    $update = $db->prepare("
            UPDATE music 
            SET weighted_rating = :wr 
            WHERE music_id = :id AND type = :type
        ");
    $update->execute([
      'wr'   => $weighted_rating,
      'id'   => $music['music_id'],
      'type' => $music['type']
    ]);
  }

  // --- Recalculate rankings for all music items ---
  recalcRankings($db);
}


function calculateByAddRating(PDO $db, string $target_type, int $target_id): void
{
  // --- Get sum of ratings and total ratings for this target ---
  $stmt = $db->prepare("
        SELECT SUM(rating) AS rating_sum, COUNT(*) AS total_ratings
        FROM ratings
        WHERE target_type = :target_type AND target_id = :target_id
    ");
  $stmt->execute(['target_type' => $target_type, 'target_id' => $target_id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $rating_sum    = (float)($row['rating_sum'] ?? 0);
  $total_ratings = (int)($row['total_ratings'] ?? 0);

  if ($total_ratings > 0) {
    $rating = round($rating_sum / $total_ratings, 2);

    // total raters = users with rater_flag = 1
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE rater_flag = 1");
    $total_raters = (int)$stmt->fetchColumn();

    $weight = ($total_raters > 0) ? ($total_ratings / $total_raters) : 1;
    $weighted_rating = round(($rating * $weight) + $rating, 2);

    // Update unified music table
    $stmt = $db->prepare("
            UPDATE music
            SET rating = :rating,
                weighted_rating = :weighted_rating,
                total_ratings = :total_ratings
            WHERE music_id = :id AND type = :type
        ");
    $stmt->execute([
      'rating'          => $rating,
      'weighted_rating' => $weighted_rating,
      'total_ratings'   => $total_ratings,
      'id'              => $target_id,
      'type'            => $target_type
    ]);

    // --- Recalculate rankings for all music items ---
    recalcRankings($db);
  }
}


// Count total messages in a thread (node + descendants)
function countThreadMessages(array $msg): int
{
  $count = 1;
  if (!empty($msg['children'])) {
    foreach ($msg['children'] as $child) {
      $count += countThreadMessages($child);
    }
  }
  return $count;
}


function countUnreadThreads(array $threads, int $user_id): int
{
  $count = 0;
  foreach ($threads as $thread) {
    // Only count if the thread has an unread message for me (receiver side)
    if (threadHasUnreadForUser($thread, $user_id)) {
      $count++;
    }
  }
  return $count;
}


function fetchAllMembers(PDO $db_connection): array
{
  $statement = $db_connection->query("SELECT * FROM users ORDER BY user_name ASC");
  return $statement->fetchAll(PDO::FETCH_ASSOC);
}


function fetchAllMusic(PDO $db_connection, string $type = 'album'): array
{
  $statement = $db_connection->prepare("
        SELECT * FROM music 
        WHERE type = :type 
        ORDER BY release_date DESC
    ");
  $statement->execute(['type' => $type]);
  return $statement->fetchAll(PDO::FETCH_ASSOC);
}


function fetchMusicById(PDO $db_connection, int $music_id, string $type = 'album'): ?array
{
  $statement = $db_connection->prepare("
        SELECT * FROM music 
        WHERE music_id = :music_id AND type = :type
    ");
  $statement->execute([
    'music_id' => $music_id,
    'type'     => $type
  ]);
  return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
}


function getInboxThreads(PDO $db_connection, int $user_id): array
{
  $stmt = $db_connection->prepare("
        SELECT m.message_id, m.subject, m.body, m.created_at,
               m.is_read, m.is_saved, m.parent_id,
               m.sender_id, m.receiver_id,
               su.full_name AS sender_name,
               ru.full_name AS receiver_name
        FROM messages m
        JOIN users su ON m.sender_id = su.user_id
        JOIN users ru ON m.receiver_id = ru.user_id
        WHERE m.receiver_id = :user_id OR m.sender_id = :user_id
        ORDER BY m.created_at DESC
    ");
  $stmt->execute(['user_id' => $user_id]);
  $all_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $threads = buildMessageTree($all_messages);

  $inbox_threads = [];
  $sent_only = [];

  foreach ($threads as $thread) {
    $started_by_me = ($thread['sender_id'] == $user_id);
    $has_replies   = !empty($thread['children']);

    if (!$started_by_me) {
      $inbox_threads[] = $thread; // started by others → inbox
    } elseif ($has_replies) {
      $inbox_threads[] = $thread; // started by me but replied → inbox
    } else {
      $sent_only[] = $thread;     // started by me, no replies → sent-only
    }
  }

  // Sort inbox threads by latest activity
  usort($inbox_threads, function ($a, $b) {
    return strcmp(getThreadLatestTime($b), getThreadLatestTime($a));
  });

  // Sort sent-only by created_at
  usort($sent_only, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
  });

  return [$inbox_threads, $sent_only];
}


function getThreadLatestTime(array $msg): string
{
  $latest = $msg['created_at'];
  if (!empty($msg['children'])) {
    foreach ($msg['children'] as $child) {
      $childLatest = getThreadLatestTime($child);
      if ($childLatest > $latest) {
        $latest = $childLatest;
      }
    }
  }
  return $latest;
}


function isLoggedIn(): bool
{
  return isset($_SESSION['user_id']);
}


function normalizeMusicLink(string $url): string
{
  $url = trim($url);

  // If it's already an iframe (Bandcamp embed)
  if (stripos($url, '<iframe') === 0) {
    return $url;
  }

  // Spotify
  if (strpos($url, 'spotify.com') !== false) {
    $url = preg_replace('#\?.*$#', '', $url);
    if (strpos($url, '/track/') !== false) {
      return str_replace("open.spotify.com/track/", "open.spotify.com/embed/track/", $url);
    }
    if (strpos($url, '/album/') !== false) {
      return str_replace("open.spotify.com/album/", "open.spotify.com/embed/album/", $url);
    }
    if (strpos($url, '/playlist/') !== false) {
      return str_replace("open.spotify.com/playlist/", "open.spotify.com/embed/playlist/", $url);
    }
  }

  // YouTube
  if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
    $videoId = null;
    $playlistId = null;

    if (preg_match('/[?&]list=([^&]+)/', $url, $matches)) {
      $playlistId = $matches[1];
    }
    if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
      $videoId = $matches[1];
    } elseif (strpos($url, 'youtu.be/') !== false) {
      $parts = explode('youtu.be/', $url);
      $videoId = strtok($parts[1], '?');
    }

    if ($playlistId && !$videoId) {
      return "https://www.youtube.com/embed/videoseries?list=$playlistId";
    } elseif ($playlistId && $videoId) {
      return "https://www.youtube.com/embed/$videoId?list=$playlistId";
    } elseif ($videoId) {
      return "https://www.youtube.com/embed/$videoId";
    }
  }

  return $url; // fallback
}


function pickLatestActivity(array $activitiesForUser): ?array
{
  if (empty($activitiesForUser)) return null;

  // Check if "added" exists in last 3
  foreach ($activitiesForUser as $act) {
    if ($act['action_type'] === 'added') {
      return $act; // prefer add
    }
  }

  // Otherwise, just return the most recent
  return $activitiesForUser[0];
}


function recalcRankings(PDO $db): void
{
  // By rating (albums)
  $db->exec("
        UPDATE music m
        JOIN (
            SELECT music_id, RANK() OVER (ORDER BY rating DESC) AS rank_val
            FROM music WHERE type = 'album'
        ) r ON m.music_id = r.music_id
        SET m.rating_ranking = r.rank_val
        WHERE m.type = 'album'
    ");

  // By weighted rating (albums)
  $db->exec("
        UPDATE music m
        JOIN (
            SELECT music_id, RANK() OVER (ORDER BY weighted_rating DESC) AS rank_val
            FROM music WHERE type = 'album'
        ) r ON m.music_id = r.music_id
        SET m.weighted_ranking = r.rank_val
        WHERE m.type = 'album'
    ");

  // By rating (songs)
  $db->exec("
        UPDATE music m
        JOIN (
            SELECT music_id, RANK() OVER (ORDER BY rating DESC) AS rank_val
            FROM music WHERE type = 'song'
        ) r ON m.music_id = r.music_id
        SET m.rating_ranking = r.rank_val
        WHERE m.type = 'song'
    ");

  // By weighted rating (songs)
  $db->exec("
        UPDATE music m
        JOIN (
            SELECT music_id, RANK() OVER (ORDER BY weighted_rating DESC) AS rank_val
            FROM music WHERE type = 'song'
        ) r ON m.music_id = r.music_id
        SET m.weighted_ranking = r.rank_val
        WHERE m.type = 'song'
    ");
}


function redirect(string $url): void
{
  header("Location: $url");
  exit;
}


function renderMessageThread(array $messages, int $depth = 0)
{
  $currentUserId = $_SESSION['user_id'] ?? 0;

  foreach ($messages as $msg):
    $isRoot     = ($depth === 0);
    $thread_id  = "thread" . (int)$msg['message_id'];
    $totalCount = countThreadMessages($msg); // root + all descendants

    // Override: if I am the sender, always treat message as read for me
    $isReadForMe = $msg['is_read'];
    if ($msg['sender_id'] == $currentUserId) {
      $isReadForMe = 1;
    }
?>

    <div class="card mb-2 ms-<?= $depth ? 3 : 0 ?> position-relative"
      style="background-color: <?= $isReadForMe ? '#ccccdd' : '#ddddcc' ?>;">

      <div class="d-flex">
        <!-- Left-side label: root only, and only if more than 1 message in thread -->
        <?php if ($isRoot && $totalCount > 1): ?>
          <div class="d-flex flex-column align-items-center justify-content-start px-2"
            style="min-width:40px; cursor:pointer; background-color:darkslateblue;"
            data-bs-toggle="collapse"
            data-bs-target="#<?= $thread_id ?>"
            aria-expanded="false"
            aria-controls="<?= $thread_id ?>"
            onclick="this.querySelector('.toggle-symbol').textContent =
                            (this.querySelector('.toggle-symbol').textContent.trim() === '+') ? '–' : '+';">
            <span class="toggle-symbol fw-bold text-light">+</span>
            <small class="text-light"><?= (int)$totalCount ?></small>
          </div>
        <?php else: ?>
          <div style="min-width:10px;"></div>
        <?php endif; ?>

        <!-- Main card body -->
        <div class="card-body d-flex justify-content-between align-items-end" style="padding: 0.75rem;">

          <!-- LEFT: subject + meta + body -->
          <div class="flex-grow-1 pe-3">
            <h6 class="mb-1 <?= $isReadForMe ? 'text-muted' : 'text-dark' ?>">
              <?= htmlspecialchars($msg['subject']) ?>
            </h6>
            <small class="text-muted mb-2 d-block">
              <?php if ($msg['sender_id'] == 17): ?>
                From: <strong>System</strong>
              <?php elseif ($msg['sender_id'] == $currentUserId): ?>
                To: <?= htmlspecialchars($msg['receiver_name']) ?>
              <?php else: ?>
                From: <?= htmlspecialchars($msg['sender_name']) ?>
              <?php endif; ?>
              | <?= date("F jS Y, g:ia", strtotime($msg['created_at'])) ?>
            </small>

            <p class="mb-0 <?= $isReadForMe ? 'text-muted' : 'text-dark' ?>">
              <?= nl2br(htmlspecialchars_decode($msg['body'])) ?>
            </p>
          </div>

          <!-- RIGHT: action buttons side by side -->
          <div class="d-flex gap-2 flex-shrink-0" style="min-width:200px; justify-content:flex-end;">
            <form method="post" action="?page=messages" class="d-inline">
              <input type="hidden" name="action" value="save">
              <input type="hidden" name="message_id" value="<?= (int)$msg['message_id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-primary">
                <?= $msg['is_saved'] ? "Unsave" : "Save" ?>
              </button>
            </form>

            <form method="post" action="?page=messages" class="d-inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="message_id" value="<?= (int)$msg['message_id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>

            <button class="btn btn-sm btn-outline-success"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#replyForm<?= (int)$msg['message_id'] ?>">
              Reply
            </button>
          </div>

          <!-- Read/unread checkmark -->
          <?php if ($isRoot): ?>
            <!-- Root thread-level check -->
            <form method="post" action="?page=messages"
              class="position-absolute top-0 end-0 m-2">
              <input type="hidden" name="thread_root_id" value="<?= (int)$msg['message_id'] ?>">
              <?php if (threadHasUnreadForUser($msg, $currentUserId)): ?>
                <input type="hidden" name="action" value="mark_thread_read">
                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Mark thread read">
                  <span class="badge rounded-circle bg-success d-flex align-items-center justify-content-center"
                    style="width:20px; height:20px;">✔</span>
                </button>
              <?php else: ?>
                <input type="hidden" name="action" value="mark_thread_unread_partial">
                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Mark root + last unread">
                  <span class="badge rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                    style="width:20px; height:20px;">✔</span>
                </button>
              <?php endif; ?>
            </form>
          <?php else: ?>
            <!-- Child messages -->
            <?php if ($msg['receiver_id'] == $currentUserId): ?>
              <!-- Receiver: toggle works -->
              <form method="post" action="?page=messages"
                class="position-absolute top-0 end-0 m-2">
                <input type="hidden" name="action" value="toggle_read">
                <input type="hidden" name="message_id" value="<?= (int)$msg['message_id'] ?>">
                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                  <?php if ($isReadForMe): ?>
                    <span class="badge rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                      style="width:20px; height:20px;">✔</span>
                  <?php else: ?>
                    <span class="badge rounded-circle bg-success d-flex align-items-center justify-content-center"
                      style="width:20px; height:20px;">✔</span>
                  <?php endif; ?>
                </button>
              </form>
            <?php elseif ($msg['sender_id'] == $currentUserId): ?>
              <!-- Sender: always show gray (read) check -->
              <div class="position-absolute top-0 end-0 m-2">
                <span class="badge rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                  style="width:20px; height:20px;">✔</span>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Reply form -->
      <div class="collapse mt-2 px-3" id="replyForm<?= (int)$msg['message_id'] ?>">
        <form method="post" action="?page=messages">
          <input type="hidden" name="action" value="reply">
          <input type="hidden" name="parent_id" value="<?= (int)$msg['message_id'] ?>">
          <div class="mb-2">
            <textarea name="body" rows="3" class="form-control" required></textarea>
          </div>
          <button type="submit" class="btn btn-sm btn-primary">Send Reply</button>
        </form>
      </div>

      <!-- Children -->
      <?php if ($isRoot && !empty($msg['children'])): ?>
        <div class="collapse" id="<?= $thread_id ?>">
          <?php renderMessageThread($msg['children'], $depth + 1); ?>
        </div>
      <?php elseif (!$isRoot && !empty($msg['children'])): ?>
        <?php renderMessageThread($msg['children'], $depth + 1); ?>
      <?php endif; ?>
    </div>
<?php endforeach;
}


function renderPageTitle(string $page_title): void
{
  $filename = strtolower(str_replace(' ', '-', $page_title)) . '-title-bg.webp';
  $image_path = "/images/" . $filename;

  echo '<div class="w-100 mb-2"
              style="height:80px; border-radius:8px;
                     background: url(\'' . $image_path . '\') repeat-x center center;
                     background-size: auto 80px;">
            <h2 class="text-light d-flex justify-content-center align-items-center h-100 m-0">'
    . htmlspecialchars($page_title) .
    '</h2>
          </div>';
}


function renderPagination(
  $current_page,
  $total_pages,
  $filter,
  $value,
  $sort,
  $dir,
  $total_items,
  $per_page,
  $type = 'album',
  $genres = [],
  $year_range = null,
  $year_start = null,
  $year_end = null
) {
  if ($total_pages < 1) return "<span class='text-light'>No " . ucfirst($type) . "s found that match filer/search.</span>";

  $html = "<nav class='mb-2'><ul class='pagination justify-content-center'>";

  $buildUrl = function ($p) use ($filter, $value, $sort, $dir, $type, $genres, $year_range, $year_start, $year_end) {
    $query = [
      'page'     => 'music_list',
      'type'     => $type,
      'page_num' => $p,
      'sort'     => $sort,
      'dir'      => $dir
    ];

    // legacy single filter
    if ($filter && $value) {
      $query['filter'] = $filter;
      $query['value']  = $value;
    }

    // multi-genre - always force [] syntax
    if (!empty($genres)) {
      $query['genres'] = array_values($genres); // force proper [] array format
    }

    // year range
    if ($year_range) {
      $query['year_range'] = $year_range;
    }

    // custom year range
    if ($year_start && $year_end) {
      $query['year_start'] = $year_start;
      $query['year_end']   = $year_end;
    }

    return '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
  };

  $renderPage = function ($i, $is_active) use ($current_page, $total_items, $per_page, $buildUrl) {
    if ($is_active) {
      $start = ($i - 1) * $per_page + 1;
      $end   = min($i * $per_page, $total_items);
      $display_page = "(" . $start . "-" . $end . ")";
    } else {
      $display_page = $i;
    }
    $active = $is_active ? ' active' : '';
    return "<li class='page-item$active'><a class='page-link' href='{$buildUrl($i)}'>$display_page</a></li>";
  };

  // Previous
  $prev_disabled = ($current_page == 1) ? ' disabled' : '';
  $html .= "<li class='page-item$prev_disabled'><a class='page-link' href='{$buildUrl(max(1,$current_page - 1))}'>&laquo;</a></li>";

  if ($total_pages <= 10) {
    for ($i = 1; $i <= $total_pages; $i++) {
      $html .= $renderPage($i, $i == $current_page);
    }
  } else {
    // first two
    for ($i = 1; $i <= 2; $i++) {
      $html .= $renderPage($i, $i == $current_page);
    }

    if ($current_page > 4) {
      $html .= "<li class='page-item disabled'><span class='page-link'>…</span></li>";
    }

    // sliding window
    for ($i = max(3, $current_page - 2); $i <= min($total_pages - 2, $current_page + 2); $i++) {
      $html .= $renderPage($i, $i == $current_page);
    }

    if ($current_page < $total_pages - 3) {
      $html .= "<li class='page-item disabled'><span class='page-link'>…</span></li>";
    }

    // last two
    for ($i = $total_pages - 1; $i <= $total_pages; $i++) {
      $html .= $renderPage($i, $i == $current_page);
    }
  }

  // Next
  $next_disabled = ($current_page == $total_pages) ? ' disabled' : '';
  $html .= "<li class='page-item$next_disabled'><a class='page-link' href='{$buildUrl(min($total_pages,$current_page + 1))}'>&raquo;</a></li>";

  $html .= '</ul></nav>';
  return $html;
}


function sanitizeInput(string $raw_input): string
{
  return trim($raw_input);
}


function sendSystemMessage(PDO $db, int $receiver_id, string $subject, string $body): void
{
  $stmt = $db->prepare("
        INSERT INTO messages (sender_id, receiver_id, subject, body, created_at)
        VALUES (17, :receiver_id, :subject, :body, NOW())
    ");
  $stmt->execute([
    'receiver_id' => $receiver_id,
    'subject'     => $subject,
    'body'        => $body
  ]);
}


function sortButton($label, $field, $current_sort, $current_dir, $filter, $value, $type = 'album')
{
  $isActive = ($current_sort === $field);

  // normalize
  $current_dir = strtolower($current_dir);

  // toggle only if same field
  $dir = ($isActive && $current_dir === 'asc') ? 'desc' : 'asc';

  $query = [
    'page' => 'music_list',
    'type' => $type,
    'sort' => $field,
    'dir'  => $dir
  ];
  if ($filter && $value) {
    $query['filter'] = $filter;
    $query['value']  = $value;
  }

  $url = '?' . http_build_query($query);

  return sprintf(
    '<a href="%s" class="btn btn-sort %s">
            <span class="icon %s">&#9650;</span>
            %s
            <span class="icon %s">&#9660;</span>
        </a>',
    htmlspecialchars($url),
    $isActive ? 'active' : '',
    ($isActive && $current_dir === 'asc') ? 'active' : '',
    htmlspecialchars($label),
    ($isActive && $current_dir === 'desc') ? 'active' : ''
  );
}


// True if this thread has any unread messages for the current user
function threadHasUnreadForUser(array $msg, int $user_id): bool
{
  $isUnreadHere = ($msg['receiver_id'] == $user_id && (int)$msg['is_read'] === 0);
  if ($isUnreadHere) return true;

  if (!empty($msg['children'])) {
    foreach ($msg['children'] as $child) {
      if (threadHasUnreadForUser($child, $user_id)) return true;
    }
  }
  return false;
}


function trackLastPage()
{
  $excluded = ['login', 'logout', 'register'];

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    return;
  }

  $uri = $_SERVER['REQUEST_URI'] ?? '/';

  foreach ($excluded as $word) {
    if (stripos($uri, $word) !== false) {
      return;
    }
  }

  $_SESSION['last_page'] = $uri;
}

// Run on every request
trackLastPage();


function urlBase(): string
{
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
  return $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/";
}

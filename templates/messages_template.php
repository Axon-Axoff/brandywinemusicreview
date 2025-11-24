<?php require __DIR__ . '/header_template.php'; ?>
<?php require __DIR__ . '/nav_template.php'; ?>

<main class="container my-2">
  <?= renderPageTitle('Messages'); ?>

  <!-- Flash Messages -->
  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
  <?php elseif (!empty($error_message)): ?>
    <div class="alert alert-danger"><?= $error_message ?></div>
  <?php endif; ?>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="messagesTab" role="tablist">
    <li class="nav-item">
      <button class="nav-link <?= $active_tab === 'inbox' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#inbox">Inbox
        <?php
        list($inbox_threads, $dummy) = getInboxThreads($db_connection, $_SESSION['user_id']);
        $unread_count = countUnreadThreads($inbox_threads, $_SESSION['user_id']);
        if ($unread_count > 0): ?>
          <span class="badge bg-danger ms-2"><?= $unread_count ?></span>
        <?php endif; ?>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link <?= $active_tab === 'sent' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#sent">Sent</button>
    </li>
    <li class="nav-item">
      <button class="nav-link <?= $active_tab === 'compose' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#compose">Send New Message</button>
    </li>
  </ul>

  <div class="tab-content mt-3">
    <!-- Inbox -->
    <div class="tab-pane fade <?= $active_tab === 'inbox' ? 'show active' : '' ?>" id="inbox">
      <?php if (!empty($inbox_threads)): ?>
        <?php renderMessageThread($inbox_threads); ?>
      <?php else: ?>
        <div class="alert alert-info">No conversations in inbox.</div>
      <?php endif; ?>
    </div>

    <!-- Sent -->
    <div class="tab-pane fade <?= $active_tab === 'sent' ? 'show active' : '' ?>" id="sent">
      <?php if (!empty($sent_only)): ?>
        <?php foreach ($sent_only as $msg): ?>
          <div class="card mb-2 p-2" style="background-color:#f9f9f9;">
            <h6 class="mb-1"><?= htmlspecialchars($msg['subject']) ?></h6>
            <small class="text-muted">
              To: <?= htmlspecialchars($msg['receiver_name']) ?>
              | <?= date("F jS Y, g:ia", strtotime($msg['created_at'])) ?>
            </small>
            <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($msg['body'])) ?></p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-info">No sent messages without replies.</div>
      <?php endif; ?>
    </div>

    <!-- Compose -->
    <div class="tab-pane fade <?= $active_tab === 'compose' ? 'show active' : '' ?>" id="compose">
      <div class="card p-4 shadow-sm muted-light-blue">
        <h3 class="h5 mb-3">Send New Message</h3>
        <form method="post" action="?page=messages">
          <input type="hidden" name="action" value="compose">

          <div class="mb-3">
            <label for="receiver_id" class="form-label">To</label>
            <select id="receiver_id" name="receiver_id" class="form-select" required>
              <option value="" disabled <?= !isset($_GET['to']) ? 'selected' : '' ?>>Select a recipient</option>
              <?php foreach ($users as $user): ?>
                <option value="<?= (int) $user['user_id'] ?>"
                  <?= (isset($_GET['to']) && $_GET['to'] == $user['user_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($user['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="body" class="form-label">Message</label>
            <textarea id="body" name="body" rows="6" class="form-control" required></textarea>
          </div>

          <button type="submit" class="btn button-green" style="color:white;">Send</button>
        </form>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/footer_template.php'; ?>
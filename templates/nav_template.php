<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="?page=home"><img src="/images/nav-logo.png"></a>

    <!-- Hamburger -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarMenu" aria-controls="navbarMenu"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsed Menu -->
    <div class="collapse navbar-collapse" id="navbarMenu">
      <!-- Left group (Albums, Songs) -->
      <ul class="navbar-nav me-auto align-items-center">
        <li class="nav-item">
          <a class="nav-link" href="?page=music_list&type=album">Albums</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="?page=music_list&type=song">Songs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="?page=members_list">Members</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="?page=stats">
            <img src="/images/stats.webp" alt="Brandywine Stats" title="Brandywine Stats" style="opacity:0.7;" />
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="?page=add">
            <img src="/images/add-music.webp" alt="Add Music" title="Add Album or Song" style="opacity:0.7;" />
          </a>
        </li>
      </ul>

      <!-- Username dropdown aligned right -->
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (isLoggedIn()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
              id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?php if (!empty($_SESSION['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['profile_pic']) ?>"
                  alt="Profile"
                  class="rounded-circle me-2"
                  width="32" height="32">
              <?php endif; ?>
              <?php
              list($inbox_threads, $dummy) = getInboxThreads($db_connection, $_SESSION['user_id']);
              $unread_count = countUnreadThreads($inbox_threads, $_SESSION['user_id']);
              ?>
              <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger ms-2"><?= $unread_count ?></span>
              <?php endif; ?>
              <?= htmlspecialchars($_SESSION['user_name']) ?>
            </a>
            <ul class="dropdown-menu user-dropdown" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="?page=profile">Profile</a></li>
              <li>
                <a class="dropdown-item d-flex justify-content-end align-items-center" href="?page=messages">
                  <span class="me-1">Messages</span>
                  <?php
                  list($inbox_threads, $dummy) = getInboxThreads($db_connection, $_SESSION['user_id']);
                  $unread_count = countUnreadThreads($inbox_threads, $_SESSION['user_id']);
                  if ($unread_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?= $unread_count ?></span>
                  <?php endif; ?>
                </a>
              </li>
              <li><a class="dropdown-item" href="?page=about">About</a>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="?page=logout">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="?page=login">Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
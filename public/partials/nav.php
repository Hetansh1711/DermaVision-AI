<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;

$initials = '';
if ($user && !empty($user['name'])) {
    $parts = explode(' ', trim($user['name']));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr($parts[1], 0, 1));
    }
}
?>

<nav class="nav">

  <div class="nav-left">
    <a href="/derma_ai_website/public/index.php" class="nav-logo">
      <img src="/derma_ai_website/public/assets/img/logo.png"
           style="height:28px; vertical-align:middle; margin-right:6px;">
      DermaVision AI
    </a>
  </div>

  <div class="nav-right">

    <?php if ($user): ?>

      <div class="user-menu">

        <button class="avatar" id="avatarBtn">
          <?= htmlspecialchars($initials) ?>
        </button>

        <ul class="user-dropdown" id="userDropdown">


          <li class="divider"></li>

          <li><a href="index.php">🏠 Home</a></li>
          <li><a href="ai-dermatologist.php">🧠 AI Dermatologist</a></li>
          <li><a href="about.php">ℹ About</a></li>

          <li class="divider"></li>

          <li><a href="logout.php" class="logout">🚪 Logout</a></li>

        </ul>
      </div>

    <?php else: ?>

      <a href="login.php" class="btn-outline btn-small">Login</a>
      <a href="signup.php" class="btn-primary btn-small">Sign Up</a>
    <?php endif; ?>

  </div>
</nav>

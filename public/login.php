<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$error = null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = "Invalid email or password.";
        } else {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];
            header("Location: ai-dermatologist.php");
            exit;
        }
    }
}
?>

<?php include __DIR__ . '/partials/header.php'; ?>
<body class="auth">

<div class="auth-container">
  <div class="auth-box">
    <h1 class="auth-title">Log In</h1>
    <p class="auth-subtitle">Access your DermaVision AI account</p>

    <?php if ($error): ?>
      <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <label class="form-label">Email</label>
      <input type="email" name="email" placeholder="you@example.com" required>

      <label class="form-label">Password</label>
      <input type="password" name="password" placeholder="Enter password" required>

      <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">
        Login
      </button>
    </form>

    <div class="auth-footer">
      Don't have an account?
      <a href="signup.php">Sign Up</a>
    </div>
  </div>
</div>

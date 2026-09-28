<?php

session_start();

require_once __DIR__ . '/../config/db.php';

$errors = [];
$name = '';
$email = '';

if (isset($_SESSION['user'])) {

    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {

            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            } else {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare('
                    INSERT INTO users (name, email, password_hash, created_at)
                    VALUES (:name, :email, :password_hash, NOW())
                ');

                $stmt->execute([
                    ':name'          => $name,
                    ':email'         => $email,
                    ':password_hash' => $hash,
                ]);

                $userId = $pdo->lastInsertId();
                $_SESSION['user'] = [
                    'id'    => $userId,
                    'name'  => $name,
                    'email' => $email,
                ];

                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up · DermaVision AI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth">
<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Create your account</h1>
        <p class="auth-subtitle">
            Sign up to securely save your skin scans and access them anytime.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <div>
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <label class="form-label" for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?php echo htmlspecialchars($name); ?>"
                required
                placeholder="Enter your full name"
            >

            <label class="form-label" for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($email); ?>"
                required
                placeholder="you@example.com"
            >

            <label class="form-label" for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                placeholder="At least 6 characters"
            >

            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
                placeholder="Re-enter your password"
            >

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 6px;">
                Create account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="login.php">Log in</a>
        </div>
    </div>
</div>
</body>
</html>

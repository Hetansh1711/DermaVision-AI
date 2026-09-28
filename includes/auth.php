<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Check login */
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

/* Get current user safely */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/* Require login for protected pages */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

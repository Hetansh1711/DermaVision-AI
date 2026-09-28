<?php
// public/includes/user.php

/**
 * Get current logged-in user from session.
 *
 * @return array|null
 */
function getCurrentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Get a nice display name for user.
 *
 * @param array|null $user
 * @return string
 */
function getUserDisplayName(?array $user): string
{
    if (!$user) {
        return 'Guest';
    }

    if (!empty($user['name'])) {
        return $user['name'];
    }

    return $user['email'] ?? 'User';
}

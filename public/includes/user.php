<?php

@return array|null

function getCurrentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}


@param array|null $user
@return string

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

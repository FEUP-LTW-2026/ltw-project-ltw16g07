<?php

function has_role(string $role): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function is_admin(): bool
{
    return has_role('admin');
}

function is_trainer(): bool
{
    return has_role('trainer');
}

function is_member(): bool
{
    return has_role('member');
}

function require_auth(): void
{
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_error'] = 'Please login to access this page.';
        header('Location: login.php');
        exit;
    }
}

function require_role(string $role): void
{
    require_auth();
    if (!has_role($role)) {
        $_SESSION['flash_error'] = 'You do not have permission to access this page.';
        header('Location: dashboard.php');
        exit;
    }
}

function require_admin(): void
{
    require_role('admin');
}

function require_trainer(): void
{
    require_role('trainer');
}

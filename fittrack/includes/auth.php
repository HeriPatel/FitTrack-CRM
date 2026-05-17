<?php
session_start();

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function require_login()
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Get current user data
function current_user()
{
    if (is_logged_in()) {
        return $_SESSION;
    }
    return null;
}

// Check role
function check_role($role)
{
    if (!is_logged_in() || $_SESSION['role'] !== $role) {
        return false;
    }
    return true;
}

// Require specific role or redirect
function require_role($role)
{
    require_login();
    if ($_SESSION['role'] !== $role) {
        // Redirect to a dashboard or error page if unauthorized
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}
?>

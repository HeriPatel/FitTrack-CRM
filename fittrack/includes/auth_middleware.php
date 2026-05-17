<?php
// includes/auth_middleware.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// --- Security Helper Functions ---

/**
 * Generate CSRF Token and store in session
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF validation failed.');
    }
}

/**
 * Sanitize Input
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// --- Authentication Functions ---

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function current_user()
{
    return is_logged_in() ? $_SESSION : null;
}

/**
 * Require Login Middleware
 */
function require_login()
{
    if (!is_logged_in()) {
        header("Location: /FitTrack/login.php"); // Adjust path if needed
        exit();
    }
}

/**
 * Require Specific Role Middleware
 * @param string|array $allowed_roles
 */
function require_role($allowed_roles)
{
    require_login();

    $roles = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];

    if (!in_array($_SESSION['role'], $roles)) {
        // Unauthorized access
        http_response_code(403);
        die("403 Forbidden - Access Denied"); // Or redirect to a custom error page
    }
}

/**
 * Redirect User based on Role (Login Success)
 */
function redirect_by_role($role)
{
    switch ($role) {
        case 'admin':
            header("Location: admin/index.php");
            break;
        case 'staff':
            header("Location: staff/index.php");
            break;
        case 'member':
            header("Location: member/index.php");
            break;
        default:
            header("Location: login.php");
    }
    exit();
}
?>

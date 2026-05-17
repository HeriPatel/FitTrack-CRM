<?php
// Test Authentication Logic
require_once 'config/db.php';
session_start();

echo "Testing Authentication Logic...\n";

// 1. Test Valid Login
$username = 'admin';
$password = 'password';

$stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    echo "[PASS] Valid Login (admin/password) verified successfully.\n";
}
else {
    echo "[FAIL] Valid Login failed.\n";
}

// 2. Test Invalid Login
$bad_pass = 'wrongpassword';
if ($user && password_verify($bad_pass, $user['password_hash'])) {
    echo "[FAIL] Invalid Login accepted wrong password.\n";
}
else {
    echo "[PASS] Invalid Login rejected correctly.\n";
}

// 3. Test Session Logic (Simulation)
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    echo "[PASS] Session simulation successful.\n";
}
else {
    echo "[FAIL] Session simulation failed.\n";
}

echo "Done.\n";
?>

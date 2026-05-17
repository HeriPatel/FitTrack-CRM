<?php
// Test Login Logic Final
require_once 'config/db.php';
session_start();

echo "Testing Authenticated Login...\n";

$email = 'admin@fittrack.com';
$password = 'admin123';

try {
    // Check if user exists with EMAIL column
    $stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "[PASS] User found by email: " . $user['email'] . "\n";

        if (password_verify($password, $user['password_hash'])) {
            echo "[PASS] Password verification successful.\n";
            echo "[SUCCESS] Login logic is working.\n";
        }
        else {
            echo "[FAIL] Password verification failed.\n";
        }
    }
    else {
        echo "[FAIL] User not found explicitly by email. Schema might still be wrong or empty.\n";

        // Debug: List columns
        $stmt = $pdo->query("DESCRIBE users");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Columns in users table: " . implode(", ", $cols) . "\n";
    }

}
catch (PDOException $e) {
    echo "[ERROR] Database Exception: " . $e->getMessage() . "\n";
}
?>

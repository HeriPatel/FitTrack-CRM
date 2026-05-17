<?php
// setup_db.php - Automates Database Creation & Seeding

require_once 'config/db.php';

try {
    // 1. Connect to MySQL server (root/no-db) to create database
    $pdo_root = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS);
    $pdo_root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server.\n";

    // 2. Re-Create Database
    $pdo_root->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    $pdo_root->exec("CREATE DATABASE " . DB_NAME);
    echo "Database '" . DB_NAME . "' (re)created.\n";

    // 3. Connect to the new database
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 4. Execute Schema
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("Error: database.sql not found.\n");
    }
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    echo "Tables created successfully.\n";

    // 5. Seed Data
    echo "Seeding data...\n";
    $password = password_hash('admin123', PASSWORD_BCRYPT);

    // Users
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (:email, :pass, :role)");

    // Admin
    $stmt->execute([':email' => 'admin@fittrack.com', ':pass' => $password, ':role' => 'admin']);
    $adminId = $pdo->lastInsertId();

    // Staff (Trainer)
    $stmt->execute([':email' => 'staff@fittrack.com', ':pass' => $password, ':role' => 'staff']);
    $staffId = $pdo->lastInsertId();

    // Member
    $stmt->execute([':email' => 'member@fittrack.com', ':pass' => $password, ':role' => 'member']);
    $memberId = $pdo->lastInsertId();

    // Member Profiles
    $stmtProfile = $pdo->prepare("INSERT INTO member_profiles (user_id, full_name, phone, digital_waiver_signed) VALUES (?, ?, ?, 1)");
    $stmtProfile->execute([$adminId, 'Super Admin', '555-0000']);
    $stmtProfile->execute([$staffId, 'John Trainer', '555-1111']);
    $stmtProfile->execute([$memberId, 'Alice Member', '555-2222']);

    // Subscriptions
    $pdo->exec("INSERT INTO subscriptions (user_id, plan_name, start_date, end_date, price) VALUES 
        ($memberId, 'Premium Monthly', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 49.99)");

    // Equipment
    $pdo->exec("INSERT INTO equipment (name, category, stock_count, low_stock_threshold) VALUES 
        ('Yoga Mat', 'Accessory', 20, 5),
        ('Dumbbell Set (5kg)', 'Weights', 4, 10), -- Low stock!
        ('Treadmill', 'Machine', 5, 1)");

    // Trainer Schedule
    $pdo->exec("INSERT INTO trainers_schedules (trainer_id, member_id, session_time, notes) VALUES 
        ($staffId, $memberId, DATE_ADD(NOW(), INTERVAL 1 DAY), 'Introductory PT Session')");

    echo "Dummy data inserted successfully.\n";
    echo "Setup Complete!\n";

}
catch (PDOException $e) {
    die("Setup Error: " . $e->getMessage() . "\n");
}
?>

<?php
require_once 'includes/auth.php';

// Ensure user is logged in
require_login();

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FitTrack CRM</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a73e8;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logout-btn {
            color: #d93025;
            text-decoration: none;
            font-weight: 500;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .welcome-text {
            font-size: 1.2rem;
            color: #333;
        }
        .role-badge {
            background-color: #e8f0fe;
            color: #1a73e8;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="brand">FitTrack CRM</div>
        <div class="user-menu">
            <span><?php echo htmlspecialchars($user['username']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h1>Dashboard</h1>
            <p class="welcome-text">
                Welcome back, <strong><?php echo htmlspecialchars($user['username']); ?></strong>!
            </p>
            <p>
                Your Role: <span class="role-badge"><?php echo htmlspecialchars($user['role']); ?></span>
            </p>
            
            <div style="margin-top: 2rem;">
                <!-- Content placeholders based on role -->
                <?php if ($user['role'] === 'admin'): ?>
                    <h3>Admin Quick Actions</h3>
                    <ul>
                        <li>Manage Users (Coming Soon)</li>
                        <li>System Settings (Coming Soon)</li>
                    </ul>
                <?php
endif; ?>
                
                <h3>Recent Activity</h3>
                <p>No recent activity to show.</p>
            </div>
        </div>
    </div>
</body>
</html>

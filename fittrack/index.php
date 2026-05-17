<?php
require_once 'includes/auth_middleware.php';

// Redirect to dashboard if logged in
if (is_logged_in()) {
    redirect_by_role($_SESSION['role']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack CRM - Home</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            padding: 5rem 0;
            margin-bottom: 2rem;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .custom-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-radius: 16px;
        }
        .custom-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.4) !important;
            border-color: #38bdf8;
        }
        .custom-card h3 {
            color: #38bdf8;
            font-weight: 700;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg glass-nav">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fa-solid fa-dumbbell"></i> FitTrack CRM</a>
            <div class="d-flex">
                <a href="login.php" class="btn btn-glass me-2">Login</a>
                <a href="register.php" class="btn btn-primary-custom">Register</a>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1>Manage Your Fitness Journey</h1>
            <p class="lead mb-4 text-light">The all-in-one platform for gyms, trainers, and members.</p>
            <a href="register.php" class="btn btn-primary-custom btn-lg fw-bold px-5 py-3">Get Started Now <i class="fa-solid fa-arrow-right ms-2"></i></a>
        </div>
    </div>

    <div class="container py-3 mb-5">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <a href="login.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm custom-card text-center p-3">
                        <div class="card-body">
                            <i class="fa-solid fa-person-running fa-3x mb-3 text-info"></i>
                            <h3>For Members</h3>
                            <p class="text-light">Track your workouts, manage subscriptions, and view payment history.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="login.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm custom-card text-center p-3">
                        <div class="card-body">
                            <i class="fa-solid fa-user-ninja fa-3x mb-3 text-info"></i>
                            <h3>For Staff</h3>
                            <p class="text-light">Manage schedules, mark attendance, and access member profiles instantly.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="login.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm custom-card text-center p-3">
                        <div class="card-body">
                            <i class="fa-solid fa-chart-line fa-3x mb-3 text-info"></i>
                            <h3>For Admins</h3>
                            <p class="text-light">Monitor revenue, manage inventory, and get real-time business insights.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <footer class="py-4 mt-5 text-center" style="border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="container text-muted">
            &copy; <?php echo date('Y'); ?> FitTrack CRM. All rights reserved.
        </div>
    </footer>
</body>
</html>

<?php
require_once 'includes/auth_middleware.php';

$error = '';

// If already logged in, redirect
if (is_logged_in()) {
    redirect_by_role($_SESSION['role']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    }
    else {
        try {


            $stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login Success
                session_regenerate_id(true); // Prevent Session Fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                redirect_by_role($user['role']);
            }
            else {
                $error = "Invalid email or password.";
            }
        }
        catch (PDOException $e) {
            $error = "System error. Please try again later.";
        // error_log($e->getMessage()); // Log error in production
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitTrack CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }
        .brand-logo {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }
    </style>
</head>
<body>
    <div class="glass-panel login-card">
        <div class="brand-logo"><i class="fa-solid fa-dumbbell"></i> FitTrack</div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger bg-danger text-white border-0"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="email" class="form-label text-light">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-light border-secondary"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control border-start-0" id="email" name="email" required autofocus placeholder="name@example.com">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label text-light">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-light border-secondary"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control border-start-0" id="password" name="password" required placeholder="••••••••">
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary-custom btn-lg fw-bold">Sign In <i class="fa-solid fa-arrow-right-to-bracket"></i></button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="register.php" class="text-info text-decoration-none hover-glow">New Member? Register Here</a>
        </div>
        
        <div class="mt-4 text-center text-muted small border-top border-secondary pt-3">
            <p class="mb-1 fw-bold text-light">Demo Accounts (Pass: admin123):</p>
            admin@fittrack.com<br>staff@fittrack.com<br>member@fittrack.com
        </div>
    </div>
</body>
</html>

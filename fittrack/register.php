<?php
require_once 'includes/auth_middleware.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);

    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $waiver = isset($_POST['waiver']) ? 1 : 0;

    // Validation
    if (empty($fullname) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required.";
    }
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    elseif (!$waiver) {
        $error = "You must sign the digital waiver.";
    }
    else {
        // Handle File Upload
        $photoPath = null;
        if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['id_photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExt, $allowed)) {
                $fileName = uniqid() . '.' . $fileExt;
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['id_photo']['tmp_name'], $targetPath)) {
                    $photoPath = $targetPath;
                }
                else {
                    $error = "Failed to upload photo.";
                }
            }
            else {
                $error = "Invalid file type. Only JPG, JPEG, PNG allowed.";
            }
        }

        if (!$error) {
            try {
                $pdo->beginTransaction();

                // 1. Create User
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (:email, :pass, 'member')");
                $stmt->execute(['email' => $email, 'pass' => $password_hash]);
                $userId = $pdo->lastInsertId();

                // 2. Create Profile
                $stmt = $pdo->prepare("INSERT INTO member_profiles (user_id, full_name, phone, digital_waiver_signed, id_photo_path) VALUES (:uid, :name, :phone, :waiver, :photo)");
                $stmt->execute([
                    'uid' => $userId,
                    'name' => $fullname,
                    'phone' => $phone,
                    'waiver' => $waiver,
                    'photo' => $photoPath
                ]);

                $pdo->commit();
                $success = "Registration successful! You can now <a href='login.php'>Login</a>.";
            }
            catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() == 23000) { // Duplicate entry
                    $error = "Email already registered.";
                }
                else {
                    $error = "Registration failed: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration - FitTrack CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 0;
        }
        .register-card {
            width: 100%;
            max-width: 600px;
            padding: 2.5rem;
        }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="glass-panel register-card mx-auto">
                    <div class="brand-logo"><i class="fa-solid fa-dumbbell"></i> Join FitTrack Today</div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger bg-danger text-white border-0"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success bg-success text-white border-0"><?php echo $success; ?></div>
                    <?php else: ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label text-light">Full Name</label>
                            <input type="text" name="fullname" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-light">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">ID Photo (for verification)</label>
                            <input type="file" name="id_photo" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="waiver" class="form-check-input" id="waiverCheck" required>
                            <label class="form-check-label text-light" for="waiverCheck">
                                I agree to the Digital Liability Waiver & Terms.
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom btn-lg fw-bold">Register Now</button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4 border-top border-secondary pt-3">
                        <a href="login.php" class="text-info text-decoration-none hover-glow">Already a member? Login Here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

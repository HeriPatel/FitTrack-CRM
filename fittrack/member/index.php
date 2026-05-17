<?php
require_once '../includes/auth_middleware.php';
require_role('member');

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$userId = $_SESSION['user_id'];
$msg = $_GET['msg'] ?? '';

// Fetch Profile details to use everywhere
$stmt = $pdo->prepare("SELECT * FROM member_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

// Process POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'book_session') {
            $trainerId = (int)$_POST['trainer_id'];
            $date = $_POST['session_date'];
            $time = $_POST['session_time'];
            $datetime = $date . ' ' . $time . ':00';
            
            $stmt = $pdo->prepare("INSERT INTO trainers_schedules (trainer_id, member_id, session_time, duration_minutes, notes) VALUES (?, ?, ?, 60, 'Member booked session via portal.')");
            $stmt->execute([$trainerId, $userId, $datetime]);
            
            header("Location: index.php?page=book&msg=booked");
            exit;
        }
        elseif ($_POST['action'] === 'renew_sub') {
            $planName = $_POST['plan'];
            $price = ($planName === 'Premium') ? 99.00 : 49.00;
            
            $pdo->beginTransaction();
            try {
                // Insert Sub
                $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_name, start_date, end_date, status, price) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'active', ?)");
                $stmt->execute([$userId, $planName, $price]);
                $subId = $pdo->lastInsertId();
                
                // Insert Payment
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, subscription_id, amount, payment_date, payment_method, invoice_ref, status) VALUES (?, ?, ?, CURDATE(), 'Credit Card', ?, 'paid')");
                $invoiceRef = 'INV-' . strtoupper(uniqid());
                $stmt->execute([$userId, $subId, $price, $invoiceRef]);
                
                $pdo->commit();
                header("Location: index.php?page=dashboard&msg=renewed");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                die("Renewal Failed: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Portal - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand bg-dark glass-nav">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-heart-pulse"></i> FitTrack Member</span>
            <div class="d-flex align-items-center">
                <?php if (!empty($profile['id_photo_path'])): ?>
                    <img src="../<?php echo htmlspecialchars($profile['id_photo_path']); ?>" alt="ID" class="rounded-circle me-2 border border-info" style="width: 40px; height: 40px; object-fit: cover;">
                <?php endif; ?>
                <span class="text-light me-3 fw-bold"><?php echo htmlspecialchars($profile['full_name'] ?? 'Member'); ?></span>
                <a href="../logout.php" class="btn btn-glass btn-sm">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar d-none d-md-block">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                            <i class="fa-solid fa-house-user me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'book' ? 'active' : ''; ?>" href="?page=book">
                            <i class="fa-solid fa-calendar-check me-2"></i> Book Session
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'renew' ? 'active' : ''; ?>" href="?page=renew">
                            <i class="fa-solid fa-credit-card me-2"></i> Subscription
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                
                <?php if ($msg === 'booked'): ?>
                    <div class="alert alert-success glass-panel text-success border-success">Session successfully booked! See you there.</div>
                <?php elseif ($msg === 'renewed'): ?>
                    <div class="alert alert-success glass-panel text-success border-success">Payment successful! Your subscription has been renewed.</div>
                <?php endif; ?>

                <?php if ($page === 'dashboard'): ?>
                    <?php
                        // Fetch Active Subscription
                        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE() ORDER BY end_date DESC LIMIT 1");
                        $stmt->execute([$userId]);
                        $subscription = $stmt->fetch();

                        // Fetch Payment History
                        $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC LIMIT 5");
                        $stmt->execute([$userId]);
                        $payments = $stmt->fetchAll();
                    ?>
                    <h2 class="fw-bold mb-4">Welcome back, <?php echo htmlspecialchars($profile['full_name'] ?? 'Guest'); ?></h2>
                    
                    <div class="row g-4">
                        <div class="col-md-8">
                            <!-- Subscription Status -->
                            <div class="glass-panel p-4 mb-4">
                                <h4 class="text-info mb-3"><i class="fa-solid fa-ranking-star"></i> Current Plan</h4>
                                <?php if ($subscription): ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="fw-bold text-light"><?php echo htmlspecialchars($subscription['plan_name']); ?></h3>
                                            <p class="text-muted mb-0">Expires: <strong class="text-light"><?php echo date('F j, Y', strtotime($subscription['end_date'])); ?></strong></p>
                                        </div>
                                        <span class="badge bg-success fs-6 px-3 py-2">Active</span>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning bg-transparent border-warning text-warning mb-0">
                                        No active subscription found. <a href="?page=renew" class="alert-link text-warning text-decoration-underline">Renew Now</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Recent Payments -->
                            <div class="glass-panel p-4">
                                <h4 class="mb-3 text-light"><i class="fa-solid fa-file-invoice-dollar text-muted"></i> Payment History</h4>
                                <table class="table table-hover text-light mb-0">
                                    <thead><tr><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                                    <tbody>
                                        <?php if(count($payments) > 0): foreach ($payments as $pay): ?>
                                        <tr>
                                            <td><?php echo $pay['payment_date']; ?></td>
                                            <td>$<?php echo number_format($pay['amount'], 2); ?></td>
                                            <td><span class="badge bg-success"><?php echo ucfirst($pay['status']); ?></span></td>
                                        </tr>
                                        <?php endforeach; else: ?>
                                        <tr><td colspan="3" class="text-center text-muted">No past payments.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Quick Profile -->
                            <div class="glass-panel p-4 mb-4">
                                <h5 class="mb-3"><i class="fa-solid fa-id-badge text-muted"></i> Profile</h5>
                                <p class="mb-2 text-light"><strong>Phone:</strong> <?php echo htmlspecialchars($profile['phone'] ?? 'N/A'); ?></p>
                                <p class="mb-2 text-light"><strong>Joined:</strong> <?php echo htmlspecialchars($profile['join_date'] ?? 'N/A'); ?></p>
                                <?php if ($profile['digital_waiver_signed']): ?>
                                    <p class="mb-0 text-success"><i class="fa-solid fa-file-signature"></i> Waiver Signed</p>
                                <?php else: ?>
                                    <p class="mb-0 text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Waiver Pending</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <a href="?page=book" class="btn btn-primary-custom py-3 fw-bold"><i class="fa-solid fa-dumbbell"></i> Book a Trainer</a>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'book'): ?>
                    <?php
                        // Fetch available trainers
                        $stmt = $pdo->query("SELECT id, email FROM users WHERE role = 'staff'");
                        $trainers = $stmt->fetchAll();
                    ?>
                    <h2 class="fw-bold mb-4">Book a Personal Training Session</h2>
                    <div class="row w-100">
                        <div class="col-md-6 col-lg-5">
                            <div class="glass-panel p-4">
                                <form method="POST" action="index.php?page=book">
                                    <input type="hidden" name="action" value="book_session">
                                    
                                    <div class="mb-4">
                                        <label class="form-label text-light">Select Trainer</label>
                                        <select name="trainer_id" class="form-select bg-dark text-light border-secondary" required>
                                            <option value="">-- Choose a Trainer --</option>
                                            <?php foreach($trainers as $t): ?>
                                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['email']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label text-light">Date</label>
                                        <input type="date" name="session_date" class="form-control bg-dark text-light border-secondary" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label text-light">Time</label>
                                        <select name="session_time" class="form-select bg-dark text-light border-secondary" required>
                                            <option value="08:00">08:00 AM</option>
                                            <option value="10:00">10:00 AM</option>
                                            <option value="12:00">12:00 PM</option>
                                            <option value="14:00">02:00 PM</option>
                                            <option value="16:00">04:00 PM</option>
                                            <option value="18:00">06:00 PM</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold py-2">Confirm Booking</button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'renew'): ?>
                    <h2 class="fw-bold mb-4">Choose Your Plan</h2>
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="glass-panel p-4 text-center h-100">
                                <h3 class="mb-3 text-light">Basic Plan</h3>
                                <h1 class="text-primary fw-bold mb-4">$49<span class="fs-5 text-muted fw-normal">/mo</span></h1>
                                <ul class="list-unstyled text-start text-light mb-4 text-center">
                                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Gym Access</li>
                                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Locker Room</li>
                                    <li class="mb-2 text-muted"><i class="fa-solid fa-xmark me-2"></i> Personal Training</li>
                                </ul>
                                <form method="POST">
                                    <input type="hidden" name="action" value="renew_sub">
                                    <input type="hidden" name="plan" value="Basic">
                                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold">Select Basic</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="glass-panel p-4 text-center h-100 metric-card-primary" style="transform: scale(1.05);">
                                <div class="badge bg-primary mb-2 position-absolute top-0 start-50 translate-middle">Most Popular</div>
                                <h3 class="mb-3 text-light mt-2">Premium Plan</h3>
                                <h1 class="text-primary fw-bold mb-4">$99<span class="fs-5 text-muted fw-normal">/mo</span></h1>
                                <ul class="list-unstyled text-start text-light mb-4 text-center">
                                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> 24/7 Gym Access</li>
                                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Locker Room & Spa</li>
                                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> 2 Training Sessions</li>
                                </ul>
                                <form method="POST">
                                    <input type="hidden" name="action" value="renew_sub">
                                    <input type="hidden" name="plan" value="Premium">
                                    <button type="submit" class="btn btn-success w-100 fw-bold" style="background:#10b981; border:none; box-shadow: 0 4px 15px rgba(16,185,129,0.4);">Select Premium</button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-danger glass-panel">Feature not found.</div>
                <?php endif; ?>
                
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

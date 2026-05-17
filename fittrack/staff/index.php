<?php
require_once '../includes/auth_middleware.php';
require_role('staff');

$page = isset($_GET['page']) ? $_GET['page'] : 'schedule';
$userId = $_SESSION['user_id'];
$msg = $_GET['msg'] ?? '';

// Process Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'complete_session') {
            $sessionId = (int)$_POST['session_id'];
            // Since we don't have a status column in trainers_schedules natively in the given schema, 
            // the MVP way to "complete" it is to delete the event or append "[Completed]" to notes.
            // Let's just delete it to clear schedule.
            $stmt = $pdo->prepare("DELETE FROM trainers_schedules WHERE id = ? AND trainer_id = ?");
            $stmt->execute([$sessionId, $userId]);
            header("Location: index.php?page=schedule&msg=completed");
            exit;
        }
        elseif ($_POST['action'] === 'checkin_member') {
            $memberId = (int)$_POST['member_id'];
            $stmt = $pdo->prepare("INSERT INTO attendance (user_id, check_in_time) VALUES (?, NOW())");
            $stmt->execute([$memberId]);
            header("Location: index.php?page=attendance&msg=checkedin");
            exit;
        }
        elseif ($_POST['action'] === 'checkout_member') {
            $attendanceId = (int)$_POST['attendance_id'];
            $stmt = $pdo->prepare("UPDATE attendance SET check_out_time = NOW() WHERE id = ?");
            $stmt->execute([$attendanceId]);
            header("Location: index.php?page=attendance&msg=checkedout");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Portal - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand bg-dark glass-nav">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-stopwatch-20"></i> FitTrack Staff</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><i class="fa-solid fa-user-ninja"></i> <?php echo htmlspecialchars($_SESSION['email'] ?? 'Trainer'); ?></span>
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
                        <a class="nav-link <?php echo $page === 'schedule' ? 'active' : ''; ?>" href="?page=schedule">
                            <i class="fa-solid fa-calendar-alt me-2"></i> My Schedule
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'attendance' ? 'active' : ''; ?>" href="?page=attendance">
                            <i class="fa-solid fa-clipboard-user me-2"></i> Attendance Log
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'profiles' ? 'active' : ''; ?>" href="?page=profiles">
                            <i class="fa-solid fa-users me-2"></i> Member Profiles
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                
                <?php if ($msg === 'completed'): ?>
                    <div class="alert alert-success glass-panel text-success border-success">Session marked as completed.</div>
                <?php elseif ($msg === 'checkedin'): ?>
                    <div class="alert alert-success glass-panel text-success border-success">Member successfully checked in.</div>
                <?php elseif ($msg === 'checkedout'): ?>
                    <div class="alert alert-info glass-panel text-info border-info">Member checked out.</div>
                <?php endif; ?>
                
                <?php if ($page === 'schedule'): ?>
                    <?php
                        $stmt = $pdo->prepare("
                            SELECT ts.*, mp.full_name as member_name 
                            FROM trainers_schedules ts 
                            LEFT JOIN member_profiles mp ON ts.member_id = mp.user_id 
                            WHERE ts.trainer_id = ? AND ts.session_time >= NOW()
                            ORDER BY ts.session_time ASC
                        ");
                        $stmt->execute([$userId]);
                        $sessions = $stmt->fetchAll();
                    ?>
                    <h2 class="fw-bold mb-4">My Upcoming Sessions</h2>
                    
                    <?php if (count($sessions) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($sessions as $session): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="glass-panel p-4 h-100 position-relative">
                                <h4 class="mb-1 text-info fw-bold">
                                    <i class="fa-solid fa-user-circle me-2"></i>
                                    <?php echo htmlspecialchars($session['member_name'] ?? 'General class'); ?>
                                </h4>
                                <div class="text-light mt-3">
                                    <p class="mb-2"><i class="fa-solid fa-clock text-muted"></i> <?php echo date('F j, Y, g:i a', strtotime($session['session_time'])); ?></p>
                                    <p class="mb-3"><i class="fa-solid fa-hourglass-half text-muted"></i> <?php echo $session['duration_minutes']; ?> mins</p>
                                    <?php if ($session['notes']): ?>
                                        <p class="small text-muted"><i class="fa-solid fa-note-sticky text-muted"></i> <?php echo htmlspecialchars($session['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="index.php?page=schedule" class="mt-4">
                                    <input type="hidden" name="action" value="complete_session">
                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold"><i class="fa-solid fa-check"></i> Mark Complete</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                        <div class="glass-panel p-5 text-center text-muted">
                            <i class="fa-solid fa-mug-hot fa-3x mb-3"></i>
                            <h4>No upcoming sessions</h4>
                            <p>You have a clear schedule! Enjoy your break.</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($page === 'attendance'): ?>
                    <h2 class="fw-bold mb-4">Front Desk Attendance</h2>
                    
                    <div class="row">
                        <!-- Check In Card -->
                        <div class="col-md-6 mb-4">
                            <div class="glass-panel p-4 h-100">
                                <h4><i class="fa-solid fa-door-open text-success"></i> Manual Check-In</h4>
                                <hr class="border-secondary">
                                <?php
                                    // Fetch list of members to check in
                                    $stmt = $pdo->query("SELECT u.id, m.full_name, u.email FROM users u LEFT JOIN member_profiles m ON u.id = m.user_id WHERE u.role = 'member'");
                                    $allMembers = $stmt->fetchAll();
                                ?>
                                <form method="POST" action="index.php?page=attendance">
                                    <input type="hidden" name="action" value="checkin_member">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Select Member</label>
                                        <select class="form-select bg-dark text-light border-secondary" name="member_id" required>
                                            <option value="">-- Choose Member --</option>
                                            <?php foreach ($allMembers as $mem): ?>
                                            <option value="<?php echo $mem['id']; ?>">
                                                <?php echo htmlspecialchars($mem['full_name'] ?? $mem['email']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 fw-bold"><i class="fa-solid fa-check-to-slot"></i> Record Check-In</button>
                                </form>
                            </div>
                        </div>

                        <!-- Currently Checked In (Check-Out List) -->
                        <div class="col-md-6 mb-4">
                            <div class="glass-panel p-4 h-100">
                                <h4><i class="fa-solid fa-person-running text-primary"></i> Currently In Gym</h4>
                                <hr class="border-secondary">
                                <?php
                                    $stmt = $pdo->query("
                                        SELECT a.id as attendance_id, m.full_name, a.check_in_time 
                                        FROM attendance a 
                                        LEFT JOIN member_profiles m ON a.user_id = m.user_id 
                                        WHERE a.check_out_time IS NULL
                                    ");
                                    $activeAttendees = $stmt->fetchAll();
                                ?>
                                <?php if (count($activeAttendees) > 0): ?>
                                    <ul class="list-group list-group-flush bg-transparent">
                                        <?php foreach ($activeAttendees as $att): ?>
                                        <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <strong><?php echo htmlspecialchars($att['full_name'] ?? 'Unknown Member'); ?></strong><br>
                                                <small class="text-muted">In since: <?php echo date('g:i a', strtotime($att['check_in_time'])); ?></small>
                                            </div>
                                            <form method="POST" action="index.php?page=attendance">
                                                <input type="hidden" name="action" value="checkout_member">
                                                <input type="hidden" name="attendance_id" value="<?php echo $att['attendance_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-info">Check Out</button>
                                            </form>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted text-center mt-4">No members currently checked in.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'profiles'): ?>
                    <?php
                        $stmt = $pdo->query("SELECT u.id, m.full_name, m.phone, m.join_date, m.digital_waiver_signed FROM users u LEFT JOIN member_profiles m ON u.id = m.user_id WHERE u.role = 'member'");
                        $memberProfiles = $stmt->fetchAll();
                    ?>
                    <h2 class="fw-bold mb-4">Member Profiles</h2>
                    <div class="glass-panel p-4">
                        <div class="table-responsive">
                            <table class="table table-hover text-light">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Waiver</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($memberProfiles as $mp): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($mp['full_name'] ?? 'Incomplete Profile'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($mp['phone'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>
                                            <?php if ($mp['digital_waiver_signed']): ?>
                                                <span class="text-success"><i class="fa-solid fa-check"></i> Signed</span>
                                            <?php else: ?>
                                                <span class="text-danger"><i class="fa-solid fa-xmark"></i> Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-glass text-info">View Notes</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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

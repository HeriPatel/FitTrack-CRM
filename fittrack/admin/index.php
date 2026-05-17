<?php
require_once '../includes/auth_middleware.php';
require_role('admin');

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Process Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'restock') {
        $itemId = (int)$_POST['item_id'];
        $stmt = $pdo->prepare("UPDATE equipment SET stock_count = stock_count + 10, last_checked_date = CURDATE() WHERE id = ?");
        $stmt->execute([$itemId]);
        header("Location: index.php?page=equipment&msg=restocked");
        exit;
    }
}

// Fetch dashboard KPIs
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'");
$activeMembers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_date = CURDATE()");
$dailyRevenue = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM equipment WHERE stock_count < low_stock_threshold");
$lowStockCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal - FitTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand bg-dark glass-nav">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-dumbbell"></i> FitTrack Admin</span>
            <div class="d-flex align-items-center">
                <span class="text-light me-3"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['email'] ?? 'Admin'); ?></span>
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
                            <i class="fa-solid fa-chart-pie me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'members' ? 'active' : ''; ?>" href="?page=members">
                            <i class="fa-solid fa-users me-2"></i> Members
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'trainers' ? 'active' : ''; ?>" href="?page=trainers">
                            <i class="fa-solid fa-user-ninja me-2"></i> Trainers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'equipment' ? 'active' : ''; ?>" href="?page=equipment">
                            <i class="fa-solid fa-box-open me-2"></i> Equipment
                            <?php if ($lowStockCount > 0): ?>
                                <span class="badge bg-danger rounded-pill float-end"><?php echo $lowStockCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === 'financials' ? 'active' : ''; ?>" href="?page=financials">
                            <i class="fa-solid fa-sack-dollar me-2"></i> Financials
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                
                <?php if ($page === 'dashboard'): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
                        <h2 class="fw-bold">Overview</h2>
                        <button class="btn btn-primary-custom btn-sm"><i class="fa-solid fa-download"></i> Export Report</button>
                    </div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="glass-panel metric-card metric-card-primary p-4 h-100">
                                <div class="text-muted text-uppercase fw-bold small">Total Active Members</div>
                                <div class="metric-value text-primary mt-2"><?php echo $activeMembers; ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="glass-panel metric-card metric-card-success p-4 h-100">
                                <div class="text-muted text-uppercase fw-bold small">Revenue Today</div>
                                <div class="metric-value text-success mt-2">$<?php echo number_format($dailyRevenue, 2); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="glass-panel metric-card <?php echo $lowStockCount > 0 ? 'metric-card-danger' : 'metric-card-warning'; ?> p-4 h-100">
                                <div class="text-muted text-uppercase fw-bold small">Equipment Alerts</div>
                                <div class="metric-value <?php echo $lowStockCount > 0 ? 'text-danger' : 'text-warning'; ?> mt-2"><?php echo $lowStockCount; ?></div>
                                <?php if ($lowStockCount > 0): ?>
                                    <div class="mt-2"><a href="?page=equipment" class="btn btn-sm btn-outline-danger">Review Items</a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'members'): ?>
                    <?php
                        $stmt = $pdo->query("SELECT u.id, u.email, u.created_at, m.full_name, m.phone, m.join_date FROM users u LEFT JOIN member_profiles m ON u.id = m.user_id WHERE u.role = 'member' ORDER BY u.created_at DESC");
                        $members = $stmt->fetchAll();
                    ?>
                    <h2 class="fw-bold mb-4">Member Directory</h2>
                    <div class="glass-panel p-4">
                        <div class="table-responsive">
                            <table class="table table-hover text-light">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Joined Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $mem): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($mem['full_name'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($mem['email']); ?></td>
                                        <td><?php echo htmlspecialchars($mem['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($mem['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-glass text-info" title="View"><i class="fa-solid fa-eye"></i></button>
                                            <button class="btn btn-sm btn-glass text-danger" title="Revoke"><i class="fa-solid fa-ban"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($page === 'trainers'): ?>
                    <?php
                        $stmt = $pdo->query("SELECT id, email, created_at FROM users WHERE role = 'staff' ORDER BY created_at DESC");
                        $trainers = $stmt->fetchAll();
                    ?>
                    <h2 class="fw-bold mb-4">Staff / Trainers</h2>
                    <div class="glass-panel p-4">
                        <table class="table table-hover text-light">
                            <thead><tr><th>ID</th><th>Email</th><th>Hired Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($trainers as $t): ?>
                                <tr>
                                    <td>#<?php echo $t['id']; ?></td>
                                    <td><?php echo htmlspecialchars($t['email']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($t['created_at'])); ?></td>
                                    <td><button class="btn btn-sm btn-glass text-info">View Schedule</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($page === 'equipment'): ?>
                    <?php
                        $stmt = $pdo->query("SELECT * FROM equipment ORDER BY stock_count ASC");
                        $equipment = $stmt->fetchAll();
                        $msg = $_GET['msg'] ?? '';
                    ?>
                    <h2 class="fw-bold mb-4">Equipment Inventory</h2>
                    <?php if ($msg === 'restocked'): ?>
                        <div class="alert alert-success bg-transparent border-success text-success glass-panel">Items have been successfully restocked (+10).</div>
                    <?php endif; ?>
                    <div class="glass-panel p-4">
                        <table class="table table-hover text-light">
                            <thead><tr><th>Name</th><th>Category</th><th>Stock</th><th>Threshold</th><th>Last Checked</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($equipment as $eq): 
                                    $isLow = $eq['stock_count'] < $eq['low_stock_threshold'];
                                ?>
                                <tr class="<?php echo $isLow ? 'bg-danger bg-opacity-25' : ''; ?>">
                                    <td><?php echo htmlspecialchars($eq['name']); ?></td>
                                    <td><?php echo htmlspecialchars($eq['category']); ?></td>
                                    <td><strong class="<?php echo $isLow ? 'text-danger' : ''; ?>"><?php echo $eq['stock_count']; ?></strong></td>
                                    <td><?php echo $eq['low_stock_threshold']; ?></td>
                                    <td><?php echo $eq['last_checked_date']; ?></td>
                                    <td>
                                        <form method="POST" action="index.php?page=equipment" class="d-inline">
                                            <input type="hidden" name="action" value="restock">
                                            <input type="hidden" name="item_id" value="<?php echo $eq['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $isLow ? 'btn-danger' : 'btn-outline-primary'; ?>"><i class="fa-solid fa-boxes-packing"></i> Restock +10</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($page === 'financials'): ?>
                    <?php
                        $stmt = $pdo->query("SELECT p.*, u.email FROM payments p JOIN users u ON p.user_id = u.id ORDER BY p.payment_date DESC LIMIT 50");
                        $payments = $stmt->fetchAll();
                    ?>
                    <h2 class="fw-bold mb-4">Financial Ledger</h2>
                    <div class="glass-panel p-4">
                        <table class="table table-hover text-light">
                            <thead><tr><th>Ref</th><th>Member Email</th><th>Date</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pay['invoice_ref'] ?? '#'.$pay['id']); ?></td>
                                    <td><?php echo htmlspecialchars($pay['email']); ?></td>
                                    <td><?php echo $pay['payment_date']; ?></td>
                                    <td><?php echo htmlspecialchars($pay['payment_method'] ?? 'N/A'); ?></td>
                                    <td class="text-success fw-bold">$<?php echo number_format($pay['amount'], 2); ?></td>
                                    <td><span class="badge <?php echo $pay['status'] === 'paid' ? 'bg-success' : 'bg-warning'; ?>"><?php echo ucfirst($pay['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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

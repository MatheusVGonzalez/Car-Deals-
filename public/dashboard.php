<?php
session_start();
include '../classes/Database.php';
include '../classes/Audit.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$cars_count = $conn->query("SELECT COUNT(*) as total FROM cars")->fetch_assoc()['total'];
$users_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$audit = new \CarDeals\Audit($conn);
$recent_activities = $audit->getRecentActivity(5);  

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CarDeals CMS</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #1a237e;
            color: white;
            padding: 20px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .recent-activity {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .nav-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>CarDeals CMS</h2>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="CRUDCars/list.php" class="nav-link">Manage Cars</a>
                <?php if($user['role'] === 'admin'): ?>
                <a href="CRUDUsers/list.php" class="nav-link">Manage Users</a>
                <?php endif; ?>
                <hr>
                <a href="logout.php" class="nav-link">Logout</a>
            </nav>
        </div>
        
        <div class="main-content">
            <header>
                <h1>Welcome, <?= htmlspecialchars($user['name']) ?></h1>
                <p>Role: <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Cars</h3>
                    <p class="stat-number"><?= $cars_count ?></p>
                </div>
                <?php if($user['role'] === 'admin'): ?>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?= $users_count ?></p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($user['role'] === 'admin'): ?>
                <div class="recent-activity">
                <h3>Recent Activity</h3>
                <table width="100%">
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Time</th>
                    </tr>
                    <?php while($activity = $recent_activities->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($activity['user_name'] ?? 'System') ?></td>
                        <td><?= htmlspecialchars($activity['action']) ?></td>
                        <td><?= htmlspecialchars($activity['details']) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($activity['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            
            <div class="quick-actions" style="margin-top: 20px;">
                <?php if($user['role'] === 'admin'): ?>
                    <h3>Quick Actions</h3>
                    <a href="CRUDCars/create.php" class="button">Add New Car</a>
                    <a href="CRUDUsers/create.php" class="button">Add New User</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
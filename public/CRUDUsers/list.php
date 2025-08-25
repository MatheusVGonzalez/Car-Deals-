<?php
session_start();
include '../../classes/Database.php';
include '../../classes/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;
$userManager = new \CarDeals\User($conn);

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Only admin can view user list
if ($user['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

// Get all users
$users = $userManager->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users List - CarDeals CMS</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>CarDeals CMS</h2>
            <nav>
                <a href="../dashboard.php" class="nav-link">Dashboard</a>
                <a href="../CRUDCars/list.php" class="nav-link">Manage Cars</a>
                <a href="list.php" class="nav-link">Manage Users</a>
                <hr>
                <a href="../logout.php" class="nav-link">Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header-actions">
                <h2>Users List</h2>
                <a href="create.php" class="button button-success">Add New User</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($userData = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($userData['name']) ?></td>
                            <td><?= htmlspecialchars($userData['email']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($userData['role'])) ?></td>
                            <td><?= date('M d, Y', strtotime($userData['created_at'])) ?></td>
                            <td>
                                <a href="edit.php?id=<?= $userData['id'] ?>" class="button button-small">Edit</a>
                                <?php if($userData['id'] !== $user_id): ?>
                                    <a href="delete.php?id=<?= $userData['id'] ?>" 
                                       class="button button-small button-danger" 
                                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
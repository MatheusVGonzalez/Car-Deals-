<?php
session_start();
include '../../classes/Database.php';
include '../../classes/User.php';
include '../../classes/Audit.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;
$state = $conn->prepare("SELECT role FROM users WHERE id = ?");
$state->bind_param("i", $_SESSION['user_id']);
$state->execute();
$currentUser = $state->get_result()->fetch_assoc();

if ($currentUser['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

$user = new \CarDeals\User($conn);
$message = '';
$userData = null;

if(isset($_GET['id'])) {
    $userData = $user->getById($_GET['id']);
    if(!$userData) {
        header('Location: list.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateData = [
            'name' => htmlspecialchars($_POST['name']),
            'email' => htmlspecialchars($_POST['email']),
            'role' => htmlspecialchars($_POST['role']),
            'user_id' => $_SESSION['user_id']
        ];

        if (!empty($_POST['password'])) {
            $updateData['password'] = $_POST['password'];
        }

        if ($user->update($_GET['id'], $updateData)) {
            header("Location: list.php");
            exit;
        } else {
            throw new Exception("Error updating user record.");
        }

    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - CarDeals CMS</title>
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
            <h2>Edit User</h2>
            
            <?php if ($message): ?>
                <p class="alert alert-danger"><?php echo $message; ?></p>
            <?php endif; ?>

            <?php if ($userData): ?>
            <form method="post" class="form">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($userData['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($userData['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password: (Leave empty to keep current)</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select name="role" id="role" required>
                        <option value="viewer" <?= $userData['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                        <option value="editor" <?= $userData['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                        <option value="admin" <?= $userData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-success">Update User</button>
                    <a href="list.php" class="button">Cancel</a>
                </div>
            </form>
            <?php else: ?>
                <p>User not found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
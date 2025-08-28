<?php
session_start();
include '../../classes/Database.php';
include '../../classes/User.php';
include '../../classes/Audit.php';
require '../../Config.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;
$user = new \CarDeals\User($conn);

$state = $conn->prepare("SELECT role FROM users WHERE id = ?");
$state->bind_param("i", $_SESSION['user_id']);
$state->execute();
$currentUser = $state->get_result()->fetch_assoc();

if ($currentUser['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];
        $role = htmlspecialchars($_POST['role']); 

        $peppered_pass = hash_hmac("sha256", $password, PEPPER);
        $hashedPassword = password_hash($peppered_pass, PASSWORD_BCRYPT, ['cost' => 11]);

        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $name, $email, $hashedPassword, $role);
            $insert->execute();

            $_SESSION['success'] = "User registered successfully!";
            header("Location: list.php"); 
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User - CarDeals CMS</title>
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
            <h2>Create New User</h2>
            
            <?php if ($error): ?>
                <p class="alert alert-danger"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" class="form">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select name="role" id="role" required>
                        <option value="viewer">Viewer</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-success">Create User</button>
                    <a href="list.php" class="button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
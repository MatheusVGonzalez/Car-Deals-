<?php
session_start();
include '../../classes/Database.php';
include '../../classes/Car.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;
$car = new \CarDeals\Car($conn);

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$cars = $car->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cars List - CarDeals CMS</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>CarDeals CMS</h2>
            <nav>
                <a href="../dashboard.php" class="nav-link">Dashboard</a>
                <a href="list.php" class="nav-link">Manage Cars</a>
                <?php if($user['role'] === 'admin'): ?>
                <a href="../CRUDUsers/list.php" class="nav-link">Manage Users</a>
                <?php endif; ?>
                <hr>
                <a href="../logout.php" class="nav-link">Logout</a>
            </nav>
        </div>


        <div class="main-content">
            <?php if($user['role'] === 'admin'): ?>
                <div class="header-actions">
                    <h2>Cars List</h2>
                    <a href="create.php" class="button button-success">Add New Car</a>
                </div>
            <?php endif; ?>

            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Price</th>
                        <th>Status</th>
                        <?php if($user['role'] === 'viewer'):?><th>Description</th> <?php endif;?>
                        <th>Actions</th>
                            
                    </tr>
                </thead>
                <tbody>
                    <?php while ($car = $cars->fetch_assoc()): ?>
                        <tr>
                            <td><img src="../uploads/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" style="width: 100px; object-fit: cover;"></td>
                            <td><?= htmlspecialchars($car['brand']) ?></td>
                            <td><?= htmlspecialchars($car['model']) ?></td>
                            <td><?= htmlspecialchars($car['year']) ?></td>
                            <td>$<?= number_format($car['price'], 2) ?></td>
                            <td><?= htmlspecialchars($car['status']) ?></td>
                            <?php if($user['role'] === 'viewer'): ?>
                                <td><?= htmlspecialchars($car['description']) ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if($user['role'] === 'admin'): ?>
                                    <a href="edit.php?id=<?= $car['id'] ?>" class="button button-small">Edit</a>
                                    <a href="delete.php?id=<?= $car['id'] ?>" class="button button-small button-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php endif; ?>
                                <a href="buy.php?id=<?= $car['id'] ?>" class="button button-small">Buy</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
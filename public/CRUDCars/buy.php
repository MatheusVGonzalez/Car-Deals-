<?php
session_start();
include '../../classes/Database.php';
include '../../classes/Car.php';
include '../../classes/User.php';
include '../../classes/Audit.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'viewer') {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;
$car = new \CarDeals\Car($conn);
$user = new \CarDeals\User($conn);
$message = '';
$carD = null;
$userData = $user->getById($_SESSION['user_id']);

if (isset($_GET['id'])) {
    $carD = $car->getById($_GET['id']);
    if (!$carD) {
        $message = "Car not found.";
    }
} else {
    $message = "No car selected.";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($carD)) {
    try {
        $updateData = ['brand' => $carD['brand'], 'model' => $carD['model'], 'year' => $carD['year'], 'price' => $carD['price'], 'mileage' => $carD['mileage'], 'description' => $carD['description'], 'image' => $carD['image'], 'status' => 'Sold', 'user_id' => $_SESSION['user_id']];
        if ($car->update($carD['id'], $updateData)) {
            \CarDeals\Audit::log($conn, $_SESSION['user_id'], 'buy', 'cars', $carD['id'],
                "User {$userData['name']} ({$userData['email']}) bought {$carD['brand']} {$carD['model']} - {$carD['price']}"
            );
            $message = "Bought {$carD['brand']} {$carD['model']} for $ {$carD['price']}.";
            header("Location: list.php?success=1");
            exit;
        } else {
            $message = "error confirming purchase.";
        }
    } catch (Exception $e) {
        $message = "error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy Car - CarDeals CMS</title>
    <link rel="stylesheet" href="../css/index.css">
    <style>
        .buy-container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .buy-container h2 {
            margin-bottom: 20px;
        }
        .buy-details {
            margin-bottom: 20px;
        }
        .buy-details img {
            max-width: 100%;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="buy-container">
        <h2>Confirm Purchase</h2>
        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($carD && !$message): ?>
            <div class="buy-details">
                <?php if($carD['image']): ?>
                    <img src="../uploads/<?= htmlspecialchars($carD['image']) ?>" alt="Car Image">
                <?php endif; ?>
                <p><strong>Car:</strong> <?= htmlspecialchars($carD['brand']) ?> <?= htmlspecialchars($carData['model']) ?></p>
                <p><strong>Year:</strong> <?= htmlspecialchars($carData['year']) ?></p>
                <p><strong>Price:</strong> $<?= number_format($carData['price'], 2) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($carData['status']) ?></p>
            </div>
            <form method="post">
                <div class="form-group">
                    <label>Your Name:</label>
                    <input type="text" value="<?= htmlspecialchars($userData['name']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Your Email:</label>
                    <input type="email" value="<?= htmlspecialchars($userData['email']) ?>" readonly>
                </div>
                <div class="form-actions">
                    <button type="submit" class="button button-success">Confirm Purchase</button>
                    <a href="list.php" class="button">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
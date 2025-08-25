<?php
session_start();
include '../../classes/Database.php';
include '../../classes/Car.php';
include '../../classes/Audit.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;
$car = new \CarDeals\Car($conn);
$message = '';

// Get car data
if(isset($_GET['id'])) {
    $carData = $car->getById($_GET['id']);
    if(!$carData) {
        header('Location: list.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $imageFileName = $carData['image']; // Keep existing image by default

        // Handle new image upload if provided
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $targetDir = "../uploads/";
            $imageFileName = time() . '_' . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $imageFileName;
            
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                throw new Exception("Error uploading file.");
            }
        }

        $updateData = [
            'brand' => htmlspecialchars($_POST['brand']),
            'model' => htmlspecialchars($_POST['model']),
            'year' => (int)$_POST['year'],
            'price' => (float)$_POST['price'],
            'mileage' => (int)$_POST['mileage'],
            'description' => htmlspecialchars($_POST['description']),
            'image' => $imageFileName,
            'status' => $_POST['status'],
            'user_id' => $_SESSION['user_id']
        ];

        if ($car->update($_GET['id'], $updateData)) {
            header("Location: list.php");
            exit;
        } else {
            throw new Exception("Error updating car record.");
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
    <title>Edit Car - CarDeals CMS</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>CarDeals CMS</h2>
            <nav>
                <a href="../dashboard.php" class="nav-link">Dashboard</a>
                <a href="list.php" class="nav-link">Manage Cars</a>
                <hr>
                <a href="../logout.php" class="nav-link">Logout</a>
            </nav>
        </div>

        <div class="main-content">
            <h2>Edit Car</h2>
            
            <?php if ($message): ?>
                <p class="alert alert-danger"><?php echo $message; ?></p>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="form">
                <div class="form-group">
                    <label for="brand">Brand:</label>
                    <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($carData['brand']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" value="<?= htmlspecialchars($carData['model']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="year">Year:</label>
                    <input type="number" id="year" name="year" value="<?= $carData['year'] ?>" min="1900" max="2025" required>
                </div>

                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" value="<?= $carData['price'] ?>" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="mileage">Mileage:</label>
                    <input type="number" id="mileage" name="mileage" value="<?= $carData['mileage'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($carData['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Image: (Leave empty to keep current image)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if($carData['image']): ?>
                        <p>Current image: <?= htmlspecialchars($carData['image']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="Available" <?= $carData['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Sold" <?= $carData['status'] == 'Sold' ? 'selected' : '' ?>>Sold</option>
                        <option value="Reserved" <?= $carData['status'] == 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-success">Update Car</button>
                    <a href="list.php" class="button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
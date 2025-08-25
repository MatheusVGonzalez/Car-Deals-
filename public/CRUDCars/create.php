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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageFileName = null;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $imageFileName = time() . '_' . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $imageFileName;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            } else {
                throw new Exception("Error uploading file.");
            }
        }

        $carData = [
            'brand' => htmlspecialchars($_POST['brand']),
            'model' => htmlspecialchars($_POST['model']),
            'year' => (int)$_POST['year'],
            'price' => (float)$_POST['price'],
            'mileage' => (int)$_POST['mileage'],
            'description' => htmlspecialchars($_POST['description']),
            'image' => $imageFileName,
            'status' => $_POST['status'],
            'created_by' => $_SESSION['user_id']
        ];

        if ($car->create($carData)) {
            header("Location: list.php");
            exit;
        } else {
            throw new Exception("Error creating car record.");
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
    <title>Add New Car - CarDeals CMS</title>
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
            <h2>Add New Car</h2>
            
            <?php if ($message): ?>
                <p class="alert alert-danger"><?php echo $message; ?></p>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="form">
                <div class="form-group">
                    <label for="brand">Brand:</label>
                    <input type="text" id="brand" name="brand" required>
                </div>

                <div class="form-group">
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" required>
                </div>

                <div class="form-group">
                    <label for="year">Year:</label>
                    <input type="number" id="year" name="year" min="1900" max="2025" required>
                </div>

                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="mileage">Mileage:</label>
                    <input type="number" id="mileage" name="mileage" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="Available">Available</option>
                        <option value="Sold">Sold</option>
                        <option value="Reserved">Reserved</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-success">Create Car</button>
                    <a href="list.php" class="button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>


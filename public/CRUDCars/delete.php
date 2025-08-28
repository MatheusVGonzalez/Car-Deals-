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

if(isset($_GET['id'])) {
    try {
        $carData = $car->getById($_GET['id']);
        if($carData) {
            if($car->delete($_GET['id'], $_SESSION['user_id'])) {
                if($carData['image']) {
                    $imagePath = "../uploads/" . $carData['image'];
                    if(file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                header("Location: list.php");
                exit;
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "error deleting car: " . $e->getMessage();
    }
}

header("Location: list.php");
exit;
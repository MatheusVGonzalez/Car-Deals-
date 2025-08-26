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

$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

if ($currentUser['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

$user = new \CarDeals\User($conn);

if(isset($_GET['id'])) {
    try {
        if($_GET['id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account";
            header("Location: list.php");
            exit;
        }

        $userData = $user->getById($_GET['id']);
        
        if($userData) {
            if($user->delete($_GET['id'], $_SESSION['user_id'])) {
                \CarDeals\Audit::log(
                    $conn,
                    $_SESSION['user_id'],
                    'delete',
                    'users',
                    $_GET['id'],
                    "Deleted user {$userData['name']}"
                );
                $_SESSION['success'] = "User deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete user";
            }
        } else {
            $_SESSION['error'] = "User not found";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
    }
}

header("Location: list.php");
exit;
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    include '../classes/Database.php';
    include '../classes/Audit.php';
    $db = new Database();
    $conn = $db->conn;
    \CarDeals\Audit::log(
        $conn, 
        $_SESSION['user_id'],
        'logout',
        'users',
        $_SESSION['user_id'],
        "User logged out"
    );
}
$_SESSION = array();
session_destroy();
header("Location: index.php");
exit();
?>
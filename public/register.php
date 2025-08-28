<?php
session_start();
include '../classes/Database.php';
require '../Config.php';
$db = new Database();
$conn = $db->conn;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $role = htmlspecialchars('viewer');

    $peppered_pass = hash_hmac("sha256", $password, PEPPER);
    $hashedPassword = password_hash($peppered_pass, PASSWORD_BCRYPT, ['cost' => 11]);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $error = "Email already exists";
    }else{
        $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $name, $email, $hashedPassword, $role);
        $insert->execute();

        $_SESSION['success'] = "User registered successfully!";
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/register.css">
</head>
<body>
<div class="register-box">
    <h2>Register</h2>
    <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
    <div class="link">
        <p>Already had a account?<a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>
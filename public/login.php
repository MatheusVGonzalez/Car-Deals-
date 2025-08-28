<?php
session_start();
include '../classes/Database.php';
require '../Config.php';

$db = new Database();
$conn = $db->conn;
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        $peppered_pass = hash_hmac("sha256", $password, PEPPER);
        if(password_verify($peppered_pass, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit;
        }else{
            $error = "Wrong password";
        }
    }else{
        $error = "User not found";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/login.css">
</head>
<body>
<div class="login-box">
    <h2>Login</h2>
    <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Sign in</button>
    </form>
    <div class="link">
        <p>You don't have an account? Click here
            <br>
            <a href="register.php">Sign Up</a></p>
    </div>
</div>
</body>
</html>
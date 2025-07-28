<?php
session_start();
require 'functions.php';


$admin_username = 'your_admin_username';
$admin_password = 'your_admin_pasword';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'ADMIN';
        header("Location: add_users.php");
        exit();
    } else {
        $msg = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Admin Login</h2>
    <?php if (isset($msg)) echo "<p class='error'>$msg</p>"; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>

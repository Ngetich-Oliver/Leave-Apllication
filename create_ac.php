<?php
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $msg = "Passwords do not match.";
    } else {
        // You may want to check if the username exists in the users table
        // and only allow signup if the admin has pre-created the user
        $user = getUserByUsername($username);
        if (!$user) {
            $msg = "Username not found. Please contact admin.";
        } else {
            // Update the password for the user
            global $conn;
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
            $stmt->bind_param("ss", $hashedPassword, $username);
            if ($stmt->execute()) {
                header("Location: login.php?signup=success");
                exit();
            } else {
                $msg = "Error setting password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="create_header">
     <img src="download.png" alt="" >
            <h2>REPUBLIC OF KENYA</h2>
            <h3>MINISTRY OF INFORMATION, COMMUNICATIONS AND THE DIGITAL ECONOMY</h3>
            <h3>STATE DEPARTMENT FOR ICT AND DIGITAL ECONOMY</h3>
            <h2>Account Registration</h2>
           <h3>To sign up kindly enter your details</h3>
        </div>
   
<div class="form-container">
    
    <?php if (isset($msg)) echo "<p>$msg</p>"; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
        <label>Role</label>
        <select name="role" required>
            <option value="STAFF">Staff</option>
            <option value="HOD">HOD</option>
            <option value="Principal Secretary">Principal Secretary</option>
        </select>
        <button type="submit">Sign Up</button>
        <p>Already have an account ? Please <a href="login.php">Login</a></p>
    </form>
</div>
</body>
</html>
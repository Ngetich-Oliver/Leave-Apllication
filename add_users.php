<?php
require 'functions.php';
session_start();

// Optional: Only allow admin access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $personal_number = $_POST['personal_number'];
    $role = $_POST['role'];
    $designation = $_POST['designation'];

    if (CreateUser($username, $password, $email, $personal_number, $role, $designation)) {
        $msg = "User created successfully!";
    } else {
        $msg = "Error creating user.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Create User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
   <div class="create_header">
      <h3>MINISTRY OF INFORMATION, COMMUNICATIONS AND THE DIGITAL ECONOMY</h3>
     <h3>STATE DEPARTMENT FOR ICT AND DIGITAL ECONOMY</h3>
     </div>
     <div class="dashboard-header">
  <h1> Admin Dashboard</h1>
  <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
  </div>
  <div class="layout">
  <nav class="sidebar">
    <ul>
      <li><a href="add_users.php">Add User</a></li>
      <li><a href="list.php">Current Users</a></li>
      <li><a href="edit.php">Edit User</a></li>
      <li><a href="admin_out.php">Logout</a></li>
  </nav>
<div class="form-container">
    <h2>Create New User</h2>
    <?php if (isset($msg)) echo "<p>$msg</p>"; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Personal Number</label>
        <input type="text" name="personal_number" required>
        <label>Role</label>
        <select name="role" required>
            <option value="STAFF">Staff</option>
            <option value="HOD">HOD</option>
            <option value="Principal Secretary">Principal Secretary</option>
        </select>
        <label>Designation</label>
        <input type="text" name="designation" required>
        <button type="submit">Create User</button>
        <?php if (isset($msg) && $msg === "User created successfully!"): ?>
            <div style="margin-top:10px;">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" fill="#4CAF50"/>
            <path d="M7 13l3 3 7-7" stroke="#fff" stroke-width="2" fill="none"/>
          </svg>
          <span style="color:#4CAF50; font-weight:bold;">User successfully created!</span>
            </div>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
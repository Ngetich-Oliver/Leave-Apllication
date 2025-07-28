<?php
require_once 'functions.php';
session_start();

if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: list.php?msg=deleted");
    exit();
}

// Fetch all users
global $conn;
$result = $conn->query("SELECT id, username, email, personal_number, role, designation FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .user-table { width: 97%; border-collapse: collapse; margin-top: 30px; margin-left: 20px;}
        .user-table th, .user-table td { border: 1px solid #ccc; padding: 8px; text-align: left;}
        .user-table th { background: #1976d2; color: #fff;}
        .action-btn { margin-right: 8px; text-decoration: none; color: #1976d2; font-weight: bold;}
        .delete-btn { color: #d32f2f; }
        .top-links { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="dashboard-header">
    <h1 > Admin Dashboard</h1>
  <p>Hi <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
  </div>
  <div class="layout">
  <nav class="sidebar">
    <ul>
      <li><a href="add_users.php">Add User</a></li>
      <li><a href="list.php">Current Users</a></li> 
      <li><a href="edit.php">Edit Users</a></li>
      <li><a href="admin_out.php">Logout</a></li>
  </nav>
<div class="formcontainer">
   
    <h2 style="text-align: center;">All Users</h2>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <p style="color: #4CAF50;">User deleted successfully.</p>
    <?php endif; ?>
    <table class="user-table">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Personal Number</th>
            <th>Role</th>
            <th>Designation</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['personal_number']); ?></td>
            <td><?php echo htmlspecialchars($row['role']); ?></td>
            <td><?php echo htmlspecialchars($row['designation']); ?></td>
            <td>
                <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn">Edit</a>
                <a href="list.php?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>




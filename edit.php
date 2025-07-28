<?php
require_once 'functions.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$user_id = intval($_GET['id']);
global $conn;

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, personal_number, role, designation FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p>User not found.</p>";
    exit();
}
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $personal_number = $_POST['personal_number'];
    $role = $_POST['role'];
    $designation = $_POST['designation'];

    $stmt = $conn->prepare("UPDATE users SET email=?, personal_number=?, role=?, designation=? WHERE id=?");
    $stmt->bind_param("ssssi", $email, $personal_number, $role, $designation, $user_id);
    if ($stmt->execute()) {
        header("Location: list.php?msg=updated");
        exit();
    } else {
        $msg = "Update failed.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
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
<div class="form-container">
    <h2>Edit User</h2>
    <?php if (isset($msg)) echo "<p class='error'>$msg</p>"; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <label>Personal Number</label>
        <input type="text" name="personal_number" value="<?php echo htmlspecialchars($user['personal_number']); ?>" required>
        <label>Role</label>
        <select name="role" required>
            <option value="STAFF" <?php if ($user['role'] == 'STAFF') echo 'selected'; ?>>Staff</option>
            <option value="HOD" <?php if ($user['role'] == 'HOD') echo 'selected'; ?>>HOD</option>
            <option value="Principal Secretary" <?php if ($user['role'] == 'Principal Secretary') echo 'selected'; ?>>Principal Secretary</option>
            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
        </select>
        <label>Designation</label>
        <input type="text" name="designation" value="<?php echo htmlspecialchars($user['designation']); ?>" required>
        <button type="submit">Update User</button>
    </form>
    <a href="list.php" style="color: blue; text-decoration: none; margin-top: 20px; display: inline-block;">Back to User List</a>
</div>
</body>
</html>
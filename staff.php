<?php
require 'functions.php';
session_start();

if(!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="create_header">
      <h3>MINISTRY OF INFORMATION, COMMUNICATIONS AND THE DIGITAL ECONOMY</h3>
     <h3>STATE DEPARTMENT FOR ICT AND DIGITAL ECONOMY</h3>
     </div>
    <div class="dashboard-header">
  <h1> Staff Dashboard</h1>
  <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
  </div>
  <div class="layout">
  <nav class="sidebar">
    <ul>
      <li><a href="staff.php">Home</a></li>
      <li> <a href="leave.php">Leave Application</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
  
  <main class="main-content">
    <div class="table-header">
    <h2>Types of Leave Available</h2>
  </div>
  <table>
    <tr>
      <th>Leave</th>
      <th>Duration</th>
    </tr>
    <tbody>
      <tr>
        <td>Maternity Leave</td>
        <td>90 Days</td>
      </tr>
      <tr>
        <td>Annual Leave</td>
        <td>30 Days</td>    
      </tr> 
      <tr>
        <td>Sick Leave</td>
        <td>30 days </td>
      </tr>
      <tr>
        <td>Paternity Leave</td>
        <td>14 Days</td>
      </tr>
    </tbody>
  </table>
  </main>
 </div>
</body>
</html>
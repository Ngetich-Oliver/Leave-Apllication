
<?php
//   db.php
$server="yourhost";
$username=""; 
$password="your pasword";
$dbname="leave_db";

$conn = mysqli_connect($server, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

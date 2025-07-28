
<?php
//   db.php
$server="localhost";
$username="root"; 
$password="";
$dbname="leave_db";

$conn = mysqli_connect($server, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
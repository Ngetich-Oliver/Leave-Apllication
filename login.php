<?php
session_start();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'functions.php';

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    $user=userLogin($username, $password); 
    if($user){
      $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] === 'Principal Secretary') {
            header("Location: principal.php");
            exit();
        } elseif($user['role'] === 'HOD') {
            header("Location: hod.php");
            exit();
        }
        elseif($user['role'] === 'STAFF') {
            header("Location: staff.php");
            exit();
        }
        else {
            echo "something went wrong,please try again.";
        }
}
else {
        echo "Invalid username or password.";
    }
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
    <img src="download.png" alt="">
     <h2>Hi,Welcome back</h2>
      <h3>Please Fill in your details to login</h3>
     </div>
  <div class="form-container">
  <form action="" method="post">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required><br><br>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required><br><br>
    <button type="submit">Login</button>
    <P>Dont have an account yet ? Please <a href="create_ac.php">Create it here</a></P>
  </form>
  </div>
</body>
</html>
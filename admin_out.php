<?php
session_start();  
session_destroy();
header("location: admin.php?logout=1");

?>
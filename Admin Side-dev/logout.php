<?php
session_start();
session_destroy(); // Destroy all sessions
header("Location: ../Login/Employee&Admin.php"); // Redirect to your login page
exit();
?>

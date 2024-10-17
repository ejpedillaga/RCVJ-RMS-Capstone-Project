<?php
session_start();

// Destroy the session to log out the user
session_unset();
session_destroy();

// Redirect to the login page (or home page)
header("Location: ../User Side/Home.php");
exit;
?>
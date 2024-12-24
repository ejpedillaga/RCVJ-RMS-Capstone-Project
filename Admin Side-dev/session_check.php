<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: /Login/Employee&Admin.php");
    exit();
}
?>

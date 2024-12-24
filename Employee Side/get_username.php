<?php
session_start(); // Start session
if (isset($_SESSION['username'])) {
    echo $_SESSION['username']; // Output the username if set
} else {
    echo 'Admin'; // Fallback if session is not set
}
?>

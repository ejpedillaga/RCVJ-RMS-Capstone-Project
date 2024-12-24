<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, return a redirect status in the response
    echo json_encode(['status' => 'redirect', 'location' => '/Login/Employee&Admin.php']);
    exit();
}

// If logged in, return the username
echo json_encode(['status' => 'success', 'username' => htmlspecialchars($_SESSION['username'])]);
?>

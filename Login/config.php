<?php
$host = "localhost";
$user = "root";
$password = "12345";
$db = "users_database";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
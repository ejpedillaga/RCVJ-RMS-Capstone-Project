<?php
$servername = "localhost";
$username = "root"; // default username for XAMPP
$password = "12345"; // default password for XAMPP
$dbname = "admin_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Count "Open" status
$sql_open = "SELECT COUNT(*) as open_count FROM job_table WHERE job_status = 'Open'";
$result_open = $conn->query($sql_open);
$row_open = $result_open->fetch_assoc();
$open_count = $row_open['open_count'];

// Count "Closed" status
$sql_closed = "SELECT COUNT(*) as closed_count FROM job_table WHERE job_status = 'Closed'";
$result_closed = $conn->query($sql_closed);
$row_closed = $result_closed->fetch_assoc();
$closed_count = $row_closed['closed_count'];

$conn->close();

// Send the counts as JSON to the frontend
echo json_encode(array("open_count" => $open_count, "closed_count" => $closed_count));

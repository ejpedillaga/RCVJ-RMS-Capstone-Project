<?php
// Database connection (update with your actual connection details)
// Database connection details
include 'connection.php';

// Create a connection
$conn = connection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the count of active and inactive employees
$sql = "SELECT status, COUNT(*) as count FROM employee_table GROUP BY status";
$result = $conn->query($sql);

$counts = [
    'active' => 0,
    'inactive' => 0,
];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Active') {
            $counts['active'] = $row['count'];
        } elseif ($row['status'] === 'Inactive') {
            $counts['inactive'] = $row['count'];
        }
    }
}

$conn->close();

// Return counts as JSON
header('Content-Type: application/json');
echo json_encode($counts);
?>

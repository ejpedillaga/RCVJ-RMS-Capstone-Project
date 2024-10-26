<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection file
include 'connection.php';

// Create a connection
$conn = connection();

// Check if the connection was successful
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Set content type to JSON before any output
header('Content-Type: application/json');

// Define the SQL query
$sql = "SELECT id, company_name FROM partner_table";

// Execute the SQL query
$result = $conn->query($sql);

// Check for SQL execution errors
if ($result === false) {
    die(json_encode(['error' => 'SQL Error: ' . $conn->error]));
}

$options = [];

// Fetch results and populate the options array
while ($row = $result->fetch_assoc()) {
    $options[] = $row;
}

// Log the number of records fetched
file_put_contents('debug.log', 'Number of records fetched: ' . count($options) . PHP_EOL);

// Return the options array as JSON
if (empty($options)) {
    echo json_encode(['message' => 'No records found']);
} else {
    echo json_encode($options);
}

// Close the database connection
$conn->close();
?>

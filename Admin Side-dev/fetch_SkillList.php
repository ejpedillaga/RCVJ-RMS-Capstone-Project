<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connection.php';

$conn = connection();

// Check if the connection was successful
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Set content type to JSON before any output
header('Content-Type: application/json');

// Define the SQL query to fetch skill names
$sql = "SELECT skill_name FROM skill_table";

// Execute the SQL query
$result = $conn->query($sql);

// Check for SQL execution errors
if ($result === false) {
    die(json_encode(['error' => 'SQL Error: ' . $conn->error]));
}

$skills = [];

// Fetch results and populate the skills array
while ($row = $result->fetch_assoc()) {
    $skills[] = $row['skill_name']; // Fetch the skill_name column
}

// Log the number of records fetched (for debugging purposes)
file_put_contents('debug.log', 'Number of records fetched: ' . count($skills) . PHP_EOL);

// Return the skills array as JSON
if (empty($skills)) {
    echo json_encode(['message' => 'No results found']);
} else {
    echo json_encode($skills);
}

// Close the database connection
$conn->close();
?>

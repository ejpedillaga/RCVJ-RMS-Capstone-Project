<?php
// Database connection details
include 'connection.php';

// Create a connection
$conn = connection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update SQL query to select the userid along with other fields
$sql = "SELECT userid, full_name, job_title, company_name, date_applied, status FROM candidate_list";
$result = $conn->query($sql);

$candidates = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Convert date format to m/d/y
        $date = new DateTime($row['date_applied']);
        $row['date_applied'] = $date->format('m/d/Y');
        $candidates[] = $row;
    }
} else {
    echo "0 results";
}
$conn->close();

echo json_encode($candidates);
?>
<?php
// Database connection details
include 'connection.php';

// Create a connection
$conn = connection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to join candidate_list and rejected_table to get all necessary data
$sql = "
    SELECT 
        DISTINCT c.full_name, 
        r.remarks, 
        r.date_rejected, 
        r.company_name, 
        r.job_title,
        r.userid,
        r.job_id
    FROM rejected_table r
    JOIN candidate_list c ON r.userid = c.userid";

$result = $conn->query($sql);

$rejects = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $rejects[] = $row;
    }
} else {
    echo json_encode([]); // Return an empty array if no results
}

$conn->close();

echo json_encode($rejects);
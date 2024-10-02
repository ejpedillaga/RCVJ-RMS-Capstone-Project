<?php
//This php retrieves the list of job titles. Used for populating the dropdowns for jobtitles

include_once("connection.php");

$conn = connection();

// Query to get all job titles
$query = "SELECT id, job_title FROM job_title_table";
$result = $conn->query($query);

$jobTitles = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobTitles[] = $row;
    }
}

// Return the job titles as JSON
echo json_encode($jobTitles);

$conn->close();
?>
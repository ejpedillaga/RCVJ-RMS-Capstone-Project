<?php
include 'connection.php';
$conn = connection();

// Check if a company name is provided
if (isset($_GET['company'])) {
    $company = $conn->real_escape_string($_GET['company']);
    // Fetch job titles for the selected company
    $query = "SELECT DISTINCT job_title FROM rejected_table WHERE company_name = '$company'";
    $result = $conn->query($query);

    $jobTitles = [];

    while ($row = $result->fetch_assoc()) {
        $jobTitles[] = $row['job_title'];
    }

    echo json_encode(['jobTitles' => array_values(array_unique($jobTitles))]);
} else {
    // Fetch unique company names
    $query = "SELECT DISTINCT company_name FROM rejected_table";
    $result = $conn->query($query);

    $companies = [];

    while ($row = $result->fetch_assoc()) {
        $companies[] = $row['company_name'];
    }

    echo json_encode(['companies' => array_values(array_unique($companies))]);
}
?>
<?php
include_once("connection.php");

$conn = connection();

$status = isset($_GET['status']) ? $_GET['status'] : '';

// Updated SQL query to join job_table with partner_table to fetch the logo
$sql = "SELECT j.company_name, p.logo AS company_logo, j.id, j.job_title, j.job_location, j.date_posted, j.job_candidates, j.job_status 
        FROM job_table j 
        JOIN partner_table p ON j.company_name = p.company_name";

$params = [];

if ($status) {
    $sql .= " WHERE j.job_status = ?";
    $params[] = $status;
}

$stmt = $conn->prepare($sql);

if ($status) {
    $stmt->bind_param("s", $status); // 's' denotes the type: string
}

$stmt->execute();
$result = $stmt->get_result();

$jobs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Convert the BLOB data to base64
        $row['company_logo'] = base64_encode($row['company_logo']);
        
        // Format the date_posted to MM/DD/YYYY
        $date = new DateTime($row['date_posted']);
        $row['date_posted'] = $date->format('m/d/Y');
        
        $jobs[] = $row;
    }
}

$stmt->close();
$conn->close();

echo json_encode($jobs);
?>
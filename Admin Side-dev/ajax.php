<?php
include 'connection.php';

$conn = connection();

if (isset($_GET['company'])) {
    $company = $_GET['company'];
    $job_titles = fetchJobTitles($company);
    echo json_encode($job_titles);
} elseif (isset($_GET['jobTitle'])) {
    $jobTitle = $_GET['jobTitle'];
    $candidates = fetchCandidates($jobTitle);
    echo json_encode($candidates);
}

mysqli_close($conn);

function fetchJobTitles($company) {
    global $conn;
    $query = "SELECT DISTINCT job_title FROM candidate_list WHERE company_name = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $company);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $job_titles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $job_titles[] = htmlspecialchars($row['job_title']);
    }
    return $job_titles;
}

function fetchCandidates($jobTitle) {
    global $conn;
    $query = "SELECT DISTINCT full_name FROM candidate_list WHERE job_title = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $jobTitle);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $full_names = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $full_names[] = htmlspecialchars($row['full_name']);
    }
    return $full_names;
}
?>

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
    $query = "SELECT DISTINCT job_title FROM candidate_list WHERE company_name = ? AND status = 'Approved' AND deployment_status = 'Pending'";
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
    $query = "
        SELECT 
            cl.userid, 
            cl.full_name, 
            at.profile_image, 
            at.location, 
            at.phone 
        FROM 
            candidate_list cl
        JOIN 
            applicant_table at ON cl.userid = at.userid
        WHERE 
            cl.job_title = ? 
            AND cl.status = 'Approved' 
            AND cl.deployment_status = 'Pending'
    ";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $jobTitle);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $candidates = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $candidates[] = [
            'userid' => $row['userid'],
            'full_name' => htmlspecialchars($row['full_name']),
            'profile_image' => base64_encode($row['profile_image']), // Encode BLOB to base64
            'location' => htmlspecialchars($row['location']),
            'phone' => htmlspecialchars($row['phone']),
        ];
    }

    return $candidates;
}
?>

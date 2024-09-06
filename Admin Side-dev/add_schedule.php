<?php
include 'connection.php'; // Include the database connection

$conn = connection();

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_title = $_POST['job_title'];
    $company_name = $_POST['company_name'];
    $candidate_name = $_POST['candidate_name'];
    $scheduled_date = $_POST['scheduled_date'];

    // Prepare and execute the insert query
    $query = "INSERT INTO schedule_table (job_title, company_name, candidate_name, scheduled_date) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $job_title, $company_name, $candidate_name, $scheduled_date);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>

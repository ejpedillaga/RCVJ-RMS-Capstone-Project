<?php
include 'connection.php'; // Include the database connection

$conn = connection();

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_title = $_POST['job_title'];
    $company_name = $_POST['company_name'];
    $candidate_name = $_POST['candidate_name'];
    $scheduled_date = $_POST['scheduled_date'];
    $start_time = $_POST['start_time'];  // New start time field
    $end_time = $_POST['end_time'];      // New end time field

    // Begin transaction to ensure both operations (insert and update) are successful
    mysqli_begin_transaction($conn);

    try {
        // Insert the new schedule into the schedule_table
        $query = "INSERT INTO schedule_table (job_title, company_name, candidate_name, scheduled_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssss', $job_title, $company_name, $candidate_name, $scheduled_date, $start_time, $end_time);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);

        // Update the deployment_status to 'Scheduled' in candidate_list
        $updateQuery = "UPDATE candidate_list SET deployment_status = 'Scheduled' 
                        WHERE full_name = ? AND job_title = ? AND company_name = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'sss', $candidate_name, $job_title, $company_name);

        if (!mysqli_stmt_execute($updateStmt)) {
            throw new Exception(mysqli_error($conn));
        }
        mysqli_stmt_close($updateStmt);

        // Commit the transaction
        mysqli_commit($conn);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        // Rollback on failure
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
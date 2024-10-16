<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'connection.php';
$conn = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateId = $_POST['candidate_id'];

    // Fetch job_id associated with the candidate
    $stmt = $conn->prepare("SELECT job_id FROM candidate_list WHERE id = ?");
    $stmt->bind_param("i", $candidateId);
    $stmt->execute();
    $stmt->bind_result($jobId);
    $stmt->fetch();
    $stmt->close();

    // Prepare and execute the update statement for candidate status
    $stmt = $conn->prepare("UPDATE candidate_list SET status = 'Pending' WHERE id = ?");
    $stmt->bind_param("i", $candidateId);

    if ($stmt->execute()) {
        // Increment job_candidates in the job_table
        $updateJobStmt = $conn->prepare("UPDATE job_table SET job_candidates = job_candidates + 1 WHERE id = ?");
        $updateJobStmt->bind_param("i", $jobId);

        if ($updateJobStmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update job candidates.']);
        }

        $updateJobStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
    }

    $stmt->close();
    $conn->close();
    exit(); // Ensure no further output is sent
}
?>
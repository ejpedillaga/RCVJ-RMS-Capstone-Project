<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'connection.php'; // Include your database connection
$conn = connection();

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the user ID and job ID from the POST request
    $userId = $_POST['userid'];
    $jobId = $_POST['jobid']; // Get job_id from the request

    // Begin a transaction to ensure both operations succeed together
    $conn->begin_transaction();

    try {
        //Update candidate's status to 'Approved' and 'Pending' deployment
        $updateCandidateSQL = "UPDATE candidate_list 
                               SET status = 'Approved', deployment_status = 'Pending' 
                               WHERE userid = ? AND job_id = ?";
        $stmt1 = $conn->prepare($updateCandidateSQL);
        if (!$stmt1) {
            throw new Exception('Failed to prepare candidate update statement.');
        }
        $stmt1->bind_param("ii", $userId, $jobId);
        if (!$stmt1->execute()) {
            throw new Exception('Failed to update candidate status.');
        }
        $stmt1->close();

        //Decrease the candidate count in the `job_table`
        $updateJobSQL = "UPDATE job_table 
                         SET job_candidates = job_candidates - 1 
                         WHERE id = ? AND job_candidates > 0";
        $stmt2 = $conn->prepare($updateJobSQL);
        if (!$stmt2) {
            throw new Exception('Failed to prepare job update statement.');
        }
        $stmt2->bind_param("i", $jobId);
        if (!$stmt2->execute()) {
            throw new Exception('Failed to update job candidate count.');
        }
        $stmt2->close();

        // Commit the transaction
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

$conn->close();
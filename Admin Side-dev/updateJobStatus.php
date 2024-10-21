<?php

include_once("connection.php");

$conn = connection();

// Get the JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

$jobId = $data['jobId'];
$status = $data['status'];

// Validate inputs 
if (!is_numeric($jobId) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Begin a transaction to ensure both operations succeed together
$conn->begin_transaction();

try {
    // Prepare the SQL statement to update job status
    $stmt = $conn->prepare("UPDATE job_table SET job_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $jobId);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update job status: ' . $stmt->error);
    }

    // Check if the job status is changing to "Open"
    if ($status === 'Open') {
        // Prepare the SQL statement to change candidates' status from Archived to Pending
        $reactivateCandidatesSQL = "UPDATE candidate_list 
                                     SET status = 'Pending' 
                                     WHERE job_id = ? AND status = 'Archived' AND status != 'Approved'";
        $stmt2 = $conn->prepare($reactivateCandidatesSQL);
        $stmt2->bind_param("i", $jobId);

        if (!$stmt2->execute()) {
            throw new Exception('Failed to reactivate candidates: ' . $stmt2->error);
        }
    } else {
        // If job status is not "Open", archive candidates who are Pending
        $archiveCandidatesSQL = "UPDATE candidate_list 
                                 SET status = 'Archived' 
                                 WHERE job_id = ? AND status = 'Pending'";
        $stmt3 = $conn->prepare($archiveCandidatesSQL);
        $stmt3->bind_param("i", $jobId);

        if (!$stmt3->execute()) {
            throw new Exception('Failed to archive candidates: ' . $stmt3->error);
        }
    }

    // Commit the transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Job status updated successfully and candidates processed accordingly']);
} catch (Exception $e) {
    // Rollback if any query fails
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // Close the connections
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($stmt2)) {
        $stmt2->close();
    }
    if (isset($stmt3)) {
        $stmt3->close();
    }
    $conn->close();
}
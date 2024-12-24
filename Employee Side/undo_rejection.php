<?php
// Database connection details
include 'connection.php';

// Create a connection
$conn = connection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userid'];
    $jobId = $_POST['jobid'];

    $conn->begin_transaction(); // Start a transaction

    try {
        // Update the candidate's status back to 'Pending'
        $updateCandidateSQL = "UPDATE candidate_list 
                               SET status = 'Pending' 
                               WHERE userid = ? AND job_id = ?";
        $stmt1 = $conn->prepare($updateCandidateSQL);
        $stmt1->bind_param("ii", $userId, $jobId);
        $stmt1->execute();
        $stmt1->close();

        // Delete the entry from the rejected_table
        $deleteRejectSQL = "DELETE FROM rejected_table 
                            WHERE userid = ? AND job_id = ?";
        $stmt2 = $conn->prepare($deleteRejectSQL);
        $stmt2->bind_param("ii", $userId, $jobId);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit(); // Commit the transaction
        $response['success'] = true;
    } catch (Exception $e) {
        $conn->rollback(); // Rollback if anything fails
        $response['error'] = $e->getMessage();
    }
} else {
    $response['error'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>
<?php
include 'connection.php';
$conn = connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_POST['userid'];
    $jobid = $_POST['jobid'];

    // Prepare the SQL statements to delete from candidate_list and rejected_table
    $deleteCandidateQuery = "DELETE FROM candidate_list WHERE userid = ? AND job_id = ?";
    $deleteRejectedQuery = "DELETE FROM rejected_table WHERE userid = ? AND job_id = ?";

    // Use prepared statements to prevent SQL injection
    if ($stmt = $conn->prepare($deleteCandidateQuery)) {
        $stmt->bind_param("ii", $userid, $jobid);
        $stmt->execute();
        $stmt->close();
    }

    if ($stmt = $conn->prepare($deleteRejectedQuery)) {
        $stmt->bind_param("ii", $userid, $jobid);
        $stmt->execute();
        $stmt->close();
    }

    // Return success response
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

$conn->close(); // Close the database connection
?>
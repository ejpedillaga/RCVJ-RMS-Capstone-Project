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

    // Prepare an SQL statement to update the candidate's status based on userId and jobId
    $sql = "UPDATE candidate_list SET status = 'Approved', deployment_status = 'Pending' WHERE userid = ? AND job_id = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameters and execute the statement
    if ($stmt) {
        $stmt->bind_param("ii", $userId, $jobId); // Assuming both userid and jobid are integers
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Statement preparation failed.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

$conn->close();

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

// Prepare the SQL statement
$stmt = $conn->prepare("UPDATE job_table SET job_status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $jobId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Job status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update job status: ' . $stmt->error]);
}

// Close the connection
$stmt->close();
$conn->close();


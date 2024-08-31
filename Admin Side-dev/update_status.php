<?php
include_once("connection.php");

$conn = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $status = $data['status'];

    // Example: Assume you need to update the status of a specific job
    $jobId = 1;  // Replace with the actual job ID you want to update
    $sql = "UPDATE job_table SET job_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $jobId);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Status updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update status']);
    }

    $stmt->close();
    $conn->close();
}
?>

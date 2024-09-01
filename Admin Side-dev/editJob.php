<?php
include_once("connection.php");

$conn = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = intval($_POST['id']);
    $jobTitle = $_POST['job_title'];
    $jobLocation = $_POST['job_location'];
    $jobCandidates = intval($_POST['job_candidates']);
    $jobDescription = $_POST['job_description'];
    $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];

    // Update job data
    $sql = "UPDATE job_table SET job_title = ?, job_location = ?, job_candidates = ?, job_description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare statement"]);
        exit;
    }
    $stmt->bind_param('ssisi', $jobTitle, $jobLocation, $jobCandidates, $jobDescription, $jobId);
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Failed to execute update job data"]);
        exit;
    }
    $stmt->close();

    //Update skills

    $conn ->begin_transaction();
    
    if (empty($skills)) {
        // $skills is empty
    }else {
        //$skills not empty
        
    }
    echo json_encode(["message" => "Job updated successfully"]);
}

$conn->close();


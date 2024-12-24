<?php
include ("session_check.php");
include_once("connection.php");
include_once("audit_script.php");

$conn = connection();

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    
    // Create the SQL query
    $sql = "SELECT employee_id FROM employee_table WHERE username = ?";
    
    // Prepare and execute the query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username); // Bind the $username parameter to the query
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $employee_id = $row['employee_id'];
            
        } else {
            // Handle case when no matching employee is found
        }
        
        $stmt->close();
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $companyName = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
        $jobId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $jobTitle = trim($_POST['job_title']);
        $jobLocation = trim($_POST['job_location']);
        $jobCandidates = intval($_POST['job_candidates']);
        $jobDescription = trim($_POST['job_description']);
        $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];
        
        // Validate input
        if (!$jobId || empty($jobTitle) || empty($jobLocation)) {
            throw new Exception("Missing required fields.");
        }
        
        // Step 1: Fetch the current job details
        $stmt = $conn->prepare("SELECT job_title, job_location, job_candidates, job_description FROM job_table WHERE id = ?");
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            throw new Exception("Job post not found.");
        }
        $currentData = $result->fetch_assoc();
        $stmt->close();
        
        // Step 2: Compare current data with new data
        $changes = [];
        if ($currentData['job_title'] !== $jobTitle) {
            $changes[] = "Job Title: \"{$currentData['job_title']}\" changed to \"$jobTitle\"";
        }
        if ($currentData['job_location'] !== $jobLocation) {
            $changes[] = "Job Location: \"{$currentData['job_location']}\" changed to \"$jobLocation\"";
        }
        if ($currentData['job_candidates'] != $jobCandidates) {
            $changes[] = "job_candidates: {$currentData['job_candidates']} changed to $jobCandidates";
        }
        if ($currentData['job_description'] !== $jobDescription) {
            $changes[] = "Job Description: \"{$currentData['job_description']}\" changed to \"$jobDescription\"";
        }
    
        if (empty($changes)) {
            echo json_encode(["message" => "No changes detected."]);
            exit;
        }
    
        // Convert changes array into a string for logging
        $details = implode(", ", $changes);

    
            // Step 3: Update the job data
        $stmt = $conn->prepare("UPDATE job_table SET job_title = ?, job_location = ?, job_candidates = ?, job_description = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $jobTitle, $jobLocation, $jobCandidates, $jobDescription, $jobId);
    
        if (!$stmt->execute()) {
            throw new Exception("Failed to update job post: " . $stmt->error);
        }
        $stmt->close();
        logAuditAction($employee_id, 'Edit', 'Job Post for ' . $companyName, $jobId, $details);
        echo json_encode(["message" => "Job updated successfully"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

$conn->close();

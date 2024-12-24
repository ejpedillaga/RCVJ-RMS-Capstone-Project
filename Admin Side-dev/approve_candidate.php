<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include ("session_check.php");
include_once("audit_script.php");
require 'connection.php'; // Include your database connection
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

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the user ID and job ID from the POST request
    $userId = $_POST['userid'];
    $jobId = $_POST['jobid']; // Get job_id from the request
    
    // Fetch the full_name of the candidate
    $candidateFullName = null; // Default value
    $candidateQuery = "SELECT full_name FROM candidate_list WHERE userid = ?";
    $stmt = $conn->prepare($candidateQuery);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($candidateFullName);
        $stmt->fetch();
        $stmt->close();
    }

    // Fetch company_name, job_title, and job_location using job_id
    $company_name = $job_title = $job_location = null; // Default values
    $jobDetailsQuery = "SELECT company_name, job_title, job_location FROM job_table WHERE id = ?";
    $stmt = $conn->prepare($jobDetailsQuery);
    if ($stmt) {
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $stmt->bind_result($company_name, $job_title, $job_location);
        $stmt->fetch();
        $stmt->close();
    }

    // Begin a transaction to ensure both operations succeed together
    $conn->begin_transaction();

    try {
        // Update candidate's status to 'Approved' and 'Pending' deployment
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

        // Decrease the candidate count in the `job_table`
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

        // Check if job_candidates has reached 0, and if so, update job_status
        $checkJobCandidatesSQL = "SELECT job_candidates FROM job_table WHERE id = ?";
        $stmt3 = $conn->prepare($checkJobCandidatesSQL);
        if (!$stmt3) {
            throw new Exception('Failed to prepare job candidate check statement.');
        }
        $stmt3->bind_param("i", $jobId);
        if (!$stmt3->execute()) {
            throw new Exception('Failed to execute job candidate check statement.');
        }
        $stmt3->bind_result($jobCandidates);
        $stmt3->fetch();
        $stmt3->close();

        // If job_candidates is 0, update job_status to 'Closed'
        if ($jobCandidates === 0) {
            // Update job status to 'Closed'
            $updateJobStatusSQL = "UPDATE job_table SET job_status = 'Closed' WHERE id = ?";
            $stmt4 = $conn->prepare($updateJobStatusSQL);
            if (!$stmt4) {
                throw new Exception('Failed to prepare job status update statement.');
            }
            $stmt4->bind_param("i", $jobId);
            if (!$stmt4->execute()) {
                throw new Exception('Failed to update job status.');
            }
            $stmt4->close();

            // Update status of all candidates (except those with 'Approved') to 'Archived'
            $archiveCandidatesSQL = "UPDATE candidate_list 
                                     SET status = 'Archived' 
                                     WHERE job_id = ? AND status NOT IN ('Approved')";
            $stmt5 = $conn->prepare($archiveCandidatesSQL);
            if (!$stmt5) {
                throw new Exception('Failed to prepare candidate archive statement.');
            }
            $stmt5->bind_param("i", $jobId);
            if (!$stmt5->execute()) {
                throw new Exception('Failed to archive candidates.');
            }
            $stmt5->close();
        }

        // Commit the transaction
        $conn->commit();
        echo json_encode(['success' => true]);
        logAuditAction($employee_id, 'Approve', 'Application', $userId, "Candidate: $candidateFullName, Company: $company_name, Title: $job_title, Location: $job_location");

    } catch (Exception $e) {
        // Rollback if any query fails
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

$conn->close();
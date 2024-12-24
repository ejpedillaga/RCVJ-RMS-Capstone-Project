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
    $userId = $_POST['userid'];
    $jobId = $_POST['jobid'];
    $remarks = $_POST['remarks'];
    $dateRejected = date('Y-m-d H:i:s'); // Capture current timestamp

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

    $conn->begin_transaction(); // Start a transaction

    try {
        // Fetch the company name and job title using jobId
        $jobInfoSQL = "
            SELECT j.company_name, jt.job_title AS job_title
            FROM job_table j
            JOIN job_title_table jt ON j.job_title_id = jt.id
            WHERE j.id = ?";
        $stmt1 = $conn->prepare($jobInfoSQL);
        if (!$stmt1) {
            throw new Exception('Failed to prepare job info statement.');
        }
        $stmt1->bind_param("i", $jobId);
        $stmt1->execute();
        $stmt1->bind_result($companyName, $jobTitle);
        if (!$stmt1->fetch()) {
            throw new Exception('Job information not found.');
        }
        $stmt1->close();

        // Update the candidate's status to 'Rejected'
        $updateCandidateSQL = "
            UPDATE candidate_list 
            SET status = 'Rejected' 
            WHERE userid = ? AND job_id = ?";
        $stmt2 = $conn->prepare($updateCandidateSQL);
        if (!$stmt2) {
            throw new Exception('Failed to prepare candidate update statement.');
        }
        $stmt2->bind_param("ii", $userId, $jobId);
        $stmt2->execute();
        $stmt2->close();

        // Insert a row into `rejected_table` with additional details
        $insertRejectSQL = "
            INSERT INTO rejected_table (userid, job_id, company_name, job_title, remarks, date_rejected) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt3 = $conn->prepare($insertRejectSQL);
        if (!$stmt3) {
            throw new Exception('Failed to prepare insert statement for rejected table.');
        }
        $stmt3->bind_param("iissss", $userId, $jobId, $companyName, $jobTitle, $remarks, $dateRejected);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit(); // Commit the transaction
        logAuditAction($employee_id, 'Reject', 'Application', $userId, "Candidate: $candidateFullName, Company: $company_name, Title: $job_title, Location: $job_location, Remarks: $remarks");
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback(); // Rollback if anything fails
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}

$conn->close();
?>
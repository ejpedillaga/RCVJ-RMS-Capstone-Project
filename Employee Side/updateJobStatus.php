<?php
include_once("session_check.php");
include_once("connection.php");
include_once("audit_script.php");
$conn = connection();

// Get the JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

$jobId = $data['jobId'];
$status = $data['status'];

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    
    // Create the SQL query to fetch employee_id
    $sql = "SELECT employee_id FROM employee_table WHERE username = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $employee_id = $row['employee_id'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
            exit;
        }
        
        $stmt->close();
    }
}

// Validate inputs 
if (!is_numeric($jobId) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Fetch the company_name associated with the jobId
$company_name = null;
$fetchCompanySQL = "SELECT company_name FROM job_table WHERE id = ?";
if ($stmt = $conn->prepare($fetchCompanySQL)) {
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $company_name = $row['company_name'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Job not found']);
        exit;
    }
    
    $stmt->close();
}

// Begin a transaction to ensure all operations succeed together
$conn->begin_transaction();

try {
    // Prepare the SQL statement to update job status
    $stmt = $conn->prepare("UPDATE job_table SET job_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $jobId);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update job status: ' . $stmt->error);
    }

    // Handle candidates based on the status
    if ($status === 'Open') {
        $reactivateCandidatesSQL = "UPDATE candidate_list 
                                    SET status = 'Pending' 
                                    WHERE job_id = ? AND status = 'Archived' AND status != 'Approved'";
        $stmt2 = $conn->prepare($reactivateCandidatesSQL);
        $stmt2->bind_param("i", $jobId);

        if (!$stmt2->execute()) {
            throw new Exception('Failed to reactivate candidates: ' . $stmt2->error);
        }
    } else {
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
    
    // Log the audit action
    logAuditAction($employee_id, 'Edit', 'Job Status', $jobId, "Job for: $company_name, Status changed to $status");
} catch (Exception $e) {
    // Rollback if any query fails
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // Close the connections
    if (isset($stmt)) $stmt->close();
    if (isset($stmt2)) $stmt2->close();
    if (isset($stmt3)) $stmt3->close();
    $conn->close();
}

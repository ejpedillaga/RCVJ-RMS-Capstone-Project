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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the POST request
    $data = json_decode(file_get_contents("php://input"), true);

    $candidateId = $data['id'];
    $oldStatus = $data['oldstatus'];
    $newStatus = $data['status'];

    if($oldStatus == null){
        $oldStatus = 'Pending';
    }
    // Validate the inputs (if necessary)
    if (empty($candidateId) || empty($newStatus)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }


    // Fetch the applicant's name
    $query = "SELECT full_name FROM candidate_list WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $candidateId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $applicantName = $row['full_name'];
    } else {
        $applicantName = "Unknown"; // Default if the candidate is not found
    }

    // Update the status in the database
    $updateQuery = "UPDATE candidate_list SET deployment_status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $newStatus, $candidateId);

    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Status updated successfully', 
        ]);
        logAuditAction($employee_id, 'Edit', 'Application Status', $candidateId, "Applicant: $applicantName, Old Status: $oldStatus, New Status: $newStatus");
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
}


<?php
include_once("connection.php");
include_once("session_check.php");
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

// Get the partner ID from the POST request
$partnerId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($partnerId > 0) {
    // Fetch the company_name of the partner being deleted
    $companyName = null;
    $fetchCompanySQL = "SELECT company_name FROM partner_table WHERE id = ?";
    if ($stmt = $conn->prepare($fetchCompanySQL)) {
        $stmt->bind_param("i", $partnerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $companyName = $row['company_name'];
        } else {
            echo json_encode(["error" => "Partner not found"]);
            exit;
        }

        $stmt->close();
    }

    // Prepare the SQL statement to delete the partner
    $sql = "DELETE FROM partner_table WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $partnerId);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Partner deleted successfully"]);
            // Log the deletion in the audit logs
            logAuditAction($employee_id, 'Delete', 'Partner', $partnerId, "Deleted partner: $companyName");
        } else {
            echo json_encode(["error" => "Error: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Failed to prepare SQL statement"]);
    }
} else {
    echo json_encode(["error" => "Invalid partner ID"]);
}

$conn->close();
?>
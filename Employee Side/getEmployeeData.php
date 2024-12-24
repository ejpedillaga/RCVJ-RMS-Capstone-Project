<?php
include_once("connection.php");

$conn = connection();

// Get the employee ID from the query string
$employeeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($employeeId > 0) {
    $sql = "SELECT employee_id, first_name, last_name, employee_title FROM employee_table WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind the employee ID to the prepared statement
    $stmt->bind_param("i", $employeeId);
    
    // Execute the prepared statement
    $stmt->execute();
    
    // Get the result set from the executed statement
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the data as an associative array
        $employeeData = $result->fetch_assoc();
        
        // Output the data as JSON
        header('Content-Type: application/json');
        echo json_encode($employeeData);
    } else {
        echo json_encode(["error" => "No employee found"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid or missing employee ID"]);
}

$conn->close();

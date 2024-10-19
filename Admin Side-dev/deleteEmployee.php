<?php
include_once("connection.php");

$conn = connection();

try {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $employeeId = isset($input['employee_id']) ? (int)$input['employee_id'] : 0;

    // Check if the employee ID is valid
    if ($employeeId <= 0) {
        echo json_encode(["error" => "Invalid employee ID."]);
        exit;
    }

    // Prepare the SQL delete query
    $stmt = $conn->prepare("DELETE FROM employee_table WHERE employee_id = ?");
    $stmt->bind_param("i", $employeeId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Employee deleted successfully"]);
    } else {
        echo json_encode(["error" => "Error executing query: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
    
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(["error" => "Exception: " . $e->getMessage()]);
}

$conn->close();
?>

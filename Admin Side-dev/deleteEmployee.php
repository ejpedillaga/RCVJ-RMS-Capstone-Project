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

    // Prepare to delete from users_table first
    $deleteUserStmt = $conn->prepare("DELETE FROM users_table WHERE employee_id = ?");
    $deleteUserStmt->bind_param("i", $employeeId);

    // Execute the deletion of the user account
    if ($deleteUserStmt->execute()) {
        // Now delete from employee_table
        $deleteEmployeeStmt = $conn->prepare("DELETE FROM employee_table WHERE employee_id = ?");
        $deleteEmployeeStmt->bind_param("i", $employeeId);

        if ($deleteEmployeeStmt->execute()) {
            echo json_encode(["message" => "Employee and associated user account deleted successfully"]);
        } else {
            echo json_encode(["error" => "Error deleting employee: " . $deleteEmployeeStmt->error]);
        }

        $deleteEmployeeStmt->close();
    } else {
        echo json_encode(["error" => "Error deleting user: " . $deleteUserStmt->error]);
    }

    $deleteUserStmt->close();

} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(["error" => "Exception: " . $e->getMessage()]);
}

$conn->close();
?>

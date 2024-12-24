<?php
include_once("connection.php");

$conn = connection();

try {
    // Fetch and sanitize POST data
    $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $newRole = isset($_POST['employee_title']) ? $_POST['employee_title'] : '';
    $employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;

    // Prepare the SQL update query for first name and last name only
    $updateSql = "UPDATE employee_table SET first_name = ?, last_name = ?, employee_title = ? WHERE employee_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssi", $firstName, $lastName, $newRole, $employeeId);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Employee updated successfully"]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
    
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>


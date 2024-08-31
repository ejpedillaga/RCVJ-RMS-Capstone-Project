<?php
include_once("connection.php");

$conn = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = intval($_POST['employee_id']);
    $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Concatenate first and last name to get full name
    $fullName = $firstName . ' ' . $lastName;

    // Update job data
    $sql = "UPDATE employee_table SET full_name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare statement"]);
        exit;
    }

    if ($stmt->execute()) {
       echo json_encode(["message" => "Partner added successfully"]);
    } else {
       echo json_encode(["error" => "Error: " . $stmt->error]);
    }
   $stmt->close();
}
$conn->close();

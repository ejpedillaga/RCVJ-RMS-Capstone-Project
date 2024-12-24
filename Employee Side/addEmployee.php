<?php
include_once("connection.php");

$conn = connection();

try {
    // Fetch and sanitize POST data
    $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : ''; // Get username directly from input
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['employee_title']) ? $_POST['employee_title'] : '';

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert first_name, last_name, username, and password into employee_table
    $stmt = $conn->prepare("INSERT INTO employee_table (first_name, last_name, username, password, employee_title) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstName, $lastName, $username, $hashed_password, $role);

    if ($stmt->execute()) {
        // Get the last inserted employee_id
        $employeeId = $stmt->insert_id;

        // Insert username and hashed password into users_table with employee_id as foreign key
        $empCredStmt = $conn->prepare("INSERT INTO users_table (employee_id, username, password) VALUES (?, ?, ?)");
        $empCredStmt->bind_param("iss", $employeeId, $username, $hashed_password); // Use employee_id as foreign key

        if ($empCredStmt->execute()) {
            echo json_encode(["message" => "Employee added successfully"]);
        } else {
            echo json_encode(["error" => "Error inserting into users_table: " . $empCredStmt->error]);
        }

        // Close the user account insert statement
        $empCredStmt->close();
    } else {
        echo json_encode(["error" => "Error inserting into employee_table: " . $stmt->error]);
    }

    // Close the employee insert statement
    $stmt->close();

} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(["error" => "Exception: " . $e->getMessage()]);
}
?>

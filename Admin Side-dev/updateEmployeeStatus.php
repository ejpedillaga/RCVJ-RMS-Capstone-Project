<?PHP
include_once("connection.php");

$conn = connection();

// Get the JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

$employeeId = $data['employeeId'];
$status = $data['status'];

// Validate inputs (optional but recommended)
if (!is_numeric($employeeId) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("UPDATE employee_table SET status = ? WHERE employee_id = ?");
$stmt->bind_param("si", $status, $employeeId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Employee status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update employee status: ' . $stmt->error]);
}

// Close the connection
$stmt->close();
$conn->close();

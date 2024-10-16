<?php
include 'connection.php'; 
$conn = connection();

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];
$status = $data['status'];

$query = "UPDATE candidate_list SET deployment_status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $status, $id);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
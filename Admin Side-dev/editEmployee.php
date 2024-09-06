<?php
include_once("connection.php");

$conn = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = intval($_POST['employee_id']);
    $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Update job data
    $sql = "UPDATE employee_table SET first_name = ?, last_name = ? WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare statement"]);
        exit;
    }
    $stmt->bind_param('ssi', $firstName, $lastName, $employeeId);
    if ($stmt->execute()) {
       echo json_encode(["message" => "Partner added successfully"]);
    } else {
       echo json_encode(["error" => "Error: " . $stmt->error]);
    }
   $stmt->close();




   /*
   //TODO Fix updating userpassword
   $user_stmt = $conn->prepare("UPDATE users_table SET password = ? WHERE userid = $employeeId" );
   if (!$user_stmt) {
      echo json_encode(["error" => "Failed to prepare update password statement"]);
      exit;
  }
  $user_stmt->bind_param("s", $password) ;
  if ($user_stmt->execute()) {
   echo json_encode(["message"=> "Password updated succesfully"]);
  } else {
   echo json_encode(["error"=> "Error " . $user_stmt->error]);
  }
  $user_stmt->close();
  */
}
$conn->close();

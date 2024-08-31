<?php
include_once("connection.php");

$conn = connection();

// Get the partner ID from the POST request
$partnerId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($partnerId > 0) {
    // Prepare the SQL statement to delete the partner
    $sql = "DELETE FROM partner_table WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $partnerId);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Partner deleted successfully"]);
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

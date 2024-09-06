<?php
include 'connection.php'; // Include the database connection

$conn = connection();

// Check if the event ID is provided
if (isset($_GET['eventId'])) {
    $eventId = intval($_GET['eventId']);
    
    // Prepare and execute the deletion query
    $query = "DELETE FROM schedule_table WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $eventId);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete event"]);
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

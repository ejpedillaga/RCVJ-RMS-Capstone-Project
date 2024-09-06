<?php
include 'connection.php'; // Include the database connection

$conn = connection();

// Fetch events from the schedule_table
$query = "SELECT * FROM schedule_table ORDER BY scheduled_date";
$result = mysqli_query($conn, $query);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
}

mysqli_close($conn);

// Output events as JSON
header('Content-Type: application/json');
echo json_encode($events);
?>

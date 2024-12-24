<?php
include 'connection.php'; // Include the database connection

$conn = connection();
$query = "SELECT id, job_title, company_name, candidate_name, scheduled_date, start_time, end_time 
          FROM schedule_table 
          ORDER BY scheduled_date DESC, start_time ASC";  // Sort by date and time in descending order

$result = mysqli_query($conn, $query);
$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
}

echo json_encode($events);
mysqli_close($conn);
?>
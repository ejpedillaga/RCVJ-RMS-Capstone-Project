<?php

include 'connection.php'; 
$conn = connection();
header('Content-Type: application/json');

try{


    // SQL query to fetch skill names
    // SQL query to fetch skill names
    $sql = "SELECT skill_name FROM skill_table";
    $result = $conn->query($sql);

    // Create an array to store the skill names
    $skills = [];

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $skills[] = $row['skill_name']; // Fetch the skill_name column
        }
    } else {
        throw new Exception("No results found.");
    }

    // Close the database connection
    $conn->close();

    // Return the data as JSON
    echo json_encode($skills);

} catch (Exception $e) {
    // Return the error message as a JSON object
    echo json_encode(['error' => $e->getMessage()]);
}
?>

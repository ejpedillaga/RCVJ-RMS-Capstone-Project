<?php
$servername = "localhost";
$username = "root"; // default username for XAMPP
$password = "12345"; // default password for XAMPP
$dbname = "admin_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT full_name, remarks, date_rejected FROM rejected_table";
$result = $conn->query($sql);

$rejects = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $rejects[] = $row;
    }
} else {
    echo "0 results";
}
$conn->close();

echo json_encode($rejects);

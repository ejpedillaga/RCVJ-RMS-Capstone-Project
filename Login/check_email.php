<?php
include 'connection.php';

// Create a connection
$conn = connection();

if (isset($_POST['email'])) {
    $email = $conn->real_escape_string($_POST['email']);

    $sql = "SELECT * FROM applicant_table WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "exists";
    } else {
        echo "available";
    }
}

$conn->close();
?>

<?php
include 'connection.php';
$conn = connection();

$message = '';

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $conn->real_escape_string($_GET['email']);
    $code = $conn->real_escape_string($_GET['code']);

    // Check if the verification code matches
    $sql = "SELECT * FROM applicant_table WHERE email = '$email' AND verification_code = '$code' AND email_verified = 0";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update the email_verified field
        $update_sql = "UPDATE applicant_table SET email_verified = 1 WHERE email = '$email'";
        if ($conn->query($update_sql) === TRUE) {
            $message = "Email verified successfully!";
        } else {
            $message = "Error updating record: " . $conn->error;
        }
    } else {
        $message = "Invalid or expired verification link.";
    }
} else {
    $message = "Invalid request.";
}

$conn->close();
echo $message;
?>

<?php
include 'connection.php';

header('Content-Type: application/json');

// Get the data from the AJAX request
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $job_title = $data['job_title'];
    $classification = $data['classification'];
    $subclassification = $data['subclassification'];
    $gender = $data['gender'];
    $educational_attainment = $data['educational_attainment'];
    $cert_license = $data['cert_license'];
    $years_of_experience = $data['years_of_experience'];

    // Connect to the database
    $conn = connection(); // Assuming your `connection.php` contains a function to connect to the database

    // Insert query
    $query = "INSERT INTO job_title_table (job_title, classification, subclassification, gender, educational_attainment, years_of_experience, cert_license)
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssis", $job_title, $classification, $subclassification, $gender, $educational_attainment, $years_of_experience, $cert_license);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
    $conn->close();
}
?>

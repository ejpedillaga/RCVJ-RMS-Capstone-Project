<?php

include_once("connection.php");

$conn = connection();

// Collect data from POST request
$company_name = $_POST['company_name'];
$industry = $_POST['industry'];
$company_location = $_POST['location'];
$company_description = $_POST['description'];

// Handle logo upload, convert to BLOB, or use default
$logo = null;
if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
    // If a file was uploaded, get the file contents
    $logo = file_get_contents($_FILES['logo']['tmp_name']);
} else {
    // No logo uploaded, use default logo
    $default_logo_path = 'img/partner_logo_default.jfif';  // Path to your default logo
    if (file_exists($default_logo_path)) {
        $logo = file_get_contents($default_logo_path);  // Read default logo as BLOB
    } else {
        // Optional: Handle the case where the default logo file is missing
        echo json_encode(["error" => "Default logo not found"]);
        exit;
    }
}

// Prepare and bind the SQL statement
$stmt = $conn->prepare("INSERT INTO partner_table (logo, company_name, industry, company_location, company_description) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("bssss", $logo, $company_name, $industry, $company_location, $company_description);

// Send the logo data as a BLOB if a logo exists
if ($logo) {
    $stmt->send_long_data(0, $logo);
}

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(["message" => "Partner added successfully"]);
} else {
    echo json_encode(["error" => "Error: " . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();


<?php

include_once("connection.php");

$conn = connection();

// Get the partner ID from the POST request
$partnerId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($partnerId > 0) {
    // Collect data from POST request
    $company_name = $_POST['company_name'];
    $industry = $_POST['industry'];
    $company_description = $_POST['company_description'];
    $company_location = $_POST['company_location'];

    // Handle logo upload and convert to BLOB
    $logo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
        $logo = file_get_contents($_FILES['logo']['tmp_name']);
    }

    // Prepare the SQL statement for updating the partner information
    $sql = "UPDATE partner_table SET company_name = ?, company_location = ?, industry = ?, company_description = ?";
    if ($logo !== null) {
        $sql .= ", logo = ?";
    }
    $sql .= " WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if ($logo !== null) {
        $stmt->bind_param("sssssi", $company_name, $company_location, $industry, $company_description, $logo, $partnerId);
    } else {
        $stmt->bind_param("ssssi", $company_name, $company_location, $industry, $company_description, $partnerId);
    }

    if ($stmt->execute()) {
        echo json_encode(["message" => "Partner updated successfully"]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid partner ID"]);
}

$conn->close();
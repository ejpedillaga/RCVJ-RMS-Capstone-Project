<?php
include_once("connection.php");

$conn = connection();

// Get the partner ID from the query string
$partnerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($partnerId > 0) {
    $sql = "SELECT company_name, industry, company_location, company_description, logo FROM partner_table WHERE id = $partnerId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $partnerData = $result->fetch_assoc();

        // Encode the company logo BLOB to base64
        $partnerData['logo'] = base64_encode($partnerData['logo']);
        
        // Ensure that you're outputting only JSON
        header('Content-Type: application/json');
        echo json_encode($partnerData);
    } else {
        echo json_encode(["error" => "No partner found"]);
    }
} else {
    echo json_encode(["error" => "Invalid partner ID"]);
}

$conn->close();
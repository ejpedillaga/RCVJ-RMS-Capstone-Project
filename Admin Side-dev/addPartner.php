<?PHP

    include_once("connection.php");

    $conn = connection();


    // Collect data from POST request
    $company_name = $_POST['company_name'];
    $industry = $_POST['industry'];
    $company_location = $_POST['location'];
    $company_description = $_POST['description'];

    // Handle logo upload and convert to BLOB
    $logo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
        $logo = file_get_contents($_FILES['logo']['tmp_name']);
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO partner_table (logo, company_name, industry, company_location, company_description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("bssss", $logo, $company_name, $industry, $company_location, $company_description);

    // Send the logo data as a BLOB
    $stmt->send_long_data(0, $logo);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Partner added successfully"]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

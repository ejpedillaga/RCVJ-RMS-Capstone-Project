<?php
include_once("connection.php");

header('Content-Type: application/json');

try {
    // Decode the JSON data from the request
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if the data is received properly
    if ($data) {
        // Retrieve the job_title_id for updating the correct row
        $job_title_id = $data['job_title_id'];
        
        // If job_title_id is missing, return an error
        if (empty($job_title_id)) {
            echo json_encode(["error" => "No job title ID provided for updating"]);
            exit;
        }

        // Retrieve other fields from the JSON payload
        $classification = $data['classification'];
        $subclassification = $data['subclassification'];
        $gender = $data['gender'];
        $educational_attainment = $data['educational_attainment'];
        $cert_license = $data['cert_license'];
        $years_of_experience = $data['years_of_experience'];

        // Connect to the database
        $conn = connection(); // Assuming this function establishes the database connection

        // Prepare the SQL query to update based on job_title_id (not job_title)
        $query = 'UPDATE job_title_table 
                  SET classification = ?, subclassification = ?, gender = ?, educational_attainment = ?, years_of_experience = ?, cert_license = ? 
                  WHERE id = ?';
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            echo json_encode(["error" => "Failed to prepare statement"]);
            exit;
        }

        // Bind parameters (classification, subclassification, gender, etc.) and job_title_id
        $stmt->bind_param('ssssssi', $classification, $subclassification, $gender, $educational_attainment, $years_of_experience, $cert_license, $job_title_id);

        // Execute the query
        if (!$stmt->execute()) {
            echo json_encode(["success" => false, 'error' => $stmt->error]);
            exit;
        }

        // Close the statement and return success response
        $stmt->close();
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "No data received"]);
    }

    // Close the database connection
    $conn->close();
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error for debugging
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

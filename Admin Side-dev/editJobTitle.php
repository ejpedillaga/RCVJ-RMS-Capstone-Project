<?php
include_once ("connection.php");

header('Content-Type: application/json');

try{
    $data = json_decode(file_get_contents("php://input"), true);

    if($data){
        $job_title = $data['job_title'];
        $classification = $data['classification'];
        $subclassification = $data['subclassification'];
        $gender = $data['gender'];
        $educational_attainment = $data['educational_attainment'];
        $cert_license = $data['cert_license'];
        $years_of_experience = $data['years_of_experience'];
        //$skills = $data['skills'];

        // Connect to the database
        $conn = connection(); // Assuming this function establishes the database connection

        // Retrieve the selected job title from the URL parameters
        $currentJobData = isset($_POST['selected_job_title']) ? $POST['selected_job_title'] : '';

        // Prepare the SQL query
        $query = 'UPDATE job_title_table SET classification = ?, subclassification = ?, gender = ?, educational_attainment = ?, years_of_experience = ?, cert_license = ? WHERE job_title = ?';
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(["error" => "Failed to prepare statement"]);
            exit;
        }
        $stmt->bind_param('sssssss', $job_title, $classification, $subclassification, $gender, $educational_attainment, $cert_license, $years_of_experience);
        if (!$stmt->execute()) {
            echo json_encode(["success" => false, 'error' => $stmt->error]);
            exit;
        }
        $stmt->close();
        echo json_encode(["success" => true]);
    }
    $conn->close();
}catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
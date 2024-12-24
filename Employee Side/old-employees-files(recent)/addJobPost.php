
text/x-generic addJobPost.php ( PHP script, ASCII text, with CRLF line terminators )
<?php
header('Content-Type: application/json');

include ("session_check.php");
include_once("connection.php");
include_once("audit_script.php");

$conn = connection();

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    
    // Create the SQL query
    $sql = "SELECT employee_id FROM employee_table WHERE username = ?";
    
    // Prepare and execute the query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username); // Bind the $username parameter to the query
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $employee_id = $row['employee_id'];
            
        } else {
            // Handle case when no matching employee is found
        }
        
        $stmt->close();
    }
}

try {
    // Collect and validate data from POST request
    $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $job_title = isset($_POST['job_title']) ? trim($_POST['job_title']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $candidates = isset($_POST['candidates']) ? trim($_POST['candidates']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];
    $username = ($_SESSION["username"]);



    // Validate JSON for skills
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON in skills data");
    }

    // Input validation
    if (empty($company_name) || empty($job_title) || empty($location)) {
        throw new Exception("All fields must be filled.");
    }

    if (!filter_var($candidates, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100]])) {
        throw new Exception("Candidates must be an integer value between 1 and 100.");
    }

    // Step 1: Get the job_title_id based on the provided job_title
    $stmt = $conn->prepare("SELECT id FROM job_title_table WHERE job_title = ?");
    $stmt->bind_param("s", $job_title);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Job title exists, get the job_title_id
        $job_title_id = $result->fetch_assoc()['id'];
    } else {
        // If job title does not exist, insert it
        $stmt = $conn->prepare("INSERT INTO job_title_table (job_title) VALUES (?)");
        $stmt->bind_param("s", $job_title);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting job title: " . $stmt->error);
        }
        // Get the newly created job_title_id
        $job_title_id = $stmt->insert_id;
    }
    $stmt->close();

    // Step 2: Insert job posting
    $stmt = $conn->prepare("INSERT INTO job_table (company_name, job_title_id, job_title, job_location, job_candidates, job_description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $company_name, $job_title_id, $job_title, $location, $candidates, $description);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting job: " . $stmt->error);
    }

    $job_id = $stmt->insert_id; // Get the inserted job ID for logging
    $stmt->close();

    // Step 3: Log the action using the universal function
    logAuditAction($employee_id, 'Add', 'Job Post', $job_id, "Company: $company_name, Title: $job_title, Location: $location");

    echo json_encode(["message" => "Job posted successfully"]);

} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>
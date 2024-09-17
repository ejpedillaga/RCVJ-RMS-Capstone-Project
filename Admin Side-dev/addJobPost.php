<?php
header('Content-Type: application/json');

include_once("connection.php");

$conn = connection();

try {
    // Collect and validate data from POST request
    $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $job_title = isset($_POST['job_title']) ? trim($_POST['job_title']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $candidates = isset($_POST['candidates']) ? trim($_POST['candidates']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];

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

    // Prepare and bind for job posting
    $stmt = $conn->prepare("INSERT INTO job_table (company_name, job_title, job_location, job_candidates, job_description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $company_name, $job_title, $location, $candidates, $description);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting job: " . $stmt->error);
    }

    // Get the last inserted job ID
    //$job_id = $stmt->insert_id;
    $stmt->close();

    /*
    // Insert skills into skill_table and job_skills_table
    foreach ($skills as $skill) {
        // Check if the skill already exists
        $skill_stmt = $conn->prepare("SELECT skill_id FROM skill_table WHERE skill_name = ?");
        $skill_stmt->bind_param("s", $skill);
        $skill_stmt->execute();
        $result = $skill_stmt->get_result();
        if ($result->num_rows > 0) {
            $skill_id = $result->fetch_assoc()['skill_id'];
        } else {
            // Insert new skill
            $skill_stmt = $conn->prepare("INSERT INTO skill_table (skill_name) VALUES (?)");
            $skill_stmt->bind_param("s", $skill);
            $skill_stmt->execute();
            $skill_id = $skill_stmt->insert_id;
        }
        $skill_stmt->close();

        // Insert into job_skills_table
        $job_skill_stmt = $conn->prepare("INSERT INTO job_skills_table (job_id, skill_id) VALUES (?, ?)");
        $job_skill_stmt->bind_param("ii", $job_id, $skill_id);
        if (!$job_skill_stmt->execute()) {
            throw new Exception("Error inserting job skill: " . $job_skill_stmt->error);
        }
        $job_skill_stmt->close();
    }
    */
    
    echo json_encode(["message" => "Job posted successfully"]);

} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>

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

    // Step 2: Prepare and bind for job posting, including job_title_id and job_title
    $stmt = $conn->prepare("INSERT INTO job_table (company_name, job_title_id, job_title, job_location, job_candidates, job_description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $company_name, $job_title_id, $job_title, $location, $candidates, $description);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting job: " . $stmt->error);
    }

    // Step 3: Insert skills into skill_table and job_skills_table (Commented out for now)
    /*
    foreach ($skills as $skill) {
        // Check if the skill already exists
        $skill_stmt = $conn->prepare("SELECT skill_id FROM skill_table WHERE skill_name = ?");
        $skill_stmt->bind_param("s", $skill);
        $skill_stmt->execute();
        $result = $skill_stmt->get_result();

        if ($result->num_rows > 0) {
            // Skill exists, get skill_id
            $skill_id = $result->fetch_assoc()['skill_id'];
        } else {
            // Insert new skill
            $skill_stmt = $conn->prepare("INSERT INTO skill_table (skill_name) VALUES (?)");
            $skill_stmt->bind_param("s", $skill);
            if (!$skill_stmt->execute()) {
                throw new Exception("Error inserting skill: " . $skill_stmt->error);
            }
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

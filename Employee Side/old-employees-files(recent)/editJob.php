<?php
include_once("connection.php");

$conn = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $jobTitle = $_POST['job_title'];
    $jobLocation = $_POST['job_location'];
    $jobCandidates = intval($_POST['job_candidates']);
    $jobDescription = $_POST['job_description'];
    $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];

    // Update job data
    $sql = "UPDATE job_table SET job_title = ?, job_location = ?, job_candidates = ?, job_description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare statement"]);
        exit;
    }
    $stmt->bind_param('ssisi', $jobTitle, $jobLocation, $jobCandidates, $jobDescription, $jobId);
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Failed to execute update job data"]);
        exit;
    }
    $stmt->close();

    /*
    // Update skills
    $conn->begin_transaction();

    try {
        if (empty($skills)) {
            // $skills is empty
            echo json_encode(["error" => "Skills cannot be empty"]);
            $conn->rollback(); // Rollback transaction
            exit;
        }

        // Remove existing skills for the job
        $delete_stmt = $conn->prepare("DELETE FROM job_skills_table WHERE job_title_id = ?");
        $delete_stmt->bind_param("i", $jobId);
        $delete_stmt->execute();
        $delete_stmt->close();

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
            $job_skill_stmt = $conn->prepare("INSERT INTO job_skills_table (job_title_id, skill_id) VALUES (?, ?)");
            $job_skill_stmt->bind_param("ii", $jobId, $skill_id);
            if (!$job_skill_stmt->execute()) {
                $conn->rollback(); // Rollback transaction
                throw new Exception("Error inserting job skill: " . $job_skill_stmt->error);
            }
            $job_skill_stmt->close();
        }

        $conn->commit(); // Commit transaction
        */
        echo json_encode(["message" => "Job updated successfully"]);
    /*} catch (Exception $e) {
        $conn->rollback(); // Rollback transaction
        echo json_encode(["error" => $e->getMessage()]);
    }*/
    
}

$conn->close();

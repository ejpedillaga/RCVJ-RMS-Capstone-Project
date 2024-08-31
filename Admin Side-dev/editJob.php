<?php
include_once("connection.php");

$conn = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = intval($_POST['id']);
    $jobCompany = $_POST['company_name'];
    $jobTitle = $_POST['job_title'];
    $jobLocation = $_POST['job_location'];
    $jobCandidates = intval($_POST['job_candidates']);
    $jobDescription = $_POST['job_description'];
    $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];

    // Update job data
    $sql = "UPDATE job_table SET company_name = ?, job_title = ?, job_location = ?, job_candidates = ?, job_description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare statement"]);
        exit;
    }
    $stmt->bind_param('sssisi', $jobCompany, $jobTitle, $jobLocation, $jobCandidates, $jobDescription, $jobId);
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Failed to execute update job data"]);
        exit;
    }
    $stmt->close();

    // Update skills
    // Begin transaction
    $conn->begin_transaction();

    // Delete existing skills
    $deleteSkillsSql = "DELETE FROM job_skills_table WHERE job_id = ?";
    $deleteStmt = $conn->prepare($deleteSkillsSql);
    if (!$deleteStmt) {
        echo json_encode(["error" => "Failed to prepare delete statement"]);
        exit;
    }
    $deleteStmt->bind_param('i', $jobId);
    if (!$deleteStmt->execute()) {
        echo json_encode(["error" => "Failed to execute delete skills"]);
        exit;
    }
    $deleteStmt->close();

    // Insert new skills
    $insertSkillsSql = "INSERT INTO job_skills_table (job_id, skill_id) VALUES (?, (SELECT skill_id FROM skill_table WHERE skill_name = ?))";
    $insertStmt = $conn->prepare($insertSkillsSql);
    if (!$insertStmt) {
        echo json_encode(["error" => "Failed to prepare insert statement"]);
        exit;
    }

    foreach ($skills as $skill) {
        $insertStmt->bind_param('is', $jobId, $skill);
        if (!$insertStmt->execute()) {
            echo json_encode(["error" => "Failed to execute insert skill"]);
            exit;
        }
    }
    $insertStmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(["message" => "Job updated successfully"]);
}

$conn->close();


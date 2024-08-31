<?php
include_once("connection.php");

$conn = connection();

// Get the job ID from the query string
$jobId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($jobId > 0) {
    // Fetch job details
    $sql = "SELECT id, company_name, job_title, job_location, job_candidates, job_description FROM job_table WHERE id = $jobId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {    
        $jobData = $result->fetch_assoc();

        // Fetch associated skills
        $skillsSql = "SELECT skill_name FROM skills_table 
                      WHERE skill_id IN (SELECT skill_id FROM job_skills_table WHERE job_id = $jobId)";
        $skillsResult = $conn->query($skillsSql);

        $skills = [];
        while ($row = $skillsResult->fetch_assoc()) {
            $skills[] = $row['skill_name'];
        }

        $jobData['skills'] = $skills;

        // Ensure that you're outputting only JSON
        header('Content-Type: application/json');
        echo json_encode($jobData);
    } else {
        echo json_encode(["error" => "No job found"]);
    }
} else {
    echo json_encode(["error" => "Invalid job ID"]);
}

$conn->close();

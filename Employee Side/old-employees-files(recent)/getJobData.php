<?php
include_once("connection.php");

$conn = connection();

// Get the job ID from the query string
$jobId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($jobId > 0) {
    $sql = "SELECT 
            j.id AS job_id, 
            jt.id AS job_title_id,  -- Correct column name
            jt.job_title, 
            j.company_name,
            j.job_location, 
            j.job_candidates, 
            j.job_description, 
            GROUP_CONCAT(s.skill_name) AS skills
        FROM 
            job_table j
        LEFT JOIN 
            job_title_table jt ON j.job_title_id = jt.id  -- Correct join using `id`
        LEFT JOIN 
            job_skills_table js ON jt.id = js.job_title_id
        LEFT JOIN 
            skill_table s ON js.skill_id = s.skill_id
        WHERE 
            j.id = ?
        GROUP BY 
            j.id";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $jobId); // Bind the job ID
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $jobData = $result->fetch_assoc();

        // Convert the skills string to an array
        $jobData['skills'] = explode(',', $jobData['skills']);

        header('Content-Type: application/json');
        echo json_encode($jobData);
    } else {
        echo json_encode(["error" => "No job found"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid job ID"]);
}

$conn->close();
?>

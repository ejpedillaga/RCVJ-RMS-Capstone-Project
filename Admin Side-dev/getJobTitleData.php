<?php
// This PHP script retrieves the data of a particular job title along with associated skills for editing job titles.

include 'connection.php';

header('Content-Type: application/json');

try {
    // Connect to the database
    $conn = connection(); // Assuming this function establishes the database connection

    // Retrieve the selected job title from the URL parameters
    $currentJobData = isset($_GET['selected_job_title']) ? $_GET['selected_job_title'] : '';

    // Prepare the SQL query
    $sql = "SELECT 
                jt.id,
                jt.job_title, 
                jt.classification, 
                jt.subclassification, 
                jt.gender, 
                jt.educational_attainment, 
                jt.cert_license, 
                jt.years_of_experience,
                GROUP_CONCAT(s.skill_name) AS skills
            FROM 
                job_title_table jt
            LEFT JOIN 
                job_skills_table js ON jt.id = js.job_title_id
            LEFT JOIN 
                skill_table s ON js.skill_id = s.skill_id";

    // If a specific job title is selected, add a WHERE clause
    if (!empty($currentJobData)) {
        // Escape the job title to prevent SQL injection
        $currentJobData = $conn->real_escape_string($currentJobData);
        $sql .= " WHERE jt.job_title = '$currentJobData'";
    }

    // Add the GROUP BY clause to ensure the aggregation works correctly
    $sql .= " GROUP BY jt.id";

    // Execute the query
    $result = $conn->query($sql);

    // Check if any data was returned
    if ($result && $result->num_rows > 0) {
        // Fetch the data
        $jobTitleData = $result->fetch_assoc();

        // Separate years of experience if it's in "min-max" format
        $yearsOfExperience = explode('-', $jobTitleData['years_of_experience']);
        $minYearsOfExperience = isset($yearsOfExperience[0]) ? $yearsOfExperience[0] : '';
        $maxYearsOfExperience = isset($yearsOfExperience[1]) ? $yearsOfExperience[1] : '';

        // Add these values to the response
        $jobTitleData['min_years_of_experience'] = $minYearsOfExperience;
        $jobTitleData['max_years_of_experience'] = $maxYearsOfExperience;

        // Convert the skills string to an array
        $jobTitleData['skills'] = !empty($jobTitleData['skills']) ? explode(',', $jobTitleData['skills']) : [];

        // Return the data as JSON
        echo json_encode(['success' => true, 'data' => $jobTitleData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No job title found.']);
    }

    // Close the connection
    $conn->close();
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

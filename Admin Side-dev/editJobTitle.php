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

        // Retrieve skills only if they exist and are an array
        $new_skills = isset($data['skills']) && is_array($data['skills']) ? $data['skills'] : [];

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

        // Close the statement
        $stmt->close();

        // Handle updating skills only if new skills are provided
        if (!empty($new_skills)) {
            // Step 1: Retrieve the current skills associated with the job title
            $current_skills_stmt = $conn->prepare("SELECT skill_id FROM job_skills_table WHERE job_title_id = ?");
            $current_skills_stmt->bind_param("i", $job_title_id);
            $current_skills_stmt->execute();
            $result = $current_skills_stmt->get_result();
            
            // Get the current skill IDs as an array
            $current_skill_ids = [];
            while ($row = $result->fetch_assoc()) {
                $current_skill_ids[] = $row['skill_id'];
            }
            $current_skills_stmt->close();

            // Step 2: Map new skills to skill IDs (inserting new ones if necessary)
            $new_skill_ids = [];
            foreach ($new_skills as $skill) {
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
                    if (!$skill_stmt->execute()) {
                        throw new Exception("Error inserting skill: " . $skill_stmt->error);
                    }
                    $skill_id = $skill_stmt->insert_id;
                }
                $skill_stmt->close();

                // Add the skill ID to the list of new skill IDs
                $new_skill_ids[] = $skill_id;
            }

            // Step 3: Identify skills to remove (present in current skills but not in new skills)
            $skills_to_remove = array_diff($current_skill_ids, $new_skill_ids);

            // Step 4: Remove obsolete skills from job_skills_table
            if (!empty($skills_to_remove)) {
                $remove_skills_stmt = $conn->prepare("DELETE FROM job_skills_table WHERE job_title_id = ? AND skill_id = ?");
                foreach ($skills_to_remove as $skill_id) {
                    $remove_skills_stmt->bind_param("ii", $job_title_id, $skill_id);
                    $remove_skills_stmt->execute();
                }
                $remove_skills_stmt->close();
            }

            // Step 5: Insert new skills into job_skills_table
            $skills_to_add = array_diff($new_skill_ids, $current_skill_ids); // Skills that need to be added
            if (!empty($skills_to_add)) {
                $insert_skill_stmt = $conn->prepare("INSERT INTO job_skills_table (job_title_id, skill_id) VALUES (?, ?)");
                foreach ($skills_to_add as $skill_id) {
                    $insert_skill_stmt->bind_param("ii", $job_title_id, $skill_id);
                    if (!$insert_skill_stmt->execute()) {
                        throw new Exception("Error inserting job skill: " . $insert_skill_stmt->error);
                    }
                }
                $insert_skill_stmt->close();
            }
        }

        // Return success response
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

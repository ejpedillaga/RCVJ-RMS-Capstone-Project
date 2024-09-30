<?php
include 'connection.php';

header('Content-Type: application/json');

try {
    // Get the data from the AJAX request
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data) {
        $job_title = $data['job_title'];
        $classification = $data['classification'];
        $subclassification = $data['subclassification'];
        $gender = $data['gender'];
        $educational_attainment = $data['educational_attainment'];
        $cert_license = $data['cert_license'];
        $years_of_experience = $data['years_of_experience'];
        $skills = $data['skills'];

        // Connect to the database
        $conn = connection();

        // Insert query for job title
        $query = "INSERT INTO job_title_table (job_title, classification, subclassification, gender, educational_attainment, cert_license, years_of_experience)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $job_title, $classification, $subclassification, $gender, $educational_attainment, $cert_license, $years_of_experience);

        if ($stmt->execute()) {
            // Get the last inserted job ID
            $job_title_id = $stmt->insert_id;
            $stmt->close();

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
                    if (!$skill_stmt->execute()) {
                        throw new Exception("Error inserting skill: " . $skill_stmt->error);
                    }
                    $skill_id = $skill_stmt->insert_id;
                }
                $skill_stmt->close();

                // Insert into job_skills_table
                $job_skill_stmt = $conn->prepare("INSERT INTO job_skills_table (job_title_id, skill_id) VALUES (?, ?)");
                $job_skill_stmt->bind_param("ii", $job_title_id, $skill_id);
                if (!$job_skill_stmt->execute()) {
                    throw new Exception("Error inserting job skill: " . $job_skill_stmt->error);
                }
                $job_skill_stmt->close();
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'No data received']);
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>

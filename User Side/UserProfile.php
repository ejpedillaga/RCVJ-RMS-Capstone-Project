<?php
session_start();

// Initialize arrays to hold user, education, and job data
$user_data = [
    'fname' => '',
    'lname' => '',
    'location' => '',
    'gender' => '',
    'phone' => '',
    'email' => '',
    'birthday' => '',
    'classi' => '',
    'subclassi' => '',
    'userid' => '', 
    'personal_description' => '',
    'profile_image' => ''
];

$education_data = [
    'school' => '',
    'course' => '',
    'sy_started' => '',
    'sy_ended' => '',
];

$vocational_data = [
    'school' => '',
    'course' => '',
    'year_started' => '',
    'year_ended' => '',
];

$job_experience_data = [
    'job_title' => '',
    'company_name' => '',
    'month_started' => '',
    'year_started' => '',
    'month_ended' => '',
    'year_ended' => '',
    'career_history' => ''
];

$license_data = [
    'id' => '',
    'license_name' => '',
    'month_issued' => '',
    'year_issued' => '',
    'month_expired' => '',
    'year_expired' => '',
    'attachment' => ''
];

$user_name = 'User';
$user_location = 'Unknown Location';

// Check if user is logged in
if (isset($_SESSION['user'])) {
    $user_email = $_SESSION['user'];

    $servername = "localhost";
    $username = "root";
    $password = "12345";
    $dbname = "admin_database";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Fetch the user's data to populate the form (from applicant_table)
    $sql = "SELECT userid, fname, lname, location, gender, phone, email, birthday, classi, subclassi, personal_description, profile_image
            FROM applicant_table WHERE email = '$user_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();  // userid is now part of user_data
        $user_name = $user_data['fname'] . ' ' . $user_data['lname'];
        $user_location = $user_data['location'];
        $userid = $user_data['userid'];  // Ensure we have the userid for further operations

        // Convert image data to base64 for display
        $profile_image = base64_encode($user_data['profile_image']);
        $image_src = "data:image/png;base64," . $profile_image; // Adjust MIME type if necessary
    } else {
        echo "Error: User not found in the applicant_table.";
    }

    // Handle personal description form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_description'])) {
        // Fetch and sanitize personal description form data
        $personal_description = $conn->real_escape_string($_POST['description']);

        // Update the user's personal description in the applicant_table
        $sql_update_description = "UPDATE applicant_table SET 
            personal_description = '$personal_description'
            WHERE userid = '$userid'";

        if ($conn->query($sql_update_description) === TRUE) {
            $_SESSION['message'] = "Personal description updated successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "Error updating personal description: " . $conn->error;
        }
    }
    
    // Handle form submission for profile data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
        // Fetch and sanitize profile form data
        $userid = trim($_POST['userid']); // Get userid from the form
        $email = $conn->real_escape_string(trim($_POST['email'])); // Read-only
        $fname = $conn->real_escape_string(trim($_POST['fname']));
        $lname = $conn->real_escape_string(trim($_POST['lname']));
        $gender = $conn->real_escape_string(trim($_POST['gender']));
        $birthday = $conn->real_escape_string(trim($_POST['birthday']));
        $location = $conn->real_escape_string(trim($_POST['location']));
        $phone = $conn->real_escape_string(trim($_POST['phone']));
        $classi = $conn->real_escape_string(trim($_POST['classi']));
        $subclassi = $conn->real_escape_string(trim($_POST['subclassi']));

        // Handle file upload for profile image
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            // Get file contents
            $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);
        }

        // Prepare the SQL update statement
        if ($profile_image !== null) {
            // Prepare with profile image
            $sql_update_profile = "UPDATE applicant_table SET fname = ?, lname = ?, gender = ?, birthday = ?, location = ?, phone = ?, classi = ?, subclassi = ?, profile_image = ? WHERE userid = ?";
            $stmt = $conn->prepare($sql_update_profile);
            $stmt->bind_param("ssssssssbi", $fname, $lname, $gender, $birthday, $location, $phone, $classi, $subclassi, $profile_image, $userid);
            $stmt->send_long_data(8, $profile_image); // Correct index for profile_image
        } else {
            // Prepare without profile image
            $sql_update_profile = "UPDATE applicant_table SET fname = ?, lname = ?, gender = ?, birthday = ?, location = ?, phone = ?, classi = ?, subclassi = ? WHERE userid = ?";
            $stmt = $conn->prepare($sql_update_profile);
            $stmt->bind_param("sssssssss", $fname, $lname, $gender, $birthday, $location, $phone, $classi, $subclassi, $userid);
        }

        // Execute the statement and check for success
        if ($stmt->execute()) {
            $_SESSION['message'] = "Profile updated successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "Error updating profile: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Fetch the education data to populate the education form
    if (isset($userid)) {  // Ensure userid is available before fetching education data
        $sql_edu = "SELECT school, course, sy_started, sy_ended, educational_attainment
                    FROM education_table WHERE userid = '$userid'";
        $result_edu = $conn->query($sql_edu);

        if ($result_edu->num_rows > 0) {
            $education_data = $result_edu->fetch_assoc();
        }
    }

    // Handle form submission for education data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_education'])) {
        $educationalAttainment = $conn->real_escape_string($_POST['educational_attainment']);
        $school = $conn->real_escape_string($_POST['school']);
        $course = $conn->real_escape_string($_POST['course']);
        $sy_started = $conn->real_escape_string($_POST['sy_started']);
        $sy_ended = $conn->real_escape_string($_POST['sy_ended']);

        // Insert or update education data for the user
        $check_sql = "SELECT * FROM education_table WHERE userid = '$userid'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $sql_update_edu = "UPDATE education_table SET
                educational_attainment = '$educationalAttainment',
                school = '$school',
                course = '$course',
                sy_started = '$sy_started',
                sy_ended = '$sy_ended'
                WHERE userid = '$userid'";
            if (!$conn->query($sql_update_edu)) {
                echo "Error updating education data: " . $conn->error;
            }
        } else {
            $sql_insert_edu = "INSERT INTO education_table (userid, school, course, sy_started, sy_ended, educational_attainment) 
                VALUES ('$userid', '$school', '$course', '$sy_started', '$sy_ended', '$educationalAttainment')";
            if (!$conn->query($sql_insert_edu)) {
                echo "Error inserting education data: " . $conn->error;
            }
        }

        $_SESSION['message'] = "Education data saved successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle education deletion
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_education'])) {
        $userId = $conn->real_escape_string($_POST['user_id']);

        // Delete the education record from the database
        $sql_delete_edu = "DELETE FROM education_table WHERE userid = '$userId'";
        if ($conn->query($sql_delete_edu)) {
            $_SESSION['message'] = "Education record deleted successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "Error deleting education record: " . $conn->error;
        }
    }

    // Fetch the vocational data to populate the vocational form
    if (isset($userid)) {  
        $sql_voc = "SELECT school, course, year_started, year_ended
                    FROM vocational_table WHERE userid = '$userid'";
        $result_voc = $conn->query($sql_voc);

        if ($result_voc->num_rows > 0) {
            $vocational_data = $result_voc->fetch_assoc();
        }
    }

    // Handle form submission for vocational data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_vocational'])) {
        $school = $conn->real_escape_string($_POST['school']);
        $course = $conn->real_escape_string($_POST['course']);
        $year_started = $conn->real_escape_string($_POST['year_started']);
        $year_ended = $conn->real_escape_string($_POST['year_ended']);

        // Insert or update vocational data for the user
        $check_sql = "SELECT * FROM vocational_table WHERE userid = '$userid'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $sql_update_voc = "UPDATE vocational_table SET
                school = '$school',
                course = '$course',
                year_started = '$year_started',
                year_ended = '$year_ended'
                WHERE userid = '$userid'";
            if (!$conn->query($sql_update_voc)) {
                echo "Error updating vocational data: " . $conn->error;
            }
        } else {
            $sql_insert_voc = "INSERT INTO vocational_table (userid, school, course, year_started, year_ended) 
                VALUES ('$userid', '$school', '$course', '$year_started', '$year_ended')";
            if (!$conn->query($sql_insert_voc)) {
                echo "Error inserting vocational data: " . $conn->error;
            }
        }

        $_SESSION['message'] = "Vocational data saved successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle vocational deletion
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_vocational'])) {
        $userId = $conn->real_escape_string($_POST['user_id']);

        // Delete the vocational record from the database
        $sql_delete_voc = "DELETE FROM vocational_table WHERE userid = '$userId'";
        if ($conn->query($sql_delete_voc)) {
            $_SESSION['message'] = "Vocational record deleted successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "Error deleting vocational record: " . $conn->error;
        }
    }

    // Fetch all job experience data to populate the job experience form, sorted by the most recent date
    if (isset($userid)) {  // Ensure userid is available before fetching job experience data
        $sql_job = "SELECT job_experience_id, job_title, company_name, month_started, year_started, month_ended, year_ended, career_history 
                    FROM job_experience_table 
                    WHERE userid = '$userid'
                    ORDER BY year_ended DESC, 
                            FIELD(month_ended, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') DESC";
        $result_job = $conn->query($sql_job);

        // Initialize an array to store all job experiences
        $job_experience_data = [];
        
        if ($result_job->num_rows > 0) {
            // Fetch all results and store them in the array
            while ($row = $result_job->fetch_assoc()) {
                $job_experience_data[] = $row;
            }
        }
    }

    // Handle form submission for job experience data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_job_experience'])) {
        // Fetch job experience ID if editing
        $job_experience_id = isset($_POST['job_experience_id']) ? (int)$_POST['job_experience_id'] : null;

        // Fetch and sanitize job experience form data
        $job_title = $conn->real_escape_string($_POST['job-title']);
        $company_name = $conn->real_escape_string($_POST['company-name-field']);
        $month_started = $conn->real_escape_string($_POST['month_started']);
        $year_started = (int) $_POST['year_started'];
        $month_ended = $conn->real_escape_string($_POST['month_ended']);
        $year_ended = !empty($_POST['year_ended']) ? (int) $_POST['year_ended'] : null;
        $career_history = $conn->real_escape_string($_POST['career_history']);

        if ($job_experience_id) {
            // Update existing job experience data
            $sql_update_job = "UPDATE job_experience_table SET 
                job_title = '$job_title', 
                company_name = '$company_name', 
                month_started = '$month_started', 
                year_started = $year_started, 
                month_ended = '$month_ended', 
                year_ended = " . ($year_ended !== null ? $year_ended : "NULL") . ", 
                career_history = '$career_history' 
                WHERE job_experience_id = $job_experience_id AND userid = '$userid'";

            if (!$conn->query($sql_update_job)) {
                echo "Error updating job experience data: " . $conn->error;
            } else {
                $_SESSION['message'] = "Job experience data updated successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            // Insert new job experience data for the user
            $sql_insert_job = "INSERT INTO job_experience_table (userid, job_title, company_name, month_started, year_started, month_ended, year_ended, career_history) 
                VALUES ('$userid', '$job_title', '$company_name', '$month_started', $year_started, '$month_ended', " . ($year_ended !== null ? $year_ended : "NULL") . ", '$career_history')";

            if (!$conn->query($sql_insert_job)) {
                echo "Error inserting job experience data: " . $conn->error;
            } else {
                $_SESSION['message'] = "Job experience data saved successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    }

    // Handle deletion of job experience data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_job_experience'])) {
        $job_experience_id = (int)$_POST['job_experience_id'];

        // SQL query to delete the job experience
        $sql_delete_job = "DELETE FROM job_experience_table WHERE job_experience_id = $job_experience_id AND userid = '$userid'";

        if (!$conn->query($sql_delete_job)) {
            echo "Error deleting job experience data: " . $conn->error;
        } else {
            $_SESSION['message'] = "Job experience data deleted successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // License
    if (isset($userid)) {
        // Fetch all license data for the user, ordered by expiration date
        $sql_license = "SELECT id, license_name, month_issued, year_issued, month_expired, year_expired, attachment 
                        FROM certification_license_table 
                        WHERE userid = '$userid' 
                        ORDER BY year_expired DESC, 
                                FIELD(month_expired, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') DESC";
        $result_license = $conn->query($sql_license);

        // Initialize an array to store all licenses
        $license_data = [];

        if ($result_license->num_rows > 0) {
            while ($row = $result_license->fetch_assoc()) {
                // If you want to include the attachment in the license data
                $row['attachment'] = base64_encode($row['attachment']); // Encode the BLOB for display
                $license_data[] = $row;
            }
        }
    }

   // Handle form submission for license data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_license'])) {
        // Fetch and sanitize license form data
        $license_name = trim($_POST['license_name']); 
        $month_issued = $conn->real_escape_string(trim($_POST['month_issued'])); 
        $year_issued = (int)$_POST['year_issued'];
        $month_expired = $conn->real_escape_string(trim($_POST['month_expired']));
        $year_expired = !empty($_POST['year_expired']) ? (int)$_POST['year_expired'] : null;

        // Handle file upload
        if (isset($_FILES['license_attachment']) && $_FILES['license_attachment']['error'] == 0) {
            // Get file contents
            $attachment = file_get_contents($_FILES['license_attachment']['tmp_name']);

            if ($license_id) {
                // Update existing license data with attachment
                $sql_update_license = "UPDATE certification_license_table SET license_name = ?, month_issued = ?, year_issued = ?, month_expired = ?, year_expired = ?, attachment = ? WHERE id = ? AND userid = ?";
                $stmt = $conn->prepare($sql_update_license);
                $stmt->bind_param("ssissbsi", $license_name, $month_issued, $year_issued, $month_expired, $year_expired, $attachment, $license_id, $userid);
                $stmt->send_long_data(5, $attachment); // Send the BLOB data
            } else {
                // Insert new license data for the user with attachment
                $sql_insert_license = "INSERT INTO certification_license_table (userid, license_name, month_issued, year_issued, month_expired, year_expired, attachment) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_insert_license);
                $stmt->bind_param("isssssb", $userid, $license_name, $month_issued, $year_issued, $month_expired, $year_expired, $attachment);
                $stmt->send_long_data(6, $attachment); // Send the BLOB data
            }

            if ($stmt->execute()) {
                $_SESSION['message'] = "License data saved successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                echo "Error saving license data: " . $stmt->error;
            }
        } else {
            // Detailed error output
            if (isset($_FILES['license_attachment']['error'])) {
                echo "File upload error: " . $_FILES['license_attachment']['error'];
            } else {
                echo "Error uploading file.";
            }
        }
    }

    // Handle deletion of license data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_license'])) {
        $license_id = (int)$_POST['license_id'];

        // SQL query to delete the license
        $sql_delete_license = "DELETE FROM certification_license_table WHERE id = $license_id AND userid = '$userid'";

        if (!$conn->query($sql_delete_license)) {
            echo "Error deleting license data: " . $conn->error;
        } else {
            $_SESSION['message'] = "License data deleted successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Fetch the user's skills if userid is set
    if (isset($userid)) {
        $sql_skills = "SELECT s.skill_name
                    FROM skill_table s
                    JOIN user_skills_table us ON s.skill_id = us.skill_id
                    WHERE us.userid = '$userid'";
        $result_skills = $conn->query($sql_skills);

        if ($result_skills->num_rows > 0) {
            // Collect all skills into an array
            $skills = [];
            while ($row = $result_skills->fetch_assoc()) {
                $skills[] = $row['skill_name'];
            }
            // Encode the skills array into JSON for use in JavaScript
            $skills_json = json_encode($skills);
        } else {
            $skills_json = json_encode([]);
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['skills_json'])) {
        $skills_json = $_POST['skills_json'];
        $skills = json_decode($skills_json, true); // Decode the JSON array
    
        // Fetch the existing skills for the user
        $sql_existing_skills = "SELECT s.skill_id, s.skill_name 
                                FROM skill_table s
                                JOIN user_skills_table us ON s.skill_id = us.skill_id
                                WHERE us.userid = ?";
        $stmt_existing = $conn->prepare($sql_existing_skills);
        $stmt_existing->bind_param('i', $userid);
        $stmt_existing->execute();
        $existing_skills_result = $stmt_existing->get_result();
        $existing_skills = [];
        while ($row = $existing_skills_result->fetch_assoc()) {
            $existing_skills[$row['skill_id']] = $row['skill_name'];
        }
    
        // Convert skills array to associative array for easy lookup
        $new_skills = array_flip($skills);
    
        // Determine skills to add
        $skills_to_add = array_diff($skills, $existing_skills);
    
        // Determine skills to delete
        $skills_to_delete = array_diff($existing_skills, $skills);
    
        // Add new skills
        foreach ($skills_to_add as $skill_name) {
            $sql_check_skill = "SELECT skill_id FROM skill_table WHERE skill_name = ?";
            $stmt_check = $conn->prepare($sql_check_skill);
            $stmt_check->bind_param('s', $skill_name);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                // Skill exists
                $skill_id = $result_check->fetch_assoc()['skill_id'];
            } else {
                // Insert new skill
                $sql_insert_skill = "INSERT INTO skill_table (skill_name) VALUES (?)";
                $stmt_insert = $conn->prepare($sql_insert_skill);
                $stmt_insert->bind_param('s', $skill_name);
                if ($stmt_insert->execute()) {
                    $skill_id = $stmt_insert->insert_id;
                } else {
                    echo "Error inserting skill: " . $conn->error;
                    continue;
                }
            }
    
            // Insert skill into user_skills_table
            $sql_insert_user_skill = "INSERT INTO user_skills_table (userid, skill_id) VALUES (?, ?)";
            $stmt_user_skill = $conn->prepare($sql_insert_user_skill);
            $stmt_user_skill->bind_param('ii', $userid, $skill_id);
            if (!$stmt_user_skill->execute()) {
                echo "Error inserting user skill: " . $conn->error;
            }
        }
    
        // Delete removed skills
        foreach ($skills_to_delete as $skill_id => $skill_name) {
            $sql_delete_user_skill = "DELETE FROM user_skills_table WHERE userid = ? AND skill_id = ?";
            $stmt_delete_user_skill = $conn->prepare($sql_delete_user_skill);
            $stmt_delete_user_skill->bind_param('ii', $userid, $skill_id);
            if (!$stmt_delete_user_skill->execute()) {
                echo "Error deleting user skill: " . $conn->error;
            }
        }
    
        $_SESSION['message'] = "Skills updated successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Initialize variable to track if a resume exists
    $resume_exists = false;

    // Check if the form is submitted for saving the resume
    if (isset($_POST['save_resume'])) {
        // Check if a file was uploaded
        if (isset($_FILES['files']) && $_FILES['files']['error'] == 0) {
            $fileData = file_get_contents($_FILES['files']['tmp_name']); // Get file content

            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO resume_table (userid, resume, uploaded_at) VALUES (?, ?, NOW())");
            // Change the parameter binding: "i" for integer userid and "s" for string resume data
            $stmt->bind_param("is", $userid, $fileData); // Use "s" for string/BLOB

            if ($stmt->execute()) {
                $_SESSION['message'] = "Resume uploaded successfully!";
            } else {
                $_SESSION['message'] = "Failed to upload resume: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $_SESSION['message'] = "No file was uploaded or there was an error!";
        }
    }

     // Check for existing resume in the database
    $stmt = $conn->prepare("SELECT resume FROM resume_table WHERE userid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    // If a resume exists, set the flag and fetch the data
    if ($result->num_rows > 0) {
        $resume_exists = true;
        $row = $result->fetch_assoc();
        $resume_data = $row['resume'];
    }

    if (isset($_POST['delete_resume'])) {
        // Prepare SQL statement to delete the existing resume
        $stmt = $conn->prepare("DELETE FROM resume_table WHERE userid = ?");
        $stmt->bind_param("i", $userid); // Bind the user ID
    
        if ($stmt->execute()) {
            $_SESSION['message'] = "Resume deleted successfully!";
        } else {
            $_SESSION['message'] = "Failed to delete resume: " . $stmt->error;
        }
    
        $stmt->close();
    
        // Refresh the page to reflect the changes
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Fetch skills from the skill_table
    $skillQuery = "SELECT skill_name FROM skill_table";
    $skillResult = $conn->query($skillQuery);

    // Create an array to hold skill names
    $availableSkills = [];

    if ($skillResult->num_rows > 0) {
        while($row = $skillResult->fetch_assoc()) {
            $availableSkills[] = $row['skill_name'];
        }
    }

    // Convert the PHP array to JSON
    $availableSkills_json = json_encode($availableSkills);

    $stmt->close();
    $conn->close();
}

// Display success message if available
if (isset($_SESSION['message'])) {
    echo "<script type='text/javascript'>
            alert('{$_SESSION['message']}');
            $(document).ready(function() {
                $('#successModal').modal('show');
            });
          </script>";
    unset($_SESSION['message']);  // Clear the message after displaying
}
?>


<!DOCTYPE html>
<html>
    <head>
        <title>RCVJ, Inc.</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
        <link rel="stylesheet" href="mediaqueries.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </head>
    <body>
        <!--Desktop Nav(Full screen)-->
        <nav class="desktopnav" id="desktop-nav">
            <div class="logo">
                <img src="images/logo.png" alt="">
            </div>
            <div>
                <ul class="nav-links">
                    <li><a href="Home.php">Home</a></li>
                    <li><a href="Jobs.php">Jobs</a></li>
                    <li><a href="About.php">About</a></li>
                    <li><a href="Partner.php">Partner Companies</a></li>
                </ul>
            </div>
            <div class="nav-acc">
                <!--Notification-->
                <div class="notification_wrap">
                    <div class="notification_icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="dropdown">
                        <div class="notify_item">
                            <div class="notify_info">
                                <p>Application on<span>[JOB TITLE]</span>was rejected.</p>
                                <span class="company_name">Company Name</span>
                            </div>
                        </div>
                        <div class="notify_item">
                            <div class="notify_info">
                                <p>Interview on<span>[JOB TITLE]</span>was scheduled.</p>
                                <span class="company_name">Company Name</span>
                            </div>
                        </div>
                        <div class="notify_item">
                            <div class="notify_info">
                                <p>Deployment on<span>[JOB TITLE]</span>is on process.</p>
                                <span class="company_name">Company Name</span>
                            </div>
                        </div>
                    </div>
                </div>
                    <img src="images/user.svg" alt="">
                    <button><?php echo htmlspecialchars($user_name); ?></button>
                </div>
            </nav>

            <!---Burger Nav (900px screen size)-->
            <nav id="hamburger-nav">
                <div class="logo">
                    <img src="images/logo.png" alt="">
                </div>
                <div class="hamburger-menu">
                    <div class="nav-icons">
                        <div class="notification_wrap">
                            <div class="notification_icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="dropdown">
                                <div class="notify_item">
                                    <div class="notify_info">
                                        <p>Application on<span>[JOB TITLE]</span>was rejected.</p>
                                        <span class="company_name">Company Name</span>
                                    </div>
                                </div>
                                <div class="notify_item">
                                    <div class="notify_info">
                                        <p>Interview on<span>[JOB TITLE]</span>was scheduled.</p>
                                        <span class="company_name">Company Name</span>
                                    </div>
                                </div>
                                <div class="notify_item">
                                    <div class="notify_info">
                                        <p>Deployment on<span>[JOB TITLE]</span>is on process.</p>
                                        <span class="company_name">Company Name</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hamburger-icon" onclick="toggleMenu()">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="menu-links">
                        <li><a class="active" href="#" onclick="toggleMenu()">Home</a></li>
                        <li><a href="Jobs.php" onclick="toggleMenu()">Jobs</a></li>
                        <li><a href="About.php" onclick="toggleMenu()">About</a></li>
                        <li><a href="Partner.php" onclick="toggleMenu()">Partner Companies</a></li>
                        <div class="nav-acc">
                            <img src="images/user.svg" alt="">
                            <button id="profile"><?php echo htmlspecialchars($user_name); ?></button>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!--Content goes here-->
            <!--Usual Structure-->
            <section class="profile-section">
                <!--Edit Profile Sidenav-->
                <div id="editProfile-sidenav" class="sidenav">
                    <div class="sidenav-header">Edit Profile</div>
                    <div class="edit-profile-form">
                    <form action="" method="POST" enctype="multipart/form-data"> 
                    <input type="hidden" name="userid" value="<?php echo htmlspecialchars($user_data['userid']); ?>">
                        <!-- Upload Logo -->
                        <div class="upload-image-group">
                            <div class="upload-image" onclick="document.getElementById('logo-upload').click()">
                                <input type="file" id="logo-upload" name="profile_image" accept="image/*" onchange="previewLogo(event)" style="display: none;">
                                <img id="logo-preview" src="<?php echo isset($image_src) ? $image_src : ''; ?>" alt="Upload Logo" style="width: 100%; <?php echo isset($image_src) ? 'display: block;' : 'display: none;'; ?>">
                                <div id="upload-placeholder" style="<?php echo isset($image_src) ? 'display: none;' : 'display: block;'; ?>">Upload Logo</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div>
                                <label class="label" for="first-name">First Name</label>
                                <input type="text" id="first-name" name="fname" class="input-field" value="<?php echo htmlspecialchars($user_data['fname']); ?>">
                            </div>

                            <div>
                                <label class="label" for="last-name">Last Name</label>
                                <input type="text" id="last-name" name="lname" class="input-field" value="<?php echo htmlspecialchars($user_data['lname']); ?>">
                            </div>
                        </div>

                        <div id="location-group" class="form-group">
                            <div>
                                <label class="label" for="location">Location</label>
                                <input type="text" id="location" name="location" class="input-field" value="<?php echo htmlspecialchars($user_data['location']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label class="label" for="gender">Gender</label>
                                <select id="gender" name="gender" class="select-field">
                                    <option value="Male" <?php if ($user_data['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if ($user_data['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>

                            <div>
                                <label class="label" for="contact-number">Contact Number</label>
                                <input type="tel" id="contact-number" name="phone" class="input-field" value="<?php echo htmlspecialchars($user_data['phone']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label class="label" for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="input-field" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
                            </div>

                            <div>
                                <label class="label" for="birthday">Birthday</label>
                                <input type="date" id="birthday" name="birthday" class="input-field" value="<?php echo htmlspecialchars($user_data['birthday']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <label class="label" for="classification">Classification</label>
                                <select id="classification" name="classi" class="select-field">
                                    <option value="Sales" <?php if ($user_data['classi'] == 'Sales') echo 'selected'; ?>>Sales</option>
                                    <!-- Add more classification options as needed -->
                                </select>
                            </div>

                            <div>
                                <label class="label" for="sub-classification">Sub-Classification</label>
                                <select id="sub-classification" name="subclassi" class="select-field">
                                    <option value="Management" <?php if ($user_data['subclassi'] == 'Management') echo 'selected'; ?>>Management</option>
                                    <!-- Add more sub-classification options as needed -->
                                </select>
                            </div>
                        </div>

                        <div id="button-group" class="form-group">
                            <button type="submit" name="save_profile" class="button">Save</button>
                        </div>
                    </form>

                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav('editProfile-sidenav', 'profile-container')">&times;</a>
                    </div>
                </div>

                <!-- Personal Description Sidenav -->
                <div id="personal-description-sidenav" class="sidenav">
                    <div class="sidenav-header">Add some description about <br> yourself<br>
                        <p>Describe people who you are</p>
                    </div>
                    <div class="personal-description-form">
                        <form action="" method="POST">
                            <div class="form-group">
                                <div>
                                    <textarea id="description" name="description" class="textarea" rows="20" cols="80"><?php echo htmlspecialchars($user_data['personal_description']); ?></textarea>
                                </div>
                            </div>
                            <div id="button-group" class="form-group">
                                <button type="submit" name="save_description" class="button">Save</button>
                            </div>
                        </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('personal-description-sidenav', 'profile-container')">&times;</a>
                </div>

                <!--Past Jobs Sidenav-->
                <div id="past-jobs-sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">Past Jobs</div>
                        <div class="past-jobs-form sidenav-content">
                            <form action="" method="POST">
                                <!-- Hidden field for job experience ID -->
                                <input type="hidden" name="job_experience_id" value="<?php echo isset($job_experience_id) ? $job_experience_id : ''; ?>">

                                <div class="form-group sidenav-content">
                                    <div>
                                        <label class="label" for="job-title">Job Title</label>
                                        <input type="text" id="job-title" name="job-title" class="input-field" value="">
                                    </div>
                                </div>

                                <div class="form-group sidenav-content">
                                    <div>
                                        <label class="label" for="company-name-field">Company Name</label>
                                        <input type="text" id="company-name-field" name="company-name-field" class="input-field" value="">
                                    </div>
                                </div>

                                <label class="label" for="started_group">Started</label>
                                <div id="started_group" class="form-group">
                                    <div>
                                    <select id="month_started" name="month_started" class="select-field">
                                        <option value="" disabled selected>Select Month</option>
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                    </div>
                                    <div>
                                    <select id="year_started" name="year_started" class="select-field">
                                        <option value="" disabled selected>Select Year</option>
                                        <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    </div>
                                </div>

                                <label class="label" for="ended_group">Ended</label>
                                <div id="ended_group" class="form-group">
                                    <div>
                                    <select id="month_ended" name="month_ended" class="select-field">
                                        <option value="" disabled selected>Select Month</option>
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                    </div>
                                    
                                    <div>
                                    <select id="year_ended" name="year_ended" class="select-field">
                                        <option value="" disabled selected>Select Year</option>
                                        <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="career_history">Tell something about your career history</label>
                                        <textarea id="career_history" name="career_history" class="textarea" rows="10" cols="80"></textarea>
                                    </div>
                                </div>

                                <div id="button-group" class="form-group">
                                    <button class="button" name="save_job_experience" type="submit">Save</button>
                                </div>
                            </form>
                        </div>
                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav('past-jobs-sidenav', 'profile-container')">&times;</a>
                    </div>

                    <!-- Education Sidenav -->
                    <div id="education_sidenav" class="sidenav">
                        <div class="sidenav-header sidenav-content">Educational Attainment</div>
                        <div class="education-form sidenav-content">
                        <form action="" method="POST">

                            <div class="form-group">
                                <label class="label" for="educational_attainment">Educational Attainment</label>
                                <select name="educational_attainment" id="educational_attainment" class="select-field" required>
                                    <option value="" disabled <?php if (empty($education_data['educational_attainment'])) echo 'selected'; ?>>Select an educational attainment</option>
                                    <option value="Highschool Graduate" <?php if (!empty($education_data['educational_attainment']) && $education_data['educational_attainment'] == 'Highschool Graduate') echo 'selected'; ?>>Highschool Graduate</option>
                                    <option value="College Graduate" <?php if (!empty($education_data['educational_attainment']) && $education_data['educational_attainment'] == 'College Graduate') echo 'selected'; ?>>College Graduate</option>
                                    <option value="Undergraduate" <?php if (!empty($education_data['educational_attainment']) && $education_data['educational_attainment'] == 'Undergraduate') echo 'selected'; ?>>Undergraduate</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <div>
                                    <label class="label" for="school">School</label>
                                    <input type="text" id="school" name="school" class="input-field" value="<?php echo htmlspecialchars($education_data['school']); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div>
                                    <label class="label" for="course">Grade Level / Course</label>
                                    <input type="text" id="course" name="course" class="input-field" value="<?php echo htmlspecialchars($education_data['course']); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="year-container">
                                    <div>
                                        <label class="label" for="sy_started">Year Started</label>
                                        <select id="sy_started" name="sy_started" class="select-field" required>
                                            <option value="" disabled <?php if (empty($education_data['sy_started'])) echo 'selected'; ?>>Select Year</option>
                                            <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                                <option value="<?php echo $year; ?>" <?php if ($education_data['sy_started'] == $year) echo 'selected'; ?>><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label" for="sy_ended">Year Ended</label>
                                        <select id="sy_ended" name="sy_ended" class="select-field" required>
                                            <option value="" disabled <?php if (empty($education_data['sy_ended'])) echo 'selected'; ?>>Select Year</option>
                                            <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                                <option value="<?php echo $year; ?>" <?php if ($education_data['sy_ended'] == $year) echo 'selected'; ?>><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="button-group" class="form-group sidenav-content">
                                <button class="button" name="save_education" type="submit">Save</button>
                            </div>
                            </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('education_sidenav', 'profile-container')">&times;</a>
                </div>

                <!-- Vocational Sidenav -->
                <div id="vocational_sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">Vocational Training</div>
                    <div class="education-form sidenav-content">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label class="label" for="course">Vocational Course</label>
                            <input type="text" id="course" name="course" class="input-field" value="<?php echo htmlspecialchars($vocational_data['course']); ?>" required>
                        </div>

                        <div class="form-group">
                            <div>
                                <label class="label" for="school">Vocational Institute</label>
                                <input type="text" id="school" name="school" class="input-field" value="<?php echo htmlspecialchars($vocational_data['school']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="year-container">
                                <div>
                                    <label class="label" for="vy_started">Year Started</label>
                                    <select id="vy_started" name="year_started" class="select-field" required>
                                        <option value="" disabled <?php if (empty($vocational_data['year_started'])) echo 'selected'; ?>>Select Year</option>
                                        <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                            <option value="<?php echo $year; ?>" <?php if ($vocational_data['year_started'] == $year) echo 'selected'; ?>><?php echo $year; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="label" for="vy_ended">Year Ended</label>
                                    <select id="vy_ended" name="year_ended" class="select-field" required>
                                        <option value="" disabled <?php if (empty($vocational_data['year_ended'])) echo 'selected'; ?>>Select Year</option>
                                        <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                            <option value="<?php echo $year; ?>" <?php if ($vocational_data['year_ended'] == $year) echo 'selected'; ?>><?php echo $year; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="button-group" class="form-group sidenav-content">
                            <button class="button" name="save_vocational" type="submit">Save</button>
                        </div>
                    </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('vocational_sidenav', 'profile-container')">&times;</a>
                </div>

                <!--License and Education Sidenav-->
                <div id="LnE-sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">Add License/Certificate
                        <br>
                        <p>Showcase your licenses, certificates, memberships, and accreditations.</p>
                    </div>
                    
                    <div class="LnE-form sidenav-content">
                        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                            <!-- Hidden field for license ID if editing -->
                            <input type="hidden" name="license_id" value="<?php echo isset($license_data['id']) ? $license_data['id'] : ''; ?>">

                            <div id="license_group" class="form-group sidenav-content">
                                <label for="license" class="label">License/Certificate Name</label>
                                <input type="text" id="license" name="license_name" class="input-field" value="" required>
                            </div>

                            <label for="issue_date_group" class="label sidenav-content">Issue Date</label>
                            <div id="issue_date_group" class="form-group sidenav-content">                              
                                <div>
                                    <select id="month_issued" class="select-field" name="month_issued" required>
                                    <option value="" disabled selected>Select Month</option>
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                </div>
                                <div>
                                    <select id="year_issued" name="year_issued" class="select-field" required>
                                        <option value="" disabled selected>Select Year</option>
                                        <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <label for="expiry_date_group" class="label sidenav-content">Expiry Date</label>
                            <div id="expiry_date_group" class="form-group sidenav-content">
                                <div>
                                    <select id="month_expired" name="month_expired" class="select-field" required>
                                        <option value="" disabled selected>Select Month</option>
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                </div>
                                <div>
                                    <select id="year_expired" name="year_expired" class="select-field" required>
                                        <option value="" disabled selected>Select Year</option>
                                        <?php for ($year = 2000; $year <= 2030; $year++): ?>
                                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                          
                            <!--License Dropbox--> 
                            <div id="license_dropbox" class="form-group">
                                <img src="images/resume-dropbox.png" alt="">
                                <label for="licenseFileUpload" style="display: block; margin-top: 10px;">
                                Drag and drop here or simply <span style="color: #007BFF; cursor: pointer;">browse</span> for an <br>image/file to upload.
                                </label>
                                <input type="file" id="licenseFileUpload" class="file-input" name="license_attachment" accept=".jpg,.jpeg,.png" multiple onchange="previewLicenseFiles()">
                                <button type="button" class="button" onclick="document.getElementById('licenseFileUpload').click()">Browse</button>
                            </div>
                            
                            <div class="preview" id="licensePreviewContainer"></div>
                            <p>Note: Upload a clear picture of your license/certificate for better evaluation.</p>


                            <div id="button-group" class="form-group">
                                <button class="button" type="submit" name="save_license">Save</button>
                            </div>
                        </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('LnE-sidenav', 'profile-container')">&times;</a>
                </div>

                <!--Skills Sidenav-->
                <div id="skills_sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">
                        Add Skills
                        <br>
                        <p>Help employers find you by showcasing all of your skills.</p>
                    </div>

                    <div class="skills-form sidenav-content">
                        <form id="skills_form" method="POST">
                            <label for="add_skills_group" class="sidenav-content">Add skill/s</label>

                            <div id="add_skills_group" class="skills-input-box">
                                <div class="skills-row">
                                    <input type="text" id="skills" name="skills[]" class="input-field" placeholder="Enter skills" autocomplete="off">   
                                    <button id="add_skill_btn" class="button" type="button">Add</button>                  
                                </div>     
                                <div class="result-box">
                                        <!--Skills are fetched here-->
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="added_skills">Added skill/s</label>
                                <ul id="added_skills_list"></ul>
                            </div>

                            <div id="button-group" class="form-group sidenav-content">
                                <button class="button" type="submit">Save</button>
                            </div>
                        </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('skills_sidenav', 'profile-container')">&times;</a>
                </div>
                
                <div id="overlay" class="overlay"></div>
                 <!--Resume Sidenav-->
                <div id="resume_sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">
                      Add Resume<br>
                      <p>Your default resume can be viewed by employers.</p>
                      <p style="color: red;">Note: Upload a PDF file only.</p>
                    </div>
                    <!--Resume Dropbox--> 
                    <div class="resume-form sidenav-content">
                      <form action="" method="post" enctype="multipart/form-data">
                        <div id="resume_dropbox" class="form-group">
                            <img src="images/resume-dropbox.png" alt="">
                            <label for="fileUpload" style="display: block; margin-top: 10px;">
                            Drag and drop here or simply <span style="color: #007BFF; cursor: pointer;">browse</span> for a file to upload your resume.
                            </label>
                            <input type="file" id="fileUpload" class="file-input" name="files" accept=".pdf" onchange="previewFiles()" >
                            <button type="button" class="button" onclick="document.getElementById('fileUpload').click()">Browse</button>
                            </div>
                            <div class="preview" id="previewContainer"></div>
                        </div>
                        <div id="button-group" class="form-group">
                            <button class="button" type="submit" name="save_resume">Save</button>
                        </div>
                    </form>
    
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('resume_sidenav', 'profile-container')">&times;</a>
                </div>

                <!--Profile Container-->
                <div id="profile-container" class="main-container">
                    <!--Header-->
                    <div class="profile-header">

                        <div class="profile-card">
                            <div class="content">
                                <?php if (!empty($user_data['profile_image'])): ?>
                                    <img id="profile-picture" class="profile-photo" src="data:image/jpeg;base64,<?php echo base64_encode($user_data['profile_image']); ?>" alt="">
                                <?php else: ?>
                                    <img id="profile-picture" src="images/profileicon.svg" alt="">
                                <?php endif; ?>
                                <div>
                                    <h1><?php echo htmlspecialchars($user_name); ?></h1>
                                    <div class="profile-contacts">
                                        <div class="user-address">
                                            <img class="profile-logo" src="images/image 29.svg" alt="">
                                            <p><?php echo htmlspecialchars($user_location); ?></p>
                                        </div>
                                        
                                        <div class="user-email">
                                            <img class="profile-logo" src="images/image 30.svg" alt="">
                                            <p><?php echo htmlspecialchars($user_email); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="profile-edit">
                                    <img src="images/image 31.svg" onclick="openNav('editProfile-sidenav', 'profile-container')" alt="">
                                </div>
                            </div>
                        </div>
                        <div id="my-jobs-card" onclick="redirectTo('MyJobs.php')" class="profile-card">
                            <div class="content">
                                <div class="mjc-header">
                                    <div class="my-jobs">
                                        <p>My Jobs</p>
                                    </div>
                                    <div>
                                         <img id="arrow" src="images/image 37.png" alt="">
                                    </div>
                                </div>
    
                                <div id="mjcdiv">
                                    <img id="mjc-image" src="images/my-jobs-image.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!--Content-->
                    <div class="profile-body">
                        <div class="sections">
                            <div class="section">
                                <h3>Personal Description</h3>
                                <p>Add a personal description to your profile as a way to introduce who you are.</p>
                                <?php if (!empty($user_data['personal_description'])): ?>
                                    <div class="info-container">
                                        <p style="text-align: justify;"><?php echo htmlspecialchars($user_data['personal_description']); ?></p>
                                    </div>
                                <?php endif; ?>
                                <button onclick="openNav('personal-description-sidenav', 'profile-container')">Edit</button>
                            </div>
                            <div class="section">
                                <h3>Licences & Certificates</h3>
                                <p>Showcase your professional credentials. Add your relevant licences, certificates, memberships, and accreditations here.</p>
                                
                                <div class="license-container">
                                    <?php if (!empty($license_data)): ?>
                                        <?php foreach ($license_data as $license): ?>
                                            <div class="info-container">
                                                <div class="icon-group">
                                                    <div class="delete-icon" onclick="deleteLicense(<?php echo $license['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </div>
                                                </div>
                                                <h4><?php echo htmlspecialchars($license['license_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($license['month_issued']); ?> <?php echo htmlspecialchars($license['year_issued']); ?> - <?php echo htmlspecialchars($license['month_expired']); ?> <?php echo htmlspecialchars($license['year_expired']); ?></p>
                                                <p style="margin: 1rem 0rem;">Your attached image:</p>
                                                
                                                <?php if (!empty($license['attachment'])): ?>
                                                    <img src="data:image/jpeg;base64,<?php echo htmlspecialchars($license['attachment']); ?>" alt="License Attachment" style="max-width: 300px; height: auto;" />
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button onclick="openNav('LnE-sidenav', 'profile-container'); resetLicenseForm()">Add</button>
                            </div>
                            <div class="section">
                                <h3>Past Jobs</h3>
                                <p>The more you let employers know about your experience, the more you can stand out.</p>
                                
                                <div class="past-jobs-container">
                                <?php if (!empty($job_experience_data)): ?>
                                    <?php foreach ($job_experience_data as $job): ?>
                                        <div class="info-container">
                                            <div class="icon-group">
                                                <div class="edit-icon" onclick="openNav('past-jobs-sidenav', 'profile-container'); populateJobExperience(<?php echo htmlspecialchars(json_encode($job)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </div>
                                                <div class="delete-icon" onclick="deleteJobExperience(<?php echo $job['job_experience_id']; ?>)"> 
                                                    <i class="fas fa-trash"></i>
                                                </div>
                                            </div>
                                            <h4 id="pj-jt"><?php echo htmlspecialchars($job['job_title']); ?></h4>
                                            <p id="pj-cn"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                            <p id="pj-year">
                                                <?php echo htmlspecialchars($job['month_started']); ?> 
                                                <?php echo htmlspecialchars($job['year_started']); ?> - 
                                                <?php echo htmlspecialchars($job['month_ended']); ?> 
                                                <?php echo htmlspecialchars($job['year_ended']); ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </div>
                                <button onclick="openNav('past-jobs-sidenav', 'profile-container'); resetJobExperienceForm()">Add</button>
                            </div>
                            <div class="section">
                                <h3>Skills</h3>
                                <p>Let employers know how valuable you can be to them.</p>
                                <?php if (!empty($skills)): ?>
                                <div class="info-container">
                                    <ul id="user_skills_list">
                                        <!-- Skills will be populated here -->
                                    </ul>
                                </div>
                                <?php endif; ?>
                                <button onclick="openNav('skills_sidenav', 'profile-container')">Add</button>
                            </div>
                            <div class="section">
                                <h3>Education</h3>
                                <p>Tell employers about your education.</p>
                                <h4>Educational Attainment</h4>
                                <p>Provide details of your highest level of education completed.</p>
                                <?php if (!empty($education_data['school'])): ?>
                                    <div class="info-container">
                                        <div class="icon-group">
                                            <div class="edit-icon" onclick="openNav('education_sidenav', 'profile-container')">
                                                <i class="fas fa-edit"></i>
                                            </div>
                                            <div class="delete-icon" onclick="deleteEducation('<?php echo htmlspecialchars($userid); ?>')"> 
                                                <i class="fas fa-trash"></i>
                                            </div>
                                        </div>
                                        <h4 id="educ-course"><?php echo htmlspecialchars($education_data['course']); ?></h4>
                                        <p id="educ-school"><?php echo htmlspecialchars($education_data['school']); ?></p>
                                        <p id="educ-attainment"><?php echo htmlspecialchars($education_data['educational_attainment']); ?></p>
                                        <p id="educ-year"><?php echo htmlspecialchars($education_data['sy_started']); ?> - <?php echo htmlspecialchars($education_data['sy_ended']); ?></p>
                                    </div>
                                    <?php else: ?>
                                        <button onclick="openNav('education_sidenav', 'profile-container')">Add</button>
                                    <?php endif; ?>
                                
                                <h4 style="margin-top: 1rem;">Vocational</h4>
                                <p>Provide details of a vocational course you completed.</p>
                                <?php if (!empty($vocational_data['school'])): ?>
                                    <div class="info-container">
                                        <div class="icon-group">
                                            <div class="edit-icon" onclick="openNav('vocational_sidenav', 'profile-container')">
                                                <i class="fas fa-edit"></i>
                                            </div>
                                            <div class="delete-icon" onclick="deleteVocational('<?php echo htmlspecialchars($userid); ?>')"> 
                                                <i class="fas fa-trash"></i>
                                            </div>
                                        </div>
                                        <h4 id="educ-course"><?php echo htmlspecialchars($vocational_data['course']); ?></h4>
                                        <p id="educ-school"><?php echo htmlspecialchars($vocational_data['school']); ?></p>
                                        <p id="educ-year"><?php echo htmlspecialchars($vocational_data['year_started']); ?> - <?php echo htmlspecialchars($vocational_data['year_ended']); ?></p>
                                    </div>
                                <?php else: ?>
                                <button onclick="openNav('vocational_sidenav', 'profile-container')">Add</button>
                                <?php endif; ?>
                            </div>
                            <div class="section">
                                <h3>Resume</h3>
                                <p>Upload a resume to provide more details about yourself.</p>
                                <div class="info-container">
                                    <?php if ($resume_exists): ?>
                                        <div class="icon-group">
                                            <div class="delete-icon" onclick="deleteResume(<?php echo $userid; ?>)"> 
                                                <i class="fas fa-trash"></i>
                                            </div>
                                        </div>
                                        <p>Your uploaded resume.</p> <br>
                                        <iframe src="data:application/pdf;base64,<?php echo base64_encode($resume_data); ?>" 
                                                width="600" height="400" style="border: none;"></iframe>
                                    <?php else: ?>
                                        <p>You have no uploaded resume.</p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!$resume_exists): // Only show the button if no resume exists ?>
                                    <button onclick="openNav('resume_sidenav', 'profile-container')">Upload</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div> 
            </section>
            <!--Footer-->
            <footer class="footer-distributed">

                <div class="footer-left">
                    <a href="#"><img src="images/logo.png" alt="Company Logo"></a>
                    <p class="footer-company-name">Copyright Â© 1992 <strong>RCVJ, Inc.</strong></p>
                </div>
        
                <div class="footer-center">
                    <div>
                        <i class="fa fa-map-marker"></i>
                        <p><span>DasmariÃ±as, Philippines</span>
                            3rd Floor RCVJ Bldg. Don P. Campos Ave.</p>
                    </div>
        
                    <div>
                        <i class="fa fa-phone"></i>
                        <p>(046) 416 0708</p>
                    </div>
                    <div>
                        <i class="fa fa-envelope"></i>
                        <p><a href="mailto:rcvj1992.recruitment@gmail.com">rcvj1992.recruitment@gmail.com</a></p>
                    </div>
                </div>
                <div class="footer-right">
                    <p class="footer-company-about">
                        <span>Contact Us</span>
                    <div class="footer-icons">
                        <a href="https://www.facebook.com/RCVJInc1992"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.linkedin.com/in/rcvj-inc-6b5599184/?originalSubdomain=ph"><i class="fab fa-linkedin"></i></a>
                    </div>                    
                </div>
            </footer>

            <!-- Success Modal -->
            <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Success</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Profile updated successfully!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Add these lines in the <head> of your HTML -->
            <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
            <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
            <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
            <script type="text/javascript">
                var userSkills = <?php echo $skills_json; ?>;

                // Function to display user skills in the info-container
                function displayUserSkills() {
                    var ul = document.getElementById('user_skills_list');
                    userSkills.forEach(function(skill) {
                        var li = document.createElement('li');
                        li.textContent = skill;
                        ul.appendChild(li);
                    });
                }

                // Call the function to populate the skills list
                document.addEventListener('DOMContentLoaded', displayUserSkills);

                let availableKeywords = <?php echo $availableSkills_json; ?>;

                const resultsBox = document.querySelector(".result-box");
                const inputBox = document.getElementById("skills");

                inputBox.onkeyup = function(){
                    let result = [];
                    let input = inputBox.value;
                    if(input.length){
                        result = availableKeywords.filter((keyword)=>{
                            return keyword.toLowerCase().includes(input.toLowerCase());
                        });
                        console.log(result);
                    }
                    display(result);

                    if(!result.length){
                        resultsBox.innerHTML = '';
                    }
                }

                function display(result){
                    const content = result.map((list)=>{
                        return "<li onclick=selectInput(this)>" + list + "</li>";
                    });
                    
                    resultsBox.innerHTML = "<ul>" + content.join('') + "</ul>";
                }

                function selectInput(list){
                    inputBox.value = list.innerHTML;
                    resultsBox.innerHTML = '';
                }
            </script>

        </body>
    </html> 
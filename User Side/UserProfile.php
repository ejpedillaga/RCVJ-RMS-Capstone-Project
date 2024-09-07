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
    'userid' => ''  // Add userid to the user_data array
];

$education_data = [
    'school' => '',
    'course' => '',
    'sy_ended' => '',
    'masters' => '',
    'masters_ended' => '',
    'doctoral' => '',
    'doctoral_ended' => ''
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
    $sql = "SELECT userid, fname, lname, location, gender, phone, email, birthday, classi, subclassi 
            FROM applicant_table WHERE email = '$user_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();  // userid is now part of user_data
        $user_name = $user_data['fname'] . ' ' . $user_data['lname'];
        $user_location = $user_data['location'];
        $userid = $user_data['userid'];  // Ensure we have the userid for further operations
    } else {
        echo "Error: User not found in the applicant_table.";
    }

    // Handle profile form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
        // Fetch and sanitize profile form data
        $fname = $conn->real_escape_string($_POST['fname']);
        $lname = $conn->real_escape_string($_POST['lname']);
        $location = $conn->real_escape_string($_POST['location']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $birthday = $conn->real_escape_string($_POST['birthday']);
        $classi = $conn->real_escape_string($_POST['classi']);
        $subclassi = $conn->real_escape_string($_POST['subclassi']);

        // Debugging: Check if data is being posted
        if (!empty($fname) && !empty($lname)) {
            // Update the user's data in the applicant_table
            $sql_update = "UPDATE applicant_table SET 
                fname = '$fname', 
                lname = '$lname', 
                location = '$location', 
                gender = '$gender', 
                phone = '$phone', 
                birthday = '$birthday', 
                classi = '$classi', 
                subclassi = '$subclassi'
                WHERE userid = '$userid'";

            if ($conn->query($sql_update) === TRUE) {
                $_SESSION['message'] = "Profile updated successfully!";
                // Debugging: Show success message
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                echo "Error updating profile: " . $conn->error;
            }
        } else {
            echo "Please fill in all required fields.";
        }
    }

    // Fetch the education data to populate the education form
    if (isset($userid)) {  // Ensure userid is available before fetching education data
        $sql_edu = "SELECT school, course, sy_ended, masters, masters_ended, doctoral, doctoral_ended 
                    FROM education_table WHERE userid = '$userid'";
        $result_edu = $conn->query($sql_edu);

        if ($result_edu->num_rows > 0) {
            $education_data = $result_edu->fetch_assoc();
        }
    }

    // Handle form submission for education data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_education'])) {
        // Fetch and sanitize education form data
        $school = $conn->real_escape_string($_POST['school']);
        $course = $conn->real_escape_string($_POST['course']);
        $sy_ended = $conn->real_escape_string($_POST['sy_ended']);
        $masters = $conn->real_escape_string($_POST['masters']);
        $masters_ended = $conn->real_escape_string($_POST['masters_ended']);
        $doctoral = $conn->real_escape_string($_POST['doctoral']);
        $doctoral_ended = $conn->real_escape_string($_POST['doctoral_ended']);

        // Insert or update education data for the user
        $check_sql = "SELECT * FROM education_table WHERE userid = '$userid'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            // If record exists, update
            $sql_update_edu = "UPDATE education_table SET
                school = '$school',
                course = '$course',
                sy_ended = '$sy_ended',
                masters = '$masters',
                masters_ended = '$masters_ended',
                doctoral = '$doctoral',
                doctoral_ended = '$doctoral_ended'
                WHERE userid = '$userid'";
            $conn->query($sql_update_edu);
        } else {
            // If no record, insert
            $sql_insert_edu = "INSERT INTO education_table (userid, school, course, sy_ended, masters, masters_ended, doctoral, doctoral_ended) 
                VALUES ('$userid', '$school', '$course', '$sy_ended', '$masters', '$masters_ended', '$doctoral', '$doctoral_ended')";
            if (!$conn->query($sql_insert_edu)) {
                echo "Error inserting education data: " . $conn->error;
            }
        }

        $_SESSION['message'] = "Education data saved successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Fetch the job experience data to populate the job experience form
    if (isset($userid)) {  // Ensure userid is available before fetching job experience data
        $sql_job = "SELECT job_title, company_name, month_started, year_started, month_ended, year_ended, career_history 
                    FROM job_experience_table WHERE userid = '$userid'";
        $result_job = $conn->query($sql_job);

        if ($result_job->num_rows > 0) {
            $job_experience_data = $result_job->fetch_assoc();
        }
    }

    // Handle form submission for job experience data
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_job_experience'])) {
        // Fetch and sanitize job experience form data
        $job_title = $conn->real_escape_string($_POST['job-title']);
        $company_name = $conn->real_escape_string($_POST['company-name-field']);
        $month_started = $conn->real_escape_string($_POST['month_started']);
        $year_started = (int) $_POST['year_started'];
        $month_ended = $conn->real_escape_string($_POST['month_ended']);
        $year_ended = !empty($_POST['year_ended']) ? (int) $_POST['year_ended'] : null;
        $career_history = $conn->real_escape_string($_POST['career_history']);

        // Insert or update job experience data for the user
        $check_sql = "SELECT * FROM job_experience_table WHERE userid = '$userid'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            // If record exists, update
            $sql_update_job = "UPDATE job_experience_table SET
                job_title = '$job_title',
                company_name = '$company_name',
                month_started = '$month_started',
                year_started = $year_started,
                month_ended = '$month_ended',
                year_ended = $year_ended,
                career_history = '$career_history'
                WHERE userid = '$userid'";
            $conn->query($sql_update_job);
        } else {
            // If no record, insert
            $sql_insert_job = "INSERT INTO job_experience_table (userid, job_title, company_name, month_started, year_started, month_ended, year_ended, career_history) 
                VALUES ('$userid', '$job_title', '$company_name', '$month_started', $year_started, '$month_ended', $year_ended, '$career_history')";
            if (!$conn->query($sql_insert_job)) {
                echo "Error inserting job experience data: " . $conn->error;
            }
        }

        $_SESSION['message'] = "Job experience data saved successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

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
        <link rel="stylesheet" href="style.css">
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
                            <button id="profile">User Name</button>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!--Content goes here-->
            <!--Usual Structure-->
            <section class="profile-section">
                <!--Edit Profile Sidenav-->
                <div id="overlay" class="overlay"></div>
                <div id="editProfile-sidenav" class="sidenav">
                    <div class="sidenav-header">Edit Profile</div>
                    <div class="edit-profile-form">
                    <form action="" method="POST">
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

                <!--Personal Description Sidenav-->
                <!-- Overlay -->
                <div id="overlay" class="overlay"></div>
                <!-- Personal Description Sidenav -->
                <div id="personal-description-sidenav" class="sidenav">
                    <div class="sidenav-header">Add some description about <br> yourself<br>
                        <p>Describe people who you are</p>
                    </div>
                    <div class="personal-description-form">
                        <form action="">
                            <div class="form-group">
                                <div>
                                    <!--<p>Describe people who you are</p>-->
                                    <textarea id="description" class="textarea" rows="20" cols="80"></textarea>
                                </div>
                            </div>

                            <div id="button-group" class="form-group">
                                <button class="button">Save</button>
                            </div>
                        </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('personal-description-sidenav', 'profile-container')">&times;</a>
                </div>

                <div id="overlay" class="overlay"></div>
                <!--Past Jobs Sidenav-->
                <div id="past-jobs-sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">Past Jobs</div>
                        <div class="past-jobs-form sidenav-content">
                            <form action="" method="POST">
                                <div class="form-group sidenav-content">
                                    <div>
                                        <label class="label" for="job-title">Job Title</label>
                                        <input type="text" id="job-title" name="job-title" class="input-field" value="<?php echo htmlspecialchars($job_experience_data['job_title']); ?>">
                                    </div>
                                </div>

                                <div class="form-group sidenav-content">
                                    <div>
                                        <label class="label" for="company-name-field">Company Name</label>
                                        <input type="text" id="company-name-field" name="company-name-field" class="input-field" value="<?php echo htmlspecialchars($job_experience_data['company_name']); ?>">
                                    </div>
                                </div>

                                <label class="label" for="started_group">Started</label>
                                <div id="started_group" class="form-group">
                                    <div>
                                        <select id="month_started" name="month_started" class="select-field">
                                            <option value="" disabled>Select Month</option>
                                            <option value="January" <?php echo $job_experience_data['month_started'] === 'January' ? 'selected' : ''; ?>>January</option>
                                            <option value="February" <?php echo $job_experience_data['month_started'] === 'February' ? 'selected' : ''; ?>>February</option>
                                            <option value="March" <?php echo $job_experience_data['month_started'] === 'March' ? 'selected' : ''; ?>>March</option>
                                            <option value="April" <?php echo $job_experience_data['month_started'] === 'April' ? 'selected' : ''; ?>>April</option>
                                            <option value="May" <?php echo $job_experience_data['month_started'] === 'May' ? 'selected' : ''; ?>>May</option>
                                            <option value="June" <?php echo $job_experience_data['month_started'] === 'June' ? 'selected' : ''; ?>>June</option>
                                            <option value="July" <?php echo $job_experience_data['month_started'] === 'July' ? 'selected' : ''; ?>>July</option>
                                            <option value="August" <?php echo $job_experience_data['month_started'] === 'August' ? 'selected' : ''; ?>>August</option>
                                            <option value="September" <?php echo $job_experience_data['month_started'] === 'September' ? 'selected' : ''; ?>>September</option>
                                            <option value="October" <?php echo $job_experience_data['month_started'] === 'October' ? 'selected' : ''; ?>>October</option>
                                            <option value="November" <?php echo $job_experience_data['month_started'] === 'November' ? 'selected' : ''; ?>>November</option>
                                            <option value="December" <?php echo $job_experience_data['month_started'] === 'December' ? 'selected' : ''; ?>>December</option>
                                        </select>
                                    </div>
                                    <div>
                                        <select id="year_started" name="year_started" class="select-field">
                                            <option value="" disabled>Select Year</option>
                                            <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                                <option value="<?php echo $year; ?>" <?php echo $job_experience_data['year_started'] == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <label class="label" for="ended_group">Ended</label>
                                <div id="ended_group" class="form-group">
                                    <div>
                                        <select id="month_ended" name="month_ended" class="select-field">
                                            <option value="" disabled>Select Month</option>
                                            <option value="January" <?php echo $job_experience_data['month_ended'] === 'January' ? 'selected' : ''; ?>>January</option>
                                            <option value="February" <?php echo $job_experience_data['month_ended'] === 'February' ? 'selected' : ''; ?>>February</option>
                                            <option value="March" <?php echo $job_experience_data['month_ended'] === 'March' ? 'selected' : ''; ?>>March</option>
                                            <option value="April" <?php echo $job_experience_data['month_ended'] === 'April' ? 'selected' : ''; ?>>April</option>
                                            <option value="May" <?php echo $job_experience_data['month_ended'] === 'May' ? 'selected' : ''; ?>>May</option>
                                            <option value="June" <?php echo $job_experience_data['month_ended'] === 'June' ? 'selected' : ''; ?>>June</option>
                                            <option value="July" <?php echo $job_experience_data['month_ended'] === 'July' ? 'selected' : ''; ?>>July</option>
                                            <option value="August" <?php echo $job_experience_data['month_ended'] === 'August' ? 'selected' : ''; ?>>August</option>
                                            <option value="September" <?php echo $job_experience_data['month_ended'] === 'September' ? 'selected' : ''; ?>>September</option>
                                            <option value="October" <?php echo $job_experience_data['month_ended'] === 'October' ? 'selected' : ''; ?>>October</option>
                                            <option value="November" <?php echo $job_experience_data['month_ended'] === 'November' ? 'selected' : ''; ?>>November</option>
                                            <option value="December" <?php echo $job_experience_data['month_ended'] === 'December' ? 'selected' : ''; ?>>December</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <select id="year_ended" name="year_ended" class="select-field">
                                            <option value="" disabled>Select Year</option>
                                            <?php for ($year = 2000; $year <= 2024; $year++): ?>
                                                <option value="<?php echo $year; ?>" <?php echo $job_experience_data['year_ended'] == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="career_history">Tell something about your career history</label>
                                        <textarea id="career_history" name="career_history" class="textarea" rows="10" cols="80"><?php echo htmlspecialchars($job_experience_data['career_history']); ?></textarea>
                                    </div>
                                </div>

                                <div id="button-group" class="form-group">
                                    <button class="button" name="save_job_experience" type="submit">Save</button>
                                </div>
                            </form>
                        </div>
                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav('past-jobs-sidenav', 'profile-container')">&times;</a>
                    </div>

                <div id="overlay" class="overlay"></div>
                    <!-- Education Sidenav -->
                    <div id="education_sidenav" class="sidenav">
                        <div class="sidenav-header sidenav-content">Education</div>
                        <div class="education-form sidenav-content">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <div>
                                        <label class="label" for="school">School</label>
                                        <input type="text" id="school" name="school" class="input-field" value="<?php echo htmlspecialchars($education_data['school']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="course">Course</label>
                                        <input type="text" id="course" name="course" class="input-field" value="<?php echo htmlspecialchars($education_data['course']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="sy_ended">Year Ended</label>
                                        <select id="sy_ended" name="sy_ended" class="select-field" required>
                                            <option value="" disabled>Year Ended</option>
                                            <?php
                                            for ($year = date('Y'); $year >= 2000; $year--) {
                                                $selected = ($year == $education_data['sy_ended']) ? 'selected' : '';
                                                echo "<option value=\"$year\" $selected>$year</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="masters">Master's <span>(Optional)</span></label>
                                        <input type="text" id="masters" name="masters" class="input-field" value="<?php echo htmlspecialchars($education_data['masters']); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="masters_ended">Year Ended</label>
                                        <select id="masters_ended" name="masters_ended" class="select-field">
                                            <option value="" disabled>Year Ended</option>
                                            <?php
                                            for ($year = date('Y'); $year >= 2000; $year--) {
                                                $selected = ($year == $education_data['masters_ended']) ? 'selected' : '';
                                                echo "<option value=\"$year\" $selected>$year</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="doctoral">Doctoral <span>(Optional)</span></label>
                                        <input type="text" id="doctoral" name="doctoral" class="input-field" value="<?php echo htmlspecialchars($education_data['doctoral']); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div>
                                        <label class="label" for="doctoral_ended">Year Ended</label>
                                        <select id="doctoral_ended" name="doctoral_ended" class="select-field">
                                            <option value="" disabled>Year Ended</option>
                                            <?php
                                            for ($year = date('Y'); $year >= 2000; $year--) {
                                                $selected = ($year == $education_data['doctoral_ended']) ? 'selected' : '';
                                                echo "<option value=\"$year\" $selected>$year</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="button-group" class="form-group sidenav-content">
                                    <button class="button" name="save_education" type="submit">Save</button>
                                </div>
                            </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('education_sidenav', 'profile-container')">&times;</a>
                </div>

                <div id="overlay" class="overlay"></div>
                <!--License and Education Sidenav-->
                <div id="LnE-sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">Add License or Certificates
                        <br>
                        <p>Showcase your licenses, certificates, memberships, and accreditations.</p>
                    </div>
                    
                    <div class="LnE-form sidenav-content">
                        <form action="">
                            <div id="license_group" class="form-group sidenav-content">
                                <label for="license" class="label">License Name</label>
                                <input type="text" id="license" class="input-field">
                            </div>

                            <label for="issue_date_group" class="label sidenav-content">Issue Date</label>
                            <div id="issue_date_group" class="form-group sidenav-content">                              
                                <div>
                                    <select id="month_issued" class="select-field">
                                        <option value="" disabled selected>Month</option>
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
                                    <select id="year_issued" class="select-field">
                                            <option value="" disabled selected>Year</option>
                                            <option value="2000">2000</option>
                                            <option value="2001">2001</option>
                                            <option value="2002">2002</option>
                                            <option value="2003">2003</option>
                                            <option value="2004">2004</option>
                                            <option value="2005">2005</option>
                                            <option value="2006">2006</option>
                                            <option value="2007">2007</option>
                                            <option value="2008">2008</option>
                                            <option value="2009">2009</option>
                                            <option value="2010">2010</option>
                                            <option value="2011">2011</option>
                                            <option value="2012">2012</option>
                                            <option value="2013">2013</option>
                                            <option value="2014">2014</option>
                                            <option value="2015">2015</option>
                                            <option value="2016">2016</option>
                                            <option value="2017">2017</option>
                                            <option value="2018">2018</option>
                                            <option value="2019">2019</option>
                                            <option value="2020">2020</option>
                                            <option value="2021">2021</option>
                                            <option value="2022">2022</option>
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                    </select>
                                </div>
                            </div>

                            <label for="expiry_date_group" class="label sidenav-content">Expiry Date</label>
                            <div id="expiry_date_group" class="form-group sidenav-content">
                                <div>
                                    <select id="month_expired" class="select-field">
                                        <option value="" disabled selected>Month</option>
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
                                    <select id="year_expired" class="select-field">
                                            <option value="" disabled selected>Year</option>
                                            <option value="2000">2000</option>
                                            <option value="2001">2001</option>
                                            <option value="2002">2002</option>
                                            <option value="2003">2003</option>
                                            <option value="2004">2004</option>
                                            <option value="2005">2005</option>
                                            <option value="2006">2006</option>
                                            <option value="2007">2007</option>
                                            <option value="2008">2008</option>
                                            <option value="2009">2009</option>
                                            <option value="2010">2010</option>
                                            <option value="2011">2011</option>
                                            <option value="2012">2012</option>
                                            <option value="2013">2013</option>
                                            <option value="2014">2014</option>
                                            <option value="2015">2015</option>
                                            <option value="2016">2016</option>
                                            <option value="2017">2017</option>
                                            <option value="2018">2018</option>
                                            <option value="2019">2019</option>
                                            <option value="2020">2020</option>
                                            <option value="2021">2021</option>
                                            <option value="2022">2022</option>
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                    </select>
                                </div>
                            </div>

                            <div id="certificate_group" class="form-group">
                                <label for="certificate" class="label">Certificate Name</label>
                                <input type="text" id="certificate" class="input-field">
                            </div>

                            <div id="button-group" class="form-group">
                                <button class="button">Save</button>
                            </div>
                        </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('LnE-sidenav', 'profile-container')">&times;</a>
                </div>

                <div id="overlay" class="overlay"></div>
                <!--Skills Sidenav-->
                <div id="skills_sidenav" class="sidenav">
                    <div class="sidenav-header sidenav-content">
                        Add Skills
                        <br>
                        <p>Help employers find you by showcasing all of your skills.</p>
                    </div>

                    <div class="skills-form sidenav-content">
                        <form id="skills_form">
                            <label for="add_skills_group" class="sidenav-content">Add skill/s</label>
                            <div id="add_skills_group" class="form-group two-columns sidenav-content">
                                <div>
                                    <input type="text" id="skills" class="input-field">
                                </div>
                                <div>
                                    <button id="add_skill_btn" class="button" type="button">Add</button>
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
                      <p>Your default resume can be viewed by employers when they search for candidates.</p>
                    </div>
                
                    <div class="resume-form sidenav-content">
                      <form action="">
                        <div id="resume_dropbox" class="form-group">
                        <img src="images/resume-dropbox.png" alt="">
                          <p>Drag and drop here or simply browse for a file to upload resume.</p>  
                          <input type="file" id="fileInput" class="file-input" multiple>
                          <button type="button" class="button" onclick="document.getElementById('fileInput').click()">Browse</button>
                        </div>
                        <div id="filePreview" class="file-preview"></div>
                      </form>
                    </div>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav('resume_sidenav', 'profile-container')">&times;</a>
                </div>

                <div id="overlay" class="overlay"></div>
                <!--Profile Container-->
                <div id="profile-container" class="main-container">
                    <!--Header-->
                    <div class="profile-header">

                        <div class="profile-card">
                            <div class="content">
                                <img id="profile-picture" src="images/profileicon.svg" alt="">
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
                                <button onclick="openNav('personal-description-sidenav', 'profile-container')">Add</button>
                            </div>
                            <div class="section">
                                <h3>Licences & Certificates</h3>
                                <p>Showcase your professional credentials. Add your relevant licences, certificates, memberships and accreditations here.</p>
                                <button onclick="openNav('LnE-sidenav', 'profile-container')">Add</button>
                            </div>
                            <div class="section">
                                <h3>Past Jobs</h3>
                                <p>The more you let employers know about your experience, the more you can stand out.</p>
                                <button onclick="openNav('past-jobs-sidenav', 'profile-container')">Add</button>
                            </div>
                            <div class="section">
                                <h3>Skills</h3>
                                <p>Let employers know how valuable you can be to them.</p>
                                <button onclick="openNav('skills_sidenav', 'profile-container')">Add</button>
                            </div>
                            <div class="section">
                                <h3>Education</h3>
                                <p>Tell employers about your education.</p>
                                <button onclick="openNav('education_sidenav', 'profile-container')">Add</button>
                            </div>
                            <div class="section">
                                <h3>ResumÃ©</h3>
                                <p>Upload a resumÃ© for easy applying and access no matter where you are.</p>
                                <button onclick="openNav('resume_sidenav', 'profile-container')">Upload</button>
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
            
            <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
            <script defer src="script.js"></script>

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
            <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>


        </body>
    </html>
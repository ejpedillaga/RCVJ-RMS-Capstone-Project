<?php
include 'connection.php';
$conn = connection();

session_start();
$user_name = 'Sign Up / Sign In'; // Default value
$user_info = [];
$education_list = [];
$vocational_list = [];
$job_experience_list = [];
$profile_image = null; // Initialize profile image
$skills = [];
$licenses = []; // Initialize licenses array
$company_name = '';
$job_title = '';

$resume_exists = false; // Ensure this variable is always defined
$resume_data = null;    // Initialize resume data variable

// Check if user is logged in
if (isset($_SESSION['user'])) {
    $user_email = $_SESSION['user']; // Fetch user's email from session
    
    // Fetch user information from applicant_table
    $sql = "SELECT userid, email, fname, lname, gender, birthday, location, phone, personal_description, profile_image FROM applicant_table WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
        $user_name = $user_info['fname'] . ' ' . $user_info['lname']; // Get full name
        $userid = $user_info['userid']; // Get user ID
        $profile_image = !empty($user_info['profile_image']) ? base64_encode($user_info['profile_image']) : null;

        // Fetch education details
        $sql = "SELECT educational_attainment, school, course, sy_started, sy_ended FROM education_table WHERE userid = ?";
        $stmt_edu = $conn->prepare($sql);
        $stmt_edu->bind_param("i", $userid);
        $stmt_edu->execute();
        $result_edu = $stmt_edu->get_result();

        if ($result_edu->num_rows > 0) {
            while ($row = $result_edu->fetch_assoc()) {
                $education_list[] = $row;
            }
        }
        $stmt_edu->close();

        // Fetch vocational education details
        $sql = "SELECT school, course, year_started, year_ended FROM vocational_table WHERE userid = ?";
        $stmt_voc = $conn->prepare($sql);
        $stmt_voc->bind_param("i", $userid);
        $stmt_voc->execute();
        $result_voc = $stmt_voc->get_result();

        if ($result_voc->num_rows > 0) {
            while ($row = $result_voc->fetch_assoc()) {
                $vocational_list[] = $row;
            }
        }
        $stmt_voc->close();

        // Fetch job experience details
        $sql = "SELECT job_title, company_name, month_started, year_started, month_ended, year_ended FROM job_experience_table WHERE userid = ?";
        $stmt_job = $conn->prepare($sql);
        $stmt_job->bind_param("i", $userid);
        $stmt_job->execute();
        $result_job = $stmt_job->get_result();

        if ($result_job->num_rows > 0) {
            while ($row = $result_job->fetch_assoc()) {
                $job_experience_list[] = $row;
            }
        }
        $stmt_job->close();
        
        // Fetch user-specific skills
        $sql = "SELECT skill_table.skill_name 
                FROM skill_table 
                JOIN user_skills_table ON skill_table.skill_id = user_skills_table.skill_id 
                WHERE user_skills_table.userid = ?";
        $stmt_skills_user = $conn->prepare($sql);
        $stmt_skills_user->bind_param("i", $userid);
        $stmt_skills_user->execute();
        $result_skills_user = $stmt_skills_user->get_result();

        if ($result_skills_user->num_rows > 0) {
            while ($row = $result_skills_user->fetch_assoc()) {
                $skills[] = $row['skill_name'];
            }
        }
        $stmt_skills_user->close();

        // Fetch licenses
        $sql = "SELECT license_name, month_issued, year_issued, month_expired, year_expired FROM certification_license_table WHERE userid = ?";
        $stmt_licenses = $conn->prepare($sql);
        $stmt_licenses->bind_param("i", $userid);
        $stmt_licenses->execute();
        $result_licenses = $stmt_licenses->get_result();

        if ($result_licenses->num_rows > 0) {
            while ($row = $result_licenses->fetch_assoc()) {
                $licenses[] = $row;
            }
        }
        $stmt_licenses->close();

        // Fetch resume from resume_table
        $sql_resume = "SELECT resume FROM resume_table WHERE userid = ?";
        $stmt_resume = $conn->prepare($sql_resume);
        $stmt_resume->bind_param("i", $userid);
        $stmt_resume->execute();
        $result_resume = $stmt_resume->get_result();

        if ($result_resume->num_rows > 0) {
            $row = $result_resume->fetch_assoc();
            $resume_data = $row['resume']; // Store resume BLOB
            $resume_exists = true; // Flag that a resume exists
        }
        $stmt_resume->close();
    }
    $stmt->close();
}

// Get job ID from query parameter
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch selected job details with company logo from job_table and partner_table
$sql = "SELECT job_table.job_title, job_table.job_location, job_table.job_candidates, job_table.company_name, job_table.job_description, job_table.date_posted, partner_table.logo
        FROM job_table
        JOIN partner_table ON job_table.company_name = partner_table.company_name
        WHERE job_table.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

// Encode the company logo in base64
if ($job && isset($job['logo'])) {
    $job['logo'] = base64_encode($job['logo']);
}

// Fetch qualifications from job_title_table
$sql_qualifications = "SELECT gender, educational_attainment, years_of_experience, cert_license FROM job_title_table WHERE job_title = ?";
$stmt_qual = $conn->prepare($sql_qualifications);
$stmt_qual->bind_param("s", $job['job_title']);
$stmt_qual->execute();
$result_qual = $stmt_qual->get_result();

if ($result_qual->num_rows > 0) {
    $qualifications = $result_qual->fetch_assoc();
}

// Fetch skills for the job from job_skills_table
$sql_skills = "SELECT skill_table.skill_name 
               FROM job_skills_table 
               JOIN job_title_table ON job_skills_table.job_title_id = job_title_table.id
               JOIN skill_table ON job_skills_table.skill_id = skill_table.skill_id
               WHERE job_title_table.job_title = ?";
$stmt_skills = $conn->prepare($sql_skills);
$stmt_skills->bind_param("s", $job['job_title']);
$stmt_skills->execute();
$result_skills = $stmt_skills->get_result();

if ($result_skills->num_rows > 0) {
    while ($row = $result_skills->fetch_assoc()) {
        $skills[] = $row['skill_name'];
    }
}
$stmt_skills->close();

$company_name = isset($job['company_name']) ? $job['company_name'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $user_info['userid'];
    $full_name = $user_name;
    $job_title = $job['job_title'];
    $company_name = $company_name;
    $job_location = $job['job_location'];
    $job_id = $job_id;
    $date_applied = date('Y-m-d');
    $status = 'Pending';

    // Check if the user has already applied for the same job
    $sql_check = "SELECT COUNT(*) AS count FROM candidate_list WHERE userid = ? AND job_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $userid, $job_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // If the user already applied, block the submission
        echo json_encode(['status' => 'error', 'message' => 'You have already applied for this job.']);
    } else {
        // Proceed with inserting the new application
        $sql_insert = "INSERT INTO candidate_list (userid, full_name, job_title, company_name, job_location, job_id, date_applied, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("issssiss", $userid, $full_name, $job_title, $company_name, $job_location, $job_id, $date_applied, $status);

        if ($stmt_insert->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Application submitted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'There was an error processing your application.']);
        }
    }

    exit;
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
<!-- Overlay --> 
<div class="overlay" id="overlay"></div>

<!-- Candidate Info Popup -->
<div class="popup" id="info">
    <!-- Back Button -->
    <div class="addpartners-back-button" onclick="hideInfo()">
        <i class="fas fa-chevron-left"></i> Back
    </div>
    
    <?php if (!isset($_SESSION['user'])): ?>
    <div class="sign-in-message" style="text-align: center; margin-top: 200px;">
        <img src="images/findjobs.png" alt="Placeholder Image" style="width: 300px; height: auto; margin-bottom: 20px;">
        <h3 style="color: #2C1875">Sign Up / Sign In Required</h3>
        <p>Don’t miss out! Sign up today and begin your career with us.</p>
    </div>
    <?php else: ?>
        <h3 style="color: #2C1875">Review your information:</h3>
        <p>This information will be reviewed by the employer.</p>
        <form id="applyForm" method="post" action="">
            <div class="candidate-container">
                <div class="candidate-header">
                    <div>
                        <h2><?php echo htmlspecialchars($user_name); ?></h2>
                        <div class="locationemail">
                            <i class="fa fa-map-pin" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['location']); ?></h4>
                        </div>
                        <div class="locationemail">
                            <i class="fa fa-envelope" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['email']); ?></h4>
                        </div>
                        <div class="locationemail">
                            <i class="fa fa-venus-mars" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['gender']); ?></h4>
                        </div>
                        <div class="locationemail">
                            <i class="fa fa-phone" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['phone']); ?></h4>
                        </div>
                        <div class="locationemail">
                            <i class="fa fa-birthday-cake" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['birthday']); ?></h4>
                        </div>
                    </div>
                    <div>
                        <?php if ($profile_image): ?>
                            <img src="data:image/jpeg;base64,<?php echo $profile_image; ?>" alt="Profile Picture" class="large-profile-photo">
                        <?php else: ?>
                            <img src="images/user.svg" alt="Default Profile Picture" class="large-profile-photo">
                        <?php endif; ?>
                    </div>
                </div>
                <div id="personal-info">
                    <h3>Personal Information</h3>
                    <p id="personal-desc"><?php echo nl2br(htmlspecialchars($user_info['personal_description'])); ?></p>
                </div>
                <!-- Past Jobs Information -->
                <div id="past-jobs">
                    <h3>Past Jobs</h3>
                    <ul class="pastjobs-list">
                        <?php if (!empty($job_experience_list)): ?>
                            <?php foreach ($job_experience_list as $job_exp): ?>
                                <li>
                                    <?php echo htmlspecialchars($job_exp['job_title']) . " at " . htmlspecialchars($job_exp['company_name']) . " (" . htmlspecialchars($job_exp['month_started']) . " " . htmlspecialchars($job_exp['year_started']) . " - " . (!empty($job_exp['month_ended']) ? htmlspecialchars($job_exp['month_ended']) . " " . htmlspecialchars($job_exp['year_ended']) : 'Present') . ")"; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No past job experience records found.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- Education Information -->
                <div id="education">
                    <h3>Educational Attainment</h3>
                    <ul class="education-list">
                        <?php if (!empty($education_list)): ?>
                            <?php foreach ($education_list as $education): ?>
                                <li>
                                    <?php echo htmlspecialchars($education['educational_attainment']) . " in " . htmlspecialchars($education['course']) . " from " . htmlspecialchars($education['school']) . " (" . htmlspecialchars($education['sy_started']) . " - " . htmlspecialchars($education['sy_ended']) . ")"; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No education records found.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Vocational Education Information -->
                <div id="vocational">
                    <h3>Vocational</h3>
                    <ul class="vocational-list">
                        <?php if (!empty($vocational_list)): ?>
                            <?php foreach ($vocational_list as $vocational): ?>
                                <li>
                                    <?php echo htmlspecialchars($vocational['course']) . " from " . htmlspecialchars($vocational['school']) . " (" . htmlspecialchars($vocational['year_started']) . " - " . htmlspecialchars($vocational['year_ended']) . ")"; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No vocational education records found.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div id="user-skills">
                    <h3>Skills</h3>
                    <ul class="skills-list">
                        <?php if (!empty($skills)) : ?>
                            <?php foreach ($skills as $skill) : ?>
                                <li><?php echo htmlspecialchars($skill); ?></li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li>No skills found</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div id="licenses-container">
                    <h3>Licenses</h3>
                    <ul class="licenses-list">
                        <?php if (!empty($licenses)): ?>
                            <?php foreach ($licenses as $index => $license): ?>
                                <li>
                                    <?php 
                                    echo htmlspecialchars($license['license_name']) . " (Issued: " . htmlspecialchars($license['month_issued']) . " " . htmlspecialchars($license['year_issued']); 
                                    ?>
                                    <?php if (!empty($license['month_expired']) && !empty($license['year_expired'])): ?>
                                        - Expired: <?php echo htmlspecialchars($license['month_expired']) . " " . htmlspecialchars($license['year_expired'])  . ")"; ?>
                                    <?php endif; ?>
                                    <!-- Create a clickable icon for viewing -->
                                    <a href="view_license.php?userid=<?php echo urlencode($user_info['userid']); ?>&licenseIndex=<?php echo $index; ?>" target="_blank" title="View License">
                                        <i class="fas fa-eye" style="font-size: 1.2em; margin-left: 10px; color: #2c1875;"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No licenses found.</li>
                        <?php endif; ?>
                    </ul>
                    <div class="resume-container">
                        <h3>Resume</h3>
                        <div>
                            <?php if ($resume_exists): ?>
                                <p>Your uploaded resume:</p> <br>
                                <iframe src="data:application/pdf;base64,<?php echo base64_encode($resume_data); ?>" 
                                        width="1000" height="800" style="border: none;"></iframe>
                            <?php else: ?>
                                <p>You have no uploaded resume.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-container">
                <button type="submit" class="button-apply" id="submitBtn">Submit</button>
                <button class="button-cp" onclick="redirectTo('UserProfile.php')">Edit</button>
            </div>
        </form>
    <?php endif; ?>
</div>
    
    <!--Desktop Nav-->
    <nav class="desktopnav" id="desktop-nav">
            <div class="logo">
                <img src="images/logo.png" alt="">
            </div>
            <div>
                <ul class="nav-links">
                    <li><a href="Home.php">Home</a></li>
                    <li><a class="active" href="#">Jobs</a></li>
                    <li><a href="About.php">About</a></li>
                    <li><a href="Partner.php">Partner Companies</a></li>
                </ul>
            </div>
            <div class="nav-acc">
                <?php if ($profile_image): ?>
                    <img src="data:image/jpeg;base64,<?php echo $profile_image; ?>" alt="Profile Picture" class="small-profile-photo">
                <?php else: ?>
                    <img src="images/user.svg" alt="Default Profile Picture" class="small-profile-photo">
                <?php endif; ?>
                <?php if (isset($_SESSION['user'])): ?>
                    <button onclick="redirectTo('UserProfile.php')"><?php echo htmlspecialchars($user_name); ?></button>
                <?php else: ?>
                    <button onclick="redirectTo('../Login/Applicant.php')"><?php echo htmlspecialchars($user_name); ?></button>
                <?php endif; ?>

                <!-- LOGOUT -->
                <?php if (isset($_SESSION['user'])): ?>
                    <button class="logout-btn" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt fa-lg"></i>
                    </button>
                <?php endif; ?>
            </div>
        </nav>

        <!---Burger Nav-->
        <nav id="hamburger-nav">
            <div class="logo">
                <img src="images/logo.png" alt="">
            </div>
            <div class="hamburger-menu">
                <div class="nav-icons">
                    <div class="hamburger-icon" onclick="toggleMenu()">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                <div class="menu-links">
                    <li><a href="Home.php" onclick="toggleMenu()">Home</a></li>
                    <li><a class="active" href="#" onclick="toggleMenu()">Jobs</a></li>
                    <li><a href="About.php" onclick="toggleMenu()">About</a></li>
                    <li><a href="Partner.php" onclick="toggleMenu()">Partner Companies</a></li>
                    <li>
                        <?php if (isset($_SESSION['user'])): ?>
                            <a href="UserProfile.php">Profile</a>
                        <?php else: ?>
                            <a href="../Login/Applicant.php"><?php echo htmlspecialchars($user_name); ?></a>
                        <?php endif; ?>
                    </li>
                </div>
            </div>
        </nav>
    
    <section class="details-section">
        <div class="main-container">
            <div class="back-button">
                <a href="#" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i>Back
                </a>
            </div>
            <div class="details-header">
                <div class="company-box">
                    <h2 class="title3"><?php echo htmlspecialchars($job['job_title']); ?></h2>
                    <p id="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                    <p id="location"><i class="fa fa-map-marker-alt" aria-hidden="true"></i> <?php echo htmlspecialchars($job['job_location']); ?></p>
                    <p id="date-posted">Posted on: <?php echo htmlspecialchars(date('m/d/Y', strtotime($job['date_posted']))); ?></p>
                    <p id="available">Available Spots: <?php echo htmlspecialchars($job['job_candidates']); ?></p>
                    <div class="buttons-container">
                        <button class="button-apply" onclick="showInfo()">Apply</button>
                        <button class="button-cp" onclick="redirectTo('CompanyProfile.php?company_name=<?php echo urlencode($company_name); ?>')">Company Profile</button>
                    </div>
                </div>
                <img id="company-logo" src="data:image/jpeg;base64,<?php echo htmlspecialchars($job['logo']); ?>" alt="Company Logo">
            </div>
            <div class="desc-box" id="job-details-desc">
                <h3>Job Description</h3>
                <p id="description"><?php echo nl2br(htmlspecialchars($job['job_description'])); ?></p>
                <h3>Qualifications</h3>
                <ul id="qualifications-list">
                    <?php if (htmlspecialchars($qualifications['gender']) === "Not Specified"): ?>
                        <li><strong>Open to all genders</strong></li>
                    <?php else: ?>
                        <li><strong><?php echo htmlspecialchars($qualifications['gender']); ?></strong></li>
                    <?php endif; ?>
                    <?php if (htmlspecialchars($qualifications['educational_attainment']) === "Undergraduate"): ?>
                        <li>Undergraduates are qualified</li>
                    <?php else: ?>
                        <li>At least a <strong><?php echo htmlspecialchars($qualifications['educational_attainment']); ?></strong></li>
                    <?php endif; ?>
                    <?php if (htmlspecialchars($qualifications['years_of_experience']) === "-" || htmlspecialchars($qualifications['years_of_experience']) === "0-0"): ?>
                        <li>No experience needed</li>
                    <?php else: ?>
                        <li>Preferably with <strong><?php echo htmlspecialchars($qualifications['years_of_experience']); ?> year/s</strong> of professional experience relevant to the field</li>
                    <?php endif; ?>
                    <?php if (htmlspecialchars($qualifications['cert_license']) === ""): ?>
                        <li>No certification/licenses needed</li>
                    <?php else: ?>
                        <li>Must a holder of a <strong><?php echo htmlspecialchars($qualifications['cert_license']); ?></strong></li>
                    <?php endif; ?>
                </ul>
                <h3>Skills</h3>
                <ul id="skills-list">
                    <?php if (!empty($skills)): ?>
                        <?php foreach ($skills as $skill): ?>
                            <li><?php echo htmlspecialchars($skill); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No skills required for this job.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="shape-container2">
            <div class="rectangle-4"></div>
            <div class="rectangle-5"></div>
        </div>
    </section>

    <footer class="footer-distributed">
        <div class="footer-left">
            <a href="#"><img src="images/logo.png" alt="Company Logo"></a>
            <p class="footer-company-name">Copyright © 1992 <strong>RCVJ, Inc.</strong></p>
        </div>

        <div class="footer-center">
            <div>
                <i class="fa fa-map-marker"></i>
                <p><span>Dasmariñas, Philippines</span>
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
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
    <script>
                $(document).ready(function() {
            $('#applyForm').submit(function(e) {
                e.preventDefault(); // Prevent form from submitting normally
                $.ajax({
                    type: 'POST',
                    url: '', // Same PHP file
                    data: $(this).serialize(),
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            alert(res.message);
                            window.location.href = 'Jobs.php';
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('There was an error processing your request.');
                    }
                });
            });
        });
    </script>
</body>
</html>

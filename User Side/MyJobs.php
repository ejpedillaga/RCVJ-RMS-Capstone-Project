<?php
include 'connection.php';
$conn = connection();
session_start();

// Initialize user name and profile image
$user_name = 'Sign Up / Sign In'; // Default value
$profile_image = null;
$applied_jobs = $interview_jobs = $for_deployment_jobs = $current_jobs = []; // Arrays to store jobs per category

if (isset($_SESSION['user'])) {
    // Fetch user's email from the session
    $user_email = $_SESSION['user'];

    // Fetch user's full name and profile image
    $user_sql = "SELECT fname, lname, profile_image FROM applicant_table WHERE email = '$user_email'";
    $user_result = $conn->query($user_sql);

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_name = $user['fname'] . ' ' . $user['lname'];
        $profile_image = !empty($user['profile_image']) ? base64_encode($user['profile_image']) : null;
    }

    // Fetch jobs applied by the current user, including the scheduled date
$user_id_sql = "
SELECT 
    cl.job_id, 
    cl.job_title, 
    cl.company_name, 
    cl.date_applied, 
    cl.job_location, 
    cl.status, 
    cl.deployment_status, 
    s.scheduled_date 
FROM candidate_list cl
LEFT JOIN schedule_table s ON cl.job_title = s.job_title 
    AND cl.company_name = s.company_name 
    AND cl.full_name = CONCAT(?, ' ', ?) 
WHERE cl.userid = (SELECT userid FROM applicant_table WHERE email = ?)
";

// Prepare and bind parameters
$stmt = $conn->prepare($user_id_sql);
$stmt->bind_param('sss', $user['fname'], $user['lname'], $user_email);
$stmt->execute();
$jobs_result = $stmt->get_result();

    // Sort jobs into categories based on status and deployment_status
    if ($jobs_result->num_rows > 0) {
        while ($job = $jobs_result->fetch_assoc()) {
            if ($job['status'] == 'Pending' || ($job['status'] == 'Approved' && $job['deployment_status'] == 'Pending')) {
                $applied_jobs[] = $job;
            } elseif ($job['deployment_status'] == 'Scheduled') {
                $interview_jobs[] = $job;
            } elseif ($job['deployment_status'] == 'Interviewed') {
                $interviewed_jobs[] = $job; 
            }elseif ($job['deployment_status'] == 'For Deployment') {
                $for_deployment_jobs[] = $job;
            } elseif ($job['deployment_status'] == 'Deployed') {
                $current_jobs[] = $job;
            }
        }
    }
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

// Close the connection
$conn->close();
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
        <!--Desktop Nav-->
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
                    <li><a href="Jobs.php" onclick="toggleMenu()">Jobs</a></li>
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

        <!-- Content -->
        <section class="myjobs-section">
            <div class="main-container">
                <div class="back-button">
                    <a href="#" onclick="history.back()">
                        <i class="fas fa-arrow-left"></i>Back
                    </a>
                </div>
                <h1 class="title1">My Jobs</h1>
                <div class="tabs">
                    <div class="tab active" onclick="openTab('applied')">Applied</div>
                    <div class="tab" onclick="openTab('interview')">Scheduled</div>
                    <div class="tab" onclick="openTab('interviewed')">Interviewed</div>
                    <div class="tab" onclick="openTab('fordeployment')">For Deployment</div>
                    <div class="tab" onclick="openTab('currentjob')">Current Job</div>
                    <div class="tab" onclick="openTab('rejected')">Rejected</div>
                </div>

                <div id="applied" class="tab-content active">
                    <ul class="job-list">
                        <?php if (!empty($applied_jobs)): ?>
                            <?php foreach ($applied_jobs as $job): ?>
                                <li>
                                    <div class="jobs-card" onclick="redirectTo('JobDetails.php?id=<?php echo $job['job_id']; ?>')">
                                        <div class="job-header">
                                            <h3 id="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                        </div>
                                        <div class="company-box">
                                            <p id="company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                            <p id="location"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($job['job_location']); ?></p>
                                            <p id="date">Date Applied: </i><?php echo date('m/d/Y', strtotime($job['date_applied'])); ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-message" style="display: flex;">
                                <img src="images/findjobs.png" alt="">
                                <p>You haven't applied for a job.<br>Look for jobs.</p>
                                <button onclick="redirectTo('Jobs.php')">Find Jobs</button>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>

                <div id="interview" class="tab-content">
                    <ul class="job-list">
                        <?php if (!empty($interview_jobs)): ?>
                            <?php foreach ($interview_jobs as $job): ?>
                                <li>
                                    <div class="jobs-card" onclick="redirectTo('JobDetails.php?id=<?php echo $job['job_id']; ?>')">
                                        <div class="job-header">
                                            <h3 id="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                        </div>
                                        <div class="company-box">
                                            <p id="company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                            <p id="location"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($job['job_location']); ?></p>
                                            <p id="date"><i class="fas fa-calendar-alt"></i><?php echo date('m/d/Y', strtotime($job['scheduled_date'])); ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-message" style="display: flex;">
                                <img src="images/findjobs.png" alt="">
                                <p>You have no interviews for a job.<br>Look for jobs.</p>
                                <button onclick="redirectTo('Jobs.php')">Find Jobs</button>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>

                <div id="interviewed" class="tab-content">
                    <ul class="job-list">
                        <?php if (!empty($interviewed_jobs)): ?>
                            <?php foreach ($interviewed_jobs as $job): ?>
                                <li>
                                    <div class="jobs-card" onclick="redirectTo('JobDetails.php?id=<?php echo $job['job_id']; ?>')">
                                        <div class="job-header">
                                            <h3 id="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                        </div>
                                        <div class="company-box">
                                            <p id="company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                            <p id="location"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($job['job_location']); ?></p>
                                            <p id="date">Date Applied: </i><?php echo date('m/d/Y', strtotime($job['date_applied'])); ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-message" style="display: flex;">
                                <img src="images/findjobs.png" alt="">
                                <p>You have no finished interviews yet.<br>Look for jobs.</p>
                                <button onclick="redirectTo('Jobs.php')">Find Jobs</button>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>

                <div id="fordeployment" class="tab-content">
                    <ul class="job-list">
                        <?php if (!empty($for_deployment_jobs)): ?>
                            <?php foreach ($for_deployment_jobs as $job): ?>
                                <li>
                                    <div class="jobs-card" onclick="redirectTo('JobDetails.php?id=<?php echo $job['job_id']; ?>')">
                                        <div class="job-header">
                                            <h3 id="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                        </div>
                                        <div class="company-box">
                                            <p id="company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                            <p id="location"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($job['job_location']); ?></p>
                                            <p id="date">Date Applied: </i><?php echo date('m/d/Y', strtotime($job['date_applied'])); ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-message" style="display: flex;">
                                <img src="images/findjobs.png" alt="">
                                <p>No jobs for deployment yet.<br>Look for jobs.</p>
                                <button onclick="redirectTo('Jobs.php')">Find Jobs</button>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>

                <div id="currentjob" class="tab-content">
                    <ul class="job-list">
                        <?php if (!empty($current_jobs)): ?>
                            <?php foreach ($current_jobs as $job): ?>
                                <li>
                                    <div class="jobs-card" onclick="redirectTo('JobDetails.php?id=<?php echo $job['job_id']; ?>')">
                                        <div class="job-header">
                                            <h3 id="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                        </div>
                                        <div class="company-box">
                                            <p id="company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                            <p id="location"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($job['job_location']); ?></p>
                                            <p id="date">Date Applied: </i><?php echo date('m/d/Y', strtotime($job['date_applied'])); ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-message" style="display: flex;">
                                <img src="images/findjobs.png" alt="">
                                <p>You have no current job.<br>Look for jobs yet.</p>
                                <button onclick="redirectTo('Jobs.php')">Find Jobs</button>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>

                <div id="rejected" class="tab-content">
                    <ul class="job-list">
                    <div class="empty-message" style="display: flex;">
                        <img src="images/findjobs.png" alt="">
                        <p>You have no declined job submission.<br>Look for jobs.</p>
                        <button onclick="redirectTo('Jobs.php')">Find Jobs</button>
                    </div>
                    </ul>
                    <!-- Empty message should be outside the job list -->
                    <div class="empty-message" style="display: none;">
                        <img src="images/findjobs.png" alt="">
                        <p>You have no job declined submission.<br>Look for jobs.</p>
                        <button onclick="redirectTo('Jobs.php')">Find Jobs</button>
                    </div>
                </div>
            </div>
        </section>

        <div class="shape-container2">
            <div class="rectangle-4"></div>
            <div class="rectangle-5"></div>
        </div>

        <footer class="footer-distributed">
            <div class="footer-left">
                <a href="#"><img src="images/logo.png" alt="Company Logo"></a>
                <p class="footer-company-name">Copyright © 1992 <strong>RCVJ, Inc.</strong></p>
            </div>
            <div class="footer-center">
                <div>
                    <i class="fa fa-map-marker"></i>
                    <p><span>Dasmariñas, Philippines</span> 3rd Floor RCVJ Bldg. Don P. Campos Ave.</p>
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
    </body>
</html>

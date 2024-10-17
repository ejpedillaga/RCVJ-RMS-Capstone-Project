<?php
include 'connection.php';
$conn = connection();
session_start();

// Check if the user is logged in and fetch their full name
$user_name = 'Sign Up / Sign In'; // Default value
$profile_image = null; // Initialize profile image

if (isset($_SESSION['user'])) {
    $user_email = $_SESSION['user'];
    
    $stmt = $conn->prepare("SELECT fname, lname, profile_image FROM applicant_table WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_name = $user['fname'] . ' ' . $user['lname'];
        $profile_image = !empty($user['profile_image']) ? base64_encode($user['profile_image']) : null;
    }
    $stmt->close();
}

// Get company name from query parameter
$company_name = isset($_GET['company_name']) ? $_GET['company_name'] : '';

// Prepare and execute SQL query to get company details
$sql = "SELECT logo, company_name, company_location, industry, company_description FROM partner_table WHERE company_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $company_name);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

if ($company && isset($company['logo'])) {
    $company['logo'] = base64_encode($company['logo']);
}

$stmt->close();

// Prepare and execute SQL query to get job listings for the company with 'open' status
$sql_jobs = "SELECT id, job_title, job_location, date_posted, job_candidates FROM job_table WHERE company_name = ? AND job_status = 'open'";
$stmt_jobs = $conn->prepare($sql_jobs);
$stmt_jobs->bind_param("s", $company_name);
$stmt_jobs->execute();
$result_jobs = $stmt_jobs->get_result();

$stmt_jobs->close();
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
                    <li><a class="active" href="#">Partner Companies</a></li>
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
                    <li><a class="active" href="#" onclick="toggleMenu()">Partner Companies</a></li>
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

        <section class="companyprofile-section">
            <div class="main-container">
                <div class="back-button">
                    <a href="#" onclick="history.back()">
                        <i class="fas fa-arrow-left"></i>Back
                    </a>
                </div>
                <div class="companyprofile-header">
                    <img id="company-logo" src="data:image/jpeg;base64,<?php echo htmlspecialchars($company['logo']); ?>" alt="Company Logo">
                    <h1 class="title3"><?php echo htmlspecialchars($company['company_name']); ?></h1>
                </div>

                <div class="tabs">
                    <div class="tab active" onclick="openTab('about')">About</div>
                    <div class="tab" onclick="openTab('jobs')">Jobs</div>
                </div>
                
                <div id="about" class="tab-content active">
                    <h2>About</h2>
                    <div class="category">
                        <h4>Industry:</h4>
                        <p id="industry"><?php echo htmlspecialchars($company['industry']); ?></p>
                    </div>
                    <div class="category">
                        <h4>Location:</h4>
                        <p id="location"><?php echo htmlspecialchars($company['company_location']); ?></p>
                    </div>
                    <p id="about-description"><?php echo nl2br(htmlspecialchars($company['company_description'])); ?></p>
                </div>
                
                <div id="jobs" class="tab-content">
                    <ul>
                        <?php while($job = $result_jobs->fetch_assoc()): ?>
                            <li>
                            <div class="jobs-card" onclick="redirectTo('JobDetails.php?id=<?php echo $job['id']; ?>')">
                                    <div class="job-header">
                                        <h3><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                        <h4><?php echo '(' . htmlspecialchars($job['job_candidates']) . ')'; ?></h4>
                                    </div>
                                    <div class="company-box">
                                        <p><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($job['job_location']); ?></p>
                                        <p><i class="fas fa-calendar-alt"></i><?php echo htmlspecialchars(date("m/d/Y", strtotime($job['date_posted']))); ?></p>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </section>

        <div class="shape-container2" style="filter: blur(10px);">
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
    </body>
</html>
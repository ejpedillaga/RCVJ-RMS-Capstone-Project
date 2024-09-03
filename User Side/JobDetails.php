<?php
include 'connection.php';
$conn = connection();

// Start session and check for logged-in user
session_start();
$user_name = 'Sign Up'; // Default username if not logged in

if (isset($_SESSION['user'])) {
    // Fetch user's full name from the session
    $user_email = $_SESSION['user'];
    
    // Use the existing database connection
    $sql = "SELECT fname, lname FROM applicant_table WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_name = $user['fname'] . ' ' . $user['lname'];
    }
    $stmt->close();
}

// Get job ID from query parameter
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Prepare and execute SQL query with JOIN
$sql = "SELECT job_table.job_title, job_table.job_location, job_table.job_candidates, job_table.company_name, job_table.job_description, job_table.date_posted, partner_table.logo
        FROM job_table
        JOIN partner_table ON job_table.company_name = partner_table.company_name
        WHERE job_table.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();
$company_name = $job['company_name'];
$stmt->close();
$conn->close();
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
            <button onclick="redirectTo('UserProfile.php')"><?php echo htmlspecialchars($user_name); ?></button>
        </div>
    </nav>

    <!---Burger Nav-->
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
                <li><a href="Home.php" onclick="toggleMenu()">Home</a></li>
                <li><a class="active" href="#" onclick="toggleMenu()">Jobs</a></li>
                <li><a href="About.php" onclick="toggleMenu()">About</a></li>
                <li><a href="Partner.php" onclick="toggleMenu()">Partner Companies</a></li>
                <div class="nav-acc">
                    <img src="images/user.svg" alt="">
                    <button onclick="redirectTo('UserProfile.php')"><?php echo htmlspecialchars($user_name); ?></button>
                </div>
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
                    <p id="location"><?php echo htmlspecialchars($job['job_location']); ?></p>
                    <p id="date-posted">Posted on: <?php echo htmlspecialchars(date('m/d/Y', strtotime($job['date_posted']))); ?></p>
                    <p id="available">Available Spots: <?php echo htmlspecialchars($job['job_candidates']); ?></p>
                    <div class="buttons-container">
                        <button class="button-apply">Apply</button>
                        <button class="button-cp" onclick="redirectTo('CompanyProfile.php?company_name=<?php echo urlencode($company_name); ?>')">Company Profile</button>
                    </div>
                </div>
                <img id="company-logo" src="data:image/jpeg;base64,<?php echo htmlspecialchars($job['logo']); ?>" alt="Company Logo">
            </div>
            <div class="desc-box">
                <h3>Job Description</h3>
                <p id="description"><?php echo nl2br(htmlspecialchars($job['job_description'])); ?></p>
                <h3>Qualifications</h3>
                <ul id="qualifications list">
                    <li>Sample</li>
                    <li>Sample</li>
                    <li>Sample</li>
                    <li>Sample</li>
                    <li>Sample</li>
                </ul>
                <h3>Skills</h3>
                <ul id="skills list">
                    <li>Sample</li>
                    <li>Sample</li>
                    <li>Sample</li>
                    <li>Sample</li>
                    <li>Sample</li>
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
    <script defer src="script.js"></script>
</body>
</html>

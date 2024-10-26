<?php
include 'connection.php';
$conn = connection();
session_start();

// Initialize user name, profile image, and user-specific data
$user_name = 'Sign Up / Sign In'; // Default value
$profile_image = null;
$user_email = null;
$user_data = [];

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    $user_email = $_SESSION['user'];

    // Fetch user's information
    $user_sql = "SELECT fname, lname, profile_image, gender, location, classi, subclassi 
                 FROM applicant_table WHERE email = '$user_email'";
    $user_result = $conn->query($user_sql);

    if ($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_name = $user_data['fname'] . ' ' . $user_data['lname'];
        $profile_image = !empty($user_data['profile_image']) ? base64_encode($user_data['profile_image']) : null;
    }
}

// Extract user-specific data safely using null coalescing operators
$classi = $user_data['classi'] ?? '';
$subclassi = $user_data['subclassi'] ?? '';
$gender = $user_data['gender'] ?? '';
$location = $user_data['location'] ?? '';

// Build the SQL query to fetch jobs with user-specific sorting if applicable
$sql = "
    SELECT 
        jt.id, jt.job_title, jt.job_location, jt.job_candidates, 
        jt.company_name, jt.job_description, jt.date_posted,
        jtt.classification, jtt.subclassification, jtt.gender,
        CASE
            WHEN '$user_email' IS NOT NULL THEN (
                (jtt.classification = '$classi') +
                (jtt.subclassification = '$subclassi') +
                (jtt.gender = '$gender') +
                (jt.job_location = '$location')
            )
            ELSE 0
        END AS match_score
    FROM 
        job_table jt
    INNER JOIN 
        job_title_table jtt ON jt.job_title_id = jtt.id
    WHERE 
        jt.job_status = 'open'
    ORDER BY 
        match_score DESC, jt.date_posted DESC
";

$jobs_result = $conn->query($sql); // Execute the query

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Jobs | RCVJ, Inc.</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
        <link rel="stylesheet" href="mediaqueries.css?v=<?php echo filemtime('mediaqueries.css'); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="apple-touch-icon" sizes="180x180" href="rcvj-logo/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="rcvj-logo/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="rcvj-logo/favicon-16x16.png">
        <link rel="manifest" href="rcvj-logo/site.webmanifest">
        <script src="//code.tidio.co/nbcdppxlzihdvensqd3g6wcz8k3yqzzp.js" async></script>
    </head>
    <body>
       <!--Desktop Nav-->
        <nav class="desktopnav" id="desktop-nav">
            <div class="logo">
                <img src="images/logo.png" alt="">
            </div>
            <div>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
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
                    <li><a href="index.php" onclick="toggleMenu()">Home</a></li>
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

        <section class="jobs-section">  
            <div class="main-container-jobs">
                <div class="jobs-header">
                    <h1 class="title6">Be part of <span style="color: #2C1875;">RCVJ, Inc.</span></h1>
                    <img src="images/jobs.png" alt="">
                </div>

                <!--Search Bar-->
                <div class="search-box">
                <div class="search-input" id="job-title">
                    <i class="search-icon fas fa-search"></i>
                    <input type="text" name="job_title" placeholder="Job title, Company" onkeyup="searchByTitleOrCompany()">
                </div>
                <div class="search-input" id="location">
                    <i class="search-icon fas fa-map-marker-alt"></i>
                    <input type="text" name="location" placeholder="City, Municipality" onkeyup="searchByLocation()">
                </div>
            </div><br>
                
                <!--List of Jobs-->
                <div class="jobs-main-container">
                    <div id="no-results-message" style="display: none; color: #999; text-align: center; font-weight: bold; font-size: 1.5rem;">No result found.</div>
                    <ul>
                        <?php
                        if ($jobs_result->num_rows > 0) {
                            while ($row = $jobs_result->fetch_assoc()) {
                                // Check if the job is a good match
                                $isGoodMatch = isset($row['match_score']) && $row['match_score'] > 2;

                                echo '<li>';
                                echo '<div class="jobs-card" onclick="window.location.href=\'JobDetails.php?id=' . $row["id"] . '\'">';
                                
                                // Job Header with Good Match Indicator
                                echo '<div class="job-header">';
                                echo '<h3 id="job-title">' . htmlspecialchars($row["job_title"]) . '</h3>';
                                echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
                                echo '<h4 id="available">(' . $row["job_candidates"] . ')</h4>';
                                if ($isGoodMatch) {
                                    echo '<div id="rec" class="good-match-badge">For You<span class="tooltiptext">This job is a good match for your profile.</span></div>';
                                }
                                echo '</div>';
                                echo '</div>';
                                
                                // Company and Location Details
                                echo '<div class="company-box">';
                                echo '<p style="margin-top: 0.5rem; padding-bottom: 0.5rem; height:2rem;" id="company-name">' . htmlspecialchars($row["company_name"]) . '</p>';
                                echo '<p id="location"><i class="location fas fa-map-marker-alt"></i>' . htmlspecialchars($row["job_location"]) . '</p>';
                                
                                // Date Posted
                                $datePosted = new DateTime($row["date_posted"]);
                                $formattedDate = $datePosted->format('m/d/Y');
                                echo '<p style="margin-top: 5px" id="date"><i class="fas fa-calendar-alt"></i> ' . $formattedDate . '</p>';
                                echo '</div>';

                                echo '</div>';
                                echo '</li>';
                            }
                        } else {
                            echo '<li>No jobs found.</li>';
                        }
                        ?>
                    </ul>
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
            function searchJobs(querySelector, inputName) {
            const input = document.querySelector(`input[name="${inputName}"]`).value.toLowerCase();
            const jobCards = document.querySelectorAll('.jobs-card');
            let hasVisibleJobs = false;

            jobCards.forEach(card => {
                const jobData = card.querySelector(querySelector).textContent.toLowerCase();
                const isVisible = jobData.includes(input);

                card.parentElement.style.display = isVisible ? '' : 'none';
                hasVisibleJobs = hasVisibleJobs || isVisible;
            });

            document.getElementById('no-results-message').style.display = hasVisibleJobs ? 'none' : 'block';
        }

        function searchByTitleOrCompany() {
            searchJobs('#job-title, #company-name', 'job_title');
        }

        function searchByLocation() {
            searchJobs('#location', 'location');
        }
        </script>
    </body>
</html>

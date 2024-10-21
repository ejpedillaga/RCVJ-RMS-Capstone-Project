<?php
session_start();

$user_name = 'Sign Up / Sign In'; // Default value
$profile_image = null; // Initialize the profile_image variable

// Database connection details
$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "admin_database";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user'])) {
    // Fetch user's email from the session
    $user_email = $_SESSION['user'];
    // Fetch the full name and profile image from the applicant_table
    $sql = "SELECT fname, lname, profile_image FROM applicant_table WHERE email = '$user_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_name = $user['fname'] . ' ' . $user['lname'];

        // Fetch the profile image
        $profile_image = !empty($user['profile_image']) ? base64_encode($user['profile_image']) : null;
    }
    // Close the connection
    
}
// Query to get the top 4 companies with the most job posts and their logos
$sql_top_partners = "SELECT partner_table.company_name, partner_table.logo FROM partner_table
                     INNER JOIN job_table ON partner_table.company_name = job_table.company_name
                     WHERE job_table.job_status = 'Open' -- Adjust this condition based on your actual statuses
                     GROUP BY partner_table.company_name, partner_table.logo
                     ORDER BY COUNT(job_table.id) DESC
                     LIMIT 4";


    $result_top_partners = $conn->query($sql_top_partners);

    $partners = [];
    if ($result_top_partners->num_rows > 0) {
    while ($row = $result_top_partners->fetch_assoc()) {
    $partners[] = $row;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Home | RCVJ, Inc.</title>
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
                    <li><a class="active" href="#">Home</a></li>
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
                    <li><a class="active" href="#" onclick="toggleMenu()">Home</a></li>
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
         <section class="home-section">
            <div class="main-container">
                <h1 class="title1">Start working with</h1>
                <h1 class="title2">RCVJ, Inc.</h1>
                <div class="searchbar1">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="icon">
                      <g>
                        <path
                          d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z"
                        ></path>
                      </g>
                    </svg>
                    <input class="input" type="search" placeholder="Jobs, Skills..." />
                </div>

                <div class="content-container">
                    <h1 class="title3">Don't know where to start?</h1>
                </div>
                <div class="tags-container">
                    <div class="tags">Bagger</div>
                    <div class="tags">Driver</div>
                    <div class="tags">Welder</div>
                    <div class="tags">Gardener</div>
                    <div class="tags">Ground Maintenance</div>
                </div>

                <div class="content-container">
                    <h1 class="title3">Partner Companies</h1>
                    <h1 class="title4">Get started with our best clients</h1>
                </div>
                <!--
                <div class="partner-container">
                    <div class="partner1" onclick="redirectTo('CompanyProfile.php')">
                        <img src="images/waltermart.png" alt="">
                    </div>
                    <div class="partner2" onclick="redirectTo('CompanyProfile.php')">
                        <img src="images/DLSU HSI.png" alt="">
                    </div>
                    <div class="partner1" onclick="redirectTo('CompanyProfile.php')">
                        <img src="images/scipark.png" alt="">
                    </div>
                    <div class="partner2" onclick="redirectTo('CompanyProfile.php')">
                        <img src="images/lpu.jpg" alt="">
                    </div>
                </div>
                        -->
                <div class="partner-container">
                    <?php 
                    $isPartner1 = true; // Flag to toggle classes
                    foreach ($partners as $partner): 
                        // Choose the class based on the flag
                        $partnerClass = $isPartner1 ? 'partner1' : 'partner2';
                        $isPartner1 = !$isPartner1; // Toggle the flag for the next iteration
                    ?>
                        <div class="<?php echo $partnerClass; ?>" onclick="redirectTo('CompanyProfile.php?company_name=<?php echo urlencode($partner['company_name']); ?>')">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($partner['logo']); ?>" alt="<?php echo htmlspecialchars($partner['company_name']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                
            </div>
    
           <div class="shape-container1">
                <div class="rectangle-1"></div>
                <div class="rectangle-2"></div>
                <div class="rectangle-3"><img src="images/bagger.jpg" alt=""></div>
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
    </body>
</html>
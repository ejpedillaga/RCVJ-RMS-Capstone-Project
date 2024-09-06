<?php
session_start();

if (isset($_SESSION['user'])) {
    // Fetch user's full name from the session
    $user_email = $_SESSION['user'];

    $servername = "localhost";
    $username = "root";
    $password = "12345";
    $dbname = "admin_database";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT fname, lname FROM applicant_table WHERE email = '$user_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_name = $user['fname'] . ' ' . $user['lname'];
    } else {
        $user_name = 'User';
    }

    $conn->close();
} else {
    $user_name = 'Sign Up';
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
                        <li><a class="active" href="#" onclick="toggleMenu()">Home</a></li>
                        <li><a href="Jobs.php" onclick="toggleMenu()">Jobs</a></li>
                        <li><a href="About.php" onclick="toggleMenu()">About</a></li>
                        <li><a href="Partner.php" onclick="toggleMenu()">Partner Companies</a></li>
                        <div class="nav-acc">
                            <img src="images/user.svg" alt="">
                            <button onclick="redirectTo('UserProfile.php')">Sign Up</button>
                        </div>
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
        <script defer src="script.js"></script>
    </body>
</html>
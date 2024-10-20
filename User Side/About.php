<?php
session_start();

if (isset($_SESSION['user'])) {
    // Fetch user's email from the session
    $user_email = $_SESSION['user'];

    $servername = "localhost";
    $username = "root";
    $password = "12345";
    $dbname = "admin_database";

    // Create a new database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user's full name and profile image
    $sql = "SELECT fname, lname, profile_image FROM applicant_table WHERE email = '$user_email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_name = $user['fname'] . ' ' . $user['lname'];
        $profile_image = !empty($user['profile_image']) ? base64_encode($user['profile_image']) : null;
    } else {
        $user_name = 'User';
        $profile_image = null; // Default to null if no profile image is found
    }

    // Close the database connection
    $conn->close();
} else {
    $user_name = 'Sign Up / Sign In'; // Default value
    $profile_image = null; // Default to null if user is not logged in
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
        <!--Desktop Nav-->
        <nav class="desktopnav" id="desktop-nav">
            <div class="logo">
                <img src="images/logo.png" alt="">
            </div>
            <div>
                <ul class="nav-links">
                    <li><a href="Home.php">Home</a></li>
                    <li><a href="Jobs.php">Jobs</a></li>
                    <li><a class="active" href="#">About</a></li>
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
                    <li><a class="active" href="#" onclick="toggleMenu()">About</a></li>
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

        <section class="about-section">
        <div class="main-container">
            <div class="flip-card">
                <div class="flip-card-inner">
                <div class="flip-card-front">
                    <div class="content">
                    <img src="images/about.png" alt="">
                    <h1 class="title5">WHO IS RCVJ?</h1>
                    </div>
                </div>
                <div class="flip-card-back">
                    <p class="rcvj-description">
                    <span style="font-weight: 500;"> RCVJ, Incorporated </span>was established in October 09, 1992, with Hon. Recto M. Cantimbuhan as President and his wife Mrs. Veronica J. Cantimbuhan as Treasurer and Vice President. RCVJ provides manpower and janitorial services to prestigious companies in Cavite, Laguna, and other places in Luzon.
                    </p>
                    <div class="rcvj-section">
                    <p class="rcvj-title">RCVJ Stands for:</p>
                    <ul class="rcvj-list">
                        <li>R - Recto</li>
                        <li>C - Cantimbuhan</li>
                        <li>V - Veronica</li>
                        <li>J - Jarata</li>
                    </ul>
                    </div>
                </div>
                </div>
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
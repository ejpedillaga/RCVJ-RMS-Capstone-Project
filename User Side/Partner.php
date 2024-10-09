<?php
include 'connection.php';

$conn = connection();
session_start();

// Fetch partner company logos and names
$sql = "SELECT logo, company_name FROM partner_table";
$result = $conn->query($sql);

// Initialize user data variables
$user_name = 'Sign Up'; // Default if not logged in
$profile_image = null; // Initialize profile image

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    $user_email = $_SESSION['user'];

    // Use a prepared statement to safely query user data
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

// Close the database connection
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

        <section class="partner-section">
            <div class="main-container">
                <!--Header-->
                <div class="partner-header">
                    <div class="search-container">
                        <div class="search-box">
                            <div class="search-input" id="partner-companies">
                                <i class="search-icon fas fa-search"></i>
                                <input type="text" placeholder="Company Name" id="search-input" onkeyup="searchPartners()">
                            </div>
                        </div>
                    </div>
                    <h1 class="title7">Find a company for you from our <span style="color: #EF9B50;">partners</span></h3>
                </div>

                <!--Body-->
                <h1 class="title3" style="margin-top: 2rem;">Partner Companies</h1>     
                <div id="partner-results" class="partner-main-container">
                    <div id="no-results-message" style="display: none; color: #999; text-align: center; font-weight: bold; font-size: 1.5rem;">No available companies found.</div>
                    <ul>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                if (isset($row['logo'])) {
                                    $row['logo'] = base64_encode($row['logo']);
                                }
                                echo '<li>';
                                echo '<div class="partner-card" onclick="window.location.href=\'CompanyProfile.php?company_name=' . urlencode($row['company_name']) . '\'">';
                                echo '<img id="company-logo" src="data:image/jpeg;base64,' . htmlspecialchars($row['logo']) . '" alt="' . htmlspecialchars($row['company_name']) . '">';
                                echo '<p id="company-name">' . htmlspecialchars($row['company_name']) . '</p>';
                                echo '</div>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ul>
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
        <script defer src="script.js"></script>
        <script>
            function searchPartners() {
                // Get the input value
                var input = document.getElementById('search-input').value.toLowerCase();
                
                // Get the list of partner company names
                var partnerCards = document.querySelectorAll('.partner-card');
                var hasVisibleCompanies = false;  // Track if any company is visible
                
                // Loop through the partner cards and hide those that do not match the search query
                partnerCards.forEach(function(card) {
                    var companyName = card.querySelector('#company-name').textContent.toLowerCase();
                    if (companyName.includes(input)) {
                        card.parentElement.style.display = '';  // Show the matching cards
                        hasVisibleCompanies = true;  // At least one company is visible
                    } else {
                        card.parentElement.style.display = 'none';  // Hide the non-matching cards
                    }
                });
                
                // Show or hide the "no results" message
                var noResultsMessage = document.getElementById('no-results-message');
                if (hasVisibleCompanies) {
                    noResultsMessage.style.display = 'none';  // Hide the message if there are visible companies
                } else {
                    noResultsMessage.style.display = 'block';  // Show the message if no companies are visible
                }
            }
        </script>
    </body>
</html>
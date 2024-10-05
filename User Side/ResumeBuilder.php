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
                    <li><a href="Home.html">Home</a></li>
                    <li><a href="Jobs.html">Jobs</a></li>
                    <li><a href="About.html">About</a></li>
                    <li><a href="Partner.html">Partner Companies</a></li>
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
                    <button id="profile">Juan</button>
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
                        <li><a href="Jobs.html" onclick="toggleMenu()">Jobs</a></li>
                        <li><a href="About.html" onclick="toggleMenu()">About</a></li>
                        <li><a href="Partner.html" onclick="toggleMenu()">Partner Companies</a></li>
                        <div class="nav-acc">
                            <img src="images/user.svg" alt="">
                            <button id="profile">User Name</button>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!--Content goes here-->
            <!--Usual Structure-->
            <section class="resume-builder-section">
                <div id="resume_builder_container" class="main-container">
                    <!--Content-->
                    <form id="resume_builder_form">
                        <h1>Personal Information</h1>
                        <table>
                            <tr id="rowName">
                                <td>
                                  <label for="first-name">Name:</label>
                                </td>
                                <td>
                                  <input type="text" id="first-name" name="first-name" placeholder="First Name">
                                  <input type="text" id="middle-name" name="middle-name" placeholder="Middle Name">
                                  <input type="text" id="last-name" name="last-name" placeholder="Last Name">
                                </td>
                            </tr>

                            <tr>
                                <td><label for="email">Email Address:</label></td>
                                <td><input type="email" id="email" name="email" placeholder="Email Address"></td>
                            </tr>
                            <tr>
                                <td><label for="mobile-number">Mobile Number:</label></td>
                                <td><input type="tel" id="mobile-number" name="mobile-number" placeholder="Mobile Number"></td>
                            </tr>
                            <tr>
                                <td><label for="birthdate">Birthdate:</label></td>
                                <td><input type="date" id="birthdate" name="birthdate"></td>
                            </tr>
                            <tr>
                                <td><label for="gender">Gender:</label></td>
                                <td id="genderRadio">
                                    <input type="radio" id="male" name="gender" value="male">
                                    <label for="male">Male</label>
                                    <input type="radio" id="female" name="gender" value="female">
                                    <label for="female">Female</label>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="address">Address:</label></td>
                                <td><input type="text" id="address" name="no-street-brgy" placeholder="No./Street/Brgy"></td>
                            </tr>
                            <tr>
                                <td><label for="city">City:</label></td>
                                <td><input type="text" id="city" name="city" placeholder="City"></td>
                            </tr>
                            <tr>
                                <td><label for="province">Province:</label></td>
                                <td><input type="text" id="province" name="province" placeholder="Province"></td>
                            </tr>                            
                            <tr>
                                <td><label for="height">Height:</label></td>
                                <td><input type="number" id="height" name="height" placeholder="Height"></td>
                            </tr>
                            <tr>
                                <td><label for="weight">Weight:</label></td>
                                <td><input type="number" id="weight" name="weight" placeholder="Weight"></td>
                            </tr>
                        </table>

                        <h1>Educational Background</h1>
                        <table>
                            <tr>
                                <td><label for="degree">Degree:</label></td>
                                <td><input type="text" id="degree" name="degree" placeholder="Degree"></td>
                            </tr>
                            <tr>
                                <td><label for="course">Course:</label></td>
                                <td><input type="text" id="course" name="course" placeholder="Course"></td>
                            </tr>
                            <tr>
                                <td><label for="school">School Attended:</label></td>
                                <td><input type="text" id="school" name="school" placeholder="School"></td>
                            </tr>
                            <tr>
                                <td><label for="start-date">Start Date:</label></td>
                                <td><input type="date" id="start-date" name="start-date"></td>
                            </tr>
                            <tr>
                                <td><label for="year-graduated">Year Graduated:</label></td>
                                <td><input type="date" id="year-graduated" name="year-graduated"></td>
                            </tr>
                        </table>

                        <h1>Work Experience</h1>
                        <table>
                            <tr>
                                <td><label for="company-name">Name of Company:</label></td>
                                <td><input type="text" id="company-name" name="company-name" placeholder="Company Name"></td>
                            </tr>
                            <tr>
                                <td><label for="position">Position:</label></td>
                                <td><input type="text" id="position" name="position" placeholder="Position Title"></td>
                            </tr>
                            <tr>
                                <td><label for="company-address">Company Address:</label></td>
                                <td><input type="text" id="company-address" name="company-address" placeholder="Company Address"></td>
                            </tr>
                            <tr>
                                <td><label for="start-date-work">Start Date:</label></td>
                                <td><input type="date" id="start-date-work" name="start-date-work" ></td>
                            </tr>
                            <tr>
                                <td><label for="finished-contract">Finished Contract:</label></td>
                                <td><input type="date" id="finished-contract" name="finished-contract"></td>
                            </tr>
                        </table>

                        <h1>Documents</h1>
                        <div class="upload-container">
                            <form id="upload-form" action="upload-handler.php" method="post" enctype="multipart/form-data">
                                <!-- Resume Upload -->
                                <div class="form-group">
                                    <label for="resume-upload">Upload Resume</label>
                                    <input type="file" id="resume-upload" name="resume" class="input-field">
                                </div>
                                
                                <!-- Certificate Upload -->
                                <div class="form-group">
                                    <label for="certificate-upload">Upload Certificate</label>
                                    <input type="file" id="certificate-upload" name="certificate" class="input-field">
                                </div>
                            </div>
                        <button onclick="submitForm()" id="submit_button">Submit</button>
                        <button id="cancel_button">Cancel</button>                  
                    </form>

                    <dialog id="submit_dialog">
                        <p>
                            You have successfully submitted your resume. Please wait for the job update. Thank You!
                        </p>
                        <br>
                        <button onclick="closeSubmitDialog()" type="button">Close</button>
                    </dialog>

                </div>
            </section>

            <div id="rBuilder_sContainer" class="shape-container">
                <div class="rectangle-1"></div>
                <div class="rectangle-2"></div>
            </div>

            <!--Footer-->
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
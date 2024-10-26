<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection details
include 'connection.php';

// Create a connection
$conn = connection();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $cpassword = $conn->real_escape_string($_POST['cpassword']);
    $fname = $conn->real_escape_string($_POST['fname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $birthday = isset($_POST['date']) ? $conn->real_escape_string($_POST['date']) : null;
    $location = $conn->real_escape_string($_POST['loc']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $classi = $conn->real_escape_string($_POST['classi']);
    $subclassi = $conn->real_escape_string($_POST['subclassi']);

    if ($password !== $cpassword) {
        $message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Profile image set to NULL
        $profile_image = 'NULL'; 

        $sql = "INSERT INTO applicant_table (email, password, fname, lname, gender, birthday, location, phone, classi, subclassi)
                VALUES ('$email', '$hashed_password', '$fname', '$lname', '$gender', '$birthday', '$location', '$phone', '$classi', '$subclassi')";

        if ($conn->query($sql) === TRUE) {
            $message = "Registration successful!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin_submit'])) {
    $email = $conn->real_escape_string($_POST['signin_email']);
    $password = $conn->real_escape_string($_POST['signin_password']);

    // FETCH
    $sql = "SELECT * FROM applicant_table WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['email'];
            $message = "Login successful! Welcome, " . $user['fname'] . ".";
            header("Location: ../index.php");
            exit;
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "No user found with this email.";
    }
}
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="ApplicantStyle.css" />
    <link rel="apple-touch-icon" sizes="180x180" href="rcvj-logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="rcvj-logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="rcvj-logo/favicon-16x16.png">
    <link rel="manifest" href="rcvj-logo/site.webmanifest">
    <title>Sign In/Sign Up | RCVJ, Inc.</title>
    <style>
        /* POPUP MESSAGE */
        .popup-message {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #2c1875;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
  <body>
    <div class="popup-message" id="popup-message"></div>

    <img class="rcvjlogo" src="RCVJlogo.png" alt="RCVJ Logo" />
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
          <!-- Sign-In Form -->
          <form action="Applicant.php" class="sign-in-form" method="post">
              <h2 class="title">Sign in</h2>
              <div class="input-field">
                  <i class="fas fa-envelope"></i>
                  <input type="email" name="signin_email" placeholder="Email" required />
              </div>
              <div class="input-field">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="signin_password" placeholder="Password" required />
              </div>
              <input type="submit" name="signin_submit" value="Login" class="btn solid" />
              <p class="social-text">Or Sign in as <a href="Employee&Admin.php" class="admin-class"> Employee </a></p>
          </form>

          <form action="#" class="sign-up-form" method="post">
            <h1 class="text-center">Create An Account</h1>
            <!-- Progress bar -->
            <div class="progressbar">
              <div class="progress" id="progress" style="width: 15%;"></div>
                <div class="progress-step progress-step-active" data-title="Credentials"></div>
                <div class="progress-step" data-title="Personal Info"></div>
                <div class="progress-step" data-title="Specialization"></div>
            </div>
            <!-- Steps -->  
            <div class="form-step form-step-active">
                <header>Credentials</header>
                <div class="input-group">
                    <input type="text" name="email" id="email" placeholder="Email"  required />
                    <i class="fa-regular fa-envelope"></i>
                </div>
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="Password"  required/>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="input-group">
                    <input type="password" name="cpassword" id="confirm-password" placeholder="Confirm Password"  required/>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="btns-group">
                    <a href="#" class="btn btn-next">Continue</a>
                </div>
            </div>
            <div class="form-step">
                <header>Personal Info</header>
                <div class="input-group form-group">
                    <div class="input-group-item">
                        <input type="text" name="fname" id="fname" placeholder="First Name"  required/>
                    </div>
                    <div class="input-group-item">
                        <input type="text" name="lname" id="lname" placeholder="Last Name"  required/>
                    </div>
                </div>
                <div class="input-group form-group">
                    <div class="input-group-item">
                        <select name="gender" id="gender">
                            <option value="">Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>   
                        <i class="fa-solid fa-venus-mars"></i>
                    </div>
                    <div class="input-group-item">
                        <div class="input-wrapper">
                          <input placeholder="Birthday" class="textbox-n" type="date" onfocus="(this.type='date')"
                          onblur="(this.type='text')" id="date" name="date"/>
                            <i class="fa-solid fa-cake-candles"></i>
                        </div>
                    </div>

                </div>
                <div class="input-group">
                    <input type="text" name="loc" id="loc" placeholder="Location"  required/>
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="input-group">
                    <input type="text" name="phone" id="phone" placeholder="Contact Number" required/>
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div class="btns-group">
                    <a href="#" class="btn btn-prev">Previous</a>
                    <a href="#" class="btn btn-next">Next</a>
                </div>
            </div>
            <div class="form-step">
                <header>Specialization</header>
                <div class="input-group">
                    <select name="classi" id="classi" class="clas" required onchange="updateSubClassifications()">
                        <option value="">Classification</option>
                        <option value="No Classification">No Classification</option>
                        <option value="Construction and Building Trades">Construction and Building Trades</option>
                        <option value="Mechanical and Technical">Mechanical and Technical</option>
                        <option value="Transportation and Logistics">Transportation and Logistics</option>
                        <option value="Janitorial and Cleaning">Janitorial and Cleaning</option>
                        <option value="Facilities and Operations">Facilities and Operations</option>
                    </select>
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                
                <div class="input-group">
                    <select name="subclassi" id="subclassi" class="clas" required>
                        <option value="">Sub-classification</option>
                    </select>
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="btns-group">
                    <a href="#" class="btn btn-prev">Previous</a>
                    <input type="submit" class="btn btn-finish" name="submit" value="Finish"/>
                </div>
            </div>
          </form>

          
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3>Don't have an account yet?</h3>
            <p>
              Click the button below to create
               an account.
            </p>
            <button class="btn transparent" id="sign-up-btn">
              Sign up
            </button>
          </div>
        </div>
        <div class="panel right-panel">
          <div class="content">
            <h3>Already have an account?</h3>
            <button class="btn transparent" id="sign-in-btn">
              Sign in
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="app.js"></script>
  </body>

  <script>
        function showPopupMessage(message) {
            const popup = document.getElementById('popup-message');
            popup.textContent = message;
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 5000);
        }

        <?php if (!empty($message)): ?>
            window.addEventListener('DOMContentLoaded', () => {
                showPopupMessage("<?php echo addslashes($message); ?>");
            });
        <?php endif; ?>

        const subClassifications = {
        "No Classification": ["General"],
        "Construction and Building Trades": [
            "Carpentry and Woodworking",
            "Masonry and Concrete",
            "Welding and Metalworking"
        ],
        "Mechanical and Technical": [
            "Maintenance and Repair",
            "Plumbing and Piping",
            "Automotive"
        ],
        "Transportation and Logistics": [
            "General Driving",
            "Truck Driving",
            "Transportation Support"
        ],
        "Janitorial and Cleaning": [
            "General Cleaning",
            "Specialized Cleaning",
            "Industrial Cleaning"
        ],
        "Facilities and Operations": [
            "Facility Maintenance and Security",
            "Customer Service",
            "Hospitality and Food Service"
        ]
    };

    function updateSubClassifications() {
        const classificationSelect = document.getElementById('classi');
        const subClassificationSelect = document.getElementById('subclassi');
        const selectedClassi = classificationSelect.value;

        // Clear previous options
        subClassificationSelect.innerHTML = '<option value="">Sub-classification</option>';

        if (subClassifications[selectedClassi]) {
            subClassifications[selectedClassi].forEach(sub => {
                const option = document.createElement('option');
                option.value = sub;
                option.textContent = sub;
                subClassificationSelect.appendChild(option);
            });
        }
    }

    // Ensure sub-classification options reset correctly when the page loads
    window.onload = function () {
        updateSubClassifications();
    };
    </script>
</html>

<?php
session_start();

// Database connection details
include 'connection.php';

// Create a connection
$conn = connection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST["username"];
    $password_input = $_POST["password"];
    $login_type = $_POST["login_type"]; // Get login type (admin or employee)

    // SQL query to fetch the user based on the username and login type
    $sql = "SELECT * FROM users_table WHERE username='$username_input' AND usertype='$login_type'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password_input, $row['password'])) {
            $_SESSION["username"] = $username_input;

            // Redirect based on the login type
            if ($login_type == "admin") {
                header("Location: ../Admin Side-dev/index.html");
            } elseif ($login_type == "employee") {
                header("Location: ../Employee Side/index.html");
            }
        } else {
            echo '<script>alert("Incorrect username or password. Please try again.");</script>';
        }
    } else {
        echo '<script>alert("Incorrect username or password. Please try again.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="rcvj-logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="rcvj-logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="rcvj-logo/favicon-16x16.png">
    <link rel="manifest" href="rcvj-logo/site.webmanifest">
    <script
      src="https://kit.fontawesome.com/64d58efce2.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="EmpAdStyle.css" />
    <title>Employee Sign In | RCVJ, Inc. </title>
  </head>
  <body>
    <img class="rcvjlogo" src="RCVJlogo.png" alt="RCVJ Logo" />
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">
        <form action="" class="sign-in-form" method="POST">
            <h2 class="title">Employee Sign in</h2>
            <div class="input-field">
                <i class="fas fa-user"></i>
                <input type="text" placeholder="Username" name="username"/>
            </div>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="password" placeholder="Password" name="password"/>
            </div>
            <input type="hidden" name="login_type" value="employee"/>
            <input type="submit" value="Login" class="btn solid" name="submit"/>
            <p class="social-text">Or Sign in as <a href="Applicant.php" class="admin-class"> Applicant </a></p>
        </form>

        <form action="" class="sign-up-form" method="POST">
            <h2 class="title">Admin Sign In</h2>
            <div class="input-field">
                <i class="fas fa-user"></i>
                <input type="text" placeholder="Username" name="username"/>
            </div>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="password" placeholder="Password" name="password"/>
            </div>
            <input type="hidden" name="login_type" value="admin"/>
            <input type="submit" class="btn" value="Log In" />
        </form>

        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3>Admin Sign-In</h3>
            <p>
              Logging in as an Admin? Click
              the button below.
            </p>
            <button class="btn transparent" id="sign-up-btn">
              Sign In
            </button>
          </div>
        </div>
        <div class="panel right-panel">
          <div class="content">
            <h3>Employee Sign In</h3>
            <p>
                Logging in as an Employee? Click
                the button below.
            </p>
            <button class="btn transparent" id="sign-in-btn">
              Sign in
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="progress-bar">
      <div class="progress" id="progress"></div>
      <div class="progress-step progress-step-active"></div>
      <div class="progress-step"></div>
    </div>

    <script src="app.js"></script>
  </body>
</html>
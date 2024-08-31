<?php

$host="localhost";
$user="root";
$password="12345";
$db="admin_database";

session_start();

$data=mysqli_connect($host,$user,$password,$db);

if($data===false)
{
	die("connection error");
}


if($_SERVER["REQUEST_METHOD"]=="POST")
{
	$username=$_POST["username"];
	$password=$_POST["password"];


	$sql="select * from users_table where username='".$username."' AND password='".$password."' ";

	$result=mysqli_query($data,$sql);

	$row=mysqli_fetch_array($result);

	if($row["usertype"]=="admin")
	{	

		$_SESSION["username"]=$username;

		header("Location: ../Admin Side-dev/index.html");
	}

	elseif($row["usertype"]=="employee")
	{

		$_SESSION["username"]=$username;
		
		header("Location: ../Employee Side/index.html");
	}

	else
	{
		echo "username or password incorrect";
	}

}


?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script
      src="https://kit.fontawesome.com/64d58efce2.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="EmpAdStyle.css" />
    <title>Sign in </title>
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
                <i class="fas fa-hashtag"></i>
                <input type="text" placeholder="User ID"/>
              </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" placeholder="Password" name="password"/>
            </div>
            <input type="submit" value="Login" class="btn solid" name="submit"/>
            <p class="social-text">Or Sign in as <a href="Applicant.php" class="admin-class"> Applicant </a></p>

          </form>
          <form action="#" class="sign-up-form">
            <h2 class="title"> Admin Sign In</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" placeholder="Username" />
            </div>
            <div class="input-field">
                <i class="fas fa-hashtag"></i>
                <input type="text" placeholder="User ID" />
              </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" placeholder="Password" />
            </div>
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
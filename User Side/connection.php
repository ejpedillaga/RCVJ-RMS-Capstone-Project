<?php
    function connection(){
        $servername = "localhost";
        $username = "root";
<<<<<<< Updated upstream:User Side/connection.php
        $password = "12345";
=======
        $password = "rcvjadmin1992";
>>>>>>> Stashed changes:Login/connection.php
        $dbname = "admin_database";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        else {
            return $conn;
        }
    }
<?php
    function connection(){
        $servername = "localhost";
        $username = "root";
<<<<<<< Updated upstream
        $password = "12345";
        $dbname = "admin_database";
=======
        $password = "rcvjadmin1992";
        $dbname = "test";
>>>>>>> Stashed changes

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
<?php
    function connection(){
        $servername = "localhost:3306";
        $username = "rcvj_admin";
        $password = "rcvjadmin1992";
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
<?PHP
    include_once("connection.php");

    $conn = connection();

    // Get the roleName and rolePrivilege sent from the client
    if (isset($_POST['roleName']) && isset($_POST['rolePrivilege'])) {
        $roleName = $_POST['roleName'];
        $rolePrivilege = $_POST['rolePrivilege'];

        // Create the SQL query to check if the role exists
        $checkQuery = "
        SELECT COUNT(*) as count
        FROM employee_title_table
        WHERE employee_title = '$roleName'";

        // Create the SQL query to insert the new role if it does not exist
        $insertQuery = "
        INSERT INTO employee_title_table (employee_title, employee_privilege)
        VALUES ('$roleName', '$rolePrivilege')";

        // Combine the queries
        $sql = $checkQuery . ';' . $insertQuery;

        // Execute the combined queries using mysqli_multi_query
        if (mysqli_multi_query($conn, $sql)) {
            // Process the results of the first query (the check query)
            do {
                // Get the result of the first query
                if ($result = mysqli_store_result($conn)) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['count'];
                    mysqli_free_result($result);

                    // If the role exists, send the response indicating failure
                    if ($count > 0) {
                        echo json_encode(['valid' => false]);
                        exit;
                    }
                }
            } while (mysqli_next_result($conn)); // Move to the next query

            // If no role was found, the insert query will execute here
            echo json_encode(['valid' => true]);
        } else {
            // Query failed, return error response
            echo json_encode(['valid' => false, 'error' => 'Database query failed']);
        }

        // Close the connection
        $conn->close();
    } else {
        // If roleName or rolePrivilege is not set, send error message
        echo json_encode(['valid' => false, 'error' => 'roleName or rolePrivilege parameter missing']);
    }
?>
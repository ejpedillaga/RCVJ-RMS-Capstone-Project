<?PHP
    include_once("connection.php");

    $conn = connection();
    
    try {
        // Fetch and sanitize POST data
        $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
        $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Concatenate first and last name to get full name
        $username = $firstName . $lastName . 'test';
    
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO employee_table (first_name, last_name) VALUES (?,?)");
        $stmt->bind_param("ss", $firstName, $lastName);
        $stmt->execute();
        $stmt->close();

        $empCredStmt = $conn -> prepare("INSERT INTO users_table (username, password) VALUES (?, ?)");
        $empCredStmt->bind_param("ss", $username, $password);
    
        if ($stmt && $empCredStmt ->execute()) {
            echo json_encode(["message" => "Employee added successfully"]);
        } else {
            echo json_encode(["error" => "Error: " . $stmt->error]);
        }
    } catch (Exception $e) {
        error_log($e->getMessage()); // Log the error
        echo json_encode(["error" => $e->getMessage()]);
    }

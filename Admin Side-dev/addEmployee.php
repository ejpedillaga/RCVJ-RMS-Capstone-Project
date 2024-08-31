<?PHP
    include_once("connection.php");

    $conn = connection();

    try {
        // Fetch and sanitize POST data
        $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
        $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Concatenate first and last name to get full name
        $fullName = $firstName . ' ' . $lastName;
    
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO employee_table (full_name) VALUES (?)");
        $stmt->bind_param("s", $fullName);
    
        if ($stmt->execute()) {
            echo json_encode(["message" => "Employee added successfully"]);
        } else {
            echo json_encode(["error" => "Error: " . $stmt->error]);
        }
    } catch (Exception $e) {
        error_log($e->getMessage()); // Log the error
        echo json_encode(["error" => $e->getMessage()]);
    }

<?PHP
    // Database connection details
    include 'connection.php';

    // Create a connection
    $conn = connection();

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

        // Set content type to JSON before any output
    header('Content-Type: application/json');

    // Define the SQL query
    $sql = "SELECT employee_title FROM employee_title_table;";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check for SQL execution errors
    if ($result === false) {
        die(json_encode(['error' => 'SQL Error: ' . $conn->error]));
    }

    $options = [];

    // Fetch results and populate the options array
    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
    }

    // Log the number of records fetched
    file_put_contents('debug.log', 'Number of records fetched: ' . count($options) . PHP_EOL);

    // Return the options array as JSON
    if (empty($options)) {
        echo json_encode(['message' => 'No records found']);
    } else {
        echo json_encode($options);
    }
    $conn->close();
?>
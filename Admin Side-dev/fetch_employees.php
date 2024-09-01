<?php
   include_once("connection.php");

   $conn = connection();

$sql = "SELECT employee_id, first_name, last_name, date_added, status FROM employee_table"; 

$result = $conn->query($sql);
    
$employees = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {

        // Convert date format to m/d/y
        $date = new DateTime($row['date_added']);
        $row['date_added'] = $date->format('m/d/Y');
        $row['full_name'] = $row['first_name'] . ' ' . $row['last_name'];
        
        $employees[] = $row;
    }
} else {
    echo json_encode([]);
}
$conn->close();

echo json_encode($employees);
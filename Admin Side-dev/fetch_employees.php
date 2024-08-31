<?php
   include_once("connection.php");

   $conn = connection();

$sql = "SELECT full_name, date_added, status FROM employee_table"; 
$result = $conn->query($sql);
    
$employees = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {

        // Convert date format to m/d/y
        $date = new DateTime($row['date_added']);
        $row['date_added'] = $date->format('m/d/Y');
        
        $employees[] = $row;
    }
} else {
    echo "0 results";
}
$conn->close();

echo json_encode($employees);
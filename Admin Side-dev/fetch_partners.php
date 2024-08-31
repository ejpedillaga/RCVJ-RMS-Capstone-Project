<?php
include_once("connection.php");

$conn = connection();

$sql = "SELECT id, logo, company_name, date_added FROM partner_table";

$result = $conn->query($sql);

$partners = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        // Convert the BLOB data to base64
        $row['logo'] = base64_encode($row['logo']);
        
        // Convert date format to m/d/y
        $date = new DateTime($row['date_added']);
        $row['date_added'] = $date->format('m/d/Y');
        
        $partners[] = $row;
    }
} else {
    echo json_encode([]);
}
$conn->close();

echo json_encode($partners);
?>

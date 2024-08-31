<?php
include_once("connection.php");

$conn = connection();

$sql = "SELECT id, company_name FROM partner_table";
$result = $conn->query($sql);

$options = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
} else {
    echo json_encode([]);
}

$conn->close();

echo json_encode($options);
<?php
include_once("connection.php");

$conn = connection();

$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Start building the SQL query
$sql = "
    SELECT 
        e.employee_id, 
        e.first_name, 
        e.last_name, 
        e.date_added, 
        e.status,
        t.employee_title, 
        t.employee_privilege 
    FROM 
        employee_table e
    LEFT JOIN 
        employee_title_table t 
    ON 
        e.employee_title = t.employee_title"; // Adjust ON condition as needed

// Add search condition if a search term is provided
if ($searchTerm) {
    $sql .= " WHERE (e.first_name LIKE '%$searchTerm%' OR e.last_name LIKE '%$searchTerm%')";
}

// Sorting functionality
$sortBy = isset($_GET['sort_by']) ? $conn->real_escape_string($_GET['sort_by']) : 'date_added';
$order = isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';

// Validate sort_by options to prevent SQL injection
$validSortColumns = ['date_added', 'first_name', 'last_name', 'employee_title'];
if (!in_array($sortBy, $validSortColumns)) {
    $sortBy = 'date_added'; // Default to date_added if invalid
}

$sql .= " ORDER BY $sortBy $order"; // Append sorting condition

$result = $conn->query($sql);

$employees = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
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
?>

<?php
include 'connection.php';
include 'session_check.php';
$conn = connection();
// Include the database connection file
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT a.log_id, CONCAT(e.first_name, ' ', e.last_name) AS employee_name, 
               a.action, a.entity_type, a.entity_id, a.details, a.timestamp
        FROM audit_logs a
        JOIN employee_table e ON a.employee_id = e.employee_id
        ORDER BY a.timestamp DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Audit Logs | RCVJ Inc.</title>

    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="stylesheet" href="mediaqueries.css?v=<?php echo filemtime('mediaqueries.css'); ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="rcvj-logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="rcvj-logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="rcvj-logo/favicon-16x16.png">
    <link rel="manifest" href="rcvj-logo/site.webmanifest">
    
    <!-- Custom JS -->
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</head>

<body>
    <!-- Sidebar -->
    <div id="mySidebar" class="sidebar closed">
        <div class="sidebar-header">
            <h3>RCVJ Inc.</h3>
            <button class="toggle-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
        <a href="jobs.html"><i class="fa-solid fa-suitcase"></i> <span>Jobs</span></a>
        <a href="smartsearch.php"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
        <a href="candidates.php"><i class="fa-solid fa-user"></i> <span>Candidates</span></a>
        <a href="schedules.php"><i class="fa-solid fa-calendar"></i> <span>Schedules</span></a>
        <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
        <a href="employees.php"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>
        <a href="chatbot.php"><i class="fa-solid fa-robot"></i> <span>Chatbot</span></a>
        <a href="activity_log.php" class="active"><i class="fa-solid fa-list"></i> <span>Activity Log</span></a> 
    </div>

    <!-- Header -->
    <div id="header">
        <img id="logo" src="img/logo.png" alt="logo">
        <div class="profile">
            <img src="img/pfp.png" alt="Profile Picture">
            <span class="name">Admin</span>
            <!-- LOGOUT -->
            <button class="logout-btn" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fa-lg"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div id="main">
        <h2 style="font-size: 36px; color: #000000">Activity Log</h2>

        <table>
        <thead>
            <tr class="th1">
                <th>ID</th>
                <th>Employee</th>
                <th>Action</th>
                <th>Entity</th>
                <th>Entity ID</th>
                <th>Details</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>    
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="tr1">
                    <td><?php echo $row['log_id']; ?></td>
                    <td><?php echo $row['employee_name']; ?></td>
                    <td><?php echo $row['action']; ?></td>
                    <td><?php echo $row['entity_type']; ?></td>
                    <td><?php echo $row['entity_id']; ?></td>
                    <td><?php echo $row['details']; ?></td>
                    <td><?php echo $row['timestamp']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center; color: #2C1875; font-size: 20px; font-weight: bold; padding: 5rem 0rem;">No available logs found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>  
</body>
</html>

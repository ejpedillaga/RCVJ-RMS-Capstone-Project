<?php
session_start();

// Include the connection.php file
include 'connection.php'; // Adjust this if necessary

// Call the connection function to establish the database connection
$conn = connection();

// Check if the connection is established
if (!$conn) {
    die("Database connection failed.");
}

// Set pagination variables
$limit = 5; // Number of candidates per page
$pagePending = isset($_GET['pagePending']) ? (int)$_GET['pagePending'] : 1;
$pageScheduled = isset($_GET['pageScheduled']) ? (int)$_GET['pageScheduled'] : 1;
$pageInterviewed = isset($_GET['pageInterviewed']) ? (int)$_GET['pageInterviewed'] : 1;
$pageForDeployment = isset($_GET['pageForDeployment']) ? (int)$_GET['pageForDeployment'] : 1;
$pageDeployed = isset($_GET['pageDeployed']) ? (int)$_GET['pageDeployed'] : 1;

$offsetPending = ($pagePending - 1) * $limit;
$offsetScheduled = ($pageScheduled - 1) * $limit;
$offsetInterviewed = ($pageInterviewed - 1) * $limit;
$offsetForDeployment = ($pageForDeployment - 1) * $limit;
$offsetDeployed = ($pageDeployed - 1) * $limit;

$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';

function fetchCandidates($conn, $status, $offset, $limit) {
    $query = "
    SELECT 
        c.id, c.userid, CONCAT(a.fname, ' ', a.lname) AS full_name, c.job_title, c.company_name, c.date_applied, c.deployment_status, 
        a.email, a.gender, DATE_FORMAT(a.birthday, '%m/%d/%y') AS birthday, a.location, a.phone, a.personal_description, 
        e.educational_attainment, e.school AS educational_school, e.course AS educational_course, e.sy_started,e.sy_ended,
        v.school AS vocational_school, v.course AS vocational_course, v.year_started AS vocational_year_started, v.year_ended AS vocational_year_ended, 
        GROUP_CONCAT(DISTINCT CONCAT(cl.license_name, ' (Issued: ', cl.month_issued, ' ', cl.year_issued, ' - Expired: ', cl.month_expired, ' ', cl.year_expired, ')') ORDER BY cl.year_issued DESC SEPARATOR '; ') AS licenses, 
        GROUP_CONCAT(DISTINCT CONCAT(j.job_title, ' at ', j.company_name, ' (', j.month_started, ' ', j.year_started, ' - ', j.month_ended, ' ', j.year_ended, ')') ORDER BY j.year_started DESC SEPARATOR '; ') AS past_jobs,
        TO_BASE64(a.profile_image) AS profile_image,
        TO_BASE64(r.resume) AS resume,
        GROUP_CONCAT(DISTINCT s.skill_name SEPARATOR ', ') AS skills
    FROM 
        candidate_list c
    JOIN 
        applicant_table a ON c.userid = a.userid
    LEFT JOIN 
        education_table e ON a.userid = e.userid
    LEFT JOIN 
        vocational_table v ON a.userid = v.userid
    LEFT JOIN 
        job_experience_table j ON a.userid = j.userid
    LEFT JOIN 
        certification_license_table cl ON a.userid = cl.userid
    LEFT JOIN 
        user_skills_table us ON a.userid = us.userid
    LEFT JOIN 
        skill_table s ON us.skill_id = s.skill_id
    LEFT JOIN 
        resume_table r ON a.userid = r.userid -- Join with resume_table
    WHERE 
        c.status = 'Approved' AND c.deployment_status = ? 
    GROUP BY 
        c.id, a.fname, a.lname, c.job_title, c.company_name, c.date_applied, c.deployment_status, 
        a.email, a.gender, a.birthday, a.location, a.phone, a.personal_description, 
        e.educational_attainment, e.school, e.course, 
        e.sy_started, e.sy_ended, v.school, v.course, 
        v.year_started, v.year_ended
    LIMIT ?, ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $status, $offset, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Get counts for each tab
$statusCounts = [];
$statuses = ['Pending', 'Scheduled', 'Interviewed', 'For Deployment', 'Deployed'];

foreach ($statuses as $status) {
    $query = "SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = '$status'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $statusCounts[$status] = $row['total'];
}

// Fetch candidates for different statuses
$resultPending = fetchCandidates($conn, 'Pending', $offsetPending, $limit);
$resultScheduled = fetchCandidates($conn, 'Scheduled', $offsetScheduled, $limit);
$resultInterviewed = fetchCandidates($conn, 'Interviewed', $offsetInterviewed, $limit);
$resultForDeployment = fetchCandidates($conn, 'For Deployment', $offsetForDeployment, $limit);
$resultDeployed = fetchCandidates($conn, 'Deployed', $offsetDeployed, $limit);
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates | RCVJ, Inc.</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
    <link rel="stylesheet" href="mediaqueries.css">
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
    <link rel="apple-touch-icon" sizes="180x180" href="rcvj-logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="rcvj-logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="rcvj-logo/favicon-16x16.png">
    <link rel="manifest" href="rcvj-logo/site.webmanifest">
</head>
<body>
    <div id="mySidebar" class="sidebar closed">
        <div class="sidebar-header">
            <h3>RCVJ Inc.</h3>
            <button class="toggle-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
            <a href="index.html"><i class="fa-solid fa-suitcase"></i> <span>Jobs</span></a>
            <a href="smartsearch.php"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.php" class="active"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php" ><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="employees.php"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>

        </div>

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

        <div id="main">
            <h2 style="font-size: 36px; margin-bottom: 0rem;">Candidates</h2>

            <div class="filter-container-candidates">
                <div class="search-wrapper">
                    <i class="fas fa-magnifying-glass search-icon"></i>
                    <input type="text" class="search-candidates" placeholder="Search Candidates">
                </div>
                <select id="sort_Company" class="company-sort">
                <option value="All Companies">All Companies</option>
                <?php
                $resultCompanies = $conn->query("SELECT DISTINCT company_name FROM candidate_list");
                while ($company = $resultCompanies->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($company['company_name']) . '">' . htmlspecialchars($company['company_name']) . '</option>';
                }
                ?>
            </select>
                <select id="sort_Type" class="sort-by">
                    <option>Date Applied</option>
                    <option>Company Name</option>
                    <option>Job Title</option>
                </select>
                <select id="sort_Order" class="order-sort">
                    <option>Ascending</option>
                    <option>Descending</option>
                </select>
            </div>

            <div class="tabs">
                <div class="tab <?php echo ($currentTab === 'pending') ? 'active' : ''; ?>" 
                    onclick="openTab('pending')">
                    Pending (<?php echo $statusCounts['Pending']; ?>)
                </div>
                <div class="tab <?php echo ($currentTab === 'scheduled') ? 'active' : ''; ?>" 
                    onclick="openTab('scheduled')">
                    Scheduled (<?php echo $statusCounts['Scheduled']; ?>)
                </div>
                <div class="tab <?php echo ($currentTab === 'interviewed') ? 'active' : ''; ?>" 
                    onclick="openTab('interviewed')">
                    Interviewed (<?php echo $statusCounts['Interviewed']; ?>)
                </div>
                <div class="tab <?php echo ($currentTab === 'forDeployment') ? 'active' : ''; ?>" 
                    onclick="openTab('forDeployment')">
                    For Deployment (<?php echo $statusCounts['For Deployment']; ?>)
                </div>
                <div class="tab <?php echo ($currentTab === 'deployed') ? 'active' : ''; ?>" 
                    onclick="openTab('deployed')">
                    Deployed (<?php echo $statusCounts['Deployed']; ?>)
                </div>
            </div>
            <!-- Pending Candidates Tab -->
            <div id="pending" class="tab-content <?php echo ($currentTab === 'pending') ? 'active' : ''; ?>">
                <table style="margin-bottom: 1rem">
                    <thead>
                        <tr class="th1">
                            <th>Candidate</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($resultPending->num_rows > 0): ?>
                        <?php while ($row = $resultPending->fetch_assoc()): ?>
                            <tr class="tr1">
                                <td class="fullname"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo (new DateTime($row['date_applied']))->format('m/d/Y'); ?></td>
                                <td>
                                    <select class="status-dropdown" onchange="updateStatus(this, <?php echo json_encode($row['id']); ?>)">
                                        <option value="Pending" <?php echo ($row['deployment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Scheduled" <?php echo ($row['deployment_status'] === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="Interviewed" <?php echo ($row['deployment_status'] === 'Interviewed') ? 'selected' : ''; ?>>Interviewed</option>
                                        <option value="For Deployment" <?php echo ($row['deployment_status'] === 'For Deployment') ? 'selected' : ''; ?>>For Deployment</option>
                                        <option value="Deployed" <?php echo ($row['deployment_status'] === 'Deployed') ? 'selected' : ''; ?>>Deployed</option>
                                    </select>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfoCandidate(<?php echo htmlspecialchars(json_encode($row)); ?>)"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                <i class="fa fa-undo fa-2xl" aria-hidden="true" style="color: #EF9B50; cursor: pointer;" onclick="undoApproval(<?php echo htmlspecialchars($row['id']); ?>)"></i>
                                    <span class="tooltip-text">Undo Approval</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #2C1875; font-weight: bold;">No candidates found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pending Pagination -->
                <div class="pagination">
                    <?php
                    // Calculate the total number of pages needed for Pending
                    $resultCountPending = $conn->query("SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = 'Pending'");
                    $rowCountPending = $resultCountPending->fetch_assoc();
                    $totalPagesPending = ceil($rowCountPending['total'] / $limit);
                   
                    if ($totalPagesPending > 1): // Only show pagination if more than one page
                        ?>
                        <a href="?pagePending=1&tab=pending" class="pagination-link <?php echo ($pagePending === 1) ? 'disabled' : ''; ?>">First</a>
                        <?php for ($i = 1; $i <= $totalPagesPending; $i++): ?>
                            <a href="?pagePending=<?php echo $i; ?>&tab=pending" class="pagination-link <?php echo ($i === (int)$pagePending) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?pagePending=<?php echo $totalPagesPending; ?>&tab=pending" class="pagination-link <?php echo ($pagePending === $totalPagesPending) ? 'disabled' : ''; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Scheduled Candidates Tab -->
            <div id="scheduled" class="tab-content <?php echo ($currentTab === 'scheduled') ? 'active' : ''; ?>">
                <table style="margin-bottom: 1rem">
                    <thead>
                        <tr class="th1">
                            <th>Candidate</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($resultScheduled->num_rows > 0): ?>
                        <?php while ($row = $resultScheduled->fetch_assoc()): ?>
                            <tr class="tr1">
                                <td class="fullname"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo (new DateTime($row['date_applied']))->format('m/d/Y'); ?></td>
                                <td>
                                    <select class="status-dropdown" onchange="updateStatus(this, <?php echo json_encode($row['id']); ?>)">
                                        <option value="Pending" <?php echo ($row['deployment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Scheduled" <?php echo ($row['deployment_status'] === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="Interviewed" <?php echo ($row['deployment_status'] === 'Interviewed') ? 'selected' : ''; ?>>Interviewed</option>
                                        <option value="For Deployment" <?php echo ($row['deployment_status'] === 'For Deployment') ? 'selected' : ''; ?>>For Deployment</option>
                                        <option value="Deployed" <?php echo ($row['deployment_status'] === 'Deployed') ? 'selected' : ''; ?>>Deployed</option>
                                    </select>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfoCandidate(<?php echo htmlspecialchars(json_encode($row)); ?>)"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-undo fa-2xl" aria-hidden="true" style="color: #EF9B50; cursor: pointer;" onclick="undoApproval(<?php echo htmlspecialchars($row['id']); ?>)"></i>
                                    <span class="tooltip-text">Undo Approval</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #2C1875; font-weight: bold;">No candidates found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- Scheduled Pagination -->
                <div class="pagination">
                    <?php
                    // Calculate the total number of pages needed for Scheduled
                    $resultCountScheduled = $conn->query("SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = 'Scheduled'");
                    $rowCountScheduled = $resultCountScheduled->fetch_assoc();
                    $totalPagesScheduled = ceil($rowCountScheduled['total'] / $limit);
                   
                    if ($totalPagesScheduled > 1): // Only show pagination if more than one page
                        ?>
                        <a href="?pageScheduled=1&tab=scheduled" class="pagination-link <?php echo ($pageScheduled === 1) ? 'disabled' : ''; ?>">First</a>
                        <?php for ($i = 1; $i <= $totalPagesScheduled; $i++): ?>
                            <a href="?pageScheduled=<?php echo $i; ?>&tab=scheduled" class="pagination-link <?php echo ($i === $pageScheduled) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?pageScheduled=<?php echo $totalPagesScheduled; ?>&tab=scheduled" class="pagination-link <?php echo ($pageScheduled === $totalPagesScheduled) ? 'disabled' : ''; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interviewed Candidates Tab -->
            <div id="interviewed" class="tab-content <?php echo ($currentTab === 'interviewed') ? 'active' : ''; ?>">
                <table style="margin-bottom: 1rem">
                    <thead>
                        <tr class="th1">
                            <th>Candidate</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($resultInterviewed->num_rows > 0): ?>
                        <?php while ($row = $resultInterviewed->fetch_assoc()): ?>
                            <tr class="tr1">
                                <td class="fullname"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td>
                                    <?php 
                                        // Create a DateTime object from the date_applied string
                                        $dateApplied = new DateTime($row['date_applied']); 
                                        // Format the date into MM/DD/YYYY
                                        echo htmlspecialchars($dateApplied->format('m/d/Y')); 
                                    ?>
                                </td>
                                <td>
                                    <select class="status-dropdown" 
                                            onchange="updateStatus(this, <?php echo json_encode($row['id']); ?>)">
                                        <option value="Pending" <?php echo ($row['deployment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Scheduled" <?php echo ($row['deployment_status'] === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="Interviewed" <?php echo ($row['deployment_status'] === 'Interviewed') ? 'selected' : ''; ?>>Interviewed</option>
                                        <option value="For Deployment" <?php echo ($row['deployment_status'] === 'For Deployment') ? 'selected' : ''; ?>>For Deployment</option>
                                        <option value="Deployed" <?php echo ($row['deployment_status'] === 'Deployed') ? 'selected' : ''; ?>>Deployed</option>
                                    </select>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfoCandidate(<?php echo htmlspecialchars(json_encode($row)); ?>)"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-undo fa-2xl" aria-hidden="true" style="color: #EF9B50; cursor: pointer;" onclick="undoApproval(<?php echo json_encode($row['id']); ?>)"></i>
                                    <span class="tooltip-text">Undo Approval</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #2C1875; font-weight: bold;">No candidates found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- Interviewed Pagination -->
                <div class="pagination">
                    <?php
                    // Calculate the total number of pages needed for Interviewed
                    $resultCountInterviewed = $conn->query("SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = 'Interviewed'");
                    $rowCountInterviewed = $resultCountInterviewed->fetch_assoc();
                    $totalPagesInterviewed = ceil($rowCountInterviewed['total'] / $limit);
                   
                    if ($totalPagesInterviewed > 1): // Only show pagination if more than one page
                        ?>
                        <a href="?pageInterviewed=1&tab=interviewed" class="pagination-link <?php echo ($pageInterviewed === 1) ? 'disabled' : ''; ?>">First</a>
                        <?php for ($i = 1; $i <= $totalPagesInterviewed; $i++): ?>
                            <a href="?pageInterviewed=<?php echo $i; ?>&tab=interviewed" class="pagination-link <?php echo ($i === $pageInterviewed) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?pageInterviewed=<?php echo $totalPagesInterviewed; ?>&tab=interviewed" class="pagination-link <?php echo ($pageInterviewed === $totalPagesInterviewed) ? 'disabled' : ''; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- For Deployment Candidates Tab -->
            <div id="forDeployment" class="tab-content <?php echo ($currentTab === 'forDeployment') ? 'active' : ''; ?>">
                <table style="margin-bottom: 1rem">
                    <thead>
                        <tr class="th1">
                            <th>Candidate</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($resultForDeployment->num_rows > 0): ?>
                        <?php while ($row = $resultForDeployment->fetch_assoc()): ?>
                            <tr class="tr1">
                                <td class="fullname"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo (new DateTime($row['date_applied']))->format('m/d/Y'); ?></td>
                                <td>
                                    <select class="status-dropdown" 
                                            onchange="updateStatus(this, <?php echo json_encode($row['id']); ?>)">
                                        <option value="Pending" <?php echo ($row['deployment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Scheduled" <?php echo ($row['deployment_status'] === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="Interviewed" <?php echo ($row['deployment_status'] === 'Interviewed') ? 'selected' : ''; ?>>Interviewed</option>
                                        <option value="For Deployment" <?php echo ($row['deployment_status'] === 'For Deployment') ? 'selected' : ''; ?>>For Deployment</option>
                                        <option value="Deployed" <?php echo ($row['deployment_status'] === 'Deployed') ? 'selected' : ''; ?>>Deployed</option>
                                    </select>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfoCandidate(<?php echo htmlspecialchars(json_encode($row)); ?>)"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-undo fa-2xl" aria-hidden="true" style="color: #EF9B50; cursor: pointer;" onclick="undoApproval(<?php echo htmlspecialchars($row['id']); ?>)"></i>
                                    <span class="tooltip-text">Undo Approval</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #2C1875; font-weight: bold;">No candidates found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- For Deployment Pagination -->
                <div class="pagination">
                    <?php
                    $resultCountForDeployment = $conn->query("SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = 'For Deployment'");
                    $rowCountForDeployment = $resultCountForDeployment->fetch_assoc();
                    $totalPagesForDeployment = ceil($rowCountForDeployment['total'] / $limit);

                    if ($totalPagesForDeployment > 1): 
                    ?>
                        <a href="?pageForDeployment=1&tab=forDeployment" class="pagination-link <?php echo ($pageForDeployment === 1) ? 'disabled' : ''; ?>">First</a>
                        <?php for ($i = 1; $i <= $totalPagesForDeployment; $i++): ?>
                            <a href="?pageForDeployment=<?php echo $i; ?>&tab=forDeployment" class="pagination-link <?php echo ($i === $pageForDeployment) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?pageForDeployment=<?php echo $totalPagesForDeployment; ?>&tab=forDeployment" class="pagination-link <?php echo ($pageForDeployment === $totalPagesForDeployment) ? 'disabled' : ''; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deployed Candidates Tab -->
            <div id="deployed" class="tab-content <?php echo ($currentTab === 'deployed') ? 'active' : ''; ?>">
                <table style="margin-bottom: 1rem">
                    <thead>
                        <tr class="th1">
                            <th>Candidate</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($resultDeployed->num_rows > 0): ?>
                        <?php while ($row = $resultDeployed->fetch_assoc()): ?>
                            <tr class="tr1">
                                <td class="fullname"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td>
                                    <?php 
                                        // Create a DateTime object from the date_applied string
                                        $dateApplied = new DateTime($row['date_applied']); 
                                        // Format the date into MM/DD/YYYY
                                        echo htmlspecialchars($dateApplied->format('m/d/Y')); 
                                    ?>
                                </td>
                                <td>
                                    <select class="status-dropdown" 
                                            onchange="updateStatus(this, <?php echo json_encode($row['id']); ?>)">
                                        <option value="Pending" <?php echo ($row['deployment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Scheduled" <?php echo ($row['deployment_status'] === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="Interviewed" <?php echo ($row['deployment_status'] === 'Interviewed') ? 'selected' : ''; ?>>Interviewed</option>
                                        <option value="For Deployment" <?php echo ($row['deployment_status'] === 'For Deployment') ? 'selected' : ''; ?>>For Deployment</option>
                                        <option value="Deployed" <?php echo ($row['deployment_status'] === 'Deployed') ? 'selected' : ''; ?>>Deployed</option>
                                    </select>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfoCandidate(<?php echo htmlspecialchars(json_encode($row)); ?>)"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-undo fa-2xl" aria-hidden="true" style="color: #EF9B50; cursor: pointer;" onclick="undoApproval(<?php echo htmlspecialchars($row['id']); ?>)"></i>
                                    <span class="tooltip-text">Undo Approval</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #2C1875; font-weight: bold;">No candidates found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <!-- Deployed Pagination -->
                <div class="pagination">
                    <?php
                    // Calculate the total number of pages needed for Deployed
                    $resultCountDeployed = $conn->query("SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = 'Deployed'");
                    $rowCountDeployed = $resultCountDeployed->fetch_assoc();
                    $totalPagesDeployed = ceil($rowCountDeployed['total'] / $limit);
                    if ($totalPagesDeployed > 1): // Only show pagination if more than one page
                    ?>
                        <a href="?pageDeployed=1&tab=deployed" class="pagination-link <?php echo ($pageDeployed === 1) ? 'disabled' : ''; ?>">First</a>
                        <?php for ($i = 1; $i <= $totalPagesDeployed; $i++): ?>
                            <a href="?pageDeployed=<?php echo $i; ?>&tab=deployed" class="pagination-link <?php echo ($i === $pageDeployed) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?pageDeployed=<?php echo $totalPagesDeployed; ?>&tab=deployed" class="pagination-link <?php echo ($pageDeployed === $totalPagesDeployed) ? 'disabled' : ''; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>

       <!--Candidate Info Popup-->
       <div class="popup" id="info">
                <!-- Back Button -->
                <div class="addpartners-back-button" onclick="hideInfo()">
                    <i class="fas fa-chevron-left"></i> Back
                </div>
                <h3 style="color: #2C1875">Review applicant information:</h3>
                <p>This information was provided by the applicant.</p>
                <div class="candidate-container">
                    <div class="candidate-header">
                        <div>
                            <h2><?php echo htmlspecialchars($user_name); ?></h2>
                            <div class="locationemail">
                                <i class="fa fa-map-pin" aria-hidden="true"></i><h4>Location</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-envelope" aria-hidden="true"></i><h4>Email</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-venus-mars" aria-hidden="true"></i><h4>gender</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-phone" aria-hidden="true"></i><h4>phone</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-birthday-cake" aria-hidden="true"></i><h4>birthday</h4>
                            </div>
                        </div>
                        <div id="profile-image">
                            
                        </div>
                    </div>
                    <div id="personal-info">
                        <h3>Personal Information</h3>
                        <p id="personal-desc">personal description</p>
                    </div>
                    <!-- Past Jobs Information -->
                    <div id="past-jobs-container">
                        <h3>Past Jobs</h3>
                        <ul class="past-jobs-list" id="past-jobs-list">
                            
                        </ul>
                    </div>
                    <!-- Education Information -->
                    <div id="education">
                        <h3>Educational Attainment</h3>
                        <ul class="education-list" id="education-list">
                            
                        </ul>
                    </div>

                    <!-- Vocational Education Information -->
                    <div id="vocational">
                        <h3>Vocational</h3>
                        <ul class="vocational-list" id="vocational-list">
                            
                        </ul>
                    </div>
                    <div id="skills">
                        <h3>Skills</h3>
                        <ul class="skills-list" id="skills-list">
                            
                        </ul>
                    </div>
                    <div id="licenses-container">
                        <h3>Licenses</h3>
                        <ul id="licenses-list"></ul>
                    </div>
                    <div id="resume-container">
                        <h3>Resume</h3>
                        <div id="resume-content">
                            <iframe id="resume-display" style="display: none; width: 100%; height: 400px;" frameborder="0"></iframe>
                            <div id="no-resume-message" style="display: none;">No resume available</div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Dialog Box -->
        <div class="rejected-dialog-box" id="dialogBox">
            <div class="rejected-back-button" onclick="hideDialog()">
                <i class="fas fa-chevron-left"></i> Back
            </div>
            
            <h2 style="text-align: center;">Are you sure you want to unapprove this candidate?</h2>
            <div class="rejected-form-group">
                <button class="rejected-save-button">Confirm</button>
            </div>
        </div>
        <div class="shape-container2">
            <div class="rectangle-4"></div>
            <div class="rectangle-5"></div>
        </div>                                   
    </div>
</body>
<script>
    function updateStatus(selectElement, candidateId) {
    const newStatus = selectElement.value;

    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: candidateId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status updated successfully!');
            location.reload();
        } else {
            alert('Failed to update status.');
        }
    })
    .catch(error => console.error('Error:', error));
}

function undoApproval(candidateId) {
    if (confirm('Are you sure you want to undo the approval for this candidate?')) {
        fetch('update_candidate_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `candidate_id=${candidateId}`
        })
        .then(response => response.text()) // Read the response as text
        .then(text => {
            console.log('Response Text:', text); // Log the response text
            return JSON.parse(text); // Attempt to parse it as JSON
        })
        .then(data => {
            if (data.status === 'success') {
                alert('Candidate status updated to Pending.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Select input elements
    const sortCompany = document.getElementById('sort_Company');
    const sortType = document.getElementById('sort_Type');
    const sortOrder = document.getElementById('sort_Order');

    // Function to send AJAX request whenever filter changes
    function updateTable() {
        const company = sortCompany.value;
        const type = sortType.value;
        const order = sortOrder.value;

        // Send AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'your_php_file.php?sort_Company=' + encodeURIComponent(company) + '&sort_Type=' + encodeURIComponent(type) + '&sort_Order=' + encodeURIComponent(order), true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                // Replace the table content with the response
                document.querySelector('#pending tbody').innerHTML = xhr.responseText;
            } else {
                console.error('Error fetching filtered data');
            }
        };
        xhr.send();
    }

    // Event listeners for the select inputs
    sortCompany.addEventListener('change', updateTable);
    sortType.addEventListener('change', updateTable);
    sortOrder.addEventListener('change', updateTable);
});


</script>


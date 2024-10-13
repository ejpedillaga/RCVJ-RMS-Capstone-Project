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
$pageDeployed = isset($_GET['pageDeployed']) ? (int)$_GET['pageDeployed'] : 1;

$offsetPending = ($pagePending - 1) * $limit;
$offsetScheduled = ($pageScheduled - 1) * $limit;
$offsetInterviewed = ($pageInterviewed - 1) * $limit;
$offsetDeployed = ($pageDeployed - 1) * $limit;

$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';

// Fetch candidates for each deployment status
$queryPending = "SELECT id, full_name, job_title, company_name, date_applied, deployment_status 
                 FROM candidate_list 
                 WHERE status = 'Approved' AND deployment_status = 'Pending' 
                 LIMIT ?, ?";
$stmtPending = $conn->prepare($queryPending);
$stmtPending->bind_param("ii", $offsetPending, $limit);
$stmtPending->execute();
$resultPending = $stmtPending->get_result();

$queryScheduled = "SELECT id, full_name, job_title, company_name, date_applied, deployment_status 
                   FROM candidate_list 
                   WHERE status = 'Approved' AND deployment_status = 'Scheduled' 
                   LIMIT ?, ?";
$stmtScheduled = $conn->prepare($queryScheduled);
$stmtScheduled->bind_param("ii", $offsetScheduled, $limit);
$stmtScheduled->execute();
$resultScheduled = $stmtScheduled->get_result();

$queryInterviewed = "SELECT id, full_name, job_title, company_name, date_applied, deployment_status 
                     FROM candidate_list 
                     WHERE status = 'Approved' AND deployment_status = 'Interviewed' 
                     LIMIT ?, ?";
$stmtInterviewed = $conn->prepare($queryInterviewed);
$stmtInterviewed->bind_param("ii", $offsetInterviewed, $limit);
$stmtInterviewed->execute();
$resultInterviewed = $stmtInterviewed->get_result();

$pageForDeployment = isset($_GET['pageForDeployment']) ? (int)$_GET['pageForDeployment'] : 1;
$offsetForDeployment = ($pageForDeployment - 1) * $limit;

$queryForDeployment = "SELECT id, full_name, job_title, company_name, date_applied, deployment_status 
                       FROM candidate_list 
                       WHERE status = 'Approved' AND deployment_status = 'For Deployment' 
                       LIMIT ?, ?";
$stmtForDeployment = $conn->prepare($queryForDeployment);
$stmtForDeployment->bind_param("ii", $offsetForDeployment, $limit);
$stmtForDeployment->execute();
$resultForDeployment = $stmtForDeployment->get_result();

$queryDeployed = "SELECT id, full_name, job_title, company_name, date_applied, deployment_status 
                  FROM candidate_list 
                  WHERE status = 'Approved' AND deployment_status = 'Deployed' 
                  LIMIT ?, ?";
$stmtDeployed = $conn->prepare($queryDeployed);
$stmtDeployed->bind_param("ii", $offsetDeployed, $limit);
$stmtDeployed->execute();
$resultDeployed = $stmtDeployed->get_result();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Side RCVJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
    <link rel="stylesheet" href="mediaqueries.css">
    <script src="script.js"></script>
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
            <a href="partners.html"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="employees.html"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>
        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name">Admin</span>
            </div>
        </div>

        <div id="main">
            <h2 style="font-size: 36px; margin-bottom: 0rem;">Candidates</h2>

            <div class="filter-container-candidates">
                <div class="search-wrapper">
                    <i class="fas fa-magnifying-glass search-icon"></i>
                    <input type="text" class="search-candidates" placeholder="Search Candidates">
                </div>
                <select class="company-sort">
                    <option>Sort by: All</option>
                    <option>Sort by: WalterMart</option>
                    <option>Sort by: Jabile</option>
                </select>
                <select class="sort-by">
                    <option>Sort by: Date Applied</option>
                    <option>Sort by: Company Name</option>
                    <option>Sort by: Job Title</option>
                </select>
                <select class="order-sort">
                    <option>Ascending</option>
                    <option>Descending</option>
                </select>

                <button class="rejected-button" onclick="redirectTo('rejected.html')">Rejected</button>
            </div>

            <div class="tabs">
                <div class="tab <?php echo ($currentTab === 'pending') ? 'active' : ''; ?>" onclick="openTab('pending')">Pending</div>
                <div class="tab <?php echo ($currentTab === 'scheduled') ? 'active' : ''; ?>" onclick="openTab('scheduled')">Scheduled</div>
                <div class="tab <?php echo ($currentTab === 'interviewed') ? 'active' : ''; ?>" onclick="openTab('interviewed')">Interviewed</div>
                <div class="tab <?php echo ($currentTab === 'forDeployment') ? 'active' : ''; ?>" onclick="openTab('forDeployment')">For Deployment</div>
                <div class="tab <?php echo ($currentTab === 'deployed') ? 'active' : ''; ?>" onclick="openTab('deployed')">Deployed</div>
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
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfo()"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50;" onclick="showDialog()"></i>
                                    <span class="tooltip-text">Delete Candidate</span>
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
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfo()"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50;" onclick="showDialog()"></i>
                                    <span class="tooltip-text">Delete Candidate</span>
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
                                    <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick="showInfo()"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
                                    <span class="tooltip-text">Delete Candidate</span>
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
                                    <i class="fa fa-info-circle fa-2xl" style="color: #2C1875;" onclick="showInfo()"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50;" onclick="showDialog()"></i>
                                    <span class="tooltip-text">Delete Candidate</span>
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
                                    <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick="showInfo()"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
                                    <span class="tooltip-text">Delete Candidate</span>
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
        <div class="popup" style="background-color: white;" id="info">
            <!-- Back Button -->
            <div class="addpartners-back-button" onclick="hideInfo()">
                <i class="fas fa-chevron-left"></i> Back
            </div>

            <div class="candidate-container">
                <div class="candidate-header">
                    <div>
                        <h2>Candidate</h2>
                        <div class="locationemail">
                            <i class="fa fa-map-pin" aria-hidden="true"></i><h3>Location</h3>
                        </div>
                        <div class="locationemail">
                            <i class="fa fa-envelope" aria-hidden="true"></i><h3>Email</h3>
                        </div>
                    </div>
                    <div><!--pic goes here--></div>
                </div>
                <div class="candidate-body">
                    <div id="personal-info">
                        <h3>Personal Information</h3>
                        <p id="personal-desc">lorem ipsum</p>
                        <ul>
                            <li>Gender:</li>
                            <li>Birthday:</li>
                            <li>Contact Number:</li>
                        </ul>
                    </div>
                    <div id="past-jobs">
                        <h3>Past Jobs</h3>
                        <ul>
                            <li>Job</li>
                            <li>job</li>
                        </ul>
                    </div>
                    <div id="education">
                        <h3>Education</h3>
                        <ul>
                            <li>Education</li>
                            <li>Education</li>
                        </ul>
                    </div>
                    <div id="skills">
                        <h3>Skills</h3>
                        <ul>
                            <li>Skills</li>
                            <li>Education</li>
                        </ul>
                    </div>          
                </div>
            </div>
        </div>

        <!-- Dialog Box -->
        <div class="rejected-dialog-box" id="dialogBox">
            <div class="rejected-back-button" onclick="hideDialog()">
                <i class="fas fa-chevron-left"></i> Back
            </div>
            
            <h2 style="text-align: center;">Are you sure you want to reject this candidate?</h2>
            <div class="rejected-form-group">
                <label for="rejected-firstname">Remarks:</label>
                <input type="text" id="rejected-firstname">
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
</script>


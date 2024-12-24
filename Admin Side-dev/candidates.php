<?php

session_start();

include 'session_check.php';

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
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Use a generic 'page' parameter for all tabs
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'pending'; // Tab that's currently active

// Calculate offset based on the current tab page
$offset = ($page - 1) * $limit;

$currentTab = isset($_POST['tab']) ? $_POST['tab'] : 'pending';

// New sorting parameters
$sortCompany = isset($_POST['sortCompany']) ? $_POST['sortCompany'] : 'All Companies';
$sortBy = isset($_POST['sortType']) ? $_POST['sortType'] : 'date_applied'; // Make sure sortBy comes from sortType
$sortOrder = isset($_POST['sortOrder']) ? $_POST['sortOrder'] : 'ASC'; // Default to ASC if not provided

// Check if these variables have the expected values
//echo "sortCompany: $sortCompany, sortBy: $sortBy, sortOrder: $sortOrder"; // Debugging line
function fetchCandidates($conn, $status, $offset, $limit, $sortCompany, $sortBy, $sortOrder, $searchQuery = '') {
    $validSortByColumns = ['date_applied', 'company_name', 'job_title']; // Allowed columns for sorting
    $validSortOrder = ['ASC', 'DESC']; // Allowed sort orders
    
    $sortBy = in_array($sortBy, $validSortByColumns) ? $sortBy : 'date_applied'; // Default to 'date_applied'
    $sortOrder = in_array($sortOrder, $validSortOrder) ? $sortOrder : 'ASC'; // Default to 'ASC'

    // Add condition for filtering by company if not "All Companies"
    $companyCondition = $sortCompany !== 'All Companies' ? "AND c.company_name = ?" : "";
    
    // Add condition for filtering by name
    $searchCondition = !empty($searchQuery) ? "AND (LOWER(CONCAT(a.fname, ' ', a.lname)) LIKE LOWER(?))" : "";

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
        resume_table r ON a.userid = r.userid
    WHERE 
        c.status = 'Approved' AND c.deployment_status = ? 
        $companyCondition
        $searchCondition
    GROUP BY 
        c.id, a.fname, a.lname, c.job_title, c.company_name, c.date_applied, c.deployment_status, 
        a.email, a.gender, a.birthday, a.location, a.phone, a.personal_description, 
        e.educational_attainment, e.school, e.course, 
        e.sy_started, e.sy_ended, v.school, v.course, 
        v.year_started, v.year_ended
    ORDER BY $sortBy $sortOrder
    LIMIT ?, ?";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Initialize variables for binding
    $statusParam = $status;
    $offsetParam = (int)$offset; // Ensure offset is an integer
    $limitParam = (int)$limit; // Ensure limit is an integer

    // Bind parameters dynamically based on the presence of $sortCompany and search query
    if ($sortCompany !== 'All Companies' && !empty($searchQuery)) {
        $searchParam = "%$searchQuery%";
        $stmt->bind_param("ssssi", $statusParam, $sortCompany, $searchParam, $offsetParam, $limitParam);
    } elseif ($sortCompany !== 'All Companies') {
        $stmt->bind_param("ssii", $statusParam, $sortCompany, $offsetParam, $limitParam);
    } elseif (!empty($searchQuery)) {
        $searchParam = "%$searchQuery%";
        $stmt->bind_param("ssii", $statusParam, $searchParam, $offsetParam, $limitParam);
    } else {
        $stmt->bind_param("sii", $statusParam, $offsetParam, $limitParam);
    }

    // Execute and return results
    $stmt->execute();
    return $stmt->get_result();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetchRequest'])) {
    $searchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';
    $result = fetchCandidates($conn, $currentTab, $offset, $limit, $sortCompany, $sortBy, $sortOrder, $searchQuery);

    // Define valid statuses for each tab
    $tabStatuses = [
        'pending' => ['Pending', 'Scheduled'],
        'scheduled' => ['Pending', 'Scheduled', 'Interviewed'],
        'interviewed' => ['Pending', 'Scheduled', 'Interviewed', 'Reserved'],
        'reserved' => ['Pending', 'Scheduled', 'Interviewed', 'Reserved', 'Deployed'],
        'deployed' => ['Pending', 'Scheduled', 'Interviewed', 'Reserved', 'Deployed']
    ];

    // Get the valid statuses for the current tab
    $validStatuses = isset($tabStatuses[$currentTab]) ? $tabStatuses[$currentTab] : [];

    // Build the HTML table dynamically
    $tableHTML = "<table style='margin-bottom: 1rem'>
                    <thead>
                        <tr class='th1'>
                            <th>Candidate</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Start building the row for the table
            $tableHTML .= "<tr class='tr1'>
                            <td class='fullname'>" . htmlspecialchars($row['full_name']) . "</td>
                            <td><strong>" . htmlspecialchars($row['job_title']) . "</strong></td>
                            <td>" . htmlspecialchars($row['company_name']) . "</td>
                            <td>" . (new DateTime($row['date_applied']))->format('m/d/Y') . "</td>
                            <td>
                            <select class='status-dropdown' data-original-value='" . ($row['deployment_status']) . "' onchange='updateStatus(this, " . json_encode($row['id']) . ")'>";
        
            // Loop through valid statuses for dropdown
            foreach ($validStatuses as $status) {
                $selected = $row['deployment_status'] === $status ? ' selected' : '';
                $tableHTML .= "<option value='$status'$selected>$status</option>";
            }
        
            // Close the dropdown and table data
            $tableHTML .= "</select>
                            </td>
                            <td class='candidates-tooltip-container'>
                                <i class='fa fa-info-circle fa-2xl' style='color: #2C1875;' onclick='showInfoCandidate(" . htmlspecialchars(json_encode($row)) . ")'></i>
                                <span class='tooltip-text'>Candidate Information</span>
                            </td>
                            <td class='candidates-tooltip-container'>
                                <i class='fa fa-undo fa-2xl' aria-hidden='true' style='color: #EF9B50; cursor: pointer;' onclick='undoApproval(" . htmlspecialchars($row['id']) . ")'></i>
                                <span class='tooltip-text'>Undo Approval</span>
                            </td>
                        </tr>";
        }
        
        // Close the if-else block and add the "No candidates found" message when there are no results
        if ($result->num_rows === 0) {
            $tableHTML .= "<tr><td colspan='7' style='text-align: center; color: #2C1875; font-weight: bold;'>No candidates found</td></tr>";
        }
        
        // Close the table and return the HTML
        $tableHTML .= "</tbody></table>";
    }
    // Add pagination
    $resultCount = $conn->query("SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = '$currentTab'");
    $rowCount = $resultCount->fetch_assoc();
    $totalPages = ceil($rowCount['total'] / $limit);

    if ($totalPages > 1) {
        $tableHTML .= "<div class='pagination'>";
        $tableHTML .= "<a href='?page$currentTab=1&tab=$currentTab' class='pagination-link " . ($page === 1 ? 'disabled' : '') . "'>First</a>";
        for ($i = 1; $i <= $totalPages; $i++) {
            $tableHTML .= "<a href='?page$currentTab=$i&tab=$currentTab' class='pagination-link " . ($i === (int)$page ? 'active' : '') . "'>$i</a>";
        }
        $tableHTML .= "<a href='?page$currentTab=$totalPages&tab=$currentTab' class='pagination-link " . ($page === $totalPages ? 'disabled' : '') . "'>Last</a>";
        $tableHTML .= "</div>";
    }

    // Return the HTML
    echo $tableHTML;
    exit();
}


// Get counts for each tab
$statusCounts = [];
$statuses = ['Pending', 'Scheduled', 'Interviewed', 'Reserved', 'Deployed'];

foreach ($statuses as $status) {
    $query = "SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = '$status'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $statusCounts[$status] = $row['total'];
}

// Fetch candidates for different statuses
$result = fetchCandidates($conn, $currentTab, $offset, $limit, $sortCompany, $sortBy, $sortOrder);

// Add this function to calculate total pages based on the current tab
function getTotalPages($conn, $currentTab, $limit) {
    $resultCount = $conn->query("SELECT COUNT(*) AS total FROM candidate_list WHERE status = 'Approved' AND deployment_status = '$currentTab'");
    $rowCount = $resultCount->fetch_assoc();
    return ceil($rowCount['total'] / $limit);
}
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
    <script>
    function updateStatus(selectElement, candidateId) {
    const oldStatus = selectElement.getAttribute("data-original-value");
    const newStatus = selectElement.value;
    
    console.log(oldStatus)
    console.log(newStatus)

        fetch('update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: candidateId, status: newStatus, oldstatus: oldStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully!');
                selectElement.setAttribute('data-original-value', newStatus);
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
document.addEventListener('DOMContentLoaded', () => {
    // Initial setup of event listeners for all select elements
    const sortCompanyDropdown = document.getElementById('sort_Company');
    const sortTypeDropdown = document.getElementById('sort_Type');
    const sortOrderDropdown = document.getElementById('sort_Order');

    // Attach the change event listener for all three select elements
    sortCompanyDropdown.addEventListener('change', setFilters);
    sortTypeDropdown.addEventListener('change', setFilters);
    sortOrderDropdown.addEventListener('change', setFilters);
});

// The function that will be called when any dropdown changes
function setFilters() {
    const sortCompany = document.getElementById('sort_Company').value;
    const sortType = document.getElementById('sort_Type').value;
    const sortOrder = document.getElementById('sort_Order').value;
    const currentTab = document.querySelector('.tab-content.active').id; // Get current tab id

    console.log('Selected values:', sortCompany, sortType, sortOrder); // Debugging output

      // Prepare data to send via POST
    const postData = new FormData();
    postData.append('sortCompany', sortCompany);
    postData.append('sortType', sortType); // 'sortBy' corresponds to your 'sort_Type'
    postData.append('sortOrder', sortOrder);
    postData.append('tab', currentTab)
    postData.append('fetchRequest', 'true'); // Indicate this is an AJAX request

    // Send data to the server
    fetch('candidates.php', {
        method: 'POST',
        body: postData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        // Update the table content with the new data
        document.getElementById(currentTab).innerHTML = data;

        // Reattach the event listeners to the new elements
        reattachEventListeners();
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Function to reattach event listeners to the select elements
function reattachEventListeners() {
    const sortCompanyDropdown = document.getElementById('sort_Company');
    const sortTypeDropdown = document.getElementById('sort_Type');
    const sortOrderDropdown = document.getElementById('sort_Order');

    console.log('Reattaching listeners:', sortCompanyDropdown, sortTypeDropdown, sortOrderDropdown);

    // Make sure the elements exist in the DOM before adding event listeners
    if (sortCompanyDropdown && sortTypeDropdown && sortOrderDropdown) {
        sortCompanyDropdown.removeEventListener('change', setFilters); // Remove any previous listener
        sortTypeDropdown.removeEventListener('change', setFilters);
        sortOrderDropdown.removeEventListener('change', setFilters);

        // Reattach the event listeners
        sortCompanyDropdown.addEventListener('change', setFilters);
        sortTypeDropdown.addEventListener('change', setFilters);
        sortOrderDropdown.addEventListener('change', setFilters);
    } else {
        console.error('One or more dropdowns not found after content update.');
    }
}

</script>
</head>
<body>
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
            <a href="candidates.php" class="active"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php" ><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="employees.php"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>
            <a href="chatbot.php"><i class="fa-solid fa-robot"></i> <span>Chatbot</span></a>
            <a href="activity_log.php"><i class="fa-solid fa-list"></i> <span>Activity Log</span></a> 
            

        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
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
                    <input type="text" class="search-candidates" id="searchInput" placeholder="Search Candidates">
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
                    <option value="date_applied">Date Applied</option>
                    <option value="company_name">Company Name</option>
                    <option value="job_title">Job Title</option>
                </select>
                <select id="sort_Order" class="order-sort">
                    <option value="ASC">Ascending</option>
                    <option value="DESC">Descending</option>
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
                <div class="tab <?php echo ($currentTab === 'reserved') ? 'active' : ''; ?>" 
                    onclick="openTab('reserved')">
                    Reserved (<?php echo $statusCounts['Reserved']; ?>)
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="tr1">
                                <td class="fullname"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo (new DateTime($row['date_applied']))->format('m/d/Y'); ?></td>
                                <td>
                                    <select class="status-dropdown" onchange="updateStatus(this, <?php echo json_encode($row['id']); ?>)">
                                        <option value="Pending" <?php echo ($row['deployment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Scheduled" <?php echo ($row['deployment_status'] === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
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
                <?php
                // Calculate the total number of pages needed for the current tab
                $totalPages = getTotalPages($conn, 'Pending', $limit); // Use the function we defined

                if ($totalPages > 1): // Only show pagination if more than one page
                    ?>
                    <div class="pagination">
                        <a href="?page=1&tab=pending" class="pagination-link <?php echo ($page === 1) ? 'disabled' : ''; ?>">First</a>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&tab=pending" class="pagination-link <?php echo ($i === (int)$page ) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?page=<?php echo $totalPages; ?>&tab=pending" class="pagination-link <?php echo ($page === $totalPages) ? 'disabled' : ''; ?>">Last</a>
                    </div>
                <?php endif; ?>
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                // Calculate the total number of pages needed for the current tab
                $totalPages = getTotalPages($conn, 'Scheduled', $limit); // Use the function we defined

                if ($totalPages > 1): // Only show pagination if more than one page
                    ?>
                    <div class="pagination">
                        <a href="?page=1&tab=scheduled" class="pagination-link <?php echo ($page === 1) ? 'disabled' : ''; ?>">First</a>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&tab=scheduled" class="pagination-link <?php echo ($i === (int)$page ) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <a href="?page=<?php echo $totalPages; ?>&tab=scheduled" class="pagination-link <?php echo ($page === $totalPages) ? 'disabled' : ''; ?>">Last</a>
                    </div>
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                                        <option value="Reserved" <?php echo ($row['deployment_status'] === 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
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
                    // Calculate the total number of pages needed for the current tab
                    $totalPages = getTotalPages($conn, 'Interviewd', $limit); // Use the function we defined

                    if ($totalPages > 1): // Only show pagination if more than one page
                        ?>
                        <div class="pagination">
                            <a href="?page=1&tab=interviewed" class="pagination-link <?php echo ($page === 1) ? 'disabled' : ''; ?>">First</a>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&tab=interviewed" class="pagination-link <?php echo ($i === (int)$page ) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <a href="?page=<?php echo $totalPages; ?>&tab=interviewed" class="pagination-link <?php echo ($page === $totalPages) ? 'disabled' : ''; ?>">Last</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reserved Candidates Tab -->
            <div id="reserved" class="tab-content <?php echo ($currentTab === 'reserved') ? 'active' : ''; ?>">
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                                        <option value="Reserved" <?php echo ($row['deployment_status'] === 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
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

                <!-- Reserved Pagination -->
                <div class="pagination">
                    <?php
                    // Calculate the total number of pages needed for the current tab
                    $totalPages = getTotalPages($conn, 'Reserved', $limit); // Use the function we defined

                    if ($totalPages > 1): // Only show pagination if more than one page
                        ?>
                        <div class="pagination">
                            <a href="?page=1&tab=reserved" class="pagination-link <?php echo ($page === 1) ? 'disabled' : ''; ?>">First</a>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&tab=reserved" class="pagination-link <?php echo ($i === (int)$page ) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <a href="?page=<?php echo $totalPages; ?>&tab=reserved" class="pagination-link <?php echo ($page === $totalPages) ? 'disabled' : ''; ?>">Last</a>
                        </div>
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                                        <option value="Reserved" <?php echo ($row['deployment_status'] === 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
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
                    // Calculate the total number of pages needed for the current tab
                    $totalPages = getTotalPages($conn, 'Deployed', $limit); // Use the function we defined

                    if ($totalPages > 1): // Only show pagination if more than one page
                        ?>
                        <div class="pagination">
                            <a href="?page=1&tab=deployed" class="pagination-link <?php echo ($page === 1) ? 'disabled' : ''; ?>">First</a>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&tab=deployed" class="pagination-link <?php echo ($i === (int)$page ) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <a href="?page=<?php echo $totalPages; ?>&tab=deployed" class="pagination-link <?php echo ($page === $totalPages) ? 'disabled' : ''; ?>">Last</a>
                        </div>
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
document.getElementById('searchInput').addEventListener('input', function() {
    const searchQuery = this.value.toLowerCase(); // Get the search query
    const currentTab = document.querySelector('.tab-content.active').id; // Get current tab id

    // Prepare data to send via POST
    const postData = new FormData();
    postData.append('searchQuery', searchQuery);
    postData.append('tab', currentTab);
    postData.append('fetchRequest', 'true'); // Indicate this is an AJAX request

    // Send data to the server
    fetch('candidates.php', {
        method: 'POST',
        body: postData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        // Update the table content with the new data
        document.getElementById(currentTab).innerHTML = data;

        // Reattach the event listeners to the new elements
        reattachEventListeners();
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

function fetchCandidatesForTab(tabName) {
    const postData = new FormData();
    postData.append('tab', tabName);
    postData.append('fetchRequest', 'true'); // Indicate this is an AJAX request

    // Send data to the server
    fetch('candidates.php', {
        method: 'POST',
        body: postData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        // Update the table content with the new data
        document.getElementById(tabName).innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>





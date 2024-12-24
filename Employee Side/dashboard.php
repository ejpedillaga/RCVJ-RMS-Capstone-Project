<?php
// Include the database connection file
include 'connection.php';
include 'session_check.php';
$conn = connection();


// --Queries for User Insights--
// Query for total applicants
$totalAppliQuery = "SELECT COUNT(*) AS total_applicants FROM applicant_table;";
$taqResult = mysqli_query($conn, $totalAppliQuery);

$totalApplicants = 0; // Default value in case the query fails

if ($taqResult && mysqli_num_rows($taqResult) > 0) {
    $row = mysqli_fetch_assoc($taqResult);
    $totalApplicants = $row['total_applicants']; // Get the count directly
}

// Query for New Applicants
$newAppliQuery = "SELECT COUNT(*) AS new_applicants
FROM candidate_list
WHERE date_applied >= CURDATE() - INTERVAL 7 DAY 
  AND status = 'Pending' 
  AND deployment_status = 'Pending'"; // For last 7 days with 'Pending' status
$naqResult = mysqli_query($conn, $newAppliQuery);

$newApplicants = 0;

if ($naqResult && mysqli_num_rows($naqResult) > 0) {
    $row = mysqli_fetch_assoc($naqResult);
    $newApplicants = $row['new_applicants']; // Get the count directly
}

//Query for Gender Distributions
$genderDistributionQuery = "SELECT gender, COUNT(*) AS Count
FROM applicant_table
GROUP BY gender";
$gdqResult = mysqli_query($conn, $genderDistributionQuery);

$genderNames = [];
$genderCounts = [];

if ($gdqResult && mysqli_num_rows($gdqResult) > 0) {
    while ($row = mysqli_fetch_assoc($gdqResult)) {
        $genderNames[] = $row['gender'];
        $genderCounts[] = (int)$row['Count'];
    }
}

// Convert arrays to JSON for JavaScript
$genderNamesJson = json_encode($genderNames);
$genderCountsJson = json_encode($genderCounts);

//Query for Age Distribution
$ageDistributionQuery = "SELECT 
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) < 20 THEN 'Under 20'
        WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 20 AND 29 THEN '20-29 Y/O'
        WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 30 AND 39 THEN '30-39 Y/O'
        WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 40 AND 49 THEN '40-49 Y/O'
        WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 50 AND 59 THEN '50-59 Y/O'
        ELSE '60 Y/O and above'
    END AS AgeRange,
    COUNT(*) AS Count
FROM applicant_table
GROUP BY AgeRange";
$adqResult = mysqli_query($conn, $ageDistributionQuery);

$ageRanges = [];
$ageCounts = [];

if($adqResult && mysqli_num_rows($adqResult) > 0){
    while ($row = mysqli_fetch_assoc($adqResult)) {
        $ageRanges[] = $row['AgeRange'];
        $ageCounts[] = (int)$row['Count'];
    }
}

// Convert arrays to JSON for JavaScript
$ageRangesJson = json_encode($ageRanges);
$ageCountsJson = json_encode($ageCounts);

// Query for Location Distribution
$locationDistributionQuery = "SELECT location, COUNT(*) AS Count
FROM applicant_table
GROUP BY location";
$ldqResult = mysqli_query($conn, $locationDistributionQuery);

$locationNames = [];
$locationCounts = [];

if ($ldqResult && mysqli_num_rows($ldqResult) > 0) {
    while ($row = mysqli_fetch_assoc($ldqResult)) {
        $locationNames[] = $row['location'];
        $locationCounts[] = (int)$row['Count'];
    }
}

// Convert arrays to JSON for JavaScript
$locationNamesJson = json_encode($locationNames);
$locationCountsJson = json_encode($locationCounts);

// Query for Classification Popularity
$classificationPopularityQuery = "SELECT classi, subclassi, COUNT(*) AS Count
FROM applicant_table
GROUP BY classi, subclassi
ORDER BY Count DESC";
$cpqResult = mysqli_query($conn, $classificationPopularityQuery);

$classiSubclassiNames = []; // Combined array for classi and subclassi
$classiCounts = [];

if ($cpqResult && mysqli_num_rows($cpqResult) > 0) {
    while ($row = mysqli_fetch_assoc($cpqResult)) {
        // Combine classi and subclassi into a single label
        $classiSubclassiNames[] = $row['classi'] . ' - ' . $row['subclassi'];
        $classiCounts[] = (int)$row['Count'];
    }
}

// Convert arrays to JSON for JavaScript
$classiSubclassiNamesJson = json_encode($classiSubclassiNames);
$classiCountsJson = json_encode($classiCounts);

//--Queries for Job Applications
// Query for Applications Over Time
$applicationsOverTimeQuery = "SELECT DATE(date_applied) AS ApplicationDate, COUNT(*) AS Applications
FROM candidate_list
GROUP BY ApplicationDate
ORDER BY ApplicationDate";
$aotqResult = mysqli_query($conn, $applicationsOverTimeQuery);

$applicationDates = [];
$applicationsCounts = [];

if ($aotqResult && mysqli_num_rows($aotqResult) > 0) {
    while ($row = mysqli_fetch_assoc($aotqResult)) {
        $applicationDates[] = $row['ApplicationDate'];
        $applicationsCounts[] = (int)$row['Applications'];
    }
}

// Convert arrays to JSON for JavaScript
$applicationDatesJson = json_encode($applicationDates);
$applicationsCountsJson = json_encode($applicationsCounts);

// Query for Deployment Status
$deploymentStatusQuery = "SELECT deployment_status, COUNT(*) AS Count
FROM candidate_list
GROUP BY deployment_status";
$dsqResult = mysqli_query($conn, $deploymentStatusQuery);

$deploymentStatusNames = [];
$deploymentStatusCounts = [];

if ($dsqResult && mysqli_num_rows($dsqResult) > 0) {
    while ($row = mysqli_fetch_assoc($dsqResult)) {
        $deploymentStatusNames[] = $row['deployment_status'];
        $deploymentStatusCounts[] = (int)$row['Count'];
    }
}

// Convert arrays to JSON for JavaScript
$deploymentStatusNamesJson = json_encode($deploymentStatusNames);
$deploymentStatusCountsJson = json_encode($deploymentStatusCounts);

//Query for ASD
$appliStatusQuery = "SELECT status, COUNT(*) AS Count
FROM candidate_list
GROUP BY status";
$asqResult = mysqli_query($conn, $appliStatusQuery);

$appliStatus = [];
$appliCounts = [];

if($asqResult && mysqli_num_rows($asqResult) > 0){
    while($row = mysqli_fetch_assoc($asqResult)){
        $appliStatus[] = $row['status'];
        $appliCounts[] = (int)$row['Count'];
    }
}

// Convert arrays to JSON for JavaScript
$appliStatusJson = json_encode($appliStatus);
$appliCountsJson = json_encode($appliCounts);



//--Queries for Candidate Profiles
// Query for Experience Levels
$experienceLevelsQuery = "SELECT
    job_title,
    AVG(YEAR(year_ended) - YEAR(year_started)) AS AvgYearsExperience
FROM job_experience_table
WHERE year_started IS NOT NULL AND year_ended IS NOT NULL
GROUP BY job_title";
$elqResult = mysqli_query($conn, $experienceLevelsQuery);

$jobTitles = [];
$avgExperienceYears = [];

if ($elqResult && mysqli_num_rows($elqResult) > 0) {
    while ($row = mysqli_fetch_assoc($elqResult)) {
        $jobTitles[] = $row['job_title'];
        $avgExperienceYears[] = (float)$row['AvgYearsExperience'];
    }
}

// Convert arrays to JSON for JavaScript
$jobTitlesJson = json_encode($jobTitles);
$avgExperienceYearsJson = json_encode($avgExperienceYears);


//--Queries for Job Postings
//Query for active job posts
$activeJobQuery = "SELECT job_status, COUNT(*) AS Count
FROM job_table
WHERE job_status = 'Open'";
$ajqResult = mysqli_query($conn, $activeJobQuery);

$activeJobs = 0;

if ($ajqResult && mysqli_num_rows($ajqResult) > 0) {
    $row = mysqli_fetch_assoc($ajqResult);
    $activeJobs = $row['Count']; // Get the count directly
}

// Query for New Job Posts in the Past 14 Days
$newJobsQuery = "SELECT COUNT(*) AS Count
FROM job_table
WHERE DATE(date_posted) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
$njqResult = mysqli_query($conn, $newJobsQuery);

$newJobs = 0;

if ($njqResult && mysqli_num_rows($njqResult) > 0) {
    $row = mysqli_fetch_assoc($njqResult);
    $newJobs = $row['Count']; // Get the count of new jobs in the past 14 days
}

// Query for Job Title Popularity
$jobTitlePopularityQuery = "SELECT job_title, COUNT(*) AS JobCount 
        FROM job_table 
        GROUP BY job_title 
        ORDER BY JobCount DESC 
        LIMIT 5";
$jtpResult = mysqli_query($conn, $jobTitlePopularityQuery);

$jtpNames = [];
$jtpCounts = [];

if ($jtpResult && mysqli_num_rows($jtpResult) > 0) {
    while ($row = mysqli_fetch_assoc($jtpResult)) {
        $jtpNames[] = $row['job_title'];
        $jtpCounts[] = (int)$row['JobCount'];
    }
}

$jtpNamesJson = json_encode($jtpNames);
$jtpCountsJson = json_encode($jtpCounts);

// Query for Job Postings Over Time
$jobPostingsOverTimeQuery = "SELECT DATE(date_posted) AS DatePosted, COUNT(*) AS JobPostings 
FROM job_table 
GROUP BY DatePosted 
ORDER BY DatePosted";
$jpotqResult = mysqli_query($conn, $jobPostingsOverTimeQuery);

$jobPostingDates = [];
$jobPostingsCounts = [];

if ($jpotqResult && mysqli_num_rows($jpotqResult) > 0) {
    while ($row = mysqli_fetch_assoc($jpotqResult)) {
        $jobPostingDates[] = $row['DatePosted'];
        $jobPostingsCounts[] = (int)$row['JobPostings'];
    }
}

$jobPostingDatesJson = json_encode($jobPostingDates);
$jobPostingsCountsJson = json_encode($jobPostingsCounts);

//--Queries for Partner Engagement
//Query for total partners
$totalPartnersQuery = "SELECT COUNT(*) AS total_partners FROM partner_table;";
$tpqResult = mysqli_query($conn, $totalPartnersQuery);

$totalPartners = 0;

if ($tpqResult && mysqli_num_rows($tpqResult) > 0) {
    $row = mysqli_fetch_assoc($tpqResult);
    $totalPartners = $row['total_partners']; // Get the count directly
}

// Query for Partner Count by Industry
$partnerIndustryQuery = "SELECT industry, COUNT(*) AS PartnerCount FROM partner_table GROUP BY industry ORDER BY PartnerCount DESC";
$piqResult = mysqli_query($conn, $partnerIndustryQuery);

$industries = [];
$partnerCounts = [];

if ($piqResult && mysqli_num_rows($piqResult) > 0) {
    while ($row = mysqli_fetch_assoc($piqResult)) {
        $industries[] = $row['industry']; // Store each industry name
        $partnerCounts[] = (int)$row['PartnerCount']; // Store the count of partners for each industry
    }
}

// Convert arrays to JSON for JavaScript
$industriesJson = json_encode($industries);
$partnerCountsJson = json_encode($partnerCounts);

// Query for Partner Count by Location
$partnerLocationQuery = "SELECT company_location, COUNT(*) AS PartnerCount 
FROM partner_table 
GROUP BY company_location 
ORDER BY PartnerCount DESC LIMIT 5";
$plqResult = mysqli_query($conn, $partnerLocationQuery);

$plqNames = [];
$plqCounts = [];

if ($plqResult && mysqli_num_rows($plqResult) > 0) {
    while ($row = mysqli_fetch_assoc($plqResult)) {
        $plqNames[] = $row['company_location']; // Store each location name
        $plqCounts[] = (int)$row['PartnerCount']; // Store the count of partners for each location
    }
}

// Convert arrays to JSON for JavaScript
$plqNamesJson = json_encode($plqNames);
$plqCountsJson = json_encode($plqCounts);


// Query for New Partners Added by Date
$newPartnersQuery = "SELECT DATE(date_added) AS DateAdded, COUNT(*) AS NewPartners 
FROM partner_table 
GROUP BY DateAdded 
ORDER BY DateAdded";
$npqResult = mysqli_query($conn, $newPartnersQuery);

$datesAdded = [];
$newPartnerCounts = [];

if ($npqResult && mysqli_num_rows($npqResult) > 0) {
    while ($row = mysqli_fetch_assoc($npqResult)) {
        $datesAdded[] = $row['DateAdded']; // Store each date partners were added
        $newPartnerCounts[] = (int)$row['NewPartners']; // Store the count of new partners for each date
    }
}

// Convert arrays to JSON for JavaScript
$npqNamesJson = json_encode($datesAdded);
$npqCountsJson = json_encode($newPartnerCounts);


//--Queries for Skills and Vocational Training
//Query for Top 5 skills
$topSkillsQuery = "SELECT skill_name, COUNT(*) AS job_count
          FROM job_skills_table
          JOIN skill_table ON job_skills_table.skill_id = skill_table.skill_id
          GROUP BY skill_name
          ORDER BY job_count DESC
          LIMIT 5";

$tsqResult = mysqli_query($conn, $topSkillsQuery);

// Store skill names and job counts in separate arrays
$skillNames = [];
$jobCounts = [];

if ($tsqResult && mysqli_num_rows($tsqResult) > 0) {
    while ($row = mysqli_fetch_assoc($tsqResult)) {
        $skillNames[] = ucfirst($row['skill_name']);//Capitalize first letter of each skill
        $jobCounts[] = (int)$row['job_count'];
    }
}

// Convert arrays to JSON for JavaScript
$skillNamesJson = json_encode($skillNames);
$jobCountsJson = json_encode($jobCounts);

$conn->close();


$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'user-insights';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | RCVJ Inc.</title>

    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
    <link rel="stylesheet" href="mediaqueries.css?v=<?php echo filemtime('mediaqueries.css'); ?>">
    <!--<link rel="stylesheet" href="dashstyle.css"> <!-- Dashboard styles -->
    <link rel="stylesheet" href="dashstyle.css?v=<?php echo filemtime('dashstyle.css'); ?>">

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
        <a href="dashboard.php" class="active"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
        <a href="jobs.html"><i class="fa-solid fa-suitcase"></i> <span>Jobs</span></a>
        <a href="smartsearch.php"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
        <a href="candidates.php"><i class="fa-solid fa-user"></i> <span>Candidates</span></a>
        <a href="schedules.php"><i class="fa-solid fa-calendar"></i> <span>Schedules</span></a>
        <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
        <a href="chatbot.php"><i class="fa-solid fa-robot"></i> <span>Chatbot</span></a>
        <a href="activity_log.php"><i class="fa-solid fa-list"></i> <span>Activity Log</span></a>
    </div>

    <!-- Header -->
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

    <!-- Main Content -->
    <div id="main">
        <h2 style="font-size: 36px; color: #000000">Dashboard</h2>

        <div class="tabs">
            <div class="tab <?php echo ($currentTab === 'user-insights') ? 'active' : ''; ?>" 
                onclick="openDashTab('user-insights')" style="color: black">
                User Insights
            </div>
            <div class="tab <?php echo ($currentTab === 'job-applications') ? 'active' : ''; ?>" 
                onclick="openDashTab('job-applications')" style="color: black">
                Job Applications
            </div>
            <div class="tab <?php echo ($currentTab === 'candidate-profiles') ? 'active' : ''; ?>" 
                onclick="openDashTab('candidate-profiles')" style="color: black">
                Candidate Profiles
            </div>
            <div class="tab <?php echo ($currentTab === 'job-postings') ? 'active' : ''; ?>" 
                onclick="openDashTab('job-postings')" style="color: black">
                Job Postings
            </div>
            <div class="tab <?php echo ($currentTab === 'partner-engagement') ? 'active' : ''; ?>" 
                onclick="openDashTab('partner-engagement')" style="color: black">
                Partner Engagement
            </div>
            <div class="tab <?php echo ($currentTab === 'snv-training') ? 'active' : ''; ?>" 
                onclick="openDashTab('snv-training')" style="color: black">
                Skills & Vocational Training
            </div>
        </div>

        <div id="user-insights" class="tab-content <?php echo ($currentTab === 'user-insights') ? 'active' : ''; ?>">
            <div class="main-cards" style="grid-template-columns: 1fr 1fr">
                <div class="card">
                    <div class="card-inner">
                        <p class="text-primary">TOTAL APPLICANTS</p>
                        <i class="fa-solid fa-users fa-2xl"></i>
                    </div>
                    <span class="text-primary font-weight-bold"><?php echo $totalApplicants?></span>
                </div>

                <div class="card">
                    <div class="card-inner">
                        <p class="text-primary">NEW APPLICANTS</p>
                        <i class="fa-solid fa-user-plus fa-2xl"></i>
                    </div>
                    <span class="text-primary font-weight-bold"><?php echo $newApplicants?></span>
                </div>
            </div>

            <div class="charts" style="grid-template-columns: 1fr 1fr 1fr 1fr">
                <div class="charts-card">
                    <p class="chart-title">Gender Distribution</p>
                    <div id="gender-pie-chart"></div>
                </div>

                <div class="charts-card">
                    <p class="chart-title">Age Distribution</p>
                    <div id="age-pie-chart"></div>
                </div>

                <div class="charts-card">
                    <p class="chart-title">Location Distribution</p>
                    <div id="location-pie-chart"></div>
                </div>

                <div class="charts-card">
                    <p class="chart-title">Classification Popularity</p>
                    <div id="classi-pie-chart"></div>
                </div>
            </div>
        </div>

        <div id="job-applications" class="tab-content <?php echo ($currentTab === 'job-applications') ? 'active' : ''; ?>">
            <div class="charts">
                <div class="charts-card">
                    <p class="chart-title">Application Status Distribution</p>
                    <div id="asd-pie-chart"></div>
                </div>

                <div class="charts-card">
                    <p class="chart-title">Deployment Status Distribution</p>
                    <div id="ads-pie-chart"></div>
                </div>
            </div>

            <div class="charts" style="grid-template-columns: 1fr">
                <div class="charts-card">
                    <p class="chart-title">Applications Over Time</p>
                    <div id="aot-chart"></div>
                </div>
            </div>
        </div>

        <div id="candidate-profiles" class="tab-content <?php echo ($currentTab === 'candidate-profiles') ? 'active' : ''; ?>">
            <div class="charts" style="grid-template-columns : 1fr">
                <div class="charts-card">
                    <p class="chart-title">Average Experience Levels</p>
                    <div id="ael-bar-chart"></div>
                </div>
            </div>
        </div>

        <div id="job-postings" class="tab-content <?php echo ($currentTab === 'job-postings') ? 'active' : ''; ?>">
            <div class="main-cards" style="grid-template-columns : 1fr 1fr">
                <div class="card">
                    <div class="card-inner">
                        <p class="text-primary">ACTIVE JOB POSTINGS</p>
                        <i class="fa-solid fa-clipboard fa-2xl"></i>
                    </div>
                    <span class="text-primary font-weight-bold"><?php echo $activeJobs?></span>
                </div>

                <div class="card">
                    <div class="card-inner">
                        <p class="text-primary">NEW JOB POSTINGS IN PREVIOUS 14 DAYS</p>
                        <i class="fa-solid fa-business-time fa-2xl"></i>
                    </div>
                    <span class="text-primary font-weight-bold"><?php echo $newJobs?></span>
                </div>
            </div>

            <div class="charts">
                <div class="charts-card">
                    <p class="chart-title">Most Posted Job Titles</p>
                    <div id="jtp-bar-chart"></div>
                </div>

                <div class="charts-card">
                    <p class="chart-title">Job Posts Over Time</p>
                    <div id="jpot-line-chart"></div>
                </div>
            </div>
        </div>

        <div id="partner-engagement" class="tab-content <?php echo ($currentTab === 'partner-engagement') ? 'active' : ''; ?>">
            <div class="main-cards" style="grid-template-columns: 1fr 1fr">
                <div class="card">
                    <div class="card-inner">
                        <p class="text-primary">TOTAL PARTNERS</p>
                        <i class="fa-solid fa-handshake fa-2xl"></i>
                    </div>
                    <span class="text-primary font-weight-bold"><?php echo $totalPartners?></span>
                </div>  
            </div>
        
            <div class="charts" style="grid-template-columns : 1fr 1fr">
                <div class="charts-card">
                    <p class="chart-title">Partner Industry Distribution</p>
                    <div id="piq-pie-chart"></div>
                </div>

                <div class="charts-card">
                    <p class="chart-title">Top Partner Locations</p>
                    <div id="plq-bar-chart"></div>
                </div>
            </div>

            <div class="charts" style="grid-template-columns : 1fr">
                <div class="charts-card">
                    <p class="chart-title">New Partners Over Time</p>
                    <div id="npq-line-chart"></div>
                </div>
            </div>
        </div>
        
        <div id="snv-training" class="tab-content <?php echo ($currentTab === 'snv-training') ? 'active' : ''; ?>">
                <div class="charts-card">
                    <p class="chart-title">Top Skills Required For Jobs </p>
                    <div id="ts-bar-chart"></div>
                </div>
        </div>
    </div>


    <!-- Scripts -->
    <!-- ApexCharts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.5/apexcharts.min.js"></script>
    <script>
        function openDashTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => content.classList.remove('active'));

        // Show the selected tab content
        document.getElementById(tabName).classList.add('active');

        // Remove 'active' from all tabs
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => tab.classList.remove('active'));

        // Add 'active' to the clicked tab
        const activeTab = document.querySelector(`.tab[onclick="openDashTab('${tabName}')"]`);
        if (activeTab) activeTab.classList.add('active');

        // Update the URL with the selected tab
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
        window.location.reload();

    }

    /*Currently interferes with chart rendering.
    // Load the correct tab on page load based on the URL parameter
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabName = urlParams.get('tab') || 'user-insights'; // Default to 'User Insights' if no tab is specified
        openDashTab(tabName);
    });
    */
                // ---------- CHARTS ----------
    function createLineChart(containerSelector, dataSeries, labels, xText = 'Labels', yText = 'Values', colors = ['#00ab57'], chartHeight = 350) {
        const lineChartOptions = {
            series: [{
                name: yText,  // Name of the series (can be customized)
                data: dataSeries  // Data values for the line
            }],
            chart: {
                type: 'line',
                background: 'transparent',
                height: chartHeight,
                zoom: {
                    enabled: true  // Disable zooming if not needed
                },
                toolbar: {
                    show: false,  // Show the toolbar
                    tools: {
                        download: false,  // Enable the download button
                    },
                },
            },
            xaxis: {
                categories: labels,  // Labels for the x-axis
                title: {
                    text: xText  // Title for the x-axis (can be customized)
                }
            },
            yaxis: {
                title: {
                    text: yText  // Title for the y-axis (can be customized)
                }
            },
            colors: colors,  // Colors for the line
            dataLabels: {
                enabled: true,  // Show data labels on the line
                style: {
                    colors: ['#000']  // Color of data labels
                }
            },
            tooltip: {
                theme: 'dark',  // Tooltip theme (can be customized)
                x: {
                    show: true  // Show x-axis value on hover
                }
            },
            stroke: {
                curve: 'smooth',  // Smooth curve for the line
                width: 2  // Width of the line
            },
            legend: {
                show: true,  // Show legend
                position: 'top',  // Position of the legend
                labels: {
                    colors: '#000'  // Color of legend labels
                }
            },
            title: {
                align: 'center',
                style: {
                    fontSize: '20px',
                    color: '#000'  // Title color
                }
            }
        };

        const lineChart = new ApexCharts(
            document.querySelector(containerSelector),  // Select the container
            lineChartOptions
        );
        lineChart.render();  // Render the chart
    }

     
        // BAR CHART
        function createBarChart(containerSelector, dataSeries, categories, dataName = 'Data Series', chartHeight = 350) {
            const barChartOptions = {
                series: [
                    {
                        data: dataSeries,
                        name: dataName,
                    },
                ],
                chart: {
                    type: 'bar',
                    background: 'transparent',
                    height: chartHeight,
                    toolbar: {
                        show: false,
                    },
                },
                colors: ['#2962ff', '#d50000', '#2e7d32', '#ff6d00', '#583cb3'],
                plotOptions: {
                    bar: {
                        distributed: true,
                        borderRadius: 4,
                        horizontal: false,
                        columnWidth: '40%',
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                fill: {
                    opacity: 1,
                },
                grid: {
                    borderColor: '#55596e',
                    yaxis: {
                        lines: {
                            show: true,
                        },
                    },
                    xaxis: {
                        lines: {
                            show: true,
                        },
                    },
                },
                legend: {
                    labels: {
                        colors: '#000000',
                    },
                    show: true,
                    position: 'top',
                },
                stroke: {
                    colors: ['transparent'],
                    show: true,
                    width: 2,
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'dark',
                },
                xaxis: {
                    categories: categories,
                    title: {
                        style: {
                            color: '#f5f7ff',
                        },
                    },
                    axisBorder: {
                        show: true,
                        color: '#55596e',
                    },
                    axisTicks: {
                        show: true,
                        color: '#55596e',
                    },
                    labels: {
                        style: {
                            colors: '#000000',
                        },
                    },
                },
                yaxis: {
                    title: {
                        text: 'Value',
                        style: {
                            color: '#000000',
                        },
                    },
                    tickAmount: 2,
                    axisBorder: {
                        color: '#55596e',
                        show: true,
                    },
                    axisTicks: {
                        color: '#55596e',
                        show: true,
                    },
                    labels: {
                        style: {
                            colors: '#000000',
                        },
                    },
                },
            };

            const barChart = new ApexCharts(document.querySelector(containerSelector), barChartOptions);
            barChart.render();
        }


    // PIE CHART
    function createPieChart(containerSelector, dataSeries, labels, colors = ['#00ab57', '#d50000', '#3a87ad', '#e0e0e0', '#ff9800'], chartHeight = 350) {
        const pieChartOptions = {
            series: dataSeries,  // data values for each slice
            chart: {
                type: 'pie',
                background: 'transparent',
                height: chartHeight,
                toolbar: {
                    show: false,  // Show the toolbar
                    tools: {
                        download: false,  // Enable the download button
                    },
                },
            },
            labels: labels,  // labels for each slice
            colors: colors,  // colors for each slice
            dataLabels: {
                enabled: true,
                style: {
                    colors: ['#fff'],  // color of data labels
                },
            },
            legend: {
                show: true,
                position: 'bottom',
                labels: {
                    colors: '#000000',
                },
            },
            tooltip: {
                theme: 'dark',
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '45%',  // optional for donut style pie chart
                    },
                },
            },
            title: {
                align: 'center',
                style: {
                    fontSize: '20px',
                    color: '#000000',
                },
            },
        };

        const pieChart = new ApexCharts(
            document.querySelector(containerSelector),  // select the container
            pieChartOptions
        );
        pieChart.render();

    }

    createPieChart('#gender-pie-chart', <?php echo $genderCountsJson;?>, <?php echo $genderNamesJson;?>);
    createPieChart('#age-pie-chart', <?php echo $ageCountsJson;?>, <?php echo $ageRangesJson;?>);
    createPieChart('#location-pie-chart', <?php echo $locationCountsJson;?>, <?php echo $locationNamesJson;?>)
    createPieChart('#classi-pie-chart', <?php echo $classiCountsJson;?>, <?php echo $classiSubclassiNamesJson;?>)
    createPieChart('#asd-pie-chart', <?php echo $appliCountsJson;?>, <?php echo $appliStatusJson;?>);
    createPieChart('#ads-pie-chart', <?php echo $deploymentStatusCountsJson;?>, <?php echo $deploymentStatusNamesJson;?>)
    createPieChart('#piq-pie-chart', <?php echo $partnerCountsJson;?>, <?php echo $industriesJson;?>)
    createLineChart('#aot-chart', <?php echo $applicationsCountsJson;?>, <?php echo $applicationDatesJson;?>, 'Date', 'Applicants')
    createLineChart('#jpot-line-chart', <?php echo $jobPostingsCountsJson;?>, <?php echo $jobPostingDatesJson;?>, 'Date', 'Job Postings')
    createLineChart('#npq-line-chart', <?php echo $npqCountsJson;?>, <?php echo $npqNamesJson;?>, 'Date', 'New Partners')
    createBarChart('#ael-bar-chart', <?php echo $avgExperienceYearsJson;?>, <?php echo $jobTitlesJson;?>, 'Average Job Experience')
    createBarChart('#jtp-bar-chart', <?php echo $jtpCountsJson;?>, <?php echo $jtpNamesJson;?>, 'Jobs')
    createBarChart('#plq-bar-chart', <?php echo $plqCountsJson;?>, <?php echo $plqNamesJson;?>, 'Partners')
    createBarChart('#ts-bar-chart', <?php echo $jobCountsJson;?>, <?php echo $skillNamesJson;?>, 'Jobs')
    
    
    function confirmOpenLink(event) {
    var userConfirmation = confirm("This link will take you to the Tidio website where you can customize the Tidio Chatbot. Please note that a login is required to access the features. Do you want to continue?");
        
        if (!userConfirmation) {
            event.preventDefault();
            return false;
        }
        
        return true;
    }
    </script>
    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>    
</body>
</html>

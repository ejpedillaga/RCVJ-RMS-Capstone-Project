<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Side RCVJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>  
      
</head>
<body>
    <div id="mySidebar" class="sidebar">
        <div class="sidebar-header">
            <h3>RCVJ Inc.</h3>
            <button class="toggle-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
            <a href="employeeHome.php"><i class="fa-solid fa-suitcase"></i> <span>Jobs</span></a>
            <a href="smartsearch.php" class="active" style="color: #EF9B50;"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.php"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name">Employee</span>
            </div>
        </div>

        <div id="main">
            <h2 style="font-size: 36px;">Smart Search</h2>
            <div class="filter-container">
                <div class="search-wrapper">
                    <div class="input-container">
                        <input type="text" class="job-bar" id="job-bar" placeholder="Search Jobs">
                        <label for="job-bar" class="input-label">Job:</label>
                    </div>
                </div>
            
                <div class="search-wrapper">
                    <div class="input-container">
                        <input type="text" class="location-bar" id="location-bar" placeholder="Search Location">
                        <label for="location-bar" class="input-label">Location:</label>
                    </div>
                </div>
            
                <div class="search-wrapper">
                    <div class="input-container">
                        <input type="text" class="skills-bar" id="skills-bar" placeholder="Search Skills">
                        <label for="skills-bar" class="input-label">Skills:</label>
                    </div>
                </div>
            </div>
            
            <div>
                <table>
                    <tr class="th1">
                        <th>Candidate</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                    </tr>

                    <tr class="spaceunder">
                        <td></td>
                    </tr>

                    <tr class="tr1">
                        <td class="fullname">Juan Miguel Escalante</td>
                        <td><strong>SUPERMARKET BAGGER</strong></td>
                        <td>WalterMart <br>Supermarket - Dasmarinas</td>
                        <td>5/13/2024</td>
                        <td>
                            <select class="status-dropdown">
                                <option>Open</option>
                                <option>Closed</option>
                            </select>
                        </td>

                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>


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
            <a href="employeeHome.php" class="active" style="color: #EF9B50;"><i class="fa-solid fa-suitcase" s></i> <span>Jobs</span></a>
            <a href="smartsearch.php"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
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
                <h2 style="font-size: 36px;">Jobs</h2>

                <div class="btnGrp">
                    <div class="toggle-button">
                        <div class="toggle-option open" onclick="toggle(this)">Open (1)</div>
                        <div class="toggle-option closed" onclick="toggle(this)">Closed (0)</div>
                        
                    </div>

                    <div>
                        <a href="jobposting.php">
                            <button class="post-button">Post a Job</button>
                        </a>
                        
                    </div>
                </div>
                

                <div class="filter-container">
                    <div class="search-wrapper">
                        <i class="fas fa-magnifying-glass search-icon"></i>
                        <input type="text" class="search-bar" placeholder="Search Jobs">
                    </div>
                    <select class="sort-by">
                        <option>Sort by: Posting Date</option>
                        <option>Sort by: Company Name</option>
                        <option>Sort by: Job Title</option>
                    </select>
                    <select class="order-sort">
                        <option>Ascending</option>
                        <option>Descending</option>
                    </select>
                </div>

                <div>
                    <table>
                        <tr class="th1">
                            <th>Company</th>
                            <th>Job Title</th>
                            <th>Date Posted</th>
                            <th>Candidates</th>
                            <th>Status</th>
                            <th>Edit</th>
                        </tr>

                        <tr class="spaceunder">
                            <td></td>
                        </tr>

                        <tr class="tr1">
                            <td><img style="height: 100px; width: 100px;" src="img/walter.png" alt="Walter Supermarket Logo"></td>
                            <td><strong>SUPERMARKET BAGGER</strong><br>Dasmarinas, Cavite</td>
                            <td>5/13/2024</td>
                            <td>20</td>
                            <td>
                                <select class="status-dropdown">
                                    <option>Open</option>
                                    <option>Closed</option>
                                </select>
                            </td>
                            <td><i class="fa-solid fa-pen-to-square fa-2xl" style="color: #EF9B50;"></i></td>
                        </tr>
                    </table>
                </div>
        </div>
    </div>
</body>

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
            <a href="smartsearch.php"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.php"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="employees.php"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>
        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name">Employee</span>
            </div>
        </div>

        <div id="main">
            <h2 style="font-size: 36px;">Candidates</h2>

            <div class="filter-container">
                <div class="search-wrapper">
                    <i class="fas fa-magnifying-glass search-icon"></i>
                    <input type="text" class="search-candidates" placeholder="Search Candidates">
                </div>
                <select class="sort-by">
                    <option>Sort by: Date</option>
                    <option>Sort by: Name</option>
                </select>
                <select class="order-sort">
                    <option>Ascending</option>
                    <option>Descending</option>
                </select>

                <div>
                    <a href="candidates.php">
                        <button class="candidates-button">Candidates</button>
                    </a>
                </div>
            </div>

            

            <div>
                <table>
                    <tr class="th1">
                        <th>Candidate</th>
                        <th>Remarks</th>
                        <th>Date Rejected</th>
                        <th>Restore</th>
                        <th>Delete</th>

                    </tr>

                    <tr class="spaceunder">
                        <td></td>
                    </tr>

                    <tr class="tr1">
                        <td class="fullname">Juan Miguel Escalante</td>
                        <td>Lacks Experience</td>
                        <td>07/07/2024</td>
                        <td><i class="fa-solid fa-rotate-left fa-2xl" style="color: #2C1875;"></i></td>
                        <td><i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50;"></i></td>
                    </tr>

                    <tr class="spaceunder">
                        <td></td>
                    </tr>

                </table>
            </div>
        </div>
    </div>
</body>

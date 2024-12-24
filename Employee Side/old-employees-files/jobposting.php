<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Side RCVJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
      
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
        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name">Employee</span>
            </div>
        </div>

        <div id="main">
            <h2 style="font-size: 36px;">Job Posting</h2>
            <p>Fill in all the needed fields</p>
            <div class="jobposting-container">
            
            <div class="jobposting-box jobposting-box-large">
                <label for="jobposting-partner-company">Partner Company</label><br>
                <select id="jobposting-partner-company" class="jobposting-select">
                    <option>WalterMart Supermarket - Dasmariñas</option>
                </select>
            </div>
            
            <div class="jobposting-box jobposting-box-medium">
                <label for="jobposting-job-title">Job Title</label><br>
                <input type="text" id="jobposting-job-title" class="jobposting-input" value="Cashier"><br><br>
                <label for="jobposting-location">Location</label><br>
                <input type="text" id="jobposting-location" class="jobposting-input" value="Dasmariñas, Cavite"><br><br>
                <label for="jobposting-openings">Available openings</label><br>
                <select id="jobposting-openings" class="jobposting-select">
                    <option>5</option>
                </select>
            </div>
            
            <div class="jobposting-box jobposting-box-xlarge">
                <label for="jobposting-description">Job Description</label><br>
                <textarea id="jobposting-description" class="jobposting-textarea"></textarea>
            </div>
            
            <div class="jobposting-box jobposting-box-small">
                <label for="jobposting-skills">Skills</label><br>
                <div class="jobposting-skills">
                    <div class="jobposting-skill">Accounting</div>
                    <div class="jobposting-skill">Communication</div>
                </div>
            </div>
            
            <div class="jobposting-buttons">
                <a href="employeeHome.php">
                    <button class="jobposting-button jobposting-button-cancel">Cancel</button>
                </a>
                <button class="jobposting-button jobposting-button-save">Save and Post</button>
            </div>
        </div>

        <script>
            CKEDITOR.replace('jobposting-description');
        </script>
    </div>
</body>

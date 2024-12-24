<?php include 'session_check.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot | RCVJ, Inc.</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
    <link rel="stylesheet" href="mediaqueries.css">
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
    <link rel="apple-touch-icon" sizes="180x180" href="rcvj-logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="rcvj-logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="rcvj-logo/favicon-16x16.png">
    <link rel="manifest" href="rcvj-logo/site.webmanifest">
    <style>
        .chatbot-cred {
            background-color: #2c1875;
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0rem;
        }
    </style>
      
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
            <a href="candidates.php"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="chatbot.php" class="active"><i class="fa-solid fa-robot"></i> <span>Chatbot</span></a>
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
            <h2 style="font-size: 36px;">Chatbot</h2>
            
            <iframe src="https://www.tidio.com/panel/flows/my-flows" width="100%" height="70%" frameborder="0" style="border-radius: 10px; overflow: hidden;"></iframe>
        </div>

        
    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>
</body>


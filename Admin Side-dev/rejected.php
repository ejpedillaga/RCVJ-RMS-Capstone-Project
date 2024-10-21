<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected | RCVJ, Inc.</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
    <link rel="stylesheet" href="mediaqueries.css?=<?php echo filemtime('mediaqueries.css'); ?>"></link>
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch companies on page load
            fetch('fetch_rejected_data.php')
                .then(response => response.json())
                .then(data => {
                    populateCompanyDropdown(data.companies);
                })
                .catch(error => console.error('Error fetching data:', error));
            
            // Listen for changes on the company select dropdown
            document.getElementById('companySelect').addEventListener('change', function() {
                const selectedCompany = this.value;
                // Reset the job titles dropdown
                populateJobTitleDropdown([]); // Clear job titles

                if (selectedCompany) {
                    fetch(`fetch_rejected_data.php?company=${encodeURIComponent(selectedCompany)}`)
                        .then(response => response.json())
                        .then(data => {
                            populateJobTitleDropdown(data.jobTitles);
                        })
                        .catch(error => console.error('Error fetching job titles:', error));
                }
            });
        });

        function populateCompanyDropdown(companies) {
            const companySelect = document.getElementById('companySelect');
            companySelect.innerHTML = '<option>All Companies</option>'; // Clear existing options

            companies.forEach(company => {
                const option = document.createElement('option');
                option.value = company;
                option.textContent = company;
                companySelect.appendChild(option);
            });
        }

        function populateJobTitleDropdown(jobTitles) {
            const jobTitleSelect = document.getElementById('jobTitleSelect');
            jobTitleSelect.innerHTML = '<option>All Job Titles</option>'; // Clear existing options

            jobTitles.forEach(jobTitle => {
                const option = document.createElement('option');
                option.value = jobTitle;
                option.textContent = jobTitle;
                jobTitleSelect.appendChild(option);
            });
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
            <a href="index.html"><i class="fa-solid fa-suitcase"></i> <span>Jobs</span></a>
            <a href="smartsearch.php" class="active"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.php"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
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
            <h2 style="font-size: 36px;">Rejected Applicants</h2>

            <div class="filter-container">
                <div class="search-wrapper">
                    <i class="fas fa-magnifying-glass search-icon"></i>
                    <input type="text" class="search-bar" id="searchBar" placeholder="Search Candidates">
                </div>
                <select id="companySelect" class="sort-by">
                    <option hidden>All Companies</option>
                </select>
                <select id="jobTitleSelect" class="sort-by">
                    <option hidden>All Job Titles</option>
                </select>
                <select id="dateNameSelect" class="sort-by">
                    <option>Date Rejected</option>
                    <option>Candidate Name</option>
                </select>
                <select id="orderSelect" class="order-sort">
                    <option>Ascending</option>
                    <option>Descending</option>
                </select>

                <button class="rejected-button" onclick="redirectTo('smartsearch.php')">Applicants</button>
            </div>

            

            <div>
                <table>
                    <thead>
                        <tr class="th1">
                            <th>Candidate</th>
                            <th>Job Title</th>
                            <th>Company Name</th>
                            <th>Remarks</th>
                            <th>Date Rejected</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="rejectsTableBody">
                        <!-- Rejected applicants will be populated here -->
                    </tbody>
                </table>

                <!-- Pagination Controls -->
                <div id="paginationControls" class="pagination"></div>
            </div>
        </div>
    </div>
    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>   
</body>
<script>
    function undoRejection(userId, jobId) {
        if (confirm("Are you sure you want to undo the rejection of this candidate?")) {
            fetch('undo_rejection.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    userid: userId,
                    jobid: jobId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Candidate status has been restored to Pending.');
                    location.reload(); // Reload the page to refresh the table
                } else {
                    console.error('Error undoing rejection:', data.error);
                    alert('Failed to undo rejection. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    }

    let currentPage = 1;
    const resultsPerPage = 5;
    let rejects = [];

    document.addEventListener('DOMContentLoaded', function() {
        const searchBar = document.getElementById('searchBar');
        const companySelect = document.getElementById('companySelect');
        const jobTitleSelect = document.getElementById('jobTitleSelect');
        const dateNameSelect = document.getElementById('dateNameSelect');
        const orderSelect = document.getElementById('orderSelect');

        searchBar.addEventListener('input', filterRejects);
        companySelect.addEventListener('change', filterRejects);
        jobTitleSelect.addEventListener('change', filterRejects);
        dateNameSelect.addEventListener('change', filterRejects);
        orderSelect.addEventListener('change', filterRejects);

        fetch('fetch_rejects.php')
            .then(response => response.json())
            .then(data => {
                populateRejectsTable(data);
            })
            .catch(error => console.error('Error fetching rejects:', error));
    });

    function filterRejects() {
        const searchTerm = document.getElementById('searchBar').value.toLowerCase();
        const selectedCompany = document.getElementById('companySelect').value;
        const selectedJobTitle = document.getElementById('jobTitleSelect').value;
        const selectedFilter = document.getElementById('dateNameSelect').value;
        const selectedOrder = document.getElementById('orderSelect').value;

        // Filter rejects
        const filteredRejects = rejects.filter(reject => {
            const matchesSearch = reject.full_name.toLowerCase().includes(searchTerm) ||
                                  reject.company_name.toLowerCase().includes(searchTerm) ||
                                  reject.job_title.toLowerCase().includes(searchTerm);
            const matchesCompany = selectedCompany === "All Companies" || reject.company_name === selectedCompany;
            const matchesJobTitle = selectedJobTitle === "All Job Titles" || reject.job_title === selectedJobTitle;

            return matchesSearch && matchesCompany && matchesJobTitle;
        });

        // Sort filtered results
        filteredRejects.sort((a, b) => {
            let comparison = 0;
            if (selectedFilter === "Candidate Name") {
                comparison = a.full_name.localeCompare(b.full_name);
            } else if (selectedFilter === "Date Rejected") {
                comparison = new Date(a.date_rejected) - new Date(b.date_rejected);
            }
            return selectedOrder === "Ascending" ? comparison : -comparison;
        });

        // Update the current page to 1 when new filtering occurs
        currentPage = 1;

        renderTable(filteredRejects);
    }

    function populateRejectsTable(data) {
        rejects = data;
        renderTable(rejects);
    }

    function renderTable(data) {
        const tableBody = document.getElementById('rejectsTableBody');
        tableBody.innerHTML = '';

        if (data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; font-weight: bold; color: #2C1875;">No rejected applicants found</td></tr>`;
            updatePaginationControls(0);
            return;
        }

        const startIndex = (currentPage - 1) * resultsPerPage;
        const endIndex = Math.min(startIndex + resultsPerPage, data.length);

        for (let i = startIndex; i < endIndex; i++) {
            const reject = data[i];
            tableBody.innerHTML += `
                <tr class="tr1">
                    <td class="fullname">${reject.full_name}</td>
                    <td><strong>${reject.job_title}</strong></td>
                    <td>${reject.company_name}</td>
                    <td>${reject.remarks}</td>
                    <td>${formatDate(reject.date_rejected)}</td>
                    <td class="candidates-tooltip-container">
                        <i class="fa-solid fa-rotate-left fa-2xl" style="color: #2C1875; cursor: pointer;" onclick="undoRejection(${reject.userid}, ${reject.job_id})"></i>
                        <span class="tooltip-text">Undo Rejection</span>
                    </td>
                    <td class="candidates-tooltip-container">
                        <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="removeApplicant(${reject.userid}, ${reject.job_id})"></i>
                        <span class="tooltip-text">Remove Applicant</span>
                    </td>
                </tr>`;
        }
        updatePaginationControls(Math.ceil(data.length / resultsPerPage));
    }

    function updatePaginationControls(totalPages) {
        const paginationContainer = document.getElementById('paginationControls');
        paginationContainer.innerHTML = ''; // Clear existing controls

        if (totalPages > 1) {
            const firstPageLink = `<a href="#" onclick="changePage(1)">First</a>`;
            paginationContainer.innerHTML += firstPageLink;

            for (let i = 1; i <= totalPages; i++) {
                const pageLink = `<a href="#" onclick="changePage(${i})" class="pagination-link ${i === currentPage ? 'active' : ''}">${i}</a>`;
                paginationContainer.innerHTML += pageLink;
            }

            const lastPageLink = `<a href="#" onclick="changePage(${totalPages})">Last</a>`;
            paginationContainer.innerHTML += lastPageLink;
        }
    }

    function changePage(page) {
        currentPage = page;
        // Render the filtered table
        const searchTerm = document.getElementById('searchBar').value.toLowerCase();
        const selectedCompany = document.getElementById('companySelect').value;
        const selectedJobTitle = document.getElementById('jobTitleSelect').value;
        const selectedFilter = document.getElementById('dateNameSelect').value;
        const selectedOrder = document.getElementById('orderSelect').value;

        // Filter again based on current selections to respect pagination
        const filteredRejects = rejects.filter(reject => {
            const matchesSearch = reject.full_name.toLowerCase().includes(searchTerm) ||
                                  reject.company_name.toLowerCase().includes(searchTerm) ||
                                  reject.job_title.toLowerCase().includes(searchTerm);
            const matchesCompany = selectedCompany === "All Companies" || reject.company_name === selectedCompany;
            const matchesJobTitle = selectedJobTitle === "All Job Titles" || reject.job_title === selectedJobTitle;

            return matchesSearch && matchesCompany && matchesJobTitle;
        });

        renderTable(filteredRejects);
    }

    function removeApplicant(userId, jobId) {
    if (confirm("Are you sure you want to remove this applicant?")) {
        fetch('remove_applicant.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                userid: userId,
                jobid: jobId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Applicant has been successfully removed.');
                // Remove the applicant from the UI
                location.reload();
            } else {
                console.error('Error removing applicant:', data.error);
                alert('Failed to remove applicant. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>


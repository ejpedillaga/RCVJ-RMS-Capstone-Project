<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partners</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
    <link rel="stylesheet" href="mediaqueries.css?=<?php echo filemtime('mediaqueries.css'); ?>"></link>
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>

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
        <a href="candidates.php"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
        <a href="schedules.php"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
        <a href="partners.php" class="active"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
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
        <h2 style="font-size: 36px;">Partners</h2>
        <div class="filter-container">
            <div class="search-wrapper">
                <i class="fas fa-magnifying-glass search-icon"></i>
                <input type="text" id="partner-search-bar" class="search-partners" placeholder="Search Partners">
            </div>
            <select id="sort-by" class="sort-by" onchange="sortPartners()">
                <option value="date">Date Added</option>
                <option value="company">Company Name</option>
            </select>
            <select id="order-sort" class="order-sort" onchange="sortPartners()">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
            <button class="add-partners-button" onclick="showDialog()">Add Partners</button>
        </div>

        <div>
            <table>
                <thead>
                    <tr class="th1">
                        <th>Logo</th>
                        <th>Company Name</th>
                        <th></th>
                        <th>Date Added</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>
    
    <!--Edit Dialog Box Partners-->
    <div class="addpartners-dialog-box" id="editPartner-dialogBox">
        <!-- Back Button -->
        <div class="addpartners-back-button" onclick="hideEditPartnerDialog()">
            <i class="fas fa-chevron-left"></i> Back
        </div>
        
        <!-- Form Content -->
        <div class="addpartners-dialog-box-content">
            <!-- Upload Logo -->
            <div class="addpartners-form-group addpartners-upload-logo" onclick="document.getElementById('edit-logo-upload').click()">
                <input type="file" id="edit-logo-upload" accept="image/*" onchange="previewEditLogo(event)" style="display: none;">
                <img id="edit-logo-preview" src="" alt="Upload Logo" style="width: 100%; display: none;">
                <div id="edit-upload-placeholder">Upload Logo</div>
            </div>
    
            <!-- Company Info -->
            <div style="display: flex; flex-direction: column;">
                <div class="addpartners-form-group">
                    <label for="editpartners-company-name">Company Name</label>
                    <input type="text" id="editpartners-company-name" >
                </div>
                <div class="inline-group">
                    <div class="addpartners-form-group">
                        <label for="editpartners-industry">Industry</label>
                        <input type="text" id="editpartners-industry" >
                    </div>
                    <div class="addpartners-form-group">
                        <label for="editpartners-location">Location</label>
                        <input type="text" id="editpartners-location" >
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Company Description -->
        <div class="addpartners-form-group">
            <label for="edditpartners-company-description">Company Description</label>
            <textarea id="editpartners-company-description"></textarea>
        </div>
    
        <!-- Save Button -->
        <button class="addpartners-save-button" onclick="editPartner()">Save and Add</button>
    </div>

    

    <!-- Dialog Box Partners-->
    <div class="addpartners-dialog-box" id="dialogBox">
        <!-- Back Button -->
        <div class="addpartners-back-button" onclick="hideDialog()">
            <i class="fas fa-chevron-left"></i> Back
        </div>
        
        <!-- Form Content -->
        <div class="addpartners-dialog-box-content">
            <!-- Upload Logo -->
            <div class="addpartners-form-group addpartners-upload-logo" onclick="document.getElementById('logo-upload').click()">
                <input type="file" id="logo-upload" accept="image/*" onchange="previewLogo(event)" style="display: none;">
                <img id="logo-preview" src="" alt="Upload Logo" style="width: 100%; display: none;">
                <div id="upload-placeholder">Upload Logo</div>
            </div>
    
            <!-- Company Info -->
            <div style="display: flex; flex-direction: column;">
                <div class="addpartners-form-group">
                    <label for="addpartners-company-name">Company Name</label>
                    <input type="text" id="addpartners-company-name" >
                </div>
                <div class="inline-group">
                    <div class="addpartners-form-group">
                        <label for="addpartners-industry">Industry</label>
                        <input type="text" id="addpartners-industry" >
                    </div>
                    <div class="addpartners-form-group">
                        <label for="addpartners-location">Location</label>
                        <input type="text" id="addpartners-location" >
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Company Description -->
        <div class="addpartners-form-group">
            <label for="addpartners-company-description">Company Description</label>
            <textarea id="addpartners-company-description"></textarea>
        </div>
    
        <!-- Save Button -->
        <button class="addpartners-save-button" onclick=submitForm()>Save and Add</button>
    </div>

    <!-- Popup dialog -->
    <div class="popup" id="thirdPopup">
        <h2>Job Posting</h2>
        <p><span style="color: red;">*</span> <strong>indicates a required field.</strong></p>
        <div class="form-container">
            <!-- Steps -->
            <div class="form-step form-step-active">
                <!-- Step 1 content -->
                <div class="jobposting-box">
                    <label for="jobposting-partner-company">Partner Company <span style="color: red;">*</span></label>
                    <input type="text" id="partner-jobposting-partner-company" class="jobposting-select" readonly>
                </div>
                
                <div class="btns-group">
                    <a href="#" class="btn btn-next"><i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="jobposting-buttons">
                    <button class="jobposting-button jobposting-button-save" onclick="partnerSaveAndPostJob()">Save and Post</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="closeThirdPopup()">Cancel</button>
                </div>
            </div>
            
            <div class="form-step">
                <div class="jobposting-box">
                    <label for="partner-jobposting-job-title">Job Title <span style="color: red;">*</span></label>
                    <div class="job-title-group">
                        <select id="partner-jobposting-job-title" class="jobposting-select" placeholder="Enter job title">
                            <option value="" disabled selected>Select a job title</option>
                        </select>
                        <i class="fa fa-pencil-square" aria-hidden="true" onclick="partner_showEditJobTitlePopup()"></i>
                        <i class="fa fa-plus-square" aria-hidden="true" onclick="partner_showJobTitle()"></i>
                    </div><br>
                    <label for="parnter-jobposting-location">Location <span style="color: red;">*</span></label>
                    <input type="text" id="partner-jobposting-location" class="jobposting-input" placeholder="Enter job location"><br>
                    <label for="parnter-jobposting-openings">Available openings <span style="color: red;">*</span></label>
                    <input type="number" id="partner-jobposting-openings" class="jobposting-select" min="1" 
                max="100" placeholder="Enter number of open positions from 1-100">
                </div>
                
                <div class="btns-group">
                    <a href="#" class="btn btn-prev"><i class="fas fa-chevron-left"></i></a>
                    <a href="#" class="btn btn-next"><i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="jobposting-buttons">
                    <button class="jobposting-button jobposting-button-save" onclick="partnerSaveAndPostJob()">Save and Post</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="closePopup('thirdPopup')">Cancel</button>
                </div>
            </div>
            
            <div class="form-step">
                <div class="jobposting-box">
                    <label for="partner-jobposting-description">Job Description</label>
                    <textarea id="partner-jobposting-description" class="jobposting-textarea"></textarea>
                </div>
                <div class="btns-group">
                    <a href="#" class="btn btn-prev"><i class="fas fa-chevron-left"></i></a>
                    <!--<a href="#" class="btn btn-next"><i class="fas fa-chevron-right"></i></a>-->
                </div>
                <div class="jobposting-buttons">
                    <button class="jobposting-button jobposting-button-save" onclick="partnerSaveAndPostJob()">Save and Post</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="closeThirdPopup()">Cancel</button>
                </div>
            </div>
            
            <!--
            <div class="form-step">
                <div class="jobposting-box">
                    <div class="jobposting-container">
                        <div id="jobposting-qualifications-container" class="jobposting-qualifications-container">
                            <label for="jobposting-qualifications">Qualifications <span style="color: red;">*</span></label>
                            <input type="text" id="jobposting-qualifications-input" placeholder="Enter a qualification and press Enter">
                        </div>
                        <div class="form-group">
                            <label for="added_qualifications">Qualifications Added</label>
                            <ul id="added_qualifications_list"></ul>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="btns-group">
                    <a href="#" class="btn btn-prev"><i class="fas fa-chevron-left"></i></a>
                    <a href="#" class="btn btn-next"><i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="jobposting-buttons">
                    <button class="jobposting-button jobposting-button-save" onclick="partnerSaveAndPostJob()">Save and Post</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="closeThirdPopup()">Cancel</button>
                </div>
            </div>   
            -->
            <!--
            <div class="form-step">
                <div class="jobposting-box">
                    <label for="jobposting-skills">Skills <span style="color: red;">*</span></label>
                    <div id="partner-jobposting-skills-container" class="jobposting-skills-container">
                        <input type="text" id="partner-jobposting-skills-input" placeholder="Enter a skill and press Enter">
                    </div>
                </div>
                <div class="btns-group">
                    <a href="#" class="btn btn-prev"><i class="fas fa-chevron-left"></i></a>
                </div>
                <div class="jobposting-buttons">
                    <button class="jobposting-button jobposting-button-save" onclick="partnerSaveAndPostJob()">Save and Post</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="closeThirdPopup()">Cancel</button>
                </div>
            </div>
            -->
        </div>
    </div>
    
    <!--Add Job Title popup-->
    <div class="popup" id="partner-add-job-title-popup">
        <h2>Job Title</h2>
        <p><span style="color: red;">*</span> <strong>indicates a required field.</strong></p>
        <div class="form-container">
            <!-- Steps -->
            <div class="form-step form-step-active">
                <div class="jobposting-box">
                    <label for="partner-jobposting-partner-company">Name of Job<span style="color: red;">*</span></label>
                    <input type="text" id="partner-job_title" class="jobposting-select" placeholder="Enter job title" required>      
                    </input><br>

                    <label>Classification <span style="color: red;">*</span></label>
                    <div class="class-group">
                        <select name="classi" id="partner-classification" class="jobposting-select" placeholder="Classification" required>
                            <option value="" disabled selected>Classification</option>
                            <option value="Class1">Class 1</option>
                            <option value="Class2">Class 2</option>
                            <option value="Class3">Class 3</option>
                        </select>

                        <select name="subclassi" id="partner-subclassification" class="jobposting-select" placeholder="Sub-classification" required>
                            <option value="" disabled selected>Sub-classification</option>
                            <option value="Sub1">Sub 1</option>
                            <option value="Sub2">Sub 2</option>
                            <option value="Sub3">Sub 3</option>
                        </select>
                    </div>
                </div>

                <div class="btns-group">
                    <a href="#" class="btn btn-next"><i class="fas fa-chevron-right"></i></a>
                </div>

                <div class="jobposting-buttons">
                    <button id="partner-saveAndPostBtn1" class="jobposting-button jobposting-button-save">Save</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="partner_hideJobTitle()">Cancel</button>
                </div>
            </div>

            <div class="form-step">
                <div class="jobposting-box">
                    <h3 style="margin-bottom: 1rem;">Requirements</h3>
                    <label>Gender <span style="color: red;">*</span></label>
                    <select name="gender" id="partner-gender" class="jobposting-select" required style="margin-bottom: 1rem;">
                        <option value="" disabled selected>Select a gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Not Specified">Not Specified</option>
                    </select>

                    <label>Minimum Educational Attainment <span style="color: red;">*</span></label>
                    <select name="educational_attainment" id="partner-educational_attainment" class="jobposting-select" required style="margin-bottom: 1rem;">
                        <option value="" disabled selected>Select an educational attainment</option>
                        <option value="Highschool Graduate">Highschool Graduate</option>
                        <option value="College Graduate">College Graduate</option>
                        <option value="Undergraduate">Undergraduate</option>
                    </select>

                    <label>Certification / License</label>
                    <div class="job-title-group" style="margin-bottom: 1rem;">
                        <input type="text" id="partner-job-title-cert" class="jobposting-input" placeholder="Enter certification / license needed">
                        <div class="cb-cert">
                            <input type="checkbox" id="partner-req-cert" name="requiredcb" value="true">
                            <label for="requiredcb">Not required</label> 
                        </div>    
                    </div>

                    <label>Years of Experience</label>
                    <div class="job-title-group">
                        <input type="number" id="partner-min-job-title-exp" class="jobposting-select override-width" min="0" max="50" placeholder="Enter minimum years of experience needed">
                        <input type="number" id="partner-max-job-title-exp" class="jobposting-select override-width" min="1" max="50" placeholder="Enter maximum years of experience needed">

                        <div class="cb-cert">
                            <input type="checkbox" id="partner-req-exp" name="requiredcb" value="true">
                            <label for="requiredcb">Not required</label> 
                        </div>    
                    </div>
                </div>

                <div class="btns-group">
                    <a href="#" class="btn btn-prev"><i class="fas fa-chevron-left"></i></a>
                    <a href="#" class="btn btn-next"><i class="fas fa-chevron-right"></i></a>
                </div>

                <div class="jobposting-buttons">
                    <button id="partner-saveAndPostBtn2" class="jobposting-button jobposting-button-save" onclick="saveJobTitle()">Save</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="partner_hideJobTitle()">Cancel</button>
                </div>
            </div>

            <div class="form-step">
                <div class="jobposting-box">
                    <label for="partner-jobposting-skills">Skills <span style="color: red;">*</span></label>
                    <div id="partner-add-jobposting-search-container" class="search-box" style="margin-bottom: 1rem;">
                        <div class="row">
                            <input type="text" id="partner-jobposting-search" placeholder="Choose from prepared skills or input your own" autocomplete="off"></input>
                            <button>Search</button>
                        </div>
                        <div class="result-box"></div>
                    </div>
                    <div id="partner-add-jobposting-skills-container" class="jobposting-skills-container">
                        <input type="text" id="partner-jobposting-skills-input" readonly>
                    </div>
                </div>
                <div class="btns-group">
                    <a href="#" class="btn btn-prev"><i class="fas fa-chevron-left"></i></a>
                </div>
                <div class="jobposting-buttons">
                    <button id="partner-saveAndPostBtn3" class="jobposting-button jobposting-button-save" onclick="saveJobTitle()">Save</button>
                    <button class="jobposting-button jobposting-button-cancel" onclick="partner_hideJobTitle()">Cancel</button>
                </div>
            </div>                                               
        </div>
    </div>

    <!-- Dialog Box Delete Partner-->
    <div class="delete-employees-dialog-box" id="dialogBox-delete">
        <div class="addpartners-back-button" onclick="hideDialogDelete()">
            <i class="fas fa-chevron-left"></i> Back
        </div>

        <h2 style="text-align: center;">Are you sure you want to delete this Partner?</h1>
        <div class="delete-employees-form-group">
            <label for="delete-employees-firstname">Admin Password:</label>
            <input type="text" id="delete-employees-firstname">
        </div>

        <button class="delete-employees-save-button" onclick="confirmDelete()">Delete</button>
    </div>
    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>   
    <script>
        /*document.getElementById('jobposting-qualifications-input').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent the form from submitting

                var skillInput = document.getElementById('jobposting-qualifications-input');
                var skillValue = skillInput.value.trim();

                if (skillValue !== '') {
                    var ul = document.getElementById('added_qualifications_list');
                    var li = document.createElement('li');
                    li.innerHTML = `
                        <span>${skillValue}</span>
                        <button class="close-btn">&times;</button>
                    `;
                    ul.appendChild(li);
                    skillInput.value = ''; // Clear the input after adding

                    // Add event listener to the close button
                    li.querySelector('.close-btn').addEventListener('click', function() {
                        li.remove();
                    });
                }
            }
        });*/
      
        const reqCertCheckbox = document.getElementById('partner-req-cert');
        const jobTitleCertInput = document.getElementById('partner-job-title-cert');

        reqCertCheckbox.addEventListener('change', () => {
        if (reqCertCheckbox.checked) {
            jobTitleCertInput.style.transition = 'opacity 0.3s ease-in-out';
            jobTitleCertInput.style.opacity = '0';
            jobTitleCertInput.style.display = 'none';
            jobTitleCertInput.required = false; 
        } else {
            jobTitleCertInput.style.transition = 'opacity 0.3s ease-in-out';
            jobTitleCertInput.style.opacity = '1';
            jobTitleCertInput.style.display = 'block'; // Show the input field
            jobTitleCertInput.required = true;
        }
        });

        const minJobExp = document.getElementById('partner-min-job-title-exp');
        const maxJobExp = document.getElementById('partner-max-job-title-exp');
        const reqExpCheckbox = document.getElementById('partner-req-exp');

        reqExpCheckbox.addEventListener('change', () => {
        if (reqExpCheckbox.checked) {
            minJobExp.style.transition = 'opacity 0.3s ease-in-out';
            minJobExp.style.opacity = '0';
            minJobExp.style.display = 'none';
            minJobExp.required = false; 

            maxJobExp.style.transition = 'opacity 0.3s ease-in-out';
            maxJobExp.style.opacity = '0';
            maxJobExp.style.display = 'none';
            maxJobExp.required = false; 
        } else {
            minJobExp.style.transition = 'opacity 0.3s ease-in-out';
            minJobExp.style.opacity = '1';
            minJobExp.style.display = 'block'; // Show the input field
            minJobExp.required = true;

            maxJobExp.style.transition = 'opacity 0.3s ease-in-out';
            maxJobExp.style.opacity = '1';
            maxJobExp.style.display = 'block'; // Show the input field
            maxJobExp.required = true;
        }
        });

        //Skills search functions
        let availableKeywords = [];

        const partnerResultsBox = document.querySelector('#partner-add-jobposting-search-container .result-box');
        const partnerInputBox = document.getElementById('partner-jobposting-search');

        // Fetch skill names from the PHP script
        fetch('fetch_SkillList.php') 
            .then(response => response.json())  // Parse the JSON response
            .then(data => {
                availableKeywords = data;  // Assign the fetched data to the availableKeywords array
                console.log(availableKeywords);  // Log the data to confirm it's fetched
            })
            .catch(error => console.error('Error fetching data:', error));

        partnerInputBox.onkeyup = function() {
            let result = [];
            let input = partnerInputBox.value;
            if (input.length) {
                result = availableKeywords.filter((keyword) => {
                    return keyword.toLowerCase().includes(input.toLowerCase());
                });
                console.log(result);
            }

            displayPartnerResults(result);

            if (!result.length) {
                partnerResultsBox.innerHTML = '';
            }
        };

        function displayPartnerResults(result) {
            const content = result.map((list) => {
                return "<li onclick=selectPartnerInput(this)>" + list + "</li>";
            });

            partnerResultsBox.innerHTML = "<ul>" + content.join('') + "</ul>";
        }

        function selectPartnerInput(list) {
            partnerInputBox.value = list.innerHTML;
            partnerResultsBox.innerHTML = '';
        }

        
         // Add an event listener to the search input
        document.getElementById('partner-search-bar').addEventListener('input', filterPartners);

        function filterPartners() {
            const searchValue = document.getElementById('partner-search-bar').value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr'); // Select all table rows
            let rowCount = 0; // Keep track of visible rows
            
            rows.forEach(row => {
                const companyName = row.querySelector('#company-name').innerText.toLowerCase(); // Get the company name text

                // Check if the company name includes the search value
                if (companyName.includes(searchValue)) {
                    row.style.display = ''; // Show the row
                    rowCount++; // Count visible rows
                } else {
                    row.style.display = 'none'; // Hide the row
                }
            });

            // If no rows are visible, display a "No results found" message
            const tableBody = document.querySelector('table tbody');
            const noResultsRow = document.querySelector('.no-results-row');

            if (rowCount === 0) {
                // If no "no-results" row exists, add one
                if (!noResultsRow) {
                    const noResultsMessage = document.createElement('tr');
                    noResultsMessage.classList.add('no-results-row');
                    noResultsMessage.innerHTML = `
                        <td colspan="6" style="text-align: center; color: #2C1875; font-size: 20px; font-weight: bold; padding: 5rem 0rem;">No available partners found.</td>
                    `;
                    tableBody.appendChild(noResultsMessage);
                }
            } else {
                // If rows are visible, remove the "No results" message if it exists
                if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
        }

        function sortPartners() {
        const sortBy = document.getElementById('sort-by').value; // Get the selected sort-by option
        const order = document.getElementById('order-sort').value; // Get the selected sort order
        const rows = Array.from(document.querySelectorAll('table tbody tr')); // Get all rows

        const compareFunction = (a, b) => {
            let valA, valB;

            // Compare based on the selected sort type
            if (sortBy === 'date') {
                valA = new Date(a.querySelector('#date-added').innerText); // Date comparison
                valB = new Date(b.querySelector('#date-added').innerText);
            } else if (sortBy === 'company') {
                valA = a.querySelector('#company-name').innerText.toLowerCase(); // Company name comparison
                valB = b.querySelector('#company-name').innerText.toLowerCase();
            }

            if (order === 'asc') {
                return valA > valB ? 1 : -1; // Ascending order
            } else {
                return valA < valB ? 1 : -1; // Descending order
            }
        };

        // Sort the rows array
        rows.sort(compareFunction);

        // Append the sorted rows back to the table body
        const tableBody = document.querySelector('table tbody');
        rows.forEach(row => tableBody.appendChild(row));

        // Trigger filtering after sorting, to display the correct results based on the search bar input
        filterPartners();
    }

    </script>
</body>
</html>
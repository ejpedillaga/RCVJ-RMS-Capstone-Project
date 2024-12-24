function toggleMenu() {
  const menu = document.querySelector(".menu-links");
  const icon = document.querySelector(".hamburger-icon");
  menu.classList.toggle("open");
  icon.classList.toggle("open");
}

function redirectTo(url) {
  window.location.href = url;
}

function openTab(tabName) {
var i, tabcontent, tabs;
tabcontent = document.getElementsByClassName('tab-content');
for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].classList.remove('active');
}
tabs = document.getElementsByClassName('tab');
for (i = 0; i < tabs.length; i++) {
    tabs[i].classList.remove('active');
}
document.getElementById(tabName).classList.add('active');
event.currentTarget.classList.add('active');
}

$(document).ready(function(){
$(".notification_icon .fa-bell").click(function(){
  $(".dropdown").toggleClass("active");
})
});

$(document).ready(function(){
$(".notification_icon .fa-bell").click(function(){
    $(this).siblings(".dropdown").toggleClass("active");
});
});

const observer = new IntersectionObserver((entries) => {
entries.forEach((entry => {
  console.log(entry)
  if(entry.isIntersecting){
    entry.target.classList.add('show');
  } else {
    entry.target.classList.remove('show');
  }
}));
});

function checkTabContent() {
const tabContents = document.querySelectorAll('.tab-content');

tabContents.forEach(tab => {
    const jobList = tab.querySelector('.job-list');
    const emptyMessage = tab.querySelector('.empty-message');

    if (jobList.children.length === 0) {
        jobList.style.display = 'none';
        emptyMessage.style.display = 'flex';
    } else {
        jobList.style.display = 'relative';
        emptyMessage.style.display = 'none';
    }
});
}

function openTab(tabName) {
const tabs = document.querySelectorAll('.tab');
const tabContents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
    tab.classList.remove('active');
});

tabContents.forEach(content => {
    content.classList.remove('active');
});

document.querySelector(`.tab[onclick="openTab('${tabName}')"]`).classList.add('active');
document.getElementById(tabName).classList.add('active');
checkTabContent(); // Call to update empty message visibility
}

/*Sidenav*/
/*Sidenav Open and Close*/
function openNav(sidenavId, containerId) {
document.getElementById(sidenavId).classList.add('active');
document.getElementById(containerId).style.opacity = "0.1";
document.body.classList.add('no-scroll', 'overlay-active');
updateSubClassifications(); 
previewLicenseFiles();
previewFiles();
}

function closeNav(sidenavId, containerId) {
document.getElementById(sidenavId).classList.remove('active');
document.getElementById(containerId).style.opacity = "1";
document.body.classList.remove('no-scroll', 'overlay-active');
// Reset the file input and clear the preview
    const licenseFileInput = document.getElementById('licenseFileUpload');
    const licensePreviewContainer = document.getElementById('licensePreviewContainer');

    // Clear the file input
    licenseFileInput.value = ''; 

    // Clear the preview container
    licensePreviewContainer.innerHTML = ''; 
}

/*Add Skills*/
document.getElementById('add_skill_btn').addEventListener('click', addSkill);

document.getElementById('skills').addEventListener('keydown', function(event) {
  if (event.key === 'Enter') {
      event.preventDefault(); // Prevent the default form submission behavior
      addSkill();
  }
});

document.addEventListener('DOMContentLoaded', function() {
var ul = document.getElementById('added_skills_list');

// Populate the skills list from userSkills
userSkills.forEach(function(skill) {
    var li = document.createElement('li');
    li.innerHTML = 
      `<span>${skill}</span>
       <button class="close-btn">&times;</button>`;
    ul.appendChild(li);

    // Add event listener to the close button
    li.querySelector('.close-btn').addEventListener('click', function() {
        li.remove();
    });
});
});

function addSkill() {
  var skillInput = document.getElementById('skills');
  var skillValue = skillInput.value.trim();

  if (skillValue !== '') {
      var ul = document.getElementById('added_skills_list');
      var li = document.createElement('li');
      li.innerHTML = 
        `<span>${skillValue}</span>
        <button class="close-btn">&times;</button>`;
      ul.appendChild(li);
      skillInput.value = '';

      // Add event listener to the close button
      li.querySelector('.close-btn').addEventListener('click', function() {
          li.remove();
      });
  }
}

document.getElementById('skills_form').addEventListener('submit', function(event) {
  event.preventDefault(); // Prevent default form submission

  // Gather skills from the list
  var skills = [];
  document.querySelectorAll('#added_skills_list li span').forEach(function(span) {
      skills.push(span.textContent.trim());
  });

  // Convert skills to JSON format
  var skillsJson = JSON.stringify(skills);

  // Create a hidden input to include skills in the form submission
  var hiddenInput = document.createElement('input');
  hiddenInput.type = 'hidden';
  hiddenInput.name = 'skills_json';
  hiddenInput.value = skillsJson;
  this.appendChild(hiddenInput);

  // Submit the form
  this.submit();
});

/*Add Course Highlight*/
document.getElementById('add_highlight_button').addEventListener('click', function() {
var highlightInput = document.getElementById('course_highlights');
var highlightValue = highlightInput.value.trim();

if (highlightValue !== '') {
    var ul = document.getElementById('highlights_list');
    var li = document.createElement('li');
    li.innerHTML = `
      <span>${highlightValue}</span>
      <button class="close-btn">&times;</button>
    `;
    ul.appendChild(li);
    highlightInput.value = '';

    // Add event listener to the close button
    li.querySelector('.close-btn').addEventListener('click', function() {
      li.remove();
    });
}
});

document.getElementById('education_form').addEventListener('submit', function(event) {
event.preventDefault();
// Handle form submission logic here
});


/******************Resume Dropbox*******************/
function previewFiles() {
  function handleDragAndDrop() {
    const dropbox = document.getElementById('resume_dropbox');
    dropbox.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropbox.classList.add('dragover');
    });

    dropbox.addEventListener('drop', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropbox.classList.remove('dragover');

      const files = e.dataTransfer.files;
      const validFiles = Array.from(files).filter(file => file.type === 'application/pdf');

      if (validFiles.length > 0) {
        const fileInput = document.getElementById('fileUpload');
        const dataTransfer = new DataTransfer();
        
        validFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        previewFiles(); 
      } else {
        alert('Only PDF files are allowed!');
      }
    });
  }
  handleDragAndDrop();

  const fileInput = document.getElementById('fileUpload');
  const files = fileInput.files;
  const previewContainer = document.getElementById('previewContainer');

  previewContainer.innerHTML = ''; // Clear the preview container

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const fileName = file.name;
    const fileSize = file.size;

    // Create a preview element for each file
    const previewElement = document.createElement('div');
    previewElement.className = 'preview-element';

    // Display the file name and size
    const fileNameElement = document.createElement('span');
    fileNameElement.textContent = fileName;
    previewElement.appendChild(fileNameElement);

    const fileSizeElement = document.createElement('span');
    fileSizeElement.textContent = `(${formatFileSize(fileSize)})`;
    previewElement.appendChild(fileSizeElement);

    // Display PDF icon
    const icon = document.createElement('i');
    icon.className = 'fas fa-file-pdf'; // You can use a PDF icon here
    previewElement.appendChild(icon);

    // Add an "X" button
    const closeButton = document.createElement('button');
    closeButton.className = 'close-button';
    closeButton.innerHTML = '&times;'; // Use HTML entity instead of the character
    closeButton.style.float = 'right';
    closeButton.style.cursor = 'pointer';
    closeButton.onclick = function() {
      // Remove the file from the file input
      const fileIndex = Array.prototype.indexOf.call(fileInput.files, file);
      if (fileIndex !== -1) {
        const dataTransfer = new DataTransfer();
        for (let j = 0; j < files.length; j++) {
          if (j !== fileIndex) {
            dataTransfer.items.add(files[j]);
          }
        }
        fileInput.files = dataTransfer.files; // Update the file input
      }
      // Remove the preview element
      previewElement.remove();
    };
    previewElement.appendChild(closeButton);

    previewContainer.appendChild(previewElement);
  }
}

// Helper function to format the file size
function formatFileSize(size) {
  if (size < 1024) {
    return `${size} bytes`;
  } else if (size < 1024 * 1024) {
    return `${(size / 1024).toFixed(2)} KB`;
  } else {
    return `${(size / (1024 * 1024)).toFixed(2)} MB`;
  }
}


/*******************License Dropbox*********************/
function previewLicenseFiles() {

  function handleDragAndDrop() {
    const dropbox = document.getElementById('license_dropbox');
    dropbox.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropbox.classList.add('dragover');
    });
  
    dropbox.addEventListener('drop', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropbox.classList.remove('dragover');
  
      const files = e.dataTransfer.files;
      const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg']; // Allowed MIME types
      const validFiles = Array.from(files).filter(file => allowedTypes.includes(file.type));

      if (validFiles.length > 0) {
        const licenseFileInput = document.getElementById('licenseFileUpload');
        const dataTransfer = new DataTransfer();

        validFiles.forEach(file => dataTransfer.items.add(file));
        licenseFileInput.files = dataTransfer.files; // Set the valid files to the input
        previewLicenseFiles(); 
      } else {
        alert("Please upload only PNG or JPG/JPEG files."); // Alert for invalid file types
      }
    });
  }

  handleDragAndDrop();
  const licenseFileInput = document.getElementById('licenseFileUpload');
  const files = licenseFileInput.files;
  const licensePreviewContainer = document.getElementById('licensePreviewContainer');

  licensePreviewContainer.innerHTML = ''; // Clear the preview container

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const fileName = file.name;
    const fileSize = file.size;

    // Create a preview element for each file
    const previewElement = document.createElement('div');
    previewElement.className = 'license-preview-element';

    // Display the file name and size in one line
    const fileInfoElement = document.createElement('span');
    fileInfoElement.textContent = `${fileName} (${formatFileSize(fileSize)})`;
    previewElement.appendChild(fileInfoElement);

    // Add a preview image or icon depending on the file type
    if (file.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.alt = fileName;
      previewElement.appendChild(img);
    } else {
      const icon = document.createElement('i');
      icon.className = 'fas fa-file-alt';
      previewElement.appendChild(icon);
    }

   // Add a "Remove" button
   const closeButton = document.createElement('button');
   closeButton.className = 'license-close-button';
   closeButton.innerText = 'Remove'; // Set button text to "Remove"
   closeButton.style.float = 'right';
   closeButton.style.cursor = 'pointer';
   closeButton.style.backgroundColor = '#2C1875'; // Set background color
   closeButton.style.border = 'none'; // Optional: remove border
   closeButton.style.color = 'white'; // Optional: set text color to white
   closeButton.style.padding = '5px 10px'; // Optional: add padding for aesthetics
   closeButton.onclick = function() {
     // Remove the file from the file input
     const fileIndex = Array.prototype.indexOf.call(licenseFileInput.files, file);
     if (fileIndex !== -1) {
       const dataTransfer = new DataTransfer();
       for (let j = 0; j < files.length; j++) {
         if (j !== fileIndex) {
           dataTransfer.items.add(files[j]); // Add the remaining files back to the DataTransfer object
         }
       }
       licenseFileInput.files = dataTransfer.files; // Update the input files
     }
      // Remove the preview element
      previewElement.remove();
    };
    previewElement.appendChild(closeButton);

    licensePreviewContainer.appendChild(previewElement);
  }
}

// Helper function to format the file size
function formatFileSize(size) {
  if (size < 1024) {
    return `${size} bytes`;
  } else if (size < 1024 * 1024) {
    return `${(size / 1024).toFixed(2)} KB`;
  } else {
    return `${(size / (1024 * 1024)).toFixed(2)} MB`;
  }
}


/*Resume Dialog Popup*/
const dialog = document.getElementById("submit_dialog");
function submitForm(){

dialog.showModal();
}

function closeSubmitDialog(){
dialog.close();
}

function populateJobExperience(job) {
// Set the job experience ID in the hidden field
document.querySelector('input[name="job_experience_id"]').value = job.job_experience_id;

// Set the job title and company name
document.getElementById('job-title').value = job.job_title;
document.getElementById('company-name-field').value = job.company_name;

// Set the started date
document.getElementById('month_started').value = job.month_started;
document.getElementById('year_started').value = job.year_started;

// Set the ended date
document.getElementById('month_ended').value = job.month_ended;
document.getElementById('year_ended').value = job.year_ended;

// Set the career history
document.getElementById('career_history').value = job.career_history;
}

function resetJobExperienceForm() {
// Clear the hidden field
document.querySelector('input[name="job_experience_id"]').value = '';

// Clear job title and company name
document.getElementById('job-title').value = '';
document.getElementById('company-name-field').value = '';

// Reset the started date
document.getElementById('month_started').selectedIndex = 0; // Set to "Select Month"
document.getElementById('year_started').selectedIndex = 0; // Set to "Select Year"

// Reset the ended date
document.getElementById('month_ended').selectedIndex = 0; // Set to "Select Month"
document.getElementById('year_ended').selectedIndex = 0; // Set to "Select Year"

// Clear the career history
document.getElementById('career_history').value = '';
}

function deleteJobExperience(jobExperienceId) {
if (confirm('Are you sure you want to delete this job experience?')) {
    // Create a form to submit the deletion request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ''; // Submit to the same page

    // Create a hidden input for the job experience ID
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'job_experience_id';
    input.value = jobExperienceId;

    // Create a hidden input to identify the deletion action
    const deleteInput = document.createElement('input');
    deleteInput.type = 'hidden';
    deleteInput.name = 'delete_job_experience';
    deleteInput.value = '1';

    // Append inputs to the form
    form.appendChild(input);
    form.appendChild(deleteInput);

    // Append the form to the body and submit it
    document.body.appendChild(form);
    form.submit();
}
}

function populateLicense(license) {
// Set the license ID in the hidden field
document.querySelector('input[name="license_id"]').value = license.id;

// Set the license name
document.getElementById('license').value = license.license_name;

// Set the issued date
document.getElementById('month_issued').value = license.month_issued;
document.getElementById('year_issued').value = license.year_issued;

// Set the expiration date
document.getElementById('month_expired').value = license.month_expired;
document.getElementById('year_expired').value = license.year_expired;
}

function resetLicenseForm() {
// Clear the hidden field
document.querySelector('input[name="license_id"]').value = '';

// Clear license name
document.getElementById('license').value = '';

// Reset the issued date
document.getElementById('month_issued').selectedIndex = 0; // Set to "Select Month"
document.getElementById('year_issued').selectedIndex = 0; // Set to "Select Year"

// Reset the expiration date
document.getElementById('month_expired').selectedIndex = 0; // Set to "Select Month"
document.getElementById('year_expired').selectedIndex = 0; // Set to "Select Year"
}

function deleteLicense(licenseId) {
if (confirm('Are you sure you want to delete this license?')) {
    // Create a form to submit the deletion request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ''; // Submit to the same page

    // Create a hidden input for the license ID
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'license_id';
    input.value = licenseId;

    // Create a hidden input to identify the deletion action
    const deleteInput = document.createElement('input');
    deleteInput.type = 'hidden';
    deleteInput.name = 'delete_license';
    deleteInput.value = '1';

    // Append inputs to the form
    form.appendChild(input);
    form.appendChild(deleteInput);

    // Append the form to the body and submit it
    document.body.appendChild(form);
    form.submit();
}
}

function deleteEducation(userId) {
if (confirm('Are you sure you want to delete this education record?')) {
  // Create a form to submit the deletion request
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = ''; // Submit to the same page

  // Create a hidden input for the user ID
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'user_id';
  input.value = userId;

  // Create a hidden input to identify the deletion action
  const deleteInput = document.createElement('input');
  deleteInput.type = 'hidden';
  deleteInput.name = 'delete_education';
  deleteInput.value = '1';

  // Append inputs to the form
  form.appendChild(input);
  form.appendChild(deleteInput);

  // Append the form to the body and submit it
  document.body.appendChild(form);
  form.submit();
}
}

function deleteVocational(userId) {
if (confirm('Are you sure you want to delete this vocational record?')) {
  // Create a form to submit the deletion request
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = ''; // Submit to the same page

  // Create a hidden input for the user ID
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'user_id';
  input.value = userId;

  // Create a hidden input to identify the deletion action
  const deleteInput = document.createElement('input');
  deleteInput.type = 'hidden';
  deleteInput.name = 'delete_vocational'; // Update this to match your PHP check
  deleteInput.value = '1';

  // Append inputs to the form
  form.appendChild(input);
  form.appendChild(deleteInput);

  // Append the form to the body and submit it
  document.body.appendChild(form);
  form.submit();
  }
}

function deleteResume(userId) {
  if (confirm('Are you sure you want to delete your resume?')) {
      // Create a form to submit the deletion request
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = ''; // Submit to the same page

      // Create a hidden input for the user ID
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'user_id';
      input.value = userId;

      // Create a hidden input to identify the deletion action
      const deleteInput = document.createElement('input');
      deleteInput.type = 'hidden';
      deleteInput.name = 'delete_resume'; // This identifies the action in PHP
      deleteInput.value = '1';

    // Append inputs to the form
    form.appendChild(input);
    form.appendChild(deleteInput);

    // Append the form to the body and submit it
    document.body.appendChild(form);
    form.submit();
}
}

function showInfo() {
document.getElementById('info').style.display = 'block';
document.getElementById('info').classList.add('show');
document.getElementById('overlay').classList.add('show');
}

function hideInfo() {
document.getElementById('info').style.display = 'none';
document.getElementById('info').classList.remove('show');
document.getElementById('overlay').classList.remove('show');
}

            function previewLogo(file) {
                    if (!file) return; // Ensure file exists

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = document.getElementById('logo-preview');
                        preview.src = e.target.result;
                        preview.style.display = 'block'; // Show the preview
                        document.getElementById('upload-placeholder').style.display = 'none'; // Hide the placeholder
                    };
                    reader.readAsDataURL(file); // Read the file as a data URL
                }

            function validateAndPreviewLogo(event) {
                const input = event.target;

                // Check if the input exists and has files
                if (input && input.files && input.files[0]) {
                    const file = input.files[0];
                    const maxSize = 1 * 1024 * 1024; // 1MB in bytes

                    if (file.size > maxSize) {
                        document.getElementById('error-message').style.display = 'block'; // Show error message
                        document.getElementById('logo-preview').style.display = 'none'; // Hide preview
                        document.getElementById('upload-placeholder').style.display = 'none'; // Show placeholder
                        input.value = '';
                    } else {
                        document.getElementById('error-message').style.display = 'none'; 
                        previewLogo(file); 
                    }
                } else {
                    console.error("No file selected or input is invalid."); // Handle invalid input
                }
            }

function validateForm() {
  const attachmentInput = document.getElementById('licenseFileUpload');
  if (attachmentInput.files.length === 0) {
      alert('Please upload a license/certificate attachment.');
      return false; // Prevent form submission
  }
  return true; // Allow form submission
}

function confirmLogout() {
  // Ask for confirmation before logging out
  var result = confirm("Are you sure you want to log out?");
  if (result) {
      // If user confirms, redirect to logout.php
      window.location.href = 'logout.php';
  }
}

    function checkSession() {
        fetch('check_session.php')
            .then(response => response.json())
            .then(data => {
                if (!data.active) {
                    // Session inactive, redirect to login page
                    alert('Your session has expired. You will be redirected to the login page.');
                    window.location.href = '../index.php'; // Update path as needed
                }
            })
            .catch(error => console.error('Error checking session:', error));
    }

    // Check session status every 5 seconds
    setInterval(checkSession, 5000);
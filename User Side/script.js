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
}

function closeNav(sidenavId, containerId) {
  document.getElementById(sidenavId).classList.remove('active');
  document.getElementById(containerId).style.opacity = "1";
  document.body.classList.remove('no-scroll', 'overlay-active');
}

/*Add Skills*/
document.getElementById('add_skill_btn').addEventListener('click', function() {
  var skillInput = document.getElementById('skills');
  var skillValue = skillInput.value.trim();

  if (skillValue !== '') {
      var ul = document.getElementById('added_skills_list');
      var li = document.createElement('li');
      li.innerHTML = `
        <span>${skillValue}</span>
        <button class="close-btn">&times;</button>
      `;
      ul.appendChild(li);
      skillInput.value = '';

      // Add event listener to the close button
      li.querySelector('.close-btn').addEventListener('click', function() {
        li.remove();
      });
  }
});

document.getElementById('skills_form').addEventListener('submit', function(event) {
  event.preventDefault();
  // Handle form submission logic here
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
      const fileInput = document.getElementById('fileUpload');
      fileInput.files = files;
  
      previewFiles(); 
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
          fileInput.files = new DataTransfer().files; // Clear the file input
          for (let j = 0; j < files.length; j++) {
            if (j !== fileIndex) {
              fileInput.files = new DataTransfer().files; // Clear the file input
              fileInput.files.item(j) = files[j]; // Add the remaining files back to the file input
            }
          }
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
        const licenseFileInput = document.getElementById('licenseFileUpload');
        licenseFileInput.files = files;
    
        previewLicenseFiles(); 
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
  
      // Display the file name and size
      const fileNameElement = document.createElement('span');
      fileNameElement.textContent = fileName;
      previewElement.appendChild(fileNameElement);
  
      const fileSizeElement = document.createElement('span');
      fileSizeElement.textContent = `(${formatFileSize(fileSize)})`;
      previewElement.appendChild(fileSizeElement);
  
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
  
      // Add an "X" button
      const closeButton = document.createElement('button');
      closeButton.className = 'license-close-button';
      closeButton.innerHTML = '&times;'; // Use HTML entity instead of the character
      closeButton.style.float = 'right';
      closeButton.style.cursor = 'pointer';
      closeButton.onclick = function() {
        // Remove the file from the file input
        const fileIndex = Array.prototype.indexOf.call(licenseFileInput.files, file);
        if (fileIndex !== -1) {
          licenseFileInput.files = new DataTransfer().files; // Clear the file input
          for (let j = 0; j < files.length; j++) {
            if (j !== fileIndex) {
              licenseFileInput.files = new DataTransfer().files; // Clear the file input
              licenseFileInput.files.item(j) = files[j]; // Add the remaining files back to the file input
            }
          }
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
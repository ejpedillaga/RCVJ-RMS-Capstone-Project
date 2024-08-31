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
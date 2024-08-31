/*Sidebar Nav*/
function toggleNav() {
    const sidebar = document.getElementById("mySidebar");
    const main = document.getElementById("main");
    const header = document.getElementById("header");
    sidebar.classList.toggle("closed");
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("open");
    }
}

/*Toggle Button*/

function toggle(element) {
    const openOption = document.querySelector('.toggle-option.open');
    const closedOption = document.querySelector('.toggle-option.closed');
    openOption.classList.toggle('open');
    openOption.classList.toggle('closed');
    closedOption.classList.toggle('open');
    closedOption.classList.toggle('closed');
}

/*Dialog Box*/
function showDialog() {
    document.getElementById('dialogBox').style.display = 'block';
}

function hideDialog() {
    document.getElementById('dialogBox').style.display = 'none';
}

/*Dialog Box Delete*/
function showDialogDelete() {
    document.getElementById('dialogBox-delete').style.display = 'block';
}

function hideDialogDelete() {
    document.getElementById('dialogBox-delete').style.display = 'none';
}

/*Dialog Box Edit*/
function showDialogEdit() {
    document.getElementById('dialogBox-edit').style.display = 'block';
}

function hideDialogEdit() {
    document.getElementById('dialogBox-edit').style.display = 'none';
}

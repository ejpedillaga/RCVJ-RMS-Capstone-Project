const sign_in_btn = document.querySelector("#sign-in-btn");
const sign_up_btn = document.querySelector("#sign-up-btn");
const container = document.querySelector(".container");

sign_up_btn.addEventListener("click", () => {
  container.classList.add("sign-up-mode");
});

sign_in_btn.addEventListener("click", () => {
  container.classList.remove("sign-up-mode");
});

const prevBtns = document.querySelectorAll(".btn-prev");
const nextBtns = document.querySelectorAll(".btn-next");
//const progress = document.getElementById("progress");
const formSteps = document.querySelectorAll(".form-step");
//const progressSteps = document.querySelectorAll(".progress-step");

let formStepsNum = 0;

function updateFormSteps() {
    // Hide all form steps
    formSteps.forEach((step, index) => {
        step.classList.toggle("active", index === formStepsNum);
    });

    // Optionally, you can disable the previous button on the first step
    const prevBtns = document.querySelectorAll(".btn-prev");
    prevBtns.forEach((btn) => {
        btn.disabled = formStepsNum === 0; // Disable if on the first step
    });

    // Optionally, you can disable the next button on the last step
    const nextBtns = document.querySelectorAll(".btn-next");
    nextBtns.forEach((btn) => {
        btn.disabled = formStepsNum === formSteps.length - 1; // Disable if on the last step
    });
}



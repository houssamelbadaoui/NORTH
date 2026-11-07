const registerForm = document.getElementById("registerForm"); // select form
const formMessage = document.getElementById("formMessage"); // select the displayed message under the form
const registerButton = document.getElementById("registerButton"); // form button

// add event listener when submit the form
registerForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  // collect the user input
  const name = document.getElementById("name").value.trim();
  const surname = document.getElementById("surname").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  // validate that password and confirmed password are same
  if (password !== confirmPassword) {
    formMessage.textContent = "Passwords do not match";
    return;
  }

  // prepare data to send to PHP
  const formdata = new FormData(registerForm);

  registerButton.disabled = true;
  registerButton.textContent = "Creating account...";
  formMessage.textContent = "";

  // send data to php
  try {
    const response = await fetch("../api/register.php", {
      method: "POST",
      body: formdata,
    });

    const result = await response.json(); // get result from php

    formMessage.textContent = result.message; // display result message for the user

    // if operation success
    if (result.success) {
      registerForm.reset();

      // we use setTimeout so user can read the displayed message
      setTimeout(() => {
        window.location.href = "../index.php";
      }, 1200);

      return;
    }
  } catch (error) {
    formMessage.textContent = "Something went wrong, Please try again.";
  } finally {
    registerButton.disabled = false;
    registerButton.textContent = "Create account";
  }
});

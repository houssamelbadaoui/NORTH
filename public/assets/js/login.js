const loginForm = document.getElementById("loginForm"); // select form
const formMessage = document.getElementById("formMessage"); // select the form message
const loginButton = document.getElementById("loginButton");

loginForm.addEventListener("submit", async (e) => {
  e.preventDefault(); // prevent the page to not load when submit the form

  // form data
  const formData = new FormData(loginForm);

  // disbale the login button
  loginButton.disabled = true;
  loginButton.textContent = "Logging in...";
  formMessage.textContent = "";

  // we send data to login.php
  try {
    const response = await fetch("../api/login.php", {
      method: "POST",
      body: formData,
    });

    // get results from php
    const results = await response.json();

    // display message to user
    formMessage.textContent = results.message;

    if (results.success) {
      loginForm.reset();
      window.location.href = "../index.php"; // redirect to main page
      return;
    }
  } catch (error) {
    formMessage.textContent = "Something went wrong, Please try again:";
  } finally {
    loginButton.disabled = false;
    loginButton.textContent = "Login";
  }
});

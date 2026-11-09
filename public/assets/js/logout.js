// select the logout button
const logoutButton = document.getElementById("logoutButton");

logoutButton.addEventListener("click", async () => {
  // disable the logout button to not get multiple logout requests
  logoutButton.disabled = true;
  logoutButton.textContent = "Logging out...";

  // send php a request
  try {
    const response = await fetch("../api/logout.php", {
      method: "POST",
    });

    // json response
    const result = await response.json();

    // validate that the logout was succeful
    if (result.success === true) {
      // redirect to homepage
      window.location.href = "../index.php";
    } else {
      alert(result.message || "Logout failed, Please try again.");

      logoutButton.disabled = false;
      logoutButton.textContent = "Logout";
    }
  } catch (error) {
    // Handle network or response errors
    console.error("Logout error:", error);

    alert("Something went wrong. Please try again.");

    logoutButton.disabled = false;
    logoutButton.textContent = "Logout";
  }
});

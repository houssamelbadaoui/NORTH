const contactForm = document.getElementById("contactForm");
const contactSuccess = document.getElementById("contactSuccess");

contactForm.addEventListener("submit", (event) => {
  event.preventDefault();

  // Check that the form is valid
  if (!contactForm.checkValidity()) {
    contactForm.reportValidity();
    return;
  }

  // Hide the form
  contactForm.hidden = true;

  // Show the thank-you message
  contactSuccess.hidden = false;

  // Move the visitor to the thank-you message
  contactSuccess.scrollIntoView({
    behavior: "smooth",
    block: "center",
  });
});

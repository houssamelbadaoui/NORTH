// select html elements
const addProductButton = document.getElementById("addProductButton");
const addProductDialog = document.getElementById("addProductDialog");
const cancelAddProductButton = document.getElementById(
  "cancelAddProductButton",
);
const addProductForm = document.getElementById("addProductForm");

// open the dialog
addProductButton.addEventListener("click", () => {
  addProductDialog.showModal();
});

// close dialog
cancelAddProductButton.addEventListener("click", () => {
  addProductDialog.close();
});

// get subimted data from the form
addProductForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  // form input
  const formData = new FormData(addProductForm);
  const submitButton = addProductForm.getElementById("buttonSubmit");

  // disable the button while submitting
  submitButton.disabled = true;
  submitButton.textContent = "Saving...";

  // send data to php to validate and save the product
  try {
    const response = await fetch("/api/products/create.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json(); // get the php response and convert it to json

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not create product.");
    }

    alert(result.message);

    addProductForm.reset();
    addProductDialog.close();
  } catch (error) {
    console.error(error);
    alert(error.message || "Something went wrong.");
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = "Save Product.";
  }
});

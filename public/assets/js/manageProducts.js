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
addProductForm.addEventListener("submit", (e) => {
  e.preventDefault();

  // form input
  const formData = new FormData(addProductForm);
});

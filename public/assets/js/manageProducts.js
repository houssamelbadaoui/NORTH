// select html elements
const addProductButton = document.getElementById("addProductButton");
const addProductDialog = document.getElementById("addProductDialog");
const cancelAddProductButton = document.getElementById(
  "cancelAddProductButton",
);
const addProductForm = document.getElementById("addProductForm");
const tableBody = document.getElementById("productsTableBody");

// Update stock elements
const updateStockDialog = document.getElementById("updateStockDialog");
const updateStockForm = document.getElementById("updateStockForm");
const updateStockProductId = document.getElementById("updateStockProductId");
const updateStockProductName = document.getElementById(
  "updateStockProductName",
);
const updateStockInput = document.getElementById("updateStockInput");
const cancelUpdateStockButton = document.getElementById(
  "cancelUpdateStockButton",
);
const updateStockSubmitButton = document.getElementById(
  "updateStockSubmitButton",
);

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
  const submitButton = document.getElementById("buttonSubmit");

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

    await loadProducts(); // add product to the table

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

// function that retrieve data of products from mongodb db
async function loadProducts() {
  const tableBody = document.getElementById("productsTableBody"); // select table body

  try {
    const response = await fetch("/api/products/list.php");

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not retrieve products.");
    }

    //pass products to display function
    addProductToTable(result.data.products);
  } catch (error) {
    console.error("Product retrieval error:", error);

    tableBody.innerHTML = `
            <tr>
                <td colspan="6">Could not load products.</td>
            </tr>
        `;
  }
}

// helper function to add a product to the table
function addProductToTable(products) {
  // Clear existing rows before rendering
  tableBody.replaceChildren();

  // Handle an empty product list
  if (products.length === 0) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");

    cell.colSpan = 6;
    cell.textContent = "No products found.";

    row.appendChild(cell);
    tableBody.appendChild(row);

    return;
  }

  // Create a row for every product
  products.forEach((product) => {
    const row = document.createElement("tr");

    // Image
    const imageCell = document.createElement("td");
    const image = document.createElement("img");

    image.src = product.imageUrl;
    image.alt = product.name;
    image.width = 55;
    image.height = 55;
    image.classList.add("product-thumbnail");

    imageCell.appendChild(image);

    // Name
    const nameCell = document.createElement("td");
    nameCell.textContent = product.name;

    // Category
    const categoryCell = document.createElement("td");
    categoryCell.textContent = product.category;

    // Price
    const priceCell = document.createElement("td");

    priceCell.textContent = new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
    }).format(product.priceCents / 100);

    // Stock
    const stockCell = document.createElement("td");
    stockCell.textContent = product.stock;

    // Actions
    const actionsCell = document.createElement("td");

    const updateButton = document.createElement("button");

    updateButton.type = "button";
    updateButton.textContent = "Update Stock";
    updateButton.classList.add("update-stock-button");

    // Store product information in the button
    updateButton.dataset.productId = product.id;
    updateButton.dataset.productName = product.name;
    updateButton.dataset.stock = product.stock;

    actionsCell.appendChild(updateButton);

    // Assemble row
    row.append(
      imageCell,
      nameCell,
      categoryCell,
      priceCell,
      stockCell,
      actionsCell,
    );

    tableBody.appendChild(row);
  });
}

// Open update stock dialog
tableBody.addEventListener("click", (e) => {
  const updateButton = e.target.closest(".update-stock-button");

  if (!updateButton) {
    return;
  }

  // Get selected product information
  const productId = updateButton.dataset.productId;
  const productName = updateButton.dataset.productName;
  const currentStock = updateButton.dataset.stock;

  // Store product ID
  updateStockProductId.value = productId;

  // Display product information
  updateStockProductName.textContent = `${productName} — Current stock: ${currentStock}`;

  updateStockInput.value = currentStock;

  updateStockDialog.showModal();
});

// Close update stock dialog
cancelUpdateStockButton.addEventListener("click", () => {
  updateStockForm.reset();
  updateStockDialog.close();
});

// Update stock form submission
updateStockForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const productId = updateStockProductId.value;
  const stock = Number(updateStockInput.value);

  // Validate stock
  if (!productId || !Number.isInteger(stock) || stock < 0) {
    alert("Please enter a valid stock quantity.");
    return;
  }

  updateStockSubmitButton.disabled = true;
  updateStockSubmitButton.textContent = "Updating...";

  try {
    const response = await fetch("/api/products/update-stock.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        productId: productId,
        stock: stock,
      }),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not update stock.");
    }

    await loadProducts();

    updateStockForm.reset();
    updateStockDialog.close();

    alert(result.message);
  } catch (error) {
    console.error("Stock update error:", error);
    alert(error.message || "Something went wrong.");
  } finally {
    updateStockSubmitButton.disabled = false;
    updateStockSubmitButton.textContent = "Update Stock";
  }
});

loadProducts();

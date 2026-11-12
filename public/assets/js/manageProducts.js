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
  const tableBody = document.getElementById("productsTableBody");

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

    // Actions (we will implement these later)
    const actionsCell = document.createElement("td");
    actionsCell.textContent = "—";

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

loadProducts();

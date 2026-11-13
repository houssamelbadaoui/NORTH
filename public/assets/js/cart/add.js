const productsGrid1 = document.getElementById("productsGrid"); // select products grid
const cartCounElement = document.getElementById("cartCount"); // cart counter

// helper function to update the cart counter
function updateCartBadge(count) {
  cartCounElement.textContent = count;
  cartCounElement.hidden = count === 0; // counter will be hidden if count = 0
}

// send an add to cart request to php
async function addToCart(productId, button) {
  button.disabled = true; // disbale the button to not get more than one request
  button.textContent = "Adding...";

  try {
    const response = await fetch("/api/cart/add.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      credentials: "same-origin",
      body: JSON.stringify({
        productId: productId,
        quantity: 1,
      }),
    });

    const result = await response.json(); // json answer

    // if customer not logged in we redirect to login page
    if (response.status === 401 || result.code === "AUTH_REQUIRED") {
      alert(result.message);

      window.location.href = "/pages/login.php"; // redirect to login page
      return;
    }

    // other errors, not enough stock
    if (!response.ok || !result.success) {
      alert(result.message || "Could not add product to cart.");
      return;
    }

    // update cart badge
    updateCartBadge(result.data.cartCount);
    alert(result.message);
  } catch (error) {
    console.error("Add to cart error:", error);
    alert("Something went wrong, Please try again.");
  } finally {
    button.disabled = false;
    button.textContent = "Add to cart";
  }
}

// event delegation
productsGrid1.addEventListener("click", (event) => {
  // find the button
  const button = event.target.closest(".add-to-cart");

  if (!button || !productsGrid1.contains(button)) return; // if button not inisde grid

  const productId = button.dataset.productId; // product id

  if (!productId) {
    console.error("Product ID is missing from the button.");
    return;
  }

  // call function to add to cart
  addToCart(productId, button);
});

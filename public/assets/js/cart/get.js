const cartItemsContainer = document.getElementById("cartItems");
const cartItemsCount = document.getElementById("cartItemsCount");
const cartSubtotal = document.getElementById("cartSubtotal");
const cartTotal = document.getElementById("cartTotal");
const cartCountElement = document.getElementById("cartCount");

const checkoutButton = document.getElementById("checkoutButton");

// Format cents as US dollars.
function formatPrice(priceCents) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(priceCents / 100);
}

// Update the cart badge in the header.
function updateCartBadge(count) {
  if (!cartCountElement) return;

  cartCountElement.textContent = count;
  cartCountElement.hidden = count === 0;
}

// Create the visual representation of one cart item.
function createCartItem(item) {
  const article = document.createElement("article");
  article.classList.add("cart-item");

  // store the product ID on the article.
  article.dataset.productId = item.productId;

  // Product image
  const imageWrapper = document.createElement("div");
  imageWrapper.classList.add("cart-item-image-wrapper");

  if (item.imageUrl) {
    const image = document.createElement("img");

    image.classList.add("cart-item-image");
    image.src = item.imageUrl;
    image.alt = item.name;
    image.loading = "lazy";
    image.decoding = "async";

    imageWrapper.appendChild(image);
  } else {
    const placeholder = document.createElement("span");
    placeholder.textContent = "No image";
    imageWrapper.appendChild(placeholder);
  }

  // Product information
  const info = document.createElement("div");
  info.classList.add("cart-item-info");

  const name = document.createElement("h3");
  name.classList.add("cart-item-name");
  name.textContent = item.name;

  info.append(name);

  // Check whether the product still exists.
  if (item.productAvailable) {
    const price = document.createElement("p");
    price.classList.add("cart-item-price");
    price.textContent = `${formatPrice(item.unitPriceCents)} each`;

    // Quantoty control
    const quantityControls = document.createElement("div");
    quantityControls.classList.add("quantity-controls");

    const decreaseButton = document.createElement("button");
    decreaseButton.type = "button";
    decreaseButton.classList.add("quantity-button");
    decreaseButton.dataset.change = "-1";
    decreaseButton.textContent = "−";
    decreaseButton.setAttribute(
      "aria-label",
      `Decrease quantity of ${item.name}`,
    );

    const quantity = document.createElement("span");
    quantity.classList.add("cart-item-quantity");
    quantity.textContent = item.quantity;

    const increaseButton = document.createElement("button");
    increaseButton.type = "button";
    increaseButton.classList.add("quantity-button");
    increaseButton.dataset.change = "1";
    increaseButton.textContent = "+";
    increaseButton.setAttribute(
      "aria-label",
      `Increase quantity of ${item.name}`,
    );

    quantityControls.append(decreaseButton, quantity, increaseButton);

    info.append(price, quantityControls);
  } else {
    const unavailable = document.createElement("p");
    unavailable.classList.add("cart-item-price");
    unavailable.textContent = "This product is no longer available.";

    const quantity = document.createElement("p");
    quantity.classList.add("cart-item-quantity");
    quantity.textContent = `Quantity: ${item.quantity}`;

    info.append(unavailable, quantity);
  }

  // Product subtotal
  const subtotal = document.createElement("p");
  subtotal.classList.add("cart-item-subtotal");

  if (item.productAvailable) {
    subtotal.textContent = formatPrice(item.lineTotalCents);
  } else {
    subtotal.textContent = "Unavailable";
  }

  article.append(imageWrapper, info, subtotal);

  return article;
}

// Display an empty cart.
function renderEmptyCart() {
  cartItemsContainer.replaceChildren();

  const emptyContainer = document.createElement("div");
  emptyContainer.classList.add("cart-empty");

  const title = document.createElement("h2");
  title.textContent = "Your cart is empty";

  const description = document.createElement("p");
  description.textContent = "You haven't added anything to your cart yet.";

  const link = document.createElement("a");
  link.href = "/pages/products.php";
  link.textContent = "Explore Products";

  emptyContainer.append(title, description, link);
  cartItemsContainer.appendChild(emptyContainer);

  cartItemsCount.textContent = "0 items";
  cartSubtotal.textContent = formatPrice(0);
  cartTotal.textContent = formatPrice(0);

  updateCartBadge(0);

  if (checkoutButton) {
    checkoutButton.disabled = true;
  }
}

// Display all cart items.
function renderCart(cart) {
  const items = cart.items;

  // Update the header badge.
  updateCartBadge(cart.cartCount);

  // Handle an empty cart.
  if (items.length === 0) {
    renderEmptyCart();
    return;
  }

  cartItemsContainer.replaceChildren();

  const fragment = document.createDocumentFragment();

  items.forEach((item) => {
    const cartItem = createCartItem(item);
    fragment.appendChild(cartItem);
  });

  cartItemsContainer.appendChild(fragment);

  // Update cart summary.
  const itemLabel = cart.cartCount === 1 ? "item" : "items";

  cartItemsCount.textContent = `${cart.cartCount} ${itemLabel}`;

  cartSubtotal.textContent = formatPrice(cart.totalCents);
  cartTotal.textContent = formatPrice(cart.totalCents);

  // Checkout is not implemented yet.
  if (checkoutButton) {
    checkoutButton.disabled = true;
  }
}

// Display an error message.
function renderCartError(message) {
  cartItemsContainer.replaceChildren();

  const errorMessage = document.createElement("p");
  errorMessage.classList.add("cart-message");
  errorMessage.textContent = message;

  cartItemsContainer.appendChild(errorMessage);

  cartItemsCount.textContent = "Unavailable";
  cartSubtotal.textContent = "—";
  cartTotal.textContent = "—";

  if (checkoutButton) {
    checkoutButton.disabled = true;
  }
}

// Retrieve the customer's cart from the API.
async function loadCart() {
  try {
    const response = await fetch("/api/cart/get.php", {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
      credentials: "same-origin",
    });

    const result = await response.json();

    // Handle an expired session.
    if (response.status === 401 || result.code === "AUTH_REQUIRED") {
      window.location.href = "/pages/login.php";
      return;
    }

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not retrieve your cart.");
    }

    renderCart(result.data);
  } catch (error) {
    console.error("Cart loading error:", error);

    renderCartError(
      "We couldn't load your cart. Please refresh the page and try again.",
    );
  }
}

// Update a product quantity through the API
async function updateCartQuantity(productId, change, article) {
  const buttons = article.querySelectorAll(".quantity-button");

  // prevent multiple requests for the same time
  buttons.forEach((button) => {
    button.disabled = true;
  });

  // send request to php
  try {
    const response = await fetch("/api/cart/update.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      credentials: "same-origin",
      body: JSON.stringify({
        productId: productId,
        change: change,
      }),
    });

    const result = await response.json();

    // Handle an expired session.
    if (response.status === 401 || result.code === "AUTH_REQUIRED") {
      window.location.href = "/pages/login.php";
      return;
    }

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not update product quantity.");
    }

    // Reload the cart to display the latest server data.
    await loadCart();
  } catch (error) {
    console.error("Cart quantity update error:", error);
    alert(error.message || "Something went wrong. Please try again.");
  } finally {
    buttons.forEach((button) => {
      button.disabled = false;
    });
  }
}

// Event delegation for quantity buttons.
cartItemsContainer.addEventListener("click", (event) => {
  const button = event.target.closest(".quantity-button");

  if (!button || !cartItemsContainer.contains(button)) {
    return;
  }

  const article = button.closest(".cart-item");

  if (!article) return;

  const productId = article.dataset.productId;
  const change = Number(button.dataset.change);

  if (!productId || ![1, -1].includes(change)) {
    console.error("Invalid product ID or quantity change.");
    return;
  }

  updateCartQuantity(productId, change, article);
});

// Load the cart when the page opens.
loadCart();

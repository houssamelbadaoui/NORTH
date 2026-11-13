const productsGrid = document.getElementById("productsGrid"); // select the products grid
const productsCount = document.getElementById("productsCount");

// helper function to format the price from cents
function formatPrice(priceCents) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(priceCents / 100);
}

// function that create the product card
function createProductCard(product) {
  const card = document.createElement("article"); // create html articale, card wrapper
  card.classList.add("product-card"); // add class to article

  // image
  const imageWrapper = document.createElement("div"); // image container
  imageWrapper.classList.add("product-card-image-wrapper");

  const image = document.createElement("img"); // create the html img
  image.classList.add("product-card-image");
  image.src = product.imageUrl;
  image.alt = product.name;
  image.loading = "lazy";
  image.decoding = "async";

  imageWrapper.appendChild(image); // put image inside the image container

  // product information
  const info = document.createElement("div");
  info.classList.add("product-card-info");

  // name
  const name = document.createElement("h2");
  name.classList.add("product-card-name");
  name.textContent = product.name;

  // price
  const price = document.createElement("p");
  price.classList.add("product-card-price");
  price.textContent = formatPrice(product.priceCents);

  // card button
  const button = document.createElement("button");
  button.type = "button";
  button.classList.add("add-to-cart");
  button.textContent = "Add to cart";
  button.disabled = true;
  button.title = "Shopping cart coming soon";

  info.append(name, price, button); // put name and price and button inisde the info container
  card.append(imageWrapper, info); // append image and info inside the card

  return card;
}

// function to display products inside products grid
function renderProducts(products) {
  // empty the grid to add all products
  productsGrid.replaceChildren();

  if (products.length === 0) {
    const message = document.createElement("p");
    message.classList.add("products-message");
    message.textContent = "No products available right now.";

    // append the message inside the grid
    productsGrid.appendChild(message);
    productsCount.textContent = "0 Products";
    return;
  }

  const fragment = document.createDocumentFragment();

  products.forEach((product) => {
    const card = createProductCard(product);
    fragment.appendChild(card);
  });

  productsGrid.appendChild(fragment);
}

// function to retrieve products
async function loadProducts() {
  try {
    // send list.php request to get products
    const response = await fetch("/api/products/list.php");

    // make the answer json
    const result = await response.json();

    // if response not ok
    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not retrieve products.");
    }

    renderProducts(result.data.products); // pass products to render function
  } catch (error) {
    console.error("Products loading error:", error);

    productsGrid.replaceChildren();

    const message = document.createElement("p");
    message.classList.add("products-message");
    message.textContent = "We couldn't load the products. Please try again.";

    productsGrid.appendChild(message);
    productsCount.textContent = "Products unavailable";
  }
}

loadProducts();

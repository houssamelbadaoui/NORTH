const ordersCount = document.getElementById("ordersCount");
const ordersList = document.getElementById("ordersList");

const currencyFormatter = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "USD",
});

const dateFormatter = new Intl.DateTimeFormat("es-ES", {
  dateStyle: "medium",
});

function formatPrice(cents) {
  return currencyFormatter.format(cents / 100);
}

function formatDate(dateString) {
  const date = new Date(dateString);

  if (Number.isNaN(date.getTime())) {
    return "Date unavailable";
  }

  return dateFormatter.format(date);
}

function setMessage(message) {
  ordersList.replaceChildren();

  const paragraph = document.createElement("p");
  paragraph.className = "orders-message";
  paragraph.textContent = message;

  ordersList.appendChild(paragraph);
}

function getStatusDetails(status) {
  const statuses = {
    pending: {
      label: "Waiting",
      className: "pending",
    },
    confirmed: {
      label: "Confirmed",
      className: "confirmed",
    },
    cancelled: {
      label: "Cancelled",
      className: "cancelled",
    },
  };

  return (
    statuses[status] || {
      label: "Unknown",
      className: "unknown",
    }
  );
}

function createOrderCard(order) {
  const card = document.createElement("article");
  card.className = "order-card";

  // Order header
  const header = document.createElement("div");
  header.className = "order-card-header";

  const orderInfo = document.createElement("div");

  const orderTitle = document.createElement("h3");
  orderTitle.className = "order-card-title";
  orderTitle.textContent = `Order #${order.id.slice(-6).toUpperCase()}`;

  const orderDate = document.createElement("p");
  orderDate.className = "order-card-date";
  orderDate.textContent = formatDate(order.createdAt);

  orderInfo.append(orderTitle, orderDate);

  const statusDetails = getStatusDetails(order.status);

  const status = document.createElement("span");
  status.className = `order-status order-status--${statusDetails.className}`;
  status.textContent = statusDetails.label;

  header.append(orderInfo, status);

  // Products
  const productsContainer = document.createElement("div");
  productsContainer.className = "order-products";

  order.items.forEach((item) => {
    const product = document.createElement("div");
    product.className = "order-product";

    if (item.imageUrl) {
      const image = document.createElement("img");
      image.className = "order-product-image";
      image.src = item.imageUrl;
      image.alt = item.name;
      image.loading = "lazy";

      product.appendChild(image);
    }

    const details = document.createElement("div");
    details.className = "order-product-details";

    const name = document.createElement("h4");
    name.className = "order-product-name";
    name.textContent = item.name;

    const quantity = document.createElement("p");
    quantity.className = "order-product-quantity";
    quantity.textContent = `Quantity: ${item.quantity}`;

    details.append(name, quantity);

    const price = document.createElement("p");
    price.className = "order-product-price";
    price.textContent = formatPrice(item.lineTotalCents);

    product.append(details, price);
    productsContainer.appendChild(product);
  });

  // Order total
  const footer = document.createElement("div");
  footer.className = "order-card-footer";

  const totalLabel = document.createElement("span");
  totalLabel.textContent = "Order total";

  const total = document.createElement("strong");
  total.className = "order-total";
  total.textContent = formatPrice(order.totalCents);

  footer.append(totalLabel, total);

  card.append(header, productsContainer, footer);

  return card;
}

async function loadOrders() {
  try {
    const response = await fetch("/api/order/get.php", {
      method: "GET",
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    if (response.status === 401) {
      window.location.href = "/pages/login.php";
      return;
    }

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not load orders.");
    }

    const orders = result.data.orders;

    ordersCount.textContent = `${orders.length} ${orders.length === 1 ? "order" : "orders"}`;

    ordersList.replaceChildren();

    if (orders.length === 0) {
      setMessage("You haven't placed any orders yet.");
      return;
    }

    orders.forEach((order) => {
      const orderCard = createOrderCard(order);
      ordersList.appendChild(orderCard);
    });
  } catch (error) {
    console.error("Error loading orders:", error);

    ordersCount.textContent = "Orders unavailable";
    setMessage(
      "We couldn't load your orders. Please refresh the page and try again.",
    );
  }
}

loadOrders();

const ordersTableBody = document.getElementById("ordersTableBody");
const pendingOrdersCount = document.getElementById("pendingOrdersCount");

const currencyFormatter = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "USD",
});

const dateFormatter = new Intl.DateTimeFormat("en-US", {
  dateStyle: "medium",
});

// Format cents into dollars
function formatPrice(cents) {
  return currencyFormatter.format(cents / 100);
}

// Format order date
function formatDate(dateString) {
  const date = new Date(dateString);

  if (Number.isNaN(date.getTime())) {
    return "Date unavailable";
  }

  return dateFormatter.format(date);
}

// Display a message inside the table
function displayTableMessage(message) {
  ordersTableBody.replaceChildren();

  const row = document.createElement("tr");
  const cell = document.createElement("td");

  cell.colSpan = 5;
  cell.className = "orders-table-message";
  cell.textContent = message;

  row.appendChild(cell);
  ordersTableBody.appendChild(row);
}

// Create a table row for one order
function createOrderRow(order) {
  const row = document.createElement("tr");

  // Order ID
  const idCell = document.createElement("td");
  idCell.className = "order-id";
  idCell.textContent = `#${order.id.slice(-6).toUpperCase()}`;

  // Date
  const dateCell = document.createElement("td");
  dateCell.className = "order-date";
  dateCell.textContent = formatDate(order.createdAt);

  // Products
  const productsCell = document.createElement("td");

  const productsList = document.createElement("div");
  productsList.className = "order-products-list";

  order.items.forEach((item) => {
    const productItem = document.createElement("div");
    productItem.className = "order-product-item";

    const productName = document.createElement("span");
    productName.className = "order-product-name";
    productName.textContent = item.name;

    const quantity = document.createElement("span");
    quantity.className = "order-product-quantity";
    quantity.textContent = `× ${item.quantity}`;

    productItem.append(productName, quantity);
    productsList.appendChild(productItem);
  });

  productsCell.appendChild(productsList);

  // Total
  const totalCell = document.createElement("td");
  totalCell.className = "order-total";
  totalCell.textContent = formatPrice(order.totalCents);

  // Actions
  const actionsCell = document.createElement("td");
  actionsCell.className = "order-actions";

  const actionsContainer = document.createElement("div");
  actionsContainer.className = "order-action-buttons";

  const confirmButton = document.createElement("button");
  confirmButton.type = "button";
  confirmButton.className = "order-action-button order-action-button--confirm";
  confirmButton.textContent = "Confirm";
  confirmButton.dataset.orderId = order.id;
  confirmButton.dataset.action = "confirmed";

  const cancelButton = document.createElement("button");
  cancelButton.type = "button";
  cancelButton.className = "order-action-button order-action-button--cancel";
  cancelButton.textContent = "Cancel";
  cancelButton.dataset.orderId = order.id;
  cancelButton.dataset.action = "cancelled";

  actionsContainer.append(confirmButton, cancelButton);
  actionsCell.appendChild(actionsContainer);

  // Assemble row
  row.append(idCell, dateCell, productsCell, totalCell, actionsCell);

  return row;
}

// Retrieve pending orders from the API
async function loadPendingOrders() {
  try {
    const response = await fetch("/api/order/pending.php", {
      method: "GET",
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not retrieve pending orders.");
    }

    const orders = result.data.orders;

    // Update pending order count
    pendingOrdersCount.textContent = `${orders.length} pending ${orders.length === 1 ? "order" : "orders"}`;

    // Clear existing rows
    ordersTableBody.replaceChildren();

    // Handle empty orders
    if (orders.length === 0) {
      displayTableMessage("There are no pending orders.");
      return;
    }

    // Render each order
    orders.forEach((order) => {
      const row = createOrderRow(order);
      ordersTableBody.appendChild(row);
    });
  } catch (error) {
    console.error("Pending order retrieval error:", error);

    pendingOrdersCount.textContent = "Orders unavailable";
    displayTableMessage("Could not load pending orders.");
  }
}

// Handle order confirmation and cancellation
ordersTableBody.addEventListener("click", async (event) => {
  const button = event.target.closest(".order-action-button");

  if (!button) {
    return;
  }

  const orderId = button.dataset.orderId;
  const newStatus = button.dataset.action;

  // Ask for confirmation before cancelling
  if (newStatus === "cancelled") {
    const confirmed = window.confirm(
      "Are you sure you want to cancel this order? Its reserved stock will be restored.",
    );

    if (!confirmed) {
      return;
    }
  }

  const row = button.closest("tr");
  const rowButtons = row.querySelectorAll(".order-action-button");

  // Prevent multiple clicks while the request is running
  rowButtons.forEach((rowButton) => {
    rowButton.disabled = true;
  });

  button.textContent = "Updating...";

  try {
    const response = await fetch("/api/order/update-status.php", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        orderId: orderId,
        status: newStatus,
      }),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not update order.");
    }

    // The order is no longer pending, so refresh the table
    await loadPendingOrders();
  } catch (error) {
    console.error("Order update error:", error);

    alert(error.message || "Something went wrong.");

    // Restore the buttons if the request failed
    rowButtons.forEach((rowButton) => {
      rowButton.disabled = false;
    });

    button.textContent = newStatus === "confirmed" ? "Confirm" : "Cancel";
  }
});

loadPendingOrders();

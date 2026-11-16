// select table body
const usersTableBody = document.getElementById("usersTableBody");
const usersCount = document.getElementById("usersCount");

// load users from the database
async function loadUsers() {
  try {
    const response = await fetch("/api/users/list.php", {
      method: "GET",
      credentials: "same-origin",
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not retrieve users.");
    }

    usersCount.textContent = `${result.data.userCount} Users`;

    renderUsers(result.data.users);
  } catch (error) {
    console.error("Users retrieval error:", error);

    usersCount.textContent = "Unavailable";

    usersTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="users-table-message">
                    Could not load users. Please try again.
                </td>
            </tr>
        `;
  }
}

// Render users in the table
function renderUsers(users) {
  usersTableBody.innerHTML = "";

  if (users.length === 0) {
    usersTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="users-table-message">
                    No users found.
                </td>
            </tr>
        `;
    return;
  }

  users.forEach((user) => {
    const row = document.createElement("tr");

    // Full name
    const nameCell = document.createElement("td");
    nameCell.classList.add("user-name");
    nameCell.textContent = user.fullName;

    // Email
    const emailCell = document.createElement("td");
    emailCell.classList.add("user-email");
    emailCell.textContent = user.email;

    // Total orders
    const ordersCell = document.createElement("td");
    ordersCell.classList.add("user-order-count");
    ordersCell.textContent = user.totalOrders;

    // Role
    const roleCell = document.createElement("td");

    const roleLabel = document.createElement("span");
    roleLabel.classList.add("user-role", `user-role--${user.role}`);
    roleLabel.textContent = user.role;

    roleCell.appendChild(roleLabel);

    // Actions: Update button
    const actionsCell = document.createElement("td");

    const updateRoleButton = document.createElement("button");
    updateRoleButton.type = "button";
    updateRoleButton.classList.add("user-role-button");

    updateRoleButton.textContent =
      user.role === "customer" ? "Make Admin" : "Make Customer";
    updateRoleButton.dataset.userId = user.id;

    actionsCell.appendChild(updateRoleButton);

    // Add cells to row
    row.appendChild(nameCell);
    row.appendChild(emailCell);
    row.appendChild(ordersCell);
    row.appendChild(roleCell);
    row.appendChild(actionsCell);

    usersTableBody.appendChild(row);
  });
}

// Handle role update buttons
usersTableBody.addEventListener("click", async (e) => {
  const button = e.target.closest(".user-role-button");

  if (!button) return;

  const userId = button.dataset.userId;

  button.disabled = true;
  button.textContent = "Updating...";

  try {
    const response = await fetch("/api/users/update-role.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "same-origin",
      body: JSON.stringify({
        userId: userId,
      }),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Could not update user role.");
    }

    await loadUsers();
  } catch (error) {
    console.error("Role update error:", error);
    alert(error.message || "Something went wrong.");
    button.disabled = false;
    button.textContent = "Update Role";
  }
});
// Load users when the page opens
loadUsers();

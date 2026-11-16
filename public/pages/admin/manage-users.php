<?php

// Admin authentication

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users | NORTH</title>

    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/manageUsers.css">
</head>

<body>

    <?php require_once __DIR__ . "/../../includes/header.php"; ?>

    <main class="manage-users">

        <section class="users-header">
            <div class="users-header__text">
                <h1>Manage Users</h1>
                <p>View registered users and manage their roles.</p>
            </div>

            <span class="users-count" id="usersCount">
                Loading...
            </span>
        </section>

        <section class="users-section">

            <div class="users-table-wrapper">

                <table class="users-table">

                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Total Orders</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="usersTableBody">
                        <tr>
                            <td colspan="5" class="users-table-message">
                                Loading users...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <script src="../../assets/js/manageUsers.js" defer></script>
    <script src="../../assets/js/logout.js" defer></script>

</body>
</html>
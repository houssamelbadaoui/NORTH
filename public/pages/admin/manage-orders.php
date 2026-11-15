<?php

//admin authetication
require_once __DIR__ . "/../../../config/admin_auth.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Orders | NORTH</title>

    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/manageOrders.css">
</head>

<body>

    <?php require_once __DIR__ . "/../../includes/header.php"; ?>

    <main class="manage-orders">

        <section class="orders-header">
            <div class="orders-header__text">
                <h1>Manage Orders</h1>
                <p>Review and manage customer orders waiting for action.</p>
            </div>

            <span class="pending-orders-count" id="pendingOrdersCount">
                Loading...
            </span>
        </section>

        <section class="orders-section">

            <div class="orders-table-wrapper">

                <table class="orders-table">

                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Products</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="ordersTableBody">
                        <tr>
                            <td colspan="5">Loading pending orders...</td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <script src="../../assets/js/manageOrders.js" defer></script>
    <script src="../../assets/js/logout.js" defer></script>

</body>
</html>
<?php
require_once __DIR__ . "/../../config/session.php";

// only customers can access their orders
if(!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "customer"){
    header("Location: /pages/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders | NORTH</title>

    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/my-orders.css">
</head>
<body>

    <?php require_once __DIR__ . "/../includes/header.php"; ?>

    <main class="orders-page">

        <!-- Page introduction -->
        <section class="orders-intro">
            <p class="orders-eyebrow">YOUR NORTH PURCHASES</p>
            <h1>My Orders</h1>
            <p class="orders-description">
                View your orders and keep track of their status.
            </p>
        </section>

        <!-- Orders content -->
        <section class="orders-section">

            <div class="orders-header">
                <h2>Your Orders</h2>
                <span id="ordersCount">Loading...</span>
            </div>

            <div class="orders-list" id="ordersList" aria-live="polite">
                <p class="orders-message">Loading your orders...</p>
            </div>

        </section>

    </main>

    <script src="/assets/js/order/get.js" defer></script>
    <script src="/assets/js/logout.js"></script>

</body>
</html>
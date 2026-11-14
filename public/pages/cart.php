<?php
require_once __DIR__ . "/../../config/session.php";

// only customer can access the shopping cart
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
    <title>Cart | NORTH</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/cart.css">
</head>
<body>
    <?php require_once __DIR__ . "/../includes/header.php" ?>

    <main class="cart-page">

    <!-- page instroduction -->
     <section class="cart-intro">
        <p class="cart-eyebrow">YOUR NORTH SELECTION</p>
        <h1>Your Cart</h1>
        <p class="cart-description">Review your selection before placing your order.</p>
     </section>

     <!-- CART Content -->
      <section class="cart-section">
        <div class="cart-layout">
            <div class="cart-items-column">
                <div class="cart-items-header">
                    <h2>Your Items</h2>
                    <span id="cartItemsCount">Loading...</span>
                </div>
                <div class="cart-items" id="cartItems" aria-live="polite">
                    <p class="cart-message">Loading your cart...</p>
                </div>
            </div>

            <!-- order summary -->
             <aside class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="cartSubtotal">-</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Calculated later</span>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span id="cartTotal">-</span>
                </div>

                <button type="button" class="checkout-button" id="checkoutButton" disabled>Order</button>
                <a href="/pages/products.php">Continue Shopping</a>
             </aside>
        </div>
      </section>
    </main>
    <script src="/assets/js/cart.js" defer></script>
    <script src="/assets/js/logout.js"></script>
</body>
</html>
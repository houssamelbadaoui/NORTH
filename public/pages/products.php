<?php
require_once __DIR__ . "/../../config/session.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shop All | NORTH</title>

    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/products.css">
</head>
<body>

    <?php require_once __DIR__ . "/../includes/header.php"; ?>

    <main class="products-page">

        <!-- PAGE INTRODUCTION -->
        <section class="products-intro">
            <p class="products-eyebrow">THE NORTH COLLECTION</p>
            <h1>Shop All</h1>
            <p class="products-description">
                Discover pieces designed for everyday wear.
            </p>
        </section>

        <!-- FILTERS -->
        <section class="products-toolbar">

            <div class="products-count">
                <span id="productsCount">All products</span>
            </div>

            <div class="products-filters">

                <div class="filter-group">
                    <label for="categoryFilter">Category</label>
                    <select id="categoryFilter">
                        <option value="all">All categories</option>
                        <option value="Shirts">Shirts</option>
                        <option value="Jackets">Jackets</option>
                        <option value="Pants">Pants</option>
                        <option value="Shoes">Shoes</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sortFilter">Sort by</label>
                    <select id="sortFilter">
                        <option value="featured">Featured</option>
                        <option value="price-asc">Price: Low to high</option>
                        <option value="price-desc">Price: High to low</option>
                    </select>
                </div>

            </div>

        </section>

        <!-- PRODUCT GRID -->
        <section class="products-section">

            <div
                class="products-grid"
                id="productsGrid"
                aria-live="polite"
            >
                <p class="products-message">Loading products...</p>
            </div>

        </section>

    </main>

   

    <script src="/assets/js/products.js" defer></script>
    <script src="/assets/js/cart/add.js" defer></script>
    <script src="/assets/js/logout.js"></script>

</body>
</html>

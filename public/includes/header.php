
<header class="header">

    <div class="header-container">

        <a href="/index.php" class="logo">
            NORTH<span>.</span>
        </a>

        <nav class="main-nav">
            <ul class="nav-links">

                <?php if (!isset($_SESSION["user_id"])): ?>

                    <li><a href="/index.php">Home</a></li>
                    <li><a href="./pages/products.php">Products</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="/pages/login.php">Login</a></li>
                    <li><a href="/pages/register.php" class="nav-register">Register</a></li>

                <?php elseif ($_SESSION["role"] === "admin"): ?>

                    <li><a href="#">Dashboard</a></li>
                    <li><a href="/pages/admin/manageProducts.php">Manage Products</a></li>
                    <li><a href="#">Manage Orders</a></li>
                    <li>
                        <button type="button" id="logoutButton" class="logout-button">
                            Logout
                        </button>
                    </li>

                <?php else: ?>

                    <li><a href="/index.php">Home</a></li>
                    <li><a href="./pages/products.php">Products</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="/public/pages/cart.php" class="cart-link" aria-label="shopping-cart">
                        <svg
            class="cart-icon"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <circle cx="9" cy="21" r="1"/>
            <circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>

                        <span id="cartCount" class="cart-count" hidden>0</span>
                    </a></li>
                    <li><a href="#">My Orders</a></li>
                    <li>
                        <button type="button" id="logoutButton" class="logout-button">
                            Logout
                        </button>
                    </li>

                <?php endif; ?>

            </ul>
        </nav>

    </div>

</header>

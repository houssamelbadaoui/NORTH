
<header class="header">

    <div class="header-container">

        <a href="/index.php" class="logo">
            NORTH<span>.</span>
        </a>

        <nav class="main-nav">
            <ul class="nav-links">

                <?php if (!isset($_SESSION["user_id"])): ?>

                    <li><a href="/index.php">Home</a></li>
                    <li><a href="#">Products</a></li>
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
                    <li><a href="#">Products</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Cart</a></li>
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

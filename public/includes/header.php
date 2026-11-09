
    
    
    <header class="header">

    <div class="logo-container">
        <h1 class="logo">NORTH</h1>
    </div>

    <nav>
        <ul class="nav-links">
            

            <?php // if user is not logged in we display link to log in or register
            if(!isset($_SESSION["user_id"])): ?>
            <li><a href="index.php">Home</a></li>
            <li><a href="">Products</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="../pages/login.php">Login</a></li>
            <li><a href="../pages/register.php">Register</a></li>

            <?php // if user is admin
                  // display admin navigation
            elseif($_SESSION["role"] === "admin"):?>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Manage Products</a></li>
            <li><a href="#">Manage Orders</a></li>
            <li><button id="logoutButton">Logout</button></li>

            <?php // if customer
            else: ?>
            <li><a href="index.php">Home</a></li>
            <li><a href="#">Products</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Cart</a></li>
            <li><a href="#">My Orders</a></li>
            <li><button id="logoutButton">Logout</button></li>

            <?php endif; ?>
        </ul>
    </nav>
    </header>

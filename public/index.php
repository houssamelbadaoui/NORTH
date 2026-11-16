<?php
// start a session
require_once __DIR__ . "/../config/session.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NORTH</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    
<?php require_once __DIR__ . "/includes/header.php"; ?>

<main class="home-page">

    <section class="hero">

        <div class="hero__image"></div>

        <div class="hero__overlay"></div>

        <div class="hero__content">
            <p class="hero__eyebrow">NORTH: NEW COLLECTION</p>

            <h1>Find Your<br>Own Direction.</h1>

            <p class="hero__description">
                Timeless essentials. Modern expression.
                Discover the NORTH collection.
            </p>

            <a href="/pages/products.php" class="hero__button">
                Shop Now
            </a>
        </div>

    </section>

</main>
<script src="assets/js/logout.js"></script>
</body>
</html>
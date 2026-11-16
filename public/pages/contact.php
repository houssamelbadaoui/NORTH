<?php
require_once __DIR__ . "/../../config/session.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | NORTH</title>

    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/contact.css">
</head>
<body>

    <?php require_once __DIR__ . "/../includes/header.php"; ?>

    <main class="contact-page">

        <section class="contact-container">

            <div class="contact-heading">
                <p class="contact-eyebrow">GET IN TOUCH</p>
                <h1>Contact NORTH</h1>
                <p>
                    Have a question or something to share?
                    Leave us a message.
                </p>
            </div>

            <form id="contactForm" class="contact-form">

                <div class="form-group">
                    <label for="contactEmail">Email Address</label>
                    <input
                        type="email"
                        id="contactEmail"
                        name="email"
                        placeholder="your@email.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contactMessage">Message</label>
                    <textarea
                        id="contactMessage"
                        name="message"
                        rows="6"
                        placeholder="Write your message here..."
                        required
                    ></textarea>
                </div>

                <button type="submit" class="contact-submit">
                    Send Message
                </button>

            </form>

            <div id="contactSuccess" class="contact-success" hidden>
                <h2>Thank You!</h2>
                <p>
                    Thank you for taking the time to contact NORTH.
                    We appreciate your message.
                </p>
            </div>

        </section>

    </main>

    <script src="/assets/js/contact.js" defer></script>

</body>
</html>

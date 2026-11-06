<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - NORTH</title>
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <main class="register-page">
        <section class="register-container">

        <a href="../index.php" class="brand">NORTH</a>

        <div class="register-heading">
            <p class="eyebrow">WELCOME TO NORTH</p>
            <h1>Create your account</h1>
            <p>Join NORTH and discover your next favourite piece.</p>
        </div>

        <!-- registration form -->

        <form method="POST" id="registerForm">


                <div class="form-row">

                    <div class="form-group">
                        <label for="name">First name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="John"
                            autocomplete="given-name"
                            required
                            minlength="2"
                            maxlength="50"
                        >
                    </div>

                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input
                            type="text"
                            id="surname"
                            name="surname"
                            placeholder="Smith"
                            autocomplete="family-name"
                            required
                            minlength="2"
                            maxlength="50"
                        >
                    </div>

                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="john@example.com"
                        autocomplete="email"
                        required
                        maxlength="254"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="At least 8 characters"
                        autocomplete="new-password"
                        required
                        minlength="8"
                    >
                    <small>Use at least 8 characters.</small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm password</label>
                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        placeholder="Enter your password again"
                        autocomplete="new-password"
                        required
                        minlength="8"
                    >
                </div>

                <!-- Messages from JavaScript -->
                <div
                    id="formMessage"
                    class="form-message"
                    role="alert"
                    aria-live="polite"
                ></div>

                <button type="submit" id="registerButton">
                    CREATE ACCOUNT
                    <span>→</span>
                </button>
        </form>

        <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>

          <p class="terms-text">
                By creating an account, you agree to our Terms and Privacy Policy.
            </p>
        </section>
    </main>

    <script></script>
</body>
</html>
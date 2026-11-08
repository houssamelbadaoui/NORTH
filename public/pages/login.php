<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NORTH</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <main class="login-container">

    <section class="login-card">
        <a href="../index.php" class="brand">NORT<span>H</span></a>

        <div class="login-heading">
            <h1>Welcome Back</h1>
            <p>Log in to your account to continue.</p>
        </div>

        <form id="loginForm">

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="email" required>
        </div>
         <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
        </div>

        <p id="formMessage" class="form-message" role="alert" aria-live="polite"></p>

        <button type="submit" id="loginButton">Log in</button>
        </form>

        <p class="register-link">Don´t have an account? <a href="register.php">Create one</a></p>
    </section>
    </main>
    <script src="../assets/js/login.js"></script>
</body>
</html>
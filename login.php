<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login attempt
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        if (Auth::login($username, $password)) {
            log_action('LOGIN_SUCCESS', "User $username logged in");
            header('Location: dashboard.php');
            exit;
        } else {
            log_action('LOGIN_FAILED', "Failed login attempt for username: $username");
            $error = "Invalid credentials";
        }
    }
    
    // Handle password reset request
    if (isset($_POST['reset_request'])) {
        $email = $_POST['email'];
        $token = bin2hex(random_bytes(32)); // Secure token
        $expires = time() + 3600; // 1 hour expiration
        
        try {
            $db = DB::connect();
            $stmt = $db->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
            $stmt->execute([hash('sha256', $token), $expires, $email]);
            
            // In a real app, you would send this link via email
            $reset_link = "http://localhost//STRIDE-banking/reset_password.php?token=$token";
            log_action('RESET_LINK', "Generated for $email: $reset_link");
            $message = "If an account exists, a reset link has been generated";
        } catch (PDOException $e) {
            $message = "Error processing request";
            log_action('RESET_ERROR', $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <?php if (isset($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST">
            <div>
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        
        <!-- Password Reset Form -->
        <div class="reset-section">
            <h3>Forgot Password?</h3>
            <form method="POST">
                <div>
                    <label>Email Address:</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" name="reset_request">Reset Password</button>
            </form>
        </div>
        
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
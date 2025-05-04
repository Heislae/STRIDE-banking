<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // No input validation or sanitization
    $userData = [
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'email' => $_POST['email'],
        'full_name' => $_POST['full_name'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone']
    ];
    
    if (Auth::register($userData)) {
        // This code has no password complexity, which means any password (even 1 character) is accepted
        header('Location: login.php');
        exit;
    } else {
        $error = "Registration failed";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div>
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>Full Name:</label>
                <input type="text" name="full_name" required>
            </div>
            <div>
                <label>Address:</label>
                <textarea name="address" required></textarea>
            </div>
            <div>
                <label>Phone:</label>
                <input type="text" name="phone" required>
            </div>
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
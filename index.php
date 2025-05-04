<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vulnerable Banking App</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Our Vulnerable Banking App</h1>
        <p>This is a intentionally vulnerable application for cybersecurity training purposes.</p>
        
        <div class="actions">
            <a href="login.php" class="button">Login</a>
            <a href="register.php" class="button">Register</a>
        </div>
        
        <div class="warning">
            <h2>Warning!</h2>
            <p>This application contains intentional security vulnerabilities for educational purposes only.</p>
            <p>Do not use real personal information or passwords.</p>
        </div>
    </div>
</body>
</html>
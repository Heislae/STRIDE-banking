<?php
// includes/config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vulnerable_app');
define('DB_USER', 'root');
define('DB_PASS', ''); // Intentionally weak for demonstration

// Application settings
define('SECRET_KEY', 'weaksecret123'); // Weak secret key for demonstration
define('ENCRYPTION_KEY', 'insecurekey'); // Weak encryption key

// Session configuration
ini_set('session.cookie_httponly', 0); // Disabled for vulnerability
ini_set('session.cookie_secure', 0);   // Disabled for vulnerability
session_start();

// Error reporting - showing all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
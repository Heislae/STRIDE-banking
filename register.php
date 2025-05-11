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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrust Banking | Register</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Registration-specific styles */
        .register-container {
            display: flex;
            min-height: calc(100vh - 120px);
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        .register-card {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .register-header {
            background: var(--primary-color);
            color: white;
            padding: 28px;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .register-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin-bottom: 0;
        }
        
        .register-body {
            padding: 32px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn-register {
            display: block;
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            background-color: var(--success-color);
            color: white;
            font-weight: 500;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            background-color: #28a745;
            transform: translateY(-2px);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.95rem;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .register-body {
                padding: 24px;
            }
            
            .register-header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-university"></i>
                    <h1>GlobalTrust Banking</h1>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="active">Register</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="register-container">
        <div class="container">
            <div class="register-card">
                <div class="register-header">
                    <h1><i class="fas fa-user-plus"></i> Create Account</h1>
                    <p>Join GlobalTrust Banking today</p>
                </div>
                
                <div class="register-body">
                    <?php if (isset($error)): ?>
                        <div class="alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required placeholder="Choose a username">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Create a password">
                            <small class="text-muted">Use at least 8 characters with numbers and symbols</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required placeholder="Your email address">
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required placeholder="Your full legal name">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Mailing Address</label>
                            <textarea id="address" name="address" class="form-control" required placeholder="Your complete address"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required placeholder="Your phone number">
                        </div>
                        
                        <button type="submit" class="btn btn-register">
                            <i class="fas fa-user-check"></i> Create Account
                        </button>
                    </form>
                    
                    <div class="login-link">
                        Already have an account? <a href="login.php">Sign in here</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> GlobalTrust Banking. All rights reserved. Member FDIC. Equal Housing Lender.</p>
            </div>
        </div>
    </footer>
</body>
</html>
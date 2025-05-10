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
            $error = "Invalid username or password";
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
            $message = "If an account exists with this email, a password reset link has been sent";
        } catch (PDOException $e) {
            $message = "Error processing your request. Please try again later.";
            log_action('RESET_ERROR', $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrust Banking - Secure Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional styles for login page refinement */
        .login-container {
            display: flex;
            min-height: calc(100vh - 120px);
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        .login-card {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 28px;
            text-align: center;
        }
        
        .login-header h2 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin-bottom: 0;
        }
        
        .login-body {
            padding: 32px;
        }
        
        .form-group {
            margin-bottom: 24px;
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
        
        .btn-block {
            display: block;
            width: 100%;
            padding: 14px;
            font-size: 1rem;
        }
        
        .login-footer {
            padding: 20px 32px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .text-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .text-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .password-options {
            text-align: right;
            margin-top: 8px;
        }
        
        /* Reset password card */
        .reset-card {
            display: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-body {
                padding: 24px;
            }
            
            .login-header {
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
            </div>
        </div>
    </header>

    <main class="login-container">
        <div class="container">
            <!-- Login Card -->
            <div class="login-card">
                <div class="login-header">
                    <h2><i class="fas fa-lock"></i> Secure Login</h2>
                    <p>Access your GlobalTrust accounts and services</p>
                </div>
                
                <div class="login-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                            <div class="password-options">
                                <a href="#reset-password" class="text-link">Forgot password?</a>
                            </div>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </form>
                </div>
                
                <div class="login-footer">
                    <p>Don't have an account? <a href="register.php" class="text-link">Enroll now</a></p>
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <span>Protected by 256-bit encryption</span>
                    </div>
                </div>
            </div>
            
            <!-- Reset Password Card -->
            <div class="login-card reset-card" id="reset-password">
                <div class="login-header">
                    <h2><i class="fas fa-key"></i> Reset Password</h2>
                    <p>We'll send you a secure reset link</p>
                </div>
                
                <div class="login-body">
                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your registered email">
                        </div>
                        
                        <button type="submit" name="reset_request" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Send Reset Link
                        </button>
                    </form>
                </div>
                
                <div class="login-footer">
                    <a href="#" class="text-link back-to-login"><i class="fas fa-arrow-left"></i> Back to login</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2023 GlobalTrust Banking. All rights reserved. Member FDIC. Equal Housing Lender.</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle between login and password reset forms
        document.addEventListener('DOMContentLoaded', function() {
            // Hide reset card by default
            document.querySelector('.reset-card').style.display = 'none';
            
            // Forgot password link
            document.querySelector('.password-options a').addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('.login-card:not(.reset-card)').style.display = 'none';
                document.querySelector('.reset-card').style.display = 'block';
            });
            
            // Back to login link
            document.querySelector('.back-to-login').addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('.reset-card').style.display = 'none';
                document.querySelector('.login-card:not(.reset-card)').style.display = 'block';
            });
        });
    </script>
</body>
</html>
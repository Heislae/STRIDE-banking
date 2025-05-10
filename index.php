<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrust Banking | Secure Online Banking</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <li><a href="#" class="active">Home</a></li>
                        <li><a href="#">Personal</a></li>
                        <li><a href="#">Business</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-text">
                        <h2>Banking Made Simple, Secure, and Smart</h2>
                        <p>Experience next-generation digital banking with industry-leading security and 24/7 customer support.</p>
                        <div class="hero-buttons">
                            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Online Banking Login</a>
                            <a href="register.php" class="btn btn-secondary"><i class="fas fa-user-plus"></i> Open an Account</a>
                        </div>
                    </div>
                    <div class="hero-image">
                        <img src="assets/images/online-banking.jpg" alt="Online Banking Illustration">
                    </div>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2 class="section-title">Why Choose GlobalTrust?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Advanced Security</h3>
                        <p>Multi-factor authentication and 256-bit encryption to protect your accounts.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobile Banking</h3>
                        <p>Full banking capabilities right from your smartphone or tablet.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h3>Competitive Rates</h3>
                        <p>Enjoy higher interest rates on savings and lower loan rates.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Our customer service team is available anytime you need help.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="security-notice">
            <div class="container">
                <div class="security-content">
                    <i class="fas fa-lock"></i>
                    <p>GlobalTrust uses the highest security standards to protect your information. <a href="#">Learn more about our security measures</a>.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>GlobalTrust Banking</h3>
                    <p>Providing trusted financial services since 1985.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#">Rates</a></li>
                        <li><a href="#">Locations</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Careers</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="#">Checking Accounts</a></li>
                        <li><a href="#">Savings Accounts</a></li>
                        <li><a href="#">Loans</a></li>
                        <li><a href="#">Investments</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Security Center</a></li>
                        <li><a href="#">Disclosures</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 GlobalTrust Banking. All rights reserved. Member FDIC. Equal Housing Lender.</p>
            </div>
        </div>
    </footer>
</body>
</html>
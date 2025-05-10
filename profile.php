<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$db = DB::connect();
$userId = $_SESSION['user_id'];

// Vulnerable to IDOR (Insecure Direct Object Reference)
$user = $db->query("SELECT * FROM users WHERE id = $userId")->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // No CSRF protection
    $stmt = $db->prepare("UPDATE users SET email = ?, full_name = ?, address = ?, phone = ? WHERE id = ?");
    $stmt->execute([
        $_POST['email'],
        $_POST['full_name'],
        $_POST['address'],
        $_POST['phone'],
        $userId
    ]);
    
    header('Location: profile.php');
    log_action('PROFILE_UPDATE', "Updated profile information");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrust Banking | Profile</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Profile-specific styles */
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .profile-card h2 {
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .profile-card h2 i {
            color: var(--primary-color);
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
            padding: 12px 15px;
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
        
        .form-control:disabled {
            background-color: #f5f5f5;
            color: #666;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn-update {
            background-color: var(--primary-color);
            color: white;
            padding: 14px 25px;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 10px;
        }
        
        .btn-update:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .profile-card {
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
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
                        <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="profile-header">
            <h1><i class="fas fa-user-circle"></i> Your Profile</h1>
            <div class="user-role">
                <i class="fas fa-user-shield"></i> <?= htmlspecialchars(ucfirst($_SESSION['role'])) ?>
            </div>
        </div>

        <div class="profile-card">
            <h2><i class="fas fa-user-edit"></i> Personal Information</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    <small class="text-muted">Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Mailing Address</label>
                    <textarea id="address" name="address" class="form-control" required><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-update">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>

        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
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
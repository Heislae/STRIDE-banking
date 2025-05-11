<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$db = DB::connect();
$userId = $_SESSION['user_id'];

// SQL injection vulnerability in query
$user = $db->query("SELECT * FROM users WHERE id = $userId")->fetch(PDO::FETCH_ASSOC);
$transactions = $db->query("SELECT * FROM transactions WHERE user_id = $userId ORDER BY transaction_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrust Banking | Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Dashboard-specific styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .welcome-message h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .welcome-message p {
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .user-role {
            background-color: var(--primary-light);
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
        }
        
        .dashboard-card h2 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .dashboard-card h2 i {
            color: var(--primary-color);
        }
        
        .balance-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 15px 0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 12px 20px;
        }
        
        .info-label {
            font-weight: 500;
            color: var(--text-light);
        }
        
        .info-value {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .transaction-table {
            width: 100%;
            margin-top: 20px;
        }
        
        .transaction-table th {
            text-align: left;
            padding: 12px 15px;
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .transaction-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .transaction-table tr:last-child td {
            border-bottom: none;
        }
        
        .dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 30px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .dashboard-actions {
                flex-direction: column;
            }
            
            .dashboard-actions .btn {
                width: 100%;
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
                        <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="dashboard-header">
            <div class="welcome-message">
                <h1>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                <p>Here's what's happening with your accounts today</p>
            </div>
            <div class="user-role">
                <i class="fas fa-user-shield"></i> <?= htmlspecialchars(ucfirst($_SESSION['role'])) ?>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2><i class="fas fa-wallet"></i> Account Summary</h2>
                <div class="info-grid">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    
                    <span class="info-label">Account Status:</span>
                    <span class="info-value">Active <i class="fas fa-check-circle" style="color: var(--success-color);"></i></span>
                    
                    <span class="info-label">Last Login:</span>
                    <span class="info-value"><?= date('M j, Y g:i a') ?></span>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h2><i class="fas fa-piggy-bank"></i> Current Balance</h2>
                <div class="balance-display">
                    Php<?= number_format(htmlspecialchars(array_sum(array_column($transactions, 'amount'))), 2) ?>
                </div>
                <div class="info-grid">
                    <span class="info-label">Available:</span>
                    <span class="info-value">Php<?= number_format(htmlspecialchars(array_sum(array_column($transactions, 'amount'))), 2) ?></span>
                    
                    <span class="info-label">Last Deposit:</span>
                    <span class="info-value">
                        <?php 
                        $lastDeposit = array_filter($transactions, function($tx) { 
                            return $tx['amount'] > 0; 
                        });
                        echo isset($lastDeposit[0]) ? 'Php'.number_format(end($lastDeposit)['amount'], 2) : 'None';
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <h2><i class="fas fa-history"></i> Recent Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td style="color: <?= $tx['amount'] > 0 ? 'var(--success-color)' : 'var(--danger-color)' ?>;">
                            <?= $tx['amount'] > 0 ? '+' : '' ?><?= number_format(htmlspecialchars($tx['amount']), 2) ?>
                        </td>
                        <td><?= htmlspecialchars($tx['description']) ?></td>
                        <td><?= date('M j, Y', strtotime(htmlspecialchars($tx['transaction_date']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-actions">
            <?php if (Auth::isAdmin()): ?>
                <a href="admin.php" class="btn btn-secondary"><i class="fas fa-cog"></i> Admin Panel</a>
            <?php endif; ?>
            <a href="transfer.php" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Make a Transfer</a>
            <a href="transactions.php" class="btn"><i class="fas fa-list"></i> View All Transactions</a>
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
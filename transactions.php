<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$db = DB::connect();
$userId = $_SESSION['user_id'];
$message = '';

// Calculate current balance from transactions
$balance = $db->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = $userId")->fetchColumn();

// Process new transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_money'])) {
    $recipient = trim($_POST['recipient']);
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    
    try {
        // 1. Validate inputs
        if ($amount <= 0) {
            throw new Exception("Amount must be positive");
        }
        
        if ($amount > $balance) {
            throw new Exception("Insufficient funds");
        }
        
        // 2. Verify recipient exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$recipient]);
        $recipientId = $stmt->fetchColumn();
        
        if (!$recipientId || $recipientId == $userId) {
            throw new Exception("Invalid recipient");
        }
        
        // 3. Record transactions (no accounts table needed)
        $db->beginTransaction();
        
        // Debit from sender
        $db->prepare("INSERT INTO transactions (user_id, amount, description) VALUES (?, ?, ?)")
           ->execute([$userId, -$amount, "Transfer to $recipient: $description"]);
        
        // Credit to recipient
        $db->prepare("INSERT INTO transactions (user_id, amount, description) VALUES (?, ?, ?)")
           ->execute([$recipientId, $amount, "Transfer from {$_SESSION['username']}: $description"]);
        
        $db->commit();
        $message = "Successfully transferred $$amount to $recipient";
        log_action('MONEY_TRANSFER', "Sent $amount to $recipient");
        
        // Update balance display
        $balance -= $amount;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $message = "Error: " . $e->getMessage();
        log_action('TRANSFER_FAILED', $e->getMessage());
    }
}

// Search functionality (existing)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM transactions WHERE user_id = $userId";

if (!empty($search)) {
    $query .= " AND description LIKE '%$search%'";
    log_action('TRANSACTION_SEARCH', "Searched transactions for: $search");
}

$transactions = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrust Banking | Transactions</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Transactions-specific styles */
        .transactions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .balance-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .balance-card h3 {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .balance-amount {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .transfer-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .transfer-card h2 {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .transfer-card h2 i {
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
        
        .btn-transfer {
            background-color: var(--success-color);
            color: white;
            padding: 14px 25px;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .btn-transfer:hover {
            background-color: #28a745;
            transform: translateY(-2px);
        }
        
        .search-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-form input {
            flex: 1;
            padding: 12px 15px;
        }
        
        .search-form button {
            padding: 12px 20px;
        }
        
        .transactions-table {
            width: 100%;
            margin-top: 20px;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .transactions-table th {
            text-align: left;
            padding: 15px;
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .transactions-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .transactions-table tr:last-child td {
            border-bottom: none;
        }
        
        .amount-cell {
            font-weight: 500;
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
            .transactions-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .transactions-table {
                display: block;
                overflow-x: auto;
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
                        <li><a href="transactions.php" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="transactions-header">
            <h1><i class="fas fa-exchange-alt"></i> Your Transactions</h1>
            <div class="user-role">
                <i class="fas fa-user-shield"></i> <?= htmlspecialchars(ucfirst($_SESSION['role'])) ?>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger' ?>">
                <i class="fas <?= strpos($message, 'Error') === false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="balance-card">
            <h3>Available Balance</h3>
            <div class="balance-amount">$<?= number_format($balance, 2) ?></div>
        </div>

        <div class="transfer-card">
            <h2><i class="fas fa-paper-plane"></i> Send Money</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="recipient">Recipient Username</label>
                    <input type="text" id="recipient" name="recipient" class="form-control" required placeholder="Enter recipient's username">
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" id="amount" name="amount" class="form-control" min="0.01" step="0.01" required placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" class="form-control" required placeholder="Payment for services">
                </div>
                
                <button type="submit" name="send_money" class="btn btn-transfer">
                    <i class="fas fa-paper-plane"></i> Send Money
                </button>
            </form>
        </div>

        <div class="search-card">
            <h2><i class="fas fa-history"></i> Transaction History</h2>
            <form method="GET" class="search-form">
                <input type="text" name="search" class="form-control" placeholder="Search transactions..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td>#<?= htmlspecialchars($tx['id']) ?></td>
                    <td class="amount-cell" style="color: <?= $tx['amount'] > 0 ? 'var(--success-color)' : 'var(--danger-color)' ?>;">
                        <?= ($tx['amount'] > 0 ? '+' : '') . number_format($tx['amount'], 2) ?>
                    </td>
                    <td><?= htmlspecialchars($tx['description']) ?></td>
                    <td><?= date('M j, Y g:i a', strtotime(htmlspecialchars($tx['transaction_date']))) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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
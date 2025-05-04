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
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
        
        <div class="user-info">
            <h2>Your Information</h2>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Balance:</strong> 
                <?= htmlspecialchars(array_sum(array_column($transactions, 'amount'))) ?></p>
        </div>
        
        <div class="recent-transactions">
            <h2>Recent Transactions</h2>
            <table>
                <tr>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><?= htmlspecialchars($tx['amount']) ?></td>
                    <td><?= htmlspecialchars($tx['description']) ?></td>
                    <td><?= htmlspecialchars($tx['transaction_date']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="actions">
            <?php if (Auth::isAdmin()): ?>
                <a href="admin.php" class="button">Admin Panel</a>
            <?php endif; ?>
            <a href="profile.php" class="button">View Profile</a>
            <a href="transactions.php" class="button">All Transactions</a>
            <a href="logout.php" class="button">Logout</a>
        </div>
    </div>
</body>
</html>
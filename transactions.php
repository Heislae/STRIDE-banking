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
<html>
<head>
    <title>Transactions</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Your Transactions</h1>
        
        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="balance">
            <h3>Current Balance: $<?= number_format($balance, 2) ?></h3>
        </div>
        
        <div class="transaction-form">
            <h2>Send Money</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Recipient Username:</label>
                    <input type="text" name="recipient" required>
                </div>
                <div class="form-group">
                    <label>Amount:</label>
                    <input type="number" name="amount" min="0.01" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <input type="text" name="description" required>
                </div>
                <button type="submit" name="send_money">Send Money</button>
            </form>
        </div>
        
        <div class="search-transactions">
            <h2>Transaction History</h2>
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search transactions..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><?= htmlspecialchars($tx['id']) ?></td>
                    <td><?= ($tx['amount'] > 0 ? '+' : '') . htmlspecialchars($tx['amount']) ?></td>
                    <td><?= htmlspecialchars($tx['description']) ?></td>
                    <td><?= htmlspecialchars($tx['transaction_date']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
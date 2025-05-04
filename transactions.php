<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$db = DB::connect();
$userId = $_SESSION['user_id'];

// Vulnerable to SQL injection through $_GET parameters
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
                <td><?= htmlspecialchars($tx['amount']) ?></td>
                <td><?= htmlspecialchars($tx['description']) ?></td>
                <td><?= htmlspecialchars($tx['transaction_date']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
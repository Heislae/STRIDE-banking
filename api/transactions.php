<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$db = DB::connect();

// No authentication check
if (isset($_GET['user_id'])) {
    // SQL injection vulnerability
    $stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = ?");
    $stmt->execute([$_GET['user_id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($transactions);
} else {
    // Return all transactions - sensitive data exposure
    $transactions = $db->query("SELECT * FROM transactions")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($transactions);
}
?>
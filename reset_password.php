<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$message = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $token = $_POST['token'];
    $password = $_POST['new_password'];
    
    try {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token=? AND reset_expires>?");
        $stmt->execute([hash('sha256', $token), time()]);
        $user = $stmt->fetch();
        
        if ($user) {
            $db->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?")
               ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            $message = "Password updated successfully";
            log_action('PASSWORD_RESET', "User {$user['id']} reset their password");
        } else {
            $message = "Invalid or expired token";
            log_action('RESET_FAILED', "Attempt with token: $token");
        }
    } catch (PDOException $e) {
        $message = "Error processing request";
        log_action('RESET_ERROR', $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        
        <?php if ($message): ?>
            <p class="<?= strpos($message, 'success') !== false ? 'message' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div>
                <label>New Password:</label>
                <input type="password" name="new_password" required>
            </div>
            <button type="submit">Update Password</button>
        </form>
    </div>
</body>
</html>
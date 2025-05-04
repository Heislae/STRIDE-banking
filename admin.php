<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Intentionally weak admin check (vulnerable to bypass)
if (!Auth::isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = DB::connect();

// Handle user edits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    // Intentionally no CSRF protection
    $userId = $_POST['user_id'];
    $newRole = $_POST['role'];
    $newEmail = $_POST['email'];
    
    // Vulnerable SQL (no prepared statement)
    $db->exec("UPDATE users SET role='$newRole', email='$newEmail' WHERE id=$userId");
    
    log_action('USER_UPDATE', "Admin updated user ID $userId");
    $_SESSION['message'] = "User updated successfully!";
    header("Location: admin.php");
    exit;
}

// Handle user deletion (intentionally unsafe)
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    // Vulnerable to SQL injection
    $db->exec("DELETE FROM users WHERE id=$userId");
    log_action('USER_DELETE', "Admin deleted user ID $userId");
    $_SESSION['message'] = "User deleted!";
    header("Location: admin.php");
    exit;
}

// Get all users (vulnerable to SQL injection)
$users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Get all transactions (for admin viewing)
$transactions = $db->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);

// Log admin panel access
log_action('ADMIN_PANEL_ACCESS', "Accessed admin panel");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <div class="admin-actions">
            <a href="dashboard.php" class="button">Back to Dashboard</a>
            <a href="audit_log.php" class="button">View Audit Logs</a>
        </div>
        
        <h2>User Management</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Full Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </td>
                    <td>
                            <select name="role">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                    </td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td>
                            <button type="submit" name="update_user" class="button button-small">Update</button>
                        </form>
                        <a href="admin.php?delete=<?= $user['id'] ?>" class="button button-danger button-small" 
                           onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>All Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><?= htmlspecialchars($tx['id']) ?></td>
                    <td><?= htmlspecialchars($tx['username']) ?></td>
                    <td><?= htmlspecialchars($tx['amount']) ?></td>
                    <td><?= htmlspecialchars($tx['description']) ?></td>
                    <td><?= htmlspecialchars($tx['transaction_date']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
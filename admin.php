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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrust Banking | Admin Panel</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Admin-specific styles */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .admin-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .admin-section {
            margin-bottom: 40px;
        }

        .admin-section h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-section h2 i {
            color: var(--primary-color);
        }

        .admin-table {
            width: 100%;
            margin-top: 20px;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .admin-table th {
            text-align: left;
            padding: 15px;
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-weight: 600;
        }

        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .inline-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .inline-form .form-control {
            padding: 8px 12px;
            font-size: 0.95rem;
        }

        .inline-form select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .btn-action {
            padding: 8px 12px;
            font-size: 0.85rem;
            border-radius: 4px;
        }

        .btn-update {
            background-color: var(--success-color);
            color: white;
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Role Select Dropdown Styles */
        .role-select-container {
            position: relative;
            width: 100%;
        }

        .role-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 100%;
            padding: 8px 30px 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 0.95rem;
            color: var(--dark-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .role-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
            outline: none;
        }

        .role-select-arrow {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--text-light);
            font-size: 0.8rem;
        }

        /* Role-specific styling */
        .role-select option[value="user"] {
            background-color: #f0f0f0;
            color: var(--dark-color);
        }

        .role-select option[value="admin"] {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 500;
        }

        /* Hover states */
        .role-select:hover {
            border-color: #bbb;
        }

        /* Focus state */
        .role-select:focus {
            border-color: var(--primary-color);
        }

        /* Role Color Coding */
        .role-select.admin-role {
            background-color: rgba(0, 86, 179, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-dark);
            font-weight: 500;
        }

        .role-select.user-role {
            background-color: rgba(0, 150, 136, 0.1);
            border-color: #e0e0e0;
            color: var(--dark-color);
        }

        /* Dropdown Options Styling */
        .admin-option {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 500;
        }

        .user-option {
            background-color: #f5f5f5;
            color: var(--dark-color);
        }

        /* Hover States */
        .admin-option:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .user-option:hover {
            background-color: #e0e0e0;
        }

        /* Selected State in Dropdown */
        .role-select option:checked {
            font-weight: bold;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .admin-table {
                display: block;
                overflow-x: auto;
            }

            .inline-form {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 768px) {
            .admin-actions {
                flex-direction: column;
            }

            .admin-actions .btn {
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
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="admin.php" class="active"><i class="fas fa-cog"></i> Admin</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="admin-header">
            <h1><i class="fas fa-user-shield"></i> Admin Panel</h1>
            <div class="user-role">
                Administrator Access
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="admin-actions">
            <a href="dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="audit_log.php" class="btn"><i class="fas fa-clipboard-list"></i> View Audit Logs</a>
        </div>

        <div class="admin-section">
            <h2><i class="fas fa-users-cog"></i> User Management</h2>
            <table class="admin-table">
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
                                    <input type="email" name="email" class="form-control"
                                        value="<?= htmlspecialchars($user['email']) ?>" required>
                            </td>
                            <td>
                                <div class="role-select-container">
                                    <select name="role"
                                        class="role-select <?= $user['role'] === 'admin' ? 'admin-role' : 'user-role' ?>">
                                        <option value="user" class="user-option" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" class="admin-option" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <div class="role-select-arrow">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td>
                                <button type="submit" name="update_user" class="btn-action btn-update">
                                    <i class="fas fa-save"></i> Update
                                </button>
                                </form>
                                <a href="admin.php?delete=<?= $user['id'] ?>" class="btn-action btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-section">
            <h2><i class="fas fa-exchange-alt"></i> All Transactions</h2>
            <table class="admin-table">
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
                            <td style="color: <?= $tx['amount'] > 0 ? 'var(--success-color)' : 'var(--danger-color)' ?>;">
                                <?= $tx['amount'] > 0 ? '+' : '' ?>     <?= number_format($tx['amount'], 2) ?>
                            </td>
                            <td><?= htmlspecialchars($tx['description']) ?></td>
                            <td><?= date('M j, Y g:i a', strtotime($tx['transaction_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> GlobalTrust Banking. All rights reserved. Member FDIC. Equal Housing Lender.
                </p>
            </div>
        </div>
    </footer>
</body>

</html>
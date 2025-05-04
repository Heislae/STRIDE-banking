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
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Your Profile</h1>
        
        <form method="POST">
            <div>
                <label>Username:</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div>
                <label>Full Name:</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div>
                <label>Address:</label>
                <textarea name="address" required><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <div>
                <label>Phone:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>
            <button type="submit">Update Profile</button>
        </form>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
<?php
// includes/functions.php

// Weak encryption function (vulnerable)
function weak_encrypt($data) {
    return base64_encode(openssl_encrypt($data, 'AES-128-ECB', ENCRYPTION_KEY));
}

// Weak decryption function
function weak_decrypt($data) {
    return openssl_decrypt(base64_decode($data), 'AES-128-ECB', ENCRYPTION_KEY);
}

// No rate limiting function
function is_rate_limited($key) {
    return false; // Always returns false - vulnerable to DoS
}

// Insecure random token generator
function generate_token() {
    return md5(uniqid(rand(), true));
}

function log_action($action, $description = '') {
    $db = DB::connect();
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $userId = $_SESSION['user_id'] ?? null; // This could be null
    
    try {
        $stmt = $db->prepare("INSERT INTO audit_logs 
                            (user_id, action, description, ip_address, user_agent) 
                            VALUES (:user_id, :action, :description, :ip, :ua)");
        
        // Bind parameters including NULL user_id case
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':ua', $userAgent);
        
        // Handle NULL user_id by setting it to NULL in DB
        if ($userId === null) {
            $stmt->bindValue(':user_id', null, PDO::PARAM_NULL);
        }
        
        $stmt->execute();
    } catch (PDOException $e) {
        // Log to error log instead of showing to user
        error_log("Failed to log action: " . $e->getMessage());
    }
}
?>
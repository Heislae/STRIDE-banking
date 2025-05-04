<?php
// includes/auth.php

require_once 'db.php';
require_once 'functions.php';

class Auth {
    // Vulnerable login function
    public static function login($username, $password) {
        $db = DB::connect();
        
        // SQL injection vulnerability
        // Can SQLi b/c no prepared statement in the code
        $stmt = $db->query("SELECT * FROM users WHERE username = '$username'");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Session fixation vulnerability
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Weak session token
            // Spoofing (Predictable pattern of Session token. Session token is md5_hashed([username] + [time]))
            $_SESSION['token'] = md5($user['username'] . time());
            
            return true;
        }
        return false;
    }
    
    // Insecure registration
    public static function register($data) {
        $db = DB::connect();
        
        // No input validation
        $stmt = $db->prepare("INSERT INTO users (username, password, email, role, full_name, address, phone) 
                             VALUES (?, ?, ?, 'user', ?, ?, ?)");
        
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $data['username'],
            $password,
            $data['email'],
            $data['full_name'],
            $data['address'],
            $data['phone']
        ]);
    }
    
    // Basic role check (vulnerable to manipulation)
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    // Weak authentication check
    public static function check() {
        return isset($_SESSION['user_id']);
    }
}
?>
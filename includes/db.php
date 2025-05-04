<?php
// includes/db.php

require_once 'config.php';

class DB {
    private static $connection;
    
    public static function connect() {
        if (!self::$connection) {
            try {
                self::$connection = new PDO(
                    "mysql:host=".DB_HOST.";dbname=".DB_NAME, 
                    DB_USER, 
                    DB_PASS
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>
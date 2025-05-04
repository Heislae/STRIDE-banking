<?php
require_once 'includes/config.php';

// Insecure logout - doesn't properly destroy session
session_unset();
session_destroy();

header('Location: login.php');
exit;
?>
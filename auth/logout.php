<?php
// auth/logout.php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Log the logout action if user is logged in
if (isset($_SESSION['user_id'])) {
    log_action($pdo, $_SESSION['user_id'], "User logged out");
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: /auth/login.php");
exit;
?>

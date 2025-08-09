<?php
// includes/functions.php
require_once __DIR__ . '/../config.php';

function check_login() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: /auth/login.php");
        exit;
    }
}

function is_admin() {
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function log_action($pdo, $user_id, $action) {
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = $pdo->prepare("INSERT INTO AuditLog (User_ID, Action, IP_Address) VALUES (:user_id, :action, :ip_address)");
        $stmt->execute([
            'user_id' => $user_id, 
            'action' => $action,
            'ip_address' => $ip_address
        ]);
    } catch (PDOException $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}

function get_user_role_name($pdo, $role_id) {
    try {
        $stmt = $pdo->prepare("SELECT Role_Name FROM Roles WHERE Role_ID = :role_id");
        $stmt->execute(['role_id' => $role_id]);
        $role = $stmt->fetch();
        return $role ? $role['Role_Name'] : 'Unknown';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_csrf_token() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function format_date($date) {
    return date('M d, Y H:i', strtotime($date));
}

function get_user_count($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM Users");
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        return 0;
    }
}

function get_customer_count($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM Customer");
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        return 0;
    }
}

function get_order_count($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM Orders");
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        return 0;
    }
}
?>

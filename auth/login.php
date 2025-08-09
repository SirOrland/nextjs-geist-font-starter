<?php
// auth/login.php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role_id'] == 1) {
        header("Location: /admin/dashboard.php");
    } else {
        header("Location: /customer/dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!validate_email($email)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT u.*, r.Role_Name FROM Users u 
                                  LEFT JOIN Roles r ON u.Role_ID = r.Role_ID 
                                  WHERE u.Email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['Password_Hash'])) {
                // Login successful
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['User_ID'];
                $_SESSION['role_id'] = $user['Role_ID'];
                $_SESSION['user_name'] = $user['User_Name'];
                $_SESSION['email'] = $user['Email'];

                // Log successful login
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                
                $logStmt = $pdo->prepare("INSERT INTO LoginLogs (User_ID, Status, IP_Address, User_Agent) 
                                        VALUES (:user_id, 'Success', :ip_address, :user_agent)");
                $logStmt->execute([
                    'user_id' => $user['User_ID'],
                    'ip_address' => $ip_address,
                    'user_agent' => $user_agent
                ]);

                // Log action in audit log
                log_action($pdo, $user['User_ID'], "User logged in successfully");

                // Redirect based on role
                if ($user['Role_ID'] == 1) {
                    header("Location: /admin/dashboard.php");
                } else {
                    header("Location: /customer/dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid email or password.";
                
                // Log failed login attempt
                if ($user) {
                    $logStmt = $pdo->prepare("INSERT INTO LoginLogs (User_ID, Status, IP_Address, User_Agent) 
                                            VALUES (:user_id, 'Failure', :ip_address, :user_agent)");
                    $logStmt->execute([
                        'user_id' => $user['User_ID'],
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                    ]);
                }
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again.";
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure CRM System</title>
    <link rel="stylesheet" href="/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Secure CRM System</h1>
                <p>Please sign in to your account</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                           placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
            </form>
            
            <div class="login-footer">
                <div class="demo-credentials">
                    <h4>Demo Credentials:</h4>
                    <p><strong>Admin:</strong> admin@securecrm.com / admin123</p>
                    <p><strong>Customer:</strong> john@example.com / customer123</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/main.js"></script>
</body>
</html>

<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure CRM System</title>
    <link rel="stylesheet" href="/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>Secure CRM</h1>
                </div>
                <?php if(isset($_SESSION['user_id'])): ?>
                <nav class="nav">
                    <ul class="nav-list">
                        <?php if($_SESSION['role_id'] == 1): // Admin ?>
                            <li><a href="/admin/dashboard.php" class="nav-link">Dashboard</a></li>
                            <li><a href="/admin/users.php" class="nav-link">Users</a></li>
                            <li><a href="/admin/customers.php" class="nav-link">Customers</a></li>
                            <li><a href="/admin/orders.php" class="nav-link">Orders</a></li>
                            <li><a href="/admin/products.php" class="nav-link">Products</a></li>
                            <li><a href="/admin/auditlogs.php" class="nav-link">Audit Logs</a></li>
                            <li><a href="/admin/reports.php" class="nav-link">Reports</a></li>
                        <?php else: // Customer ?>
                            <li><a href="/customer/dashboard.php" class="nav-link">Dashboard</a></li>
                            <li><a href="/customer/profile.php" class="nav-link">My Profile</a></li>
                            <li><a href="/customer/orders.php" class="nav-link">My Orders</a></li>
                            <li><a href="/customer/service_request.php" class="nav-link">Service Request</a></li>
                            <li><a href="/customer/support.php" class="nav-link">Support</a></li>
                        <?php endif; ?>
                        <li class="user-info">
                            <span class="user-name">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            <a href="/auth/logout.php" class="logout-btn">Logout</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="main-content">

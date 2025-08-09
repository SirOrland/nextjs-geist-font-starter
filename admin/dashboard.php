<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/functions.php';
check_login();
if (!is_admin()) {
    header("Location: /customer/dashboard.php");
    exit;
}

// Get dashboard statistics
require_once __DIR__ . '/../config.php';
$userCount = get_user_count($pdo);
$customerCount = get_customer_count($pdo);
$orderCount = get_order_count($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Secure CRM System</title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <?php include_once __DIR__ . '/../includes/header.php'; ?>
    
    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
            </div>
            
            <div class="card-container">
                <div class="card">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?= $userCount ?></p>
                    <p>Manage all registered users</p>
                    <a href="/admin/users.php">Manage Users</a>
                </div>
                
                <div class="card">
                    <h3>Total Customers</h3>
                    <p class="stat-number"><?= $customerCount ?></p>
                    <p>Manage customer profiles</p>
                    <a href="/admin/customers.php">Manage Customers</a>
                </div>
                
                <div class="card">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?= $orderCount ?></p>
                    <p>View and manage orders</p>
                    <a href="/admin/orders.php">Manage Orders</a>
                </div>
                
                <div class="card">
                    <h3>Products</h3>
                    <p>Manage product catalog</p>
                    <a href="/admin/products.php">Manage Products</a>
                </div>
                
                <div class="card">
                    <h3>Audit Logs</h3>
                    <p>View system audit logs</p>
                    <a href="/admin/auditlogs.php">View Logs</a>
                </div>
                
                <div class="card">
                    <h3>Reports</h3>
                    <p>Generate system reports</p>
                    <a href="/admin/reports.php">View Reports</a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

<?php
// customer/dashboard.php
require_once __DIR__ . '/../includes/functions.php';
check_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Secure CRM System</title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <?php include_once __DIR__ . '/../includes/header.php'; ?>
    
    <main class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Customer Dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
            </div>
            
            <div class="card-container">
                <div class="card">
                    <h3>My Profile</h3>
                    <p>View and update your profile information</p>
                    <a href="/customer/profile.php">View Profile</a>
                </div>
                
                <div class="card">
                    <h3>My Orders</h3>
                    <p>View your order history</p>
                    <a href="/customer/orders.php">View Orders</a>
                </div>
                
                <div class="card">
                    <h3>Service Request</h3>
                    <p>Submit new service requests</p>
                    <a href="/customer/service_request.php">New Request</a>
                </div>
                
                <div class="card">
                    <h3>Support</h3>
                    <p>Contact customer support</p>
                    <a href="/customer/support.php">Contact Support</a>
                </div>
                
                <div class="card">
                    <h3>Privacy Settings</h3>
                    <p>Manage your privacy preferences</p>
                    <a href="/customer/privacy_settings.php">Privacy Settings</a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

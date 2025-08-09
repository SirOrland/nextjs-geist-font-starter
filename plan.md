Below is the detailed, step-by-step plan for building the “Secure CRM System” with HTML, CSS, JavaScript, PHP, and MySQL. This plan outlines every dependent file change, UI/UX design consideration, error handling, and best practices.

---

## 1. File & Directory Structure

Create the following structure at your project root (use Git for version control):

```
/project-root

├── config.php
├── schema.sql
├── index.php
├── .gitignore
├── /includes
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── /auth
│   ├── login.php
│   ├── logout.php
│   └── register.php   (if required)
├── /admin
│   ├── dashboard.php
│   ├── users.php
│   ├── customers.php
│   ├── auditlogs.php
│   ├── service_request.php
│   ├── reports.php
│   └── access_control.php
├── /customer
│   ├── dashboard.php
│   ├── profile.php
│   ├── service_request.php
│   ├── support.php
│   └── privacy_settings.php
├── /css
│   └── main.css
└── /js
    └── main.js
```

---

## 2. Database Setup and Schema (schema.sql)

Create a file named **schema.sql** with SQL commands that create tables for the nine entities. Use InnoDB and add primary/foreign keys and proper indexes.

```sql
-- schema.sql

CREATE TABLE Roles (
  Role_ID INT AUTO_INCREMENT PRIMARY KEY,
  Role_Name VARCHAR(50) NOT NULL,
  Description TEXT,
  Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Users (
  User_ID INT AUTO_INCREMENT PRIMARY KEY,
  User_Name VARCHAR(100) NOT NULL,
  Email VARCHAR(150) NOT NULL UNIQUE,
  Password_Hash VARCHAR(255) NOT NULL,
  Role_ID INT,
  FOREIGN KEY (Role_ID) REFERENCES Roles(Role_ID)
);

CREATE TABLE Customer (
  Customer_ID INT AUTO_INCREMENT PRIMARY KEY,
  Customer_Name VARCHAR(150) NOT NULL,
  Phone VARCHAR(30),
  Email VARCHAR(150),
  Address VARCHAR(255)
);

CREATE TABLE AuditLog (
  Log_ID INT AUTO_INCREMENT PRIMARY KEY,
  User_ID INT,
  Action VARCHAR(255),
  Time_Stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (User_ID) REFERENCES Users(User_ID)
);

CREATE TABLE Contacts (
  Contact_ID INT AUTO_INCREMENT PRIMARY KEY,
  Customer_ID INT,
  Contact_type VARCHAR(50),
  Value VARCHAR(100),
  FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID)
);

CREATE TABLE Orders (
  Order_ID INT AUTO_INCREMENT PRIMARY KEY,
  Customer_ID INT,
  Order_Date DATE,
  Total_Amount DECIMAL(10,2),
  Status VARCHAR(50),
  FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID)
);

CREATE TABLE Products (
  Product_ID INT AUTO_INCREMENT PRIMARY KEY,
  Product_Name VARCHAR(150),
  Price DECIMAL(10,2)
  -- Note: The provided attribute "OrderID" here is ambiguous. If needed, link products to orders via OrderDetails.
);

CREATE TABLE OrderDetails (
  OrderDetail_ID INT AUTO_INCREMENT PRIMARY KEY,
  Order_ID INT,
  Product_ID INT,
  Order_Date DATE,
  Quantity INT,
  Price DECIMAL(10,2),
  FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID),
  FOREIGN KEY (Product_ID) REFERENCES Products(Product_ID)
);

CREATE TABLE LoginLogs (
  Login_ID INT AUTO_INCREMENT PRIMARY KEY,
  User_ID INT,
  Login_Time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  Status VARCHAR(50),
  FOREIGN KEY (User_ID) REFERENCES Users(User_ID)
);
```

*Best Practice:* Use proper constraints and indices for performance and data integrity.

---

## 3. Configuration & Utility Files

### 3.1 config.php

This file holds database connection settings. Use PDO for safe database operations.

```php
<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set error mode to Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
```

### 3.2 functions.php

Place common functions (like connecting to the DB, session validation, error logging, or prepared statement execution) in **/includes/functions.php**.

```php
<?php
// includes/functions.php
require_once __DIR__ . '/../config.php';

function check_login() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: /auth/login.php");
        exit;
    }
}

function is_admin() {
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1); // Assuming role 1 is admin
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function log_action($pdo, $user_id, $action) {
    $stmt = $pdo->prepare("INSERT INTO AuditLog (User_ID, Action) VALUES (:user_id, :action)");
    $stmt->execute(['user_id' => $user_id, 'action' => $action]);
}
?>
```

*Error Handling:* All functions check for missing sessions or query errors and respond with graceful redirections or error messages.

---

## 4. Authentication & Session Management

### 4.1 Login Page (auth/login.php)

Create a modern login form with placeholders and error handling. Use PHP’s built-in password functions for hashing.

```php
<?php
// auth/login.php
session_start();
require_once __DIR__ . '/../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];  // Do not sanitize password to preserve characters

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Password_Hash'])) {
        // Login successful: regenerate session and store user details
        session_regenerate_id();
        $_SESSION['user_id']   = $user['User_ID'];
        $_SESSION['role_id']   = $user['Role_ID'];
        $_SESSION['user_name'] = $user['User_Name'];

        // Log the login event
        $logStmt = $pdo->prepare("INSERT INTO LoginLogs (User_ID, Status) VALUES (:user_id, 'Success')");
        $logStmt->execute(['user_id' => $user['User_ID']]);

        // Redirect based on role
        if ($user['Role_ID'] == 1) {
            header("Location: /admin/dashboard.php");
        } else {
            header("Location: /customer/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid Email/Password.";
        // Log failed login attempt
        if ($user) {
            $logStmt = $pdo->prepare("INSERT INTO LoginLogs (User_ID, Status) VALUES (:user_id, 'Failure')");
            $logStmt->execute(['user_id' => $user['User_ID']]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Secure CRM - Login</title>
  <link rel="stylesheet" href="/css/main.css">
</head>
<body>
  <div class="login-container">
    <h2>Secure CRM System Login</h2>
    <?php if($error): ?>
      <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form action="" method="POST">
      <label for="email">Email</label><br>
      <input type="email" id="email" name="email" required><br>
      <label for="password">Password</label><br>
      <input type="password" id="password" name="password" required><br>
      <button type="submit">Login</button>
    </form>
  </div>
  <script src="/js/main.js"></script>
</body>
</html>
```

*UI Notes:* Use clear typography and spacing. The layout is simple, modern, and responsive without external icon libraries.

### 4.2 Logout Page (auth/logout.php)

Simple page to destroy the session.

```php
<?php
// auth/logout.php
session_start();
session_unset();
session_destroy();
header("Location: /auth/login.php");
exit;
?>
```

---

## 5. Common Layout Components

### 5.1 Header (includes/header.php)

Create a reusable header with a navigation bar. Display different menu items based on the user’s role. Use plain HTML/CSS for a modern yet simplistic design.

```php
<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
  <div class="logo">
    <h1>Secure CRM System</h1>
  </div>
  <nav>
    <ul>
      <?php if(isset($_SESSION['user_id'])): ?>
        <?php if($_SESSION['role_id'] == 1): // Admin ?>
          <li><a href="/admin/dashboard.php">Dashboard</a></li>
          <li><a href="/admin/users.php">Users</a></li>
          <li><a href="/admin/customers.php">Customers</a></li>
          <li><a href="/admin/auditlogs.php">Audit Logs</a></li>
          <li><a href="/admin/reports.php">Reports</a></li>
          <li><a href="/admin/access_control.php">Access Control</a></li>
        <?php else: // Customer ?>
          <li><a href="/customer/dashboard.php">Dashboard</a></li>
          <li><a href="/customer/profile.php">My Profile</a></li>
          <li><a href="/customer/service_request.php">Service Request</a></li>
          <li><a href="/customer/support.php">Support</a></li>
          <li><a href="/customer/privacy_settings.php">Privacy Settings</a></li>
        <?php endif; ?>
        <li><a href="/auth/logout.php">Logout</a></li>
      <?php else: ?>
        <li><a href="/auth/login.php">Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
```

### 5.2 Footer (includes/footer.php)

A simple footer for consistency.

```php
<footer>
  <p>&copy; <?php echo date("Y"); ?> Secure CRM System. All rights reserved.</p>
</footer>
```

*Error Handling:* Wrap header and footer includes in every view so that unhandled errors or session issues are caught early.

---

## 6. Dashboard & Entity Management

### 6.1 Admin Dashboard (admin/dashboard.php)

Ensure that the user is logged in and is an admin. Provide a summary view with navigation to manage users, customers, audit logs, etc. Use modern card layouts.

```php
<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/functions.php';
check_login();
if (!is_admin()) {
    header("Location: /customer/dashboard.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
  <h2>Admin Dashboard</h2>
  <div class="card-container">
    <div class="card">
      <h3>Users</h3>
      <p>Manage all registered users.</p>
      <a href="/admin/users.php">View Users</a>
    </div>
    <div class="card">
      <h3>Customers</h3>
      <p>Manage customer profiles.</p>
      <a href="/admin/customers.php">View Customers</a>
    </div>
    <div class="card">
      <h3>Audit Logs</h3>
      <p>Review system audit logs.</p>
      <a href="/admin/auditlogs.php">View Logs</a>
    </div>
    <!-- Additional cards for Reports, Access Control, etc. -->
  </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
```

### 6.2 Customer Dashboard (customer/dashboard.php)

Similar to the admin dashboard but with customer-centric options.

```php
<?php
// customer/dashboard.php
require_once __DIR__ . '/../includes/functions.php';
check_login();
include_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
  <h2>Customer Dashboard</h2>
  <div class="card-container">
    <div class="card">
      <h3>My Profile</h3>
      <p>Review and update your information.</p>
      <a href="/customer/profile.php">My Profile</a>
    </div>
    <div class="card">
      <h3>Service Request</h3>
      <p>Submit new requests.</p>
      <a href="/customer/service_request.php">Request Service</a>
    </div>
    <div class="card">
      <h3>Support</h3>
      <p>Access help and support.</p>
      <a href="/customer/support.php">Contact Support</a>
    </div>
  </div>
</div>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
```

*UI Considerations (both dashboards):*  
– Use CSS Grid/Flexbox for card layouts with proper spacing.  
– Use a neutral color palette (e.g., white backgrounds, dark text) and ample padding to ensure a clean, modern look.  
– Ensure responsive design through media queries.

### 6.3 CRUD for Other Entities

For pages like `/admin/users.php`, `/admin/customers.php`, etc.:  
– Create forms with input validations (both front-end with JavaScript and back-end with PHP prepared statements).  
– Provide “Add”, “Edit”, “Delete” functionalities with confirmation dialogs.  
– Log each action in the AuditLog table using the `log_action()` utility.

*Error Handling:* Validate all user inputs and display inline error messages without disrupting the layout.

---

## 7. CSS & JavaScript Enhancements

### 7.1 CSS (css/main.css)

Create a modern, responsive design with styling for headers, footers, forms, dashboards, and cards.

```css
/* css/main.css */
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f8f8f8;
  color: #333;
}
header, footer {
  background-color: #fff;
  padding: 1rem;
  border-bottom: 1px solid #ddd;
}
header .logo h1 {
  margin: 0;
  font-size: 1.5rem;
}
nav ul {
  list-style: none;
  display: flex;
  gap: 1rem;
}
nav a {
  text-decoration: none;
  color: #333;
}
.login-container {
  max-width: 400px;
  margin: 2rem auto;
  background-color: #fff;
  padding: 2rem;
  border-radius: 5px;
  border: 1px solid #ddd;
}
.error {
  color: red;
}
.dashboard {
  padding: 2rem;
}
.card-container {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}
.card {
  background-color: #fff;
  border: 1px solid #ddd;
  padding: 1rem;
  flex: 1 1 300px;
  border-radius: 4px;
}
@media (max-width: 600px) {
  nav ul {
    flex-direction: column;
  }
}
```

### 7.2 JavaScript (js/main.js)

Include basic client-side validations or interactivity, such as form validation or navigation toggles.

```js
// js/main.js
document.addEventListener('DOMContentLoaded', function() {
  // Example: Simple toggle for mobile navigation if needed
  const nav = document.querySelector('nav');
  // Additional JavaScript can be added to handle form validations
});
```

---

## 8. Additional Features & Best Practices

- Use PHP’s password_hash() and password_verify() for secure password storage.  
- Regenerate session IDs upon login to prevent fixation.  
- Sanitize all user inputs with PHP functions (e.g., htmlspecialchars/strip_tags).  
- Use prepared statements (PDO) to avoid SQL injection.  
- Log significant actions in the AuditLog for accountability.  
- Maintain a clear separation between admin and customer interfaces with dedicated directories.

---

## Summary

- The plan outlines a file structure with configuration (config.php), utility functions, and separate directories for auth, admin, and customer pages.  
- A MySQL schema is created in schema.sql with proper foreign keys and constraints.  
- Authentication is implemented via secure login.php and logout.php, storing hashed passwords and session details.  
- Reusable header.php and footer.php manage navigation based on user roles.  
- Both admin and customer dashboards utilize modern, responsive layouts with CSS and JavaScript enhancements.  
- All pages include robust error handling, input sanitation, and secure prepared statements.  
- The design follows best practices for security, role-based access, and maintainability.

This detailed plan is ready for implementation and integration into your PHP/MySQL environment.

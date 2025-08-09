-- schema.sql
-- Secure CRM System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS secure_crm_db;
USE secure_crm_db;

-- Create Roles table
CREATE TABLE Roles (
    Role_ID INT AUTO_INCREMENT PRIMARY KEY,
    Role_Name VARCHAR(50) NOT NULL UNIQUE,
    Description TEXT,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Users table
CREATE TABLE Users (
    User_ID INT AUTO_INCREMENT PRIMARY KEY,
    User_Name VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Password_Hash VARCHAR(255) NOT NULL,
    Role_ID INT,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Role_ID) REFERENCES Roles(Role_ID) ON DELETE SET NULL
);

-- Create Customer table
CREATE TABLE Customer (
    Customer_ID INT AUTO_INCREMENT PRIMARY KEY,
    Customer_Name VARCHAR(150) NOT NULL,
    Phone VARCHAR(30),
    Email VARCHAR(150),
    Address TEXT,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create AuditLog table
CREATE TABLE AuditLog (
    Log_ID INT AUTO_INCREMENT PRIMARY KEY,
    User_ID INT,
    Action VARCHAR(255) NOT NULL,
    Time_Stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    IP_Address VARCHAR(45),
    FOREIGN KEY (User_ID) REFERENCES Users(User_ID) ON DELETE SET NULL
);

-- Create Contacts table
CREATE TABLE Contacts (
    Contact_ID INT AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT,
    Contact_type VARCHAR(50),
    Value VARCHAR(100),
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID) ON DELETE CASCADE
);

-- Create Orders table
CREATE TABLE Orders (
    Order_ID INT AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT,
    Order_Date DATE,
    Total_Amount DECIMAL(10,2),
    Status VARCHAR(50) DEFAULT 'Pending',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID) ON DELETE CASCADE
);

-- Create Products table
CREATE TABLE Products (
    Product_ID INT AUTO_INCREMENT PRIMARY KEY,
    Product_Name VARCHAR(150) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Description TEXT,
    Stock_Quantity INT DEFAULT 0,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create OrderDetails table
CREATE TABLE OrderDetails (
    OrderDetail_ID INT AUTO_INCREMENT PRIMARY KEY,
    Order_ID INT,
    Product_ID INT,
    Quantity INT NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE CASCADE,
    FOREIGN KEY (Product_ID) REFERENCES Products(Product_ID) ON DELETE CASCADE
);

-- Create LoginLogs table
CREATE TABLE LoginLogs (
    Login_ID INT AUTO_INCREMENT PRIMARY KEY,
    User_ID INT,
    Login_Time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status VARCHAR(50),
    IP_Address VARCHAR(45),
    User_Agent TEXT,
    FOREIGN KEY (User_ID) REFERENCES Users(User_ID) ON DELETE SET NULL
);

-- Insert default roles
INSERT INTO Roles (Role_Name, Description) VALUES 
('Admin', 'System Administrator with full access'),
('Customer', 'Customer with limited access to their own data');

-- Insert default admin user (password: admin123)
INSERT INTO Users (User_Name, Email, Password_Hash, Role_ID) VALUES 
('Administrator', 'admin@securecrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample customer user (password: customer123)
INSERT INTO Users (User_Name, Email, Password_Hash, Role_ID) VALUES 
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2);

-- Insert sample customer data
INSERT INTO Customer (Customer_Name, Phone, Email, Address) VALUES 
('John Doe', '+1234567890', 'john@example.com', '123 Main St, City, State 12345'),
('Jane Smith', '+0987654321', 'jane@example.com', '456 Oak Ave, City, State 67890');

-- Insert sample products
INSERT INTO Products (Product_Name, Price, Description, Stock_Quantity) VALUES 
('Premium Service Package', 299.99, 'Comprehensive service package with full support', 100),
('Basic Service Package', 99.99, 'Basic service package for small businesses', 200),
('Enterprise Solution', 999.99, 'Enterprise-level solution with advanced features', 50);
